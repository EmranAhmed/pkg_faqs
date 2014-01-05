<?php
/**
 * @package     Faqs
 * @subpackage  com_faqs
 *
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @copyright   Copyright (C) 2013 CTIS IT Services. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Faqs Component Question Model.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @since       3.2
 */
class FaqsModelQuestion extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var     string
	 */
	protected $_context = 'com_faqs.question';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   3.2
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('question.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_faqs')) && (!$user->authorise('core.edit', 'com_faqs')))
		{
			$this->setState('filter.state', 1);
			$this->setState('filter.archived', 2);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	/**
	 * Method to get question data.
	 *
	 * @param   integer  $pk  The id of the question.
	 *
	 * @since   3.2
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('question.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				// Initialiase variables.
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select(
						$this->getState(
							'item.select', 'a.id, a.asset_id, a.question, a.alias, a.answer AS text'
							// If badcats is not null, this means that the question is inside an unpublished category.
							// In this case, the state is set to 0 to indicate Unpublished (even if the question state is Published).
							. ', CASE WHEN badcats.id is null THEN a.state ELSE 0 END AS state'
							. ', a.catid, a.created, a.created_by, a.created_by_alias'
							// Use created if modified is 0.
							. ', CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified'
							. ', a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down'
							. ', a.params, a.version, a.ordering'
							. ', a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language, a.xreference'
						)
					);
				$query->from('#__faqs AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
					->join('LEFT', '#__categories AS c on c.id = a.catid');

				// Join on user table.
				$query->select('u.name AS author')
					->join('LEFT', '#__users AS u on u.id = a.created_by');

				// Get contact id.
				$subQuery = $db->getQuery(true)
					->select('MAX(contact.id) AS id')
					->from('#__contact_details AS contact')
					->where('contact.published = 1')
					->where('contact.user_id = a.created_by');

				// Filter by language.
				if ($this->getState('filter.language'))
				{
					$subQuery->where('(contact.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR contact.language IS NULL)');
				}

				$query->select('(' . $subQuery . ') as contactid');

				// Filter by language.
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				// Join over the categories to get parent category titles.
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
					->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				$query->where('a.id = ' . (int) $pk);

				// Filter by start and end dates.
				$nullDate = $db->quote($db->getNullDate());
				$date = JFactory::getDate();

				$nowDate = $db->quote($date->toSql());

				$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
					->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

				// Join to check for category published state in parent categories up the tree.
				// If all categories are published, badcats.id will be null, and we just use the question state.
				$subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
				$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
				$subquery .= 'WHERE parent.extension = ' . $db->quote('com_faqs');
				$subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
				$query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');

				// Filter by published state.
				$published = $this->getState('filter.state');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					return JError::raiseError(404, JText::_('COM_FAQS_ERROR_QUESTION_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_FAQS_ERROR_QUESTION_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($data->params);

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadString($data->metadata);
				$data->metadata = $registry;

				// Compute selected asset permissions.
				$user = JFactory::getUser();

				// Technically guest could edit an question, but lets not check that to improve performance a little.
				if (!$user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_faqs.question.' . $data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}

					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the question.
	 *
	 * @param   integer  $pk  Optional primary key of the question to increment.
	 *
	 * @since   3.2
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		// Get the input.
		$input = JFactory::getApplication()->input;

		// Initialiase variables.
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('question.id');

			$table = JTable::getInstance('Question', 'FaqsTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}

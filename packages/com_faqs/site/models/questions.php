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
 * This models supports retrieving lists of questions.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @since       3.2
 */
class FaqsModelQuestions extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'question', 'a.question',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		// Get the application.
		$app = JFactory::getApplication();

		// List state information.
		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$orderCol = $app->input->get('filter_order', 'a.ordering');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);

		// Get the current user object.
		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_faqs')) && (!$user->authorise('core.edit', 'com_faqs')))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		// Process show_noauth parameter.
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.2
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.state'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('filter.question_id');
		$id .= ':' . $this->getState('filter.question_id.include');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.author_alias'));
		$id .= ':' . $this->getState('filter.author_alias.include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of questions subject to the model state.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.2
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.question, a.alias, a.answer'
				. ', a.checked_out, a.checked_out_time'
				. ', a.catid, a.created, a.created_by, a.created_by_alias'
				// Use created if modified is 0.
				. ', CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified'
				. ', a.modified_by, uam.name as modified_by_name'
				// Use created if publish_up is 0.
				. ', CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up'
				. ', a.publish_down, a.params, a.metadata, a.metakey, a.metadesc, a.access'
				. ', a.hits, a.xreference, a.featured'
			)
		);

		// Process an Archived Question layout.
		if ($this->getState('filter.state') == 2)
		{
			// If badcats is not null, this means that the question is inside an archived category.
			// In this case, the state is set to 2 to indicate Archived (even if the question state is Published).
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
		}
		else
		{
			/*
			Process non-archived layout.
			If badcats is not null, this means that the question is inside an unpublished category.
			In this case, the state is set to 0 to indicate Unpublished (even if the question state is Published).
			*/
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
		}

		$query->from('#__faqs AS a');

		// Join over the frontpage questions.
		if ($this->context != 'com_faqs.featured')
		{
			$query->join('LEFT', '#__faqs_frontpage AS fp ON fp.question_id = a.id');
		}

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
			->select("ua.email AS author_email")
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

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

		// Join over the categories to get parent category titles.
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
			->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		// Join to check for category published state in parent categories up the tree.
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_faqs');

		if ($this->getState('filter.state') == 2)
		{
			// Find any up-path categories that are archived.
			// If any up-path categories are archived, include all children in archived layout.
			$subquery .= ' AND parent.published = 2 GROUP BY cat.id ';

			// Set effective state to archived if up-path category is archived.
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
		}
		else
		{
			// Find any up-path categories that are not published.
			// If all categories are published, badcats.id will be null, and we just use the question state.
			$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';

			// Select state to unpublished if up-path category is unpublished.
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
		}

		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$user = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')
				->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state.
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			// Use question state if badcats.id is null, otherwise, force 0 for unpublished.
			$query->where($publishedWhere . ' = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);

			$published = implode(',', $published);

			// Use question state if badcats.id is null, otherwise, force 0 for unpublished.
			$query->where($publishedWhere . ' IN (' . $published . ')');
		}

		// Filter by featured state.
		$featured = $this->getState('filter.featured');

		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;

			case 'only':
				$query->where('a.featured = 1');
				break;

			case 'show':
			default:
				// Normally we do not discriminate
				// between featured/unfeatured items.
				break;
		}

		// Filter by a single or group of questions.
		$questionId = $this->getState('filter.question_id');

		if (is_numeric($questionId))
		{
			$type = $this->getState('filter.question_id.include', true) ? '= ' : '<> ';
			$query->where('a.id ' . $type . (int) $questionId);
		}
		elseif (is_array($questionId))
		{
			JArrayHelper::toInteger($questionId);

			$questionId = implode(',', $questionId);
			$type = $this->getState('filter.question_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id ' . $type . ' (' . $questionId . ')');
		}

		// Filter by a single or group of categories.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check.
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid ' . $type . (int) $categoryId;

			if ($includeSubcategories)
			{
				$levels = (int) $this->getState('filter.max_category_levels', '1');

				// Create a subquery for the subcategory list.
				$subQuery = $db->getQuery(true)
					->select('sub.id')
					->from('#__categories as sub')
					->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id = ' . (int) $categoryId);

				if ($levels >= 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}

				// Add the subquery to the main query.
				$query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');
			}
			else
			{
				$query->where($categoryEquals);
			}
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0))
		{
			JArrayHelper::toInteger($categoryId);

			$categoryId = implode(',', $categoryId);

			if (!empty($categoryId))
			{
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid ' . $type . ' (' . $categoryId . ')');
			}
		}

		// Filter by author.
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by ' . $type . (int) $authorId;
		}
		elseif (is_array($authorId))
		{
			JArrayHelper::toInteger($authorId);

			$authorId = implode(',', $authorId);

			if ($authorId)
			{
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by ' . $type . ' (' . $authorId . ')';
			}
		}

		// Filter by author alias.
		$authorAlias = $this->getState('filter.author_alias');
		$authorAliasWhere = '';

		if (is_string($authorAlias))
		{
			$type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias ' . $type . $db->quote($authorAlias);
		}
		elseif (is_array($authorAlias))
		{
			$first = current($authorAlias);

			if (!empty($first))
			{
				JArrayHelper::toString($authorAlias);

				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->quote($alias);
				}

				$authorAlias = implode(',', $authorAlias);

				if ($authorAlias)
				{
					$type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias ' . $type . ' (' . $authorAlias . ')';
				}
			}
		}

		if (!empty($authorWhere) && !empty($authorAliasWhere))
		{
			$query->where('(' . $authorWhere . ' OR ' . $authorAliasWhere . ')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere))
		{
			// If both are empty we don't want to add to the query.
		}
		else
		{
			// One of these is empty, the other is not so we just add both.
			$query->where($authorWhere . $authorAliasWhere);
		}

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by Date Range or Relative Date.
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField     = $this->getState('filter.date_field', 'a.created');

		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->quote($this->getState('filter.end_date_range', $nullDate));
				$query->where('(' . $dateField . ' >= ' . $startDateRange . ' AND ' . $dateField . ' <= ' . $endDateRange . ')');
				break;

			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where($dateField . ' >= DATE_SUB(' . $nowDate . ', INTERVAL ' . $relativeDate . ' DAY)');
				break;

			case 'off':
			default:
				break;
		}

		// Process the filter for list views with user-entered filters.
		$params = $this->getState('params');

		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $this->getState('list.filter')))
		{
			// Clean filter variable.
			$filter     = JString::strtolower($filter);
			$hitsFilter = (int) $filter;
			$filter     = $db->quote('%' . $db->escape($filter, true) . '%', false);

			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where(
						'LOWER( CASE WHEN a.created_by_alias > ' . $db->quote(' ')
						. ' THEN a.created_by_alias ELSE ua.name END ) LIKE ' . $filter . ' '
					);
					break;

				case 'hits':
					$query->where('a.hits >= ' . $hitsFilter . ' ');
					break;

				case 'question':
				default:
					// Default to 'question' if parameter is not valid.
					$query->where('LOWER( a.question ) LIKE ' . $filter);
					break;
			}
		}

		// Filter by language.
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
	}

	/**
	 * Method to get a list of questions.
	 *
	 * Overriden to inject convert the params field into a JParameter object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getItems()
	{
		// Initialiase variables.
		$items  = parent::getItems();
		$user   = JFactory::getUser();
		$userId = $user->get('id');
		$guest  = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input  = JFactory::getApplication()->input;

		// Get the global params.
		$globalParams = JComponentHelper::getParams('com_faqs', true);

		// Convert the parameter fields into objects.
		foreach ($items as &$item)
		{
			$questionParams = new JRegistry;
			$questionParams->loadString($item->params);

			// Unpack readmore and layout params.
			$item->alternative_readmore = $questionParams->get('alternative_readmore');
			$item->layout = $questionParams->get('layout');
			$item->params = clone $this->getState('params');

			/*
			For blogs, question params override menu item params only if menu param = 'use_question'.
			Otherwise, menu item params control the layout.
			If menu item is 'use_question' and there is no question param, use global.
			*/
			if (($input->getString('layout') == 'blog') || ($input->getString('view') == 'featured')
				|| ($this->getState('params')->get('layout_type') == 'blog'))
			{
				// Create an array of just the params set to 'use_question'.
				$menuParamsArray = $this->getState('params')->toArray();
				$questionArray = array();

				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_question')
					{
						// If the question has a value, use it.
						if ($questionParams->get($key) != '')
						{
							// Get the value from the question.
							$questionArray[$key] = $questionParams->get($key);
						}
						else
						{
							// Otherwise, use the global value.
							$questionArray[$key] = $globalParams->get($key);
						}
					}
				}

				// Merge the selected question params.
				if (count($questionArray) > 0)
				{
					$questionParams = new JRegistry;
					$questionParams->loadArray($questionArray);
					$item->params->merge($questionParams);
				}
			}
			else
			{
				// For non-blog layouts, merge all of the question params.
				$item->params->merge($questionParams);
			}

			// Get display date.
			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			// Compute the asset access permissions.
			// Technically guest could edit an question, but lets not check that to improve performance a little.
			if (!$guest)
			{
				$asset = 'com_faqs.question.' . $item->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}

				// Now check if edit.own is available.
				elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by)
					{
						$item->params->set('access-edit', true);
					}
				}
			}

			$access = $this->getState('filter.access');

			if ($access)
			{
				// If the access filter has been set, we already have only the questions this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null)
				{
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else
				{
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}

			// Get the tags.
			$item->tags = new JHelperTags;
			$item->tags->getItemTags('com_faqs.question', $item->id);

			// Add router helpers.
			$item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			$item->link        = JRoute::_(FaqsHelperRoute::getQuestionRoute($item->slug, $item->catslug));
		}

		return $items;
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   3.2
	 */
	public function getStart()
	{
		return $this->getState('list.start');
	}
}

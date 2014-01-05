<?php
/**
 * @package     Faqs
 * @subpackage  com_faqs
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

// Load the helper class.
JLoader::register('FaqsHelper', JPATH_ADMINISTRATOR . '/components/com_faqs/helpers/faqs.php');

/**
 * Item Model for an Question.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class FaqsModelQuestion extends JModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $text_prefix = 'COM_FAQS_QUESTION';

	/**
	 * The type alias for this content type (for example, 'com_faqs.question').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_faqs.question';

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $value     The new category.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since   3.2
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		// Initialiase variables.
		$categoryId = (int) $value;
		$i          = 0;

		// Check that the category exists.
		if (!parent::checkCategoryId($categoryId))
		{
			return false;
		}

		// Parent exists so we let's proceed.
		while (!empty($pks))
		{
			// Pop the first ID off the stack.
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists.
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error.
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error.
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));

					continue;
				}
			}

			// Alter the question & alias.
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->question);
			$this->table->question = $data['0'];
			$this->table->alias = $data['1'];

			// Reset the ID because we are making a copy.
			$this->table->id = 0;

			// New category ID.
			$this->table->catid = $categoryId;

			// Get the featured state.
			$featured = $this->table->featured;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Get the new item ID.
			$newId = $this->table->get('id');

			// Add the new ID to the array.
			$newIds[$i] = $newId;
			$i++;

			// Check if the question was featured and update the #__faqs_frontpage table.
			if ($featured == 1)
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__faqs_frontpage'))
					->values($newId . ', 0');
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Clean the cache.
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   3.2
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return;
			}

			// Get the current user object.
			$user = JFactory::getUser();

			return $user->authorise('core.delete', 'com_faqs.question.' . (int) $record->id);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   3.2
	 */
	protected function canEditState($record)
	{
		// Get the current user object.
		$user = JFactory::getUser();

		// Check for existing question.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_faqs.question.' . (int) $record->id);
		}
		// New question, so check against the category.
		elseif (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_faqs.category.' . (int) $record->catid);
		}
		// Default to component settings if neither question nor category known.
		else
		{
			return parent::canEditState('com_faqs');
		}
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function prepareTable($table)
	{
		// Set the publish date to now.
		$db = $this->getDbo();

		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Increment the questions version number.
		$table->version++;

		// Reorder the questions within the category so the new question is first.
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate.
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object.
	 *
	 * @since   3.2
	 */
	public function getTable($type = 'Question', $prefix = 'FaqsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_faqs.question');
			}
		}

		// Load associated questions items.
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$item->associations = array();

			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations('com_faqs', '#__faqsquestions', 'com_faqs.item', $item->id);

				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_faqs.question', 'question', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses f_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('f_id'))
		{
			$id = $jinput->get('f_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}

		// Determine correct permissions to check.
		if ($this->getState('question.id'))
		{
			$id = $this->getState('question.id');

			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');

			// Existing record. Can only edit own questions in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Get the current user object.
		$user = JFactory::getUser();

		// Check for existing question.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_faqs.question.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_faqs')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('featured', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an question you can edit.
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('featured', 'filter', 'unset');
		}

		// Prevent messing with question language and category when editing existing question with associations.
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($app->isSite() && $assoc && $this->getState('question.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('catid', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
			$form->setFieldAttribute('catid', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   3.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = JFactory::getApplication();
		$data = $app->getUserState('com_faqs.edit.question.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('question.id') == 0)
			{
				$data->set('catid', $app->input->getInt('catid', $app->getUserState('com_faqs.questions.filter.category_id')));
			}
		}

		$this->preprocessData('com_faqs.question', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function save($data)
	{
		// Get the application.
		$app = JFactory::getApplication();

		// Alter the question for save as copy.
		if ($app->input->get('task') == 'save2copy')
		{
			list($question, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['question']);

			$data['question'] = $question;
			$data['alias'] = $alias;
			$data['state'] = 0;
		}

		if (parent::save($data))
		{
			if (isset($data['featured']))
			{
				$this->featured($this->getState($this->getName() . '.id'), $data['featured']);
			}

			$assoc = JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$id   = (int) $this->getState($this->getName() . '.id');
				$item = $this->getItem($id);

				// Adding self to the association.
				$associations = $data['associations'];

				foreach ($associations as $tag => $id)
				{
					if (empty($id))
					{
						unset($associations[$tag]);
					}
				}

				// Detecting all item menus.
				$all_language = $item->language == '*';

				if ($all_language && !empty($associations))
				{
					JError::raiseNotice(403, JText::_('COM_FAQS_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}

				$associations[$item->language] = $item->id;

				// Deleting old association for these items.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_faqs.item'))
					->where('id IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();

				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);

					return false;
				}

				if (!$all_language && count($associations))
				{
					// Adding new association for these items.
					$key = md5(json_encode($associations));
					$query->clear()
						->insert('#__associations');

					foreach ($associations as $id)
					{
						$query->values($id . ',' . $db->quote('com_faqs.item') . ',' . $db->quote($key));
					}

					$db->setQuery($query);
					$db->execute();

					if ($error = $db->getErrorMsg())
					{
						$this->setError($error);

						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to toggle the featured setting of questions.
	 *
	 * @param   array    $pks    The ids of the items to toggle.
	 * @param   integer  $value  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;

		JArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_FAQS_NO_ITEM_SELECTED'));

			return false;
		}

		$table = $this->getTable('Featured', 'FaqsTable');

		try
		{
			// Initialiase variables.
			$db = $this->getDbo();

			// Create the base update statement.
			$query = $db->getQuery(true)
				->update($db->quoteName('#__faqs'))
				->set('featured = ' . (int) $value)
				->where('id IN (' . implode(',', $pks) . ')');

			// Set the query and execute the update.
			$db->setQuery($query);
			$db->execute();

			if ((int) $value == 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__faqs_frontpage'))
					->where('question_id IN (' . implode(',', $pks) . ')');

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// First, we find out which of our new featured questions are already featured.
				$query = $db->getQuery(true)
					->select('f.question_id')
					->from('#__faqs_frontpage AS f')
					->where('question_id IN (' . implode(',', $pks) . ')');

				// Set the query and execute the update.
				$db->setQuery($query);

				$old_featured = $db->loadColumn();

				// We diff the arrays to get a list of the questions that are newly featured.
				$new_featured = array_diff($pks, $old_featured);

				// Featuring.
				$tuples = array();

				foreach ($new_featured as $pk)
				{
					$tuples[] = $pk . ', 0';
				}

				if (count($tuples))
				{
					// Initialiase variables.
					$db = $this->getDbo();
					$columns = array('question_id', 'ordering');

					// Create the base insert statement.
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__faqs_frontpage'))
						->columns($db->quoteName($columns))
						->values($tuples);

					// Set the query and execute the insert.
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$table->reorder();

		// Clear the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 * @since   3.2
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import.
	 *
	 * @return  void
	 *
	 * @since   $TM_VERSION
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Association questions items.
		$app   = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');

			// Force to array (perhaps move to $this->loadFormData()).
			$data = (array) $data;

			$addform  = new SimpleXMLElement('<form />');
			$fields   = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_FAQS_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;

			foreach ($languages as $tag => $language)
			{
				if (empty($data['language']) || $tag != $data['language'])
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_article');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}

			if ($add)
			{
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}
}

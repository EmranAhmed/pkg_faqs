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

/**
 * View to edit a question.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class FaqsViewQuestion extends JViewLegacy
{
	/**
	 * The form to use for the view.
	 *
	 * @var     JForm
	 */
	protected $form;

	/**
	 * The item to edit.
	 *
	 * @var     JObject
	 */
	protected $item;

	/**
	 * The model state.
	 *
	 * @var     JObject
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		try
		{
			// Initialiase variables.
			$this->form  = $this->get('Form');
			$this->item  = $this->get('Item');
			$this->state = $this->get('State');
			$this->canDo = QuestionsHelper::getActions($this->state->get('filter.category_id'), 0, 'com_faqs');
		}
		catch (Exception $e)
		{
			JErrorPage::render($e);

			return false;
		}

		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Initialiase variables.
		$user       = JFactory::getUser();
		$userId     = $user->get('id');
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Since we do not track these assets at the item level, use the category id.
		$canDo      = $this->canDo;

		JToolbarHelper::title(JText::_('COM_FAQS_PAGE_' . ($checkedOut ? 'VIEW_QUESTION' : ($isNew ? 'ADD_QUESTION' : 'EDIT_QUESTION'))), 'pencil-2 question-add');

		// Built the actions for new and existing records.
		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_faqs', 'core.create')) > 0))
		{
			JToolbarHelper::apply('question.apply');
			JToolbarHelper::save('question.save');
			JToolbarHelper::save2new('question.save2new');
			JToolbarHelper::cancel('question.cancel');
		}
		else
		{
			// Can not save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolbarHelper::apply('question.apply');
					JToolbarHelper::save('question.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('question.save2new');
					}
				}
			}

			// If checked out, we can still save.
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('question.save2copy');
			}

			if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit'))
			{
				JToolbarHelper::versions('com_faqs.question', $this->item->id);
			}

			JToolbarHelper::cancel('question.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::help('question', $com = true);
	}
}

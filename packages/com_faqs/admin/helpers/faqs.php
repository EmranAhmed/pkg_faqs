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
 * Faqs helper.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class FaqsHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_FAQS_SUBMENU_QUESTIONS'),
			'index.php?option=com_faqs&view=questions',
			$vName == 'questions'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_FAQS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_faqs',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_FAQS_SUBMENU_FEATURED'),
			'index.php?option=com_faqs&view=featured',
			$vName == 'featured'
		);
	}
}

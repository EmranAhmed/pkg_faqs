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

// Register dependent classes.
JLoader::register('FaqsHelper', JPATH_ADMINISTRATOR . '/components/com_faqs/helpers/faqs.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Faqs Component Association Helper.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
abstract class FaqsHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item.
	 *
	 * @param   integer  $id    Id of the item.
	 * @param   string   $view  Name of the view.
	 *
	 * @return  array  Array of associations for the item.
	 *
	 * @since   3.2
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		// Load route helper.
		jimport('helper.route', JPATH_COMPONENT_SITE);

		// Initialiase variables.
		$app  = JFactory::getApplication();
		$view = is_null($view) ? $app->input->get('view') : $view;
		$id   = empty($id) ? $app->input->getInt('id') : $id;

		if ($view == 'question')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_faqs', '#__faqs', 'com_faqs.item', $id);
				$return       = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = FaqsHelperRoute::getQuestionRoute($item->id, $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view == 'category' || $view == 'categories')
		{
			return self::getCategoryAssociations($id, 'com_faqs');
		}

		return array();
	}
}

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
 * Utility class working with question.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
abstract class JHtmlQuestion
{
	/**
	 * Render the list of associated items.
	 *
	 * @param   int  $questionid  The question item id.
	 *
	 * @return  string  The language HTML.
	 */
	public static function association($questionid)
	{
		// Defaults.
		$html = '';

		// Get the associations.
		if ($associations = JLanguageAssociations::getAssociations('com_faqs', '#__faqs', 'com_faqs.item', $questionid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('q.*')
				->select('l.sef as lang_sef')
				->from('#__faqs as q')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id = q.catid')
				->where('q.id IN (' . implode(',', array_values($associations)) . ')')
				->join('LEFT', '#__languages as l ON q.language = l.lang_code')
				->select('l.image')
				->select('l.title as language_title');

			// Set the query and load the result.
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_faqs&task=question.edit&id=' . (int) $item->id);
					$tooltipParts = array(
						JHtml::_('image', 'mod_languages/' . $item->image . '.gif',
							$item->language_title,
							array('title' => $item->language_title),
							true
						),
						$item->title,
						'(' . $item->category_title . ')'
					);

					$item->link = JHtml::_('tooltip', implode(' ', $tooltipParts), null, null, $text, $url, null, 'hasTooltip label label-association label-' . $item->lang_sef);
				}
			}

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links.
	 *
	 * @param   int      $value      The state value.
	 * @param   int      $i          Row number.
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string  HTML code.
	 */
	public static function featured($value = 0, $i = 0, $canChange = true)
	{
		// Load the tooltip bootstrap script.
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action.
		$states = array(
			0 => array('unfeatured', 'questions.featured', 'COM_FAQS_UNFEATURED', 'COM_FAQS_TOGGLE_TO_FEATURE'),
			1 => array('featured', 'questions.unfeatured', 'COM_FAQS_FEATURED', 'COM_FAQS_TOGGLE_TO_UNFEATURE'),
		);
		$state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon   = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><i class="icon-' . $icon . '"></i></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[2]) . '"><i class="icon-' . $icon . '"></i></a>';
		}

		return $html;
	}
}

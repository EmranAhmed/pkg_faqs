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

// Load the tabstate behavior script.
JHtml::_('behavior.tabstate');

// Get the input.
$input = JFactory::getApplication()->input;

// Include dependancies.
require_once JPATH_COMPONENT . '/helpers/route.php';
require_once JPATH_COMPONENT . '/helpers/query.php';

// Execute the task.
$controller = JControllerLegacy::getInstance('Faqs');
$controller->execute($input->get('task'));
$controller->redirect();

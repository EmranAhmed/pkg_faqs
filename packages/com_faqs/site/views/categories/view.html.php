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
 * Questions categories view.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @since       3.2
 */
class FaqsViewCategories extends JViewCategories
{
	/**
	 * Language key for default page heading.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $pageHeading = 'COM_FAQS_DEFAULT_PAGE_TITLE';

	/**
	 * The name of the extension for the category.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $extension = 'com_faqs';
}

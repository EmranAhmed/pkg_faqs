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
 * Faqs Component Category Tree.
 *
 * @static
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class FaqsCategories extends JCategories
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Array of options.
	 *
	 * @since   3.2
	 */
	public function __construct($options = array())
	{
		$options['table']      = '#__faqs';
		$options['extension']  = 'com_faqs';
		$options['countItems'] = 1;

		parent::__construct($options);
	}
}

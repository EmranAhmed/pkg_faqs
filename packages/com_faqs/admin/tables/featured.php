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
 * Featured Table class.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class FaqsTableFeatured extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  Driver A database connector object.
	 *
	 * @since   3.2
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__faqs_frontpage', 'question_id', $db);
	}
}

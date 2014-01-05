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
 * Script file of Faqs Component.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class Com_FaqsInstallerScript
{
	/**
	 * Called after any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
		// Adding content type for questions.
		$table = JTable::getInstance('Contenttype', 'JTable');

		if (!$table->load(array('type_alias' => 'com_faqs.question')))
		{
			// Table column.
			$special = new stdClass;
			$special->dbtable = '#__faqs';
			$special->key     = 'id';
			$special->type    = 'Question';
			$special->prefix  = 'FaqsTable';
			$special->config  = 'array()';

			$common = new stdClass;
			$common->dbtable  = '#__ucm_content';
			$common->key      = 'ucm_id';
			$common->type     = 'Corecontent';
			$common->prefix   = 'JTable';
			$common->config   = 'array()';

			$table_object = new stdClass;
			$table_object->special = $special;
			$table_object->common  = $common;

			// Field mappings column.
			$common = new stdClass;
			$common->core_content_item_id = 'id';
			$common->core_title           = 'question';
			$common->core_state           = 'state';
			$common->core_alias           = 'alias';
			$common->core_created_time    = 'created';
			$common->core_modified_time   = 'modified';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = 'publish_up';
			$common->core_publish_down    = 'publish_down';
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = 'featured';
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = 'images';
			$common->core_urls            = 'null';
			$common->core_version         = 'version';
			$common->core_ordering        = 'ordering';
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = 'catid';
			$common->core_xreference      = 'xreference';
			$common->asset_id             = 'asset_id';

			$field_mappings = new stdClass;
			$field_mappings->common  = $common;
			$field_mappings->special = new stdClass;

			// Content history options column.
			$hideFields = array(
				'asset_id',
				'checked_out',
				'checked_out_time',
				'version'
			);

			$ignoreChanges = array(
				'modified_by',
				'modified',
				'checked_out',
				'checked_out_time',
				'version',
				'hits'
			);

			$convertToInt = array(
				'publish_up',
				'publish_down',
				'featured',
				'ordering'
			);

			$displayLookup = array(
				array(
					'sourceColumn' => 'catid',
					'targetTable' => '#__categories',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'created_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'access',
					'targetTable' => '#__viewlevels',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'modified_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				)
			);

			$content_history_options = new stdClass;
			$content_history_options->formFile      = 'administrator/components/com_faqs/models/forms/question.xml';
			$content_history_options->hideFields    = $hideFields;
			$content_history_options->ignoreChanges = $ignoreChanges;
			$content_history_options->convertToInt  = $convertToInt;
			$content_history_options->displayLookup = $displayLookup;

			$content_types['type_title']              = 'Question';
			$content_types['type_alias']              = 'com_faqs.question';
			$content_types['table']                   = json_encode($table_object);
			$content_types['rules']                   = '';
			$content_types['field_mappings']          = json_encode($field_mappings);
			$content_types['router']                  = 'FaqsHelperRoute::getQuestionRoute';
			$content_types['content_history_options'] = json_encode($content_history_options);

			$table->save($content_types);
		}

		// Adding content type for faqs category.
		$table = JTable::getInstance('Contenttype', 'JTable');

		if (!$table->load(array('type_alias' => 'com_faqs.category')))
		{
			// Table column.
			$special = new stdClass;
			$special->dbtable = '#__categories';
			$special->key     = 'id';
			$special->type    = 'Category';
			$special->prefix  = 'JTable';
			$special->config  = 'array()';

			$common = new stdClass;
			$common->dbtable  = '#__ucm_content';
			$common->key      = 'ucm_id';
			$common->type     = 'Corecontent';
			$common->prefix   = 'JTable';
			$common->config   = 'array()';

			$table_object = new stdClass;
			$table_object->special = $special;
			$table_object->common  = $common;

			// Field mappings column.
			$common = new stdClass;
			$common->core_content_item_id = 'id';
			$common->core_title           = 'title';
			$common->core_state           = 'published';
			$common->core_alias           = 'alias';
			$common->core_created_time    = 'created_time';
			$common->core_modified_time   = 'modified_time';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = null;
			$common->core_publish_down    = null;
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = null;
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = null;
			$common->core_urls            = null;
			$common->core_version         = 'version';
			$common->core_ordering        = null;
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = 'parent_id';
			$common->core_xreference      = null;
			$common->asset_id             = 'asset_id';

			$special = new stdClass;
			$special->parent_id = 'parent_id';
			$special->lft       = 'lft';
			$special->rgt       = 'rgt';
			$special->level     = 'level';
			$special->path      = 'path';
			$special->extension = 'extension';
			$special->note      = 'note';

			$field_mappings = new stdClass;
			$field_mappings->common  = $common;
			$field_mappings->special = $special;

			// Content history options column.
			$hideFields = array(
				'asset_id',
				'checked_out',
				'checked_out_time',
				'version',
				'lft',
				'rgt',
				'level',
				'path',
				'extension'
			);

			$ignoreChanges = array(
				'modified_user_id',
				'modified_time',
				'checked_out',
				'checked_out_time',
				'version',
				'hits',
				'path'
			);

			$convertToInt = array(
				'publish_up',
				'publish_down'
			);

			$displayLookup = array(
				array(
					'sourceColumn' => 'created_user_id',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'access',
					'targetTable' => '#__viewlevels',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'modified_user_id',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'parent_id',
					'targetTable' => '#__categories',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				)
			);

			$content_history_options = new stdClass;
			$content_history_options->formFile      = 'administrator/components/com_categories/models/forms/category.xml';
			$content_history_options->hideFields    = $hideFields;
			$content_history_options->ignoreChanges = $ignoreChanges;
			$content_history_options->convertToInt  = $convertToInt;
			$content_history_options->displayLookup = $displayLookup;

			$content_types['type_title']              = 'Question Category';
			$content_types['type_alias']              = 'com_faqs.category';
			$content_types['table']                   = json_encode($table_object);
			$content_types['rules']                   = '';
			$content_types['field_mappings']          = json_encode($field_mappings);
			$content_types['router']                  = 'FaqsHelperRoute::getCategoryRoute';
			$content_types['content_history_options'] = json_encode($content_history_options);

			$table->save($content_types);
		}
	}

	/**
	 * Called on installation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Set the redirect location.
		$adapter->getParent()->setRedirectURL('index.php?option=com_faqs');
	}

	/**
	 * Called on uninstallation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		echo '<p>' . JText::_('COM_FAQS_UNINSTALL_TEXT') . '</p>';
	}
}

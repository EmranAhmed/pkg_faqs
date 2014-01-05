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
 * This models supports retrieving lists of faq categories.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class FaqsModelCategories extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var     string
	 */
	public $_context = 'com_faqs.categories';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var     string
	 */
	protected $_extension = 'com_faqs';

	/**
	 * The parent object.
	 *
	 * @type    JCategoryNode
	 */
	private $_parent = null;

	/**
	 * List of items.
	 *
	 * @type    array
	 */
	private $_items = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialiase variables.
		$app = JFactory::getApplication();
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.state', 1);
		$this->setState('filter.access', true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.2
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.extension');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * redefine the function an add some properties to make the styling more easy
	 *
	 * @param   boolean  $recursive  True if you want to return children recursively.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getItems($recursive = false)
	{
		if (!count($this->_items))
		{
			// Initialiase variables.
			$app    = JFactory::getApplication();
			$menu   = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry;

			if ($active)
			{
				$params->loadString($active->params);
			}

			$options               = array();
			$options['countItems'] = $params->get('show_cat_num_faqs_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$categories            = JCategories::getInstance('Faqs', $options);
			$this->_parent         = $categories->get($this->getState('filter.parentId', 'root'));

			if (is_object($this->_parent))
			{
				$this->_items = $this->_parent->getChildren($recursive);
			}
			else
			{
				$this->_items = false;
			}
		}

		return $this->_items;
	}

	/**
	 * Get the parent categorie.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 *
	 * @since   3.2
	 */
	public function getParent()
	{
		if (!is_object($this->_parent))
		{
			$this->getItems();
		}

		return $this->_parent;
	}
}

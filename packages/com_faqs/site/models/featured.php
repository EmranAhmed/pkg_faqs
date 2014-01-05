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

// Load dependent classes.
require_once __DIR__ . '/questions.php';

/**
 * Frontpage Component Model.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @since       3.2
 */
class FaqsModelFeatured extends FaqsModelQuestions
{
	/**
	 * Model context string.
	 *
	 * @var     string
	 */
	public $_context = 'com_faqs.frontpage';

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
		parent::populateState($ordering, $direction);

		// Initialise variables.
		$input = JFactory::getApplication()->input;
		$user  = JFactory::getUser();

		// List state information.
		$limitstart = $input->getUInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		$params = $this->state->params;
		$limit = $params->get('num_leading_questions') + $params->get('num_intro_questions') + $params->get('num_links');
		$this->setState('list.limit', $limit);
		$this->setState('list.links', $params->get('num_links'));

		$this->setState('filter.frontpage', true);

		if ((!$user->authorise('core.edit.state', 'com_faqs')) && (!$user->authorise('core.edit', 'com_faqs')))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.state', 1);
		}
		else
		{
			$this->setState('filter.state', array(0, 1, 2));
		}

		// Check for category selection.
		if ($params->get('featured_categories') && implode(',', $params->get('featured_categories')) == true)
		{
			$featuredCategories = $params->get('featured_categories');
			$this->setState('filter.frontpage.categories', $featuredCategories);
		}
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function getItems()
	{
		// Initialiase variables.
		$params = clone $this->getState('params');
		$limit  = $params->get('num_leading_questions') + $params->get('num_intro_questions') + $params->get('num_links');

		if ($limit > 0)
		{
			$this->setState('list.limit', $limit);

			return parent::getItems();
		}

		return array();
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
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= $this->getState('filter.frontpage');

		return parent::getStoreId($id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query.
	 *
	 * @since   3.2
	 */
	protected function getListQuery()
	{
		// Set the blog ordering.
		$params            = $this->state->params;
		$questionOrderby   = $params->get('orderby_sec', 'rdate');
		$questionOrderDate = $params->get('order_date');
		$categoryOrderby   = $params->def('orderby_pri', '');
		$secondary         = FaqsHelperQuery::orderbySecondary($questionOrderby, $questionOrderDate) . ', ';
		$primary           = FaqsHelperQuery::orderbyPrimary($categoryOrderby);

		$orderby = $primary . ' ' . $secondary . ' a.created DESC ';
		$this->setState('list.ordering', $orderby);
		$this->setState('list.direction', '');

		// Create a new query object.
		$query = parent::getListQuery();

		// Filter by frontpage.
		if ($this->getState('filter.frontpage'))
		{
			$query->join('INNER', '#__faqs_frontpage AS fp ON fp.question_id = a.id');
		}

		// Filter by categories.
		$featuredCategories = $this->getState('filter.frontpage.categories');

		if (is_array($featuredCategories) && !in_array('', $featuredCategories))
		{
			$query->where('a.catid IN (' . implode(',', $featuredCategories) . ')');
		}

		return $query;
	}
}

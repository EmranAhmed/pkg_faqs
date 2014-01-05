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
 * Faqs Component Controller.
 *
 * @package     Faqs
 * @subpackage  com_faqs
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class FaqsController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   3.2
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Initialise variables.
		$cachable = true;
		$user     = JFactory::getUser();

		// Set the default view name and format from the Request.
		// Note we are using f_id to avoid collisions with the router and the return page.
		$id       = $this->input->getInt('f_id');
		$vName    = $this->input->get('view', 'search');
		$this->input->set('view', $vName);

		if ($user->get('id') || ($this->input->getMethod() == 'POST' && $vName == 'archive'))
		{
			$cachable = false;
		}

		$safeurlparams = array(
			'catid'            => 'INT',
			'id'               => 'INT',
			'cid'              => 'ARRAY',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'Itemid'           => 'INT',
		);

		// Check for edit form.
		if ($vName == 'faqform' && !$this->checkEditId('com_faqs.edit.faq', $id))
		{
			// Somehow the person just went to the form - we do not allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}

		return parent::display($cachable, $safeurlparams);
	}

	/**
	 * Method to search.
	 *
	 * @since   3.2
	 *
	 * @return  void
	 */
	public function search()
	{
		// Slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');
		$searchword = trim(str_replace($badchars, '', $this->input->getString('searchword', null, 'post')));

		// If searchword enclosed in double quotes, strip quotes and do exact match.
		if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"')
		{
			$post['searchword'] = substr($searchword, 1, -1);
			$this->input->set('searchphrase', 'exact');
		}
		else
		{
			$post['searchword'] = $searchword;
		}

		$post['ordering']     = $this->input->getWord('ordering', null, 'post');
		$post['searchphrase'] = $this->input->getWord('searchphrase', 'all', 'post');
		$post['limit']        = $this->input->getUInt('limit', null, 'post');

		if ($post['limit'] === null)
		{
			unset($post['limit']);
		}

		$categories = $this->input->post->get('categories', null, 'array');

		if ($categories)
		{
			foreach ($categories as $category)
			{
				$post['categories'][] = JFilterInput::getInstance()->clean($category, 'cmd');
			}
		}

		// Set Itemid id for links from menu.
		$app   = JFactory::getApplication();
		$menu  = $app->getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_faqs&view=search');

		if (isset($items[0]))
		{
			$post['Itemid'] = $items[0]->id;
		}
		elseif ($this->input->getInt('Itemid') > 0)
		{
			// Use Itemid from requesting page only if there is no existing menu.
			$post['Itemid'] = $this->input->getInt('Itemid');
		}

		unset($post['task']);
		unset($post['submit']);

		// Get the full current URI.
		$uri = JUri::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_faqs');

		$this->setRedirect(JRoute::_('index.php' . $uri->toString(array('query', 'fragment')), false));
	}
}

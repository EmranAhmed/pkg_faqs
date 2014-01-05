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

// Create a shortcut for params.
$params  = $this->item->params;
$canEdit = $this->item->params->get('access-edit');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the modal behavior script.
JHtml::_('behavior.framework');
?>
<?php if ($this->item->state == 0): ?>
	<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
<?php endif; ?>
<?php if ($params->get('show_title') || $this->item->state == 0 || ($params->get('show_author') && !empty($this->item->author ))): ?>
	<div class="page-header">
		<?php if ($params->get('show_title')): ?>
			<h2>
				<?php if ($params->get('link_titles') && $params->get('access-view')): ?>
					<a href="<?php echo JRoute::_(FaqsHelperRoute::getQuestionRoute($this->item->slug, $this->item->catid)); ?>">
					<?php echo $this->escape($this->item->question); ?></a>
				<?php else: ?>
					<?php echo $this->escape($this->item->question); ?>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ($this->item->state == 0): ?>
			<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>
<?php // Todo Not that elegant would be nice to group the params. ?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date') || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') ); ?>
<?php if ($useDefList): ?>
	<?php // echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
<?php endif; ?>
<?php if (!$params->get('show_intro')): ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<?php echo $this->item->introtext; ?>
<?php if ($useDefList): ?>
	<?php // echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
<?php endif; ?>
<?php if ($params->get('show_readmore')):
	if ($params->get('access-view'))
	{
		$link = JRoute::_(FaqsHelperRoute::getQuestionRoute($this->item->slug, $this->item->catid));
	}
	else
	{
		$menu      = JFactory::getApplication()->getMenu();
		$active    = $menu->getActive();
		$itemId    = $active->id;
		$link1     = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
		$returnURL = JRoute::_(FaqsHelperRoute::getQuestionRoute($this->item->slug, $this->item->catid));
		$link      = new JUri($link1);
		$link->setVar('return', base64_encode($returnURL));
	}
	?>
	<p class="readmore">
		<a class="btn" href="<?php echo $link; ?>"> <span class="icon-chevron-right"></span>
			<?php
			if (!$params->get('access-view'))
			{
				echo JText::_('COM_FAQS_REGISTER_TO_READ_MORE');
			}
			elseif ($readmore = $this->item->alternative_readmore)
			{
				echo $readmore;

				if ($params->get('show_readmore_title', 0) != 0)
				{
					echo JHtml::_('string.truncate', ($this->item->question), $params->get('readmore_limit'));
				}
			}
			elseif ($params->get('show_readmore_title', 0) == 0)
			{
				echo JText::sprintf('COM_FAQS_READ_MORE_TITLE');
			}
			else
			{
				echo JText::_('COM_FAQS_READ_MORE');
				echo JHtml::_('string.truncate', ($this->item->question), $params->get('readmore_limit'));
			}
			?>
		</a>
	</p>
<?php endif;

echo $this->item->event->afterDisplayContent;

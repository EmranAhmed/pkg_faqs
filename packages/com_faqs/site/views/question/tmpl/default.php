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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Create shortcuts to some parameters.
$params  = $this->item->params;
$canEdit = $params->get('access-edit');
$user    = JFactory::getUser();

// Load the tooltip behavior script.
JHtml::_('behavior.caption');
?>
<div class="faqs question-item<?php echo $this->pageclass_sfx; ?>">
	<?php if (!$this->print): ?>
		<?php if ($canEdit || $params->get('show_print_icon', 1) || $params->get('show_email_icon', 1)): ?>
			<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
			<ul class="list-unstyled list-inline actions pull-right">
				<?php if ($params->get('show_print_icon', 1)): ?>
					<li class="print-icon"><?php echo JHtml::_('icon.print_popup', $this->item, $params); ?></li>
				<?php endif; ?>
				<?php if ($params->get('show_email_icon', 1)): ?>
					<li class="email-icon"><?php echo JHtml::_('icon.email', $this->item, $params); ?></li>
				<?php endif; ?>
				<?php if ($canEdit): ?>
					<li class="edit-icon"><?php echo JHtml::_('icon.edit', $this->item, $params); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	<?php else: ?>
		<div class="pull-right">
			<?php echo JHtml::_('icon.print_screen', $this->item, $params); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_page_heading', 1)): ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<h1>
		<?php echo $this->escape($this->item->question); ?>
	</h1>

	<?php echo $this->item->text; ?>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>

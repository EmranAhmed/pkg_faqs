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

// Load the behavior scripts.
JHtml::_('behavior.modal');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'question.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
			<?php echo $this->form->getField('answer')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)): ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_faqs&f_id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" role="form">
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('JEDITOR') ?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_FAQS_PUBLISHING') ?></a></li>
				<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_FAQS_METADATA') ?></a></li>
			</ul>
			<br>
			<div class="tab-content">
				<div class="tab-pane fade in active" id="editor">
					<div class="form-group">
						<?php echo $this->form->getLabel('question'); ?>
						<?php echo $this->form->getInput('question'); ?>
					</div>
					<?php if (is_null($this->item->id)): ?>
						<div class="form-group">
							<?php echo $this->form->getLabel('alias'); ?>
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					<?php endif; ?>
					<?php echo $this->form->getInput('answer'); ?>
				</div>
				<div class="tab-pane" id="publishing">
					<div class="form-group">
						<?php echo $this->form->getLabel('catid'); ?>
						<?php echo $this->form->getInput('catid'); ?>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('tags'); ?>
						<?php echo $this->form->getInput('tags'); ?>
					</div>
					<?php if ($params->get('save_history', 0)): ?>
						<div class="form-group">
							<?php echo $this->form->getLabel('version_note'); ?>
							<?php echo $this->form->getInput('version_note'); ?>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<?php echo $this->form->getLabel('created_by_alias'); ?>
						<?php echo $this->form->getInput('created_by_alias'); ?>
					</div>
					<?php if ($this->item->params->get('access-change')): ?>
						<div class="form-group">
							<?php echo $this->form->getLabel('state'); ?>
							<?php echo $this->form->getInput('state'); ?>
						</div>
						<div class="form-group">
							<?php echo $this->form->getLabel('featured'); ?>
							<?php echo $this->form->getInput('featured'); ?>
						</div>
						<div class="form-group">
							<?php echo $this->form->getLabel('publish_up'); ?>
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
						<div class="form-group">
							<?php echo $this->form->getLabel('publish_down'); ?>
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<?php echo $this->form->getLabel('access'); ?>
						<?php echo $this->form->getInput('access'); ?>
					</div>
					<?php if (is_null($this->item->id)): ?>
						<div class="form-group">
							<?php echo JText::_('COM_FAQS_ORDERING'); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="tab-pane" id="language">
					<div class="form-group">
						<?php echo $this->form->getLabel('language'); ?>
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
				<div class="tab-pane" id="metadata">
					<div class="form-group">
						<?php echo $this->form->getLabel('metadesc'); ?>
						<?php echo $this->form->getInput('metadesc'); ?>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('metakey'); ?>
						<?php echo $this->form->getInput('metakey'); ?>
					</div>
					<div>
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
						<?php if ($this->params->get('enable_category', 0) == 1): ?>
							<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
						<?php endif; ?>
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
			</div>
		</fieldset>
		<br>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('question.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('question.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
			<?php if ($params->get('save_history', 0)): ?>
				<div class="btn-group">
					<?php echo $this->form->getInput('contenthistory'); ?>
				</div>
			<?php endif; ?>
		</div>
	</form>
</div>

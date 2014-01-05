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

// Load the modal behavior script.
JHtml::_('behavior.framework');

// Create some shortcuts.
$params    = &$this->item->params;
$n         = count($this->items);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Check for at least one editable question.
$isEditable = false;

if (!empty($this->items))
{
	foreach ($this->items as $question)
	{
		if ($question->params->get('access-edit'))
		{
			$isEditable = true;
			break;
		}
	}
}
?>
<?php if (empty($this->items)): ?>
	<?php if ($this->params->get('show_no_questions', 1)): ?>
		<p><?php echo JText::_('COM_FAQS_NO_QUESTIONS'); ?></p>
	<?php endif; ?>
<?php else: ?>
	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<?php if ($this->params->get('show_headings') || $this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit')): ?>
			<fieldset class="filters btn-toolbar clearfix">
				<?php if ($this->params->get('show_pagination_limit')): ?>
					<div class="btn-group pull-right">
						<label for="limit" class="element-invisible">
							<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
						</label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				<?php endif; ?>
				<div>
					<input type="hidden" name="filter_order" value="" />
					<input type="hidden" name="filter_order_Dir" value="" />
					<input type="hidden" name="limitstart" value="" />
					<input type="hidden" name="task" value="" />
				</div>
			</fieldset>
		<?php endif; ?>
		<table class="category table table-striped table-bordered table-hover">
			<?php if ($this->params->get('show_headings')): ?>
			<thead>
				<tr>
					<th id="categorylist_header_title">
						<?php echo JHtml::_('grid.sort', 'COM_FAQS_HEADING_QUESTION', 'a.question', $listDirn, $listOrder); ?>
					</th>
					<?php if ($date = $this->params->get('list_show_date')): ?>
						<th id="categorylist_header_date">
							<?php if ($date == "created"): ?>
								<?php echo JHtml::_('grid.sort', 'COM_FAQS_' . $date . '_DATE', 'a.created', $listDirn, $listOrder); ?>
							<?php elseif ($date == "modified"): ?>
								<?php echo JHtml::_('grid.sort', 'COM_FAQS_' . $date . '_DATE', 'a.modified', $listDirn, $listOrder); ?>
							<?php elseif ($date == "published"): ?>
								<?php echo JHtml::_('grid.sort', 'COM_FAQS_' . $date . '_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
							<?php endif; ?>
						</th>
					<?php endif; ?>
					<?php if ($this->params->get('list_show_author')): ?>
						<th id="categorylist_header_author">
							<?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
					<?php if ($this->params->get('list_show_hits')): ?>
						<th id="categorylist_header_hits">
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
					<?php if ($isEditable): ?>
						<th id="categorylist_header_edit"><?php echo JText::_('COM_FAQS_EDIT_ITEM'); ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody>
				<?php foreach ($this->items as $i => $question): ?>
					<?php if ($this->items[$i]->state == 0): ?>
						<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<tr class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>
						<td headers="categorylist_header_title" class="list-title">
							<?php if (in_array($question->access, $this->user->getAuthorisedViewLevels())): ?>
								<a href="<?php echo JRoute::_(FaqsHelperRoute::getQuestionRoute($question->slug, $question->catid)); ?>">
									<?php echo $this->escape($question->question); ?>
								</a>
							<?php else: ?>
								<?php
								echo $this->escape($question->question) . ' : ';

								$menu      = JFactory::getApplication()->getMenu();
								$active    = $menu->getActive();
								$itemId    = $active->id;
								$link      = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
								$returnURL = JRoute::_(FaqsHelperRoute::getQuestionRoute($question->slug));
								$fullURL   = new JUri($link);
								$fullURL->setVar('return', base64_encode($returnURL));
								?>
								<a href="<?php echo $fullURL; ?>" class="register">
									<?php echo JText::_('COM_FAQS_REGISTER_TO_READ_MORE'); ?>
								</a>
							<?php endif; ?>
							<?php if ($question->state == 0): ?>
								<span class="list-published label label-warning">
									<?php echo JText::_('JUNPUBLISHED'); ?>
								</span>
							<?php endif; ?>
						</td>
						<?php if ($this->params->get('list_show_date')): ?>
							<td headers="categorylist_header_date" class="list-date small">
								<?php echo JHtml::_('date', $question->displayDate, $this->escape($this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?>
							</td>
						<?php endif; ?>
						<?php if ($this->params->get('list_show_author', 1)): ?>
							<td headers="categorylist_header_author" class="list-author">
								<?php if (!empty($question->author) || !empty($question->created_by_alias)): ?>
									<?php $author = $question->author; ?>
									<?php $author = ($question->created_by_alias ? $question->created_by_alias : $author); ?>
									<?php if (!empty($question->contactid ) && $this->params->get('link_author') == true): ?>
										<?php echo JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id=' . $question->contactid), $author); ?>
									<?php else: ?>
										<?php echo JText::sprintf('COM_FAQS_WRITTEN_BY', $author); ?>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<?php if ($this->params->get('list_show_hits', 1)): ?>
							<td headers="categorylist_header_hits" class="list-hits">
								<span class="badge badge-info">
									<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $question->hits); ?>
								</span>
							</td>
						<?php endif; ?>
						<?php if ($isEditable): ?>
							<td headers="categorylist_header_edit" class="list-edit wrap">
								<?php if ($question->params->get('access-edit')): ?>
									<?php echo JHtml::_('icon.edit', $question, $params); ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php // Code to add a link to submit an question. ?>
	<?php if ($this->category->getParams()->get('access-create')): ?>
		<?php echo JHtml::_('icon.create', $this->category, $this->category->params); ?>
	<?php endif; ?>
	<?php // Add pagination links. ?>
	<?php if (!empty($this->items)): ?>
		<?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)): ?>
			<div class="pagination">
				<?php if ($this->params->def('show_pagination_results', 1)): ?>
					<p class="counter pull-right">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php endif; ?>
	</form>
<?php endif;

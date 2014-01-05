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

?>
<div class="items-more">
	<ol class="nav nav-tabs nav-stacked">
		<?php foreach ($this->link_items as &$item): ?>
			<li>
				<a href="<?php echo JRoute::_(FaqsHelperRoute::getQuestionRoute($item->slug, $item->catid)); ?>"><?php echo $item->question; ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</div>

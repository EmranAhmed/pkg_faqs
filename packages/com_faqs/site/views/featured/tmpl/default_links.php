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
<ol class="nav nav-tabs nav-stacked">
	<?php foreach ($this->link_items as &$item): ?>
		<li>
			<a href="<?php echo JRoute::_(FaqsHelperRoute::getQuestionRoute($item->slug, $item->catslug)); ?>"><?php echo $item->question; ?></a>
		</li>
	<?php endforeach; ?>
</ol>

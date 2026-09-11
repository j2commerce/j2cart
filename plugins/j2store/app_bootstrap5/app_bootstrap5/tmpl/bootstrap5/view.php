<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;

// product_type has no server-side whitelist when saved, so without this
// check a crafted value could break out of the class attribute (XSS) or,
// via loadTemplate() below, traverse out of the template directory (LFI).
$product_type = preg_match('/^[a-zA-Z0-9_]+$/', $this->product->product_type) ? $this->product->product_type : '';
?>
<div class="j2store-single-product <?php echo $this->escape($product_type); ?> detail bs4 <?php echo $this->escape($this->product->params->get('product_css_class',''));?>">
	<?php if ($this->params->get('item_show_page_heading')) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
		</div>
	<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-single-product-top'); ?>
	<?php if($this->params->get('item_show_back_to',0) && isset($this->back_link) && !empty($this->back_link)):?>
		<div class="j2store-view-back-button">
			<a href="<?php echo $this->escape($this->back_link); ?>" class="j2store-product-back-btn btn btn-small btn-info">
				<i class="fa fa-chevron-left"> </i> <?php echo JText::_('J2STORE_PRODUCT_BACK_TO').' '.$this->escape($this->back_link_title); ?>
			</a>
		</div>
	<?php endif;?>
	<?php if ($product_type) : ?>
	<?php echo $this->loadTemplate($product_type); ?>
	<?php endif; ?>
	<?php echo J2Store::plugin ()->eventWithHtml ( 'AfterProductDisplay', array($this->product,$this) )?>
<?php echo J2Store::modules()->loadposition('j2store-single-product-bottom'); ?>
</div>


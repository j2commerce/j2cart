<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

// product_type has no server-side whitelist when saved, so without this
// check a crafted value could break out of the class attribute (XSS) or,
// via loadTemplate() below, traverse out of the template directory (LFI).
$product_type = preg_match('/^[a-zA-Z0-9_]+$/', $this->product->product_type) ? $this->product->product_type : '';
?>
<div class="j2store-product j2store-product-<?php echo $this->product->j2store_product_id; ?> product-<?php echo $this->product->j2store_product_id; ?> <?php echo $this->escape($product_type); ?> default">
	<?php if(isset($this->sublayout) && !empty($this->sublayout)): ?>
		<?php echo $this->loadTemplate($this->sublayout); ?>
	<?php elseif ($product_type): ?>
		<?php echo $this->loadTemplate($product_type); ?>
	<?php endif;?>
	<?php echo J2Store::plugin ()->eventWithHtml ( 'AfterProductDisplay', array($this->product,$this) )?>
</div>
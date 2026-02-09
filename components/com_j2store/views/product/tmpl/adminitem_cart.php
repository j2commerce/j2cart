<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication();
if(!empty($this->product->addtocart_text)) {
	$cart_text = Text::_($this->product->addtocart_text);
} else {
	$cart_text = Text::_('J2STORE_ADD_TO_CART');
}

$show = J2Store::product ()->validateVariableProduct($this->product);
?>
	<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', [$this->product, J2Store::utilities()->getContext('item_cart')]); ?>
	<?php if($show): ?>
		<div class="cart-action-complete" style="display:none;">
				<p class="text-success">
					<?php echo Text::_('J2STORE_ITEM_ADDED_TO_CART');?>
					<a href="<?php echo $this->product->checkout_link; ?>" class="j2store-checkout-link">
						<?php echo Text::_('J2STORE_CHECKOUT'); ?>
					</a>
				</p>
		</div>


		<div id="add-to-cart-<?php echo $this->product->j2store_product_id; ?>" class="j2store-add-to-cart">
	
		<?php echo J2Store::product()->displayQuantity('com_j2store.product.item', $this->product, $this->params, ['class'=>'input-mini form-control '] ); ?>

			<input type="hidden" id="j2store_product_id_<?php echo $this->product->j2store_product_id;?>" name="product_id" value="<?php echo $this->product->j2store_product_id; ?>" />

				<input
					data-cart-action-always="<?php echo Text::_('J2STORE_ADDING_TO_CART'); ?>"
					data-cart-action-done="<?php echo $cart_text; ?>"
					data-cart-action-timeout="1000"
				   value="<?php echo $cart_text; ?>"
				   type="submit"
				   class="j2store-cart-button <?php echo $this->params->get('addtocart_button_class', 'btn btn-primary');?>"
				   />

	   </div>
	<?php else: ?>
			<input value="<?php echo Text::_('J2STORE_OUT_OF_STOCK'); ?>" type="button" class="j2store_button_no_stock btn btn-warning" />
	<?php endif; ?>
	
	<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', [$this->product, J2Store::utilities()->getContext('item_cart')]); ?>

	<input type="hidden" name="option" value="com_j2store" />
	<input type="hidden" name="view" value="carts" />
	<input type="hidden" name="task" value="addItem" />
	<input type="hidden" name="ajax" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="return" value="<?php echo base64_encode( (string) Uri::getInstance()->toString() ); ?>" />

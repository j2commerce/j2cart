<?php
use Joomla\CMS\Language\Text;
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="j2store-product-general">
	<div class="control-group">
		<?php echo J2Html::label(Text::_('J2STORE_PRODUCT_SKU'), 'sku',['class'=>'control-label']); ?>
		<?php echo J2Html::text($this->form_prefix.'[sku]', $this->item->sku,['class'=>'input-small ']); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(Text::_('J2STORE_PRODUCT_UPC'), 'upc',['class'=>'control-label']); ?>
		<?php echo J2Html::text($this->form_prefix.'[upc]', $this->item->upc,['class'=>'input-small ']); ?>
	</div>

</div>

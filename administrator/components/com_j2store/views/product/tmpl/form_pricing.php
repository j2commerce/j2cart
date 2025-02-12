<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

//pricing options
$variant_pricing_calculator = (isset($this->variant->pricing_calculator))?$this->variant->pricing_calculator :'';
$pricing_calculator = J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[pricing_calculator]')
->value($variant_pricing_calculator)
->setPlaceHolders(J2Store::product()->getPricingCalculators())
->getHtml();
$base_path = rtrim(Uri::root(),'/').'/administrator';
?>

<div class="j2store-product-pricing">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_TAB_PRICE');?></legend>
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_REGULAR_PRICE'), 'price'); ?></div>
                <div class="controls">
	                <?php echo J2Html::price($this->form_prefix.'[price]',(isset($this->variant->price))? $this->variant->price:'', array('class'=>'form-control')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_SET_ADVANCED_PRICING'), 'sale_price'); ?></div>
                <div class="controls">
                    <a data-fancybox class="btn btn-success" data-type="iframe" data-src="<?php echo $base_path."/index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$this->variant->j2store_variant_id."&layout=productpricing&tmpl=component";?>" href="javascript:;">
		                <?php echo Text::_('J2STORE_PRODUCT_SET_PRICES');?>
                    </a>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_PRICING_CALCULATOR'), 'price_calculator'); ?></div>
                <div class="controls"><?php echo str_replace('<select', '<select class="form-select"', $pricing_calculator); ?></div>
            </div>
        </div>
    </fieldset>
</div>

<div class="alert alert-info mt-3 mb-0">
    <h4 class="alert-heading"><?php echo Text::_('J2STORE_QUICK_HELP'); ?></h4>
    <?php echo Text::_('J2STORE_PRODUCT_PRICE_HELP_TEXT'); ?>
</div>

<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;


?>
<div class="j2store-product-general">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_TAB_GENERAL');?></legend>
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_VISIBILITY'), 'visibility'); ?></div>
		        <?php echo J2Html::radioBooleanList($this->form_prefix.'[visibility]', $this->item->visibility ); ?>
            </div>
            
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_SKU'), 'sku'); ?></div>
			    <div class="controls">
				    <?php echo J2Html::text($this->form_prefix.'[sku]',(isset($this->variant->sku))?$this->variant->sku:'',array('class'=>'form-control')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_UPC'), 'upc'); ?></div>
                <div class="controls"><?php echo J2Html::text($this->form_prefix.'[upc]', (isset($this->variant->upc))?$this->variant->upc:'',array('class'=>'form-control')); ?></div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MANUFACTURER'), 'manufacturer'); ?></div>
                <div class="controls"><?php echo str_replace('<select', '<select class="form-select"', $this->manufacturers); ?></div>
            </div>
	        <?php if(J2Store::isPro()): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_VENDOR'), 'vendor'); ?></div>
                    <div class="controls">
				        <?php echo str_replace('<select', '<select class="form-select"', $this->vendors); ?>
                    </div>
                </div>
	        <?php endif;?>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_TAX_PROFILE'), 'tax_profile'); ?></div>
                <div class="controls">
	                <?php echo str_replace('<select', '<select class="form-select"', $this->taxprofiles); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MAIN_TAG'), 'main_tag'); ?></div>
                <div class="controls">
	                <?php echo str_replace('<select', '<select class="form-select"', $this->tag_lists); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_CART_TEXT'), 'addtocart_text'); ?></div>
                <div class="controls">
			        <?php echo J2Html::text($this->form_prefix.'[addtocart_text]', Text::_($this->item->addtocart_text), array('class'=>'form-control')); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_CUSTOM_CSS_CLASS'), 'custom_css_class'); ?></div>
                <div class="controls">
			        <?php echo J2Html::text($this->form_prefix.'[params][product_css_class]', $this->item->params->get('product_css_class',''), array('class'=>'form-control')); ?>
                </div>
            </div>
        </div>
    </fieldset>
</div>

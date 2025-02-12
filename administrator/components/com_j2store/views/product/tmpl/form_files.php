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
use Joomla\CMS\Uri\Uri;

?>

<div class="product-files">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_TAB_FILES');?></legend>
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_SET_PRODUCT_FILES') ,'product_files_option'); ?></div>
                <div class="controls">
	                <?php
	                $base_path = rtrim(Uri::root(),'/').'/administrator';
	                echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setproductfiles&product_id=".$this->item->j2store_product_id."&layout=productfiles&tmpl=component", Text::_( "J2STORE_PRODUCT_SET_FILES" ), array('class'=>'btn btn-success'));?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_FILE_DOWNLOAD_LIMIT') ,'product_files_option'); ?></div>
                <div class="controls">
			        <?php echo J2Html::text($this->form_prefix.'[params][download_limit]', $this->item->params->get('download_limit'), array('class'=>'form-control'));?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_FILE_DOWNLOAD_EXPIRY') ,'product_files_option'); ?></div>
                <div class="controls">
			        <?php echo J2Html::text($this->form_prefix.'[params][download_expiry]', $this->item->params->get('download_expiry') ,array('id'=>'expiry_date','class'=>'form-control'));?>
                </div>
            </div>
        </div>
    </fieldset>
</div>

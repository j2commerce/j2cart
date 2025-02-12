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



$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');

$this->params = J2Store::config();
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();


$style = ".j2-steps{gap:5px;text-align:center;font-size:0.825rem;}.j2-steps .j2-step{position:relative;flex:1;padding-bottom:30px;font-weight:400!important;}.j2-steps .j2-step.active{font-weight:700!important;}.j2-steps .j2-step::before{content:\"\";position:absolute;left:50%;bottom:0;transform:translateX(-50%);z-index:9;width:20px;height:20px;background-color:#132f53;border-radius:50%;border:3px solid #fff;opacity:50%;}.j2-steps .j2-step.active::before{background-color:#132f53;border:3px solid #fff;opacity:100%;}.j2-steps .j2-step::after{content:\"\";position:absolute;right:50%;bottom:8px;width:100%;height:3px;background-color:#fff;}.j2-steps .j2-step.active::after{background-color:#132f53;z-index:8;width:calc(100% - 6px);}.j2-steps .j2-step:first-child:after{display:none;}";
$wa->addInlineStyle($style, [], []);
if ($this->_layout == 'default_shipping') {
	$active = ' active';
} elseif ($this->_layout == 'default_shipping_product') {
	$active2 = ' active';
} else {
	$active = '';
	$active2 = '';
}
?>


<div class="j2-steps d-flex my-4">
    <h4 class="j2-step"><?php echo Text::_('J2STORE_SHIPPING_START_WIZARD');?></h4>
    <h4 class="j2-step<?php echo $active;?>"><?php echo Text::_("J2STORE_SHIPPING_METHOD_VALIDATE"); ?></h4>
    <h4 class="j2-step<?php echo $active2;?>"><?php echo Text::_("J2STORE_SHIPPING_PRODUCT_VALIDATE");?></h4>
</div>


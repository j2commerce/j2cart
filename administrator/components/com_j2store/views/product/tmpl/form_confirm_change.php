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

$app = Factory::getApplication();
$doc = $app->getDocument();
$tpl = $app->getTemplate(true);
$wa  = $doc->getWebAssetManager();

$style = '.j2store-confirm-change {margin-top:100px;-moz-border-radius: 6px;-webkit-border-radius: 6px;border-radius: 6px;border-width: 6px;border:1px solid #000000;}';
$wa->addInlineStyle($style, [], []);

?>

<div class="j2store-modal">
		<div class="j2store-confirm-change" style="display: none;" id="j2storeConfirmChange" >
            <h3><?php echo Text::_('J2STORE_WARNING');?></h3>
            <hr>
            <div class="alert alert-warning">
                <span class="bi bi-exclamation-triangle-fill"></span>
                <?php echo Text::_('J2STORE_PRODUCT_TYPE_CHANGE_WARNING_MSG');?>
            </div>
            <div class="message-footer d-flex justify-content-between">
                <button type="button" id="closeTypeBtn" class="btn btn-primary-outline btn-sm" ><?php echo Text::_('J2STORE_CLOSE');?></button>
                <?php J2Html::text('product_id', $this->item->j2store_product_id ,array('id'=>'product_id'));?>
                <button type="button" id="changeTypeBtn" class="btn btn-primary btn-sm"><?php echo Text::_('J2STORE_CONTINUE');?></button>
            </div>

	</div>
</div>

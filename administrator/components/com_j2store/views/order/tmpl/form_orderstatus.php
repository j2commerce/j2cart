<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.collapse', '[data-bs-toggle="collapse"]');

$order_state_save_link = Route::_('index.php?option=com_j2store&view=orders&task=orderstatesave');
$attr = array('class'=>'form-select form-select-sm');
$this->order_state =J2Html::select()
	->type('genericlist')
	->name('order_state_id')
	->value($this->item->order_state_id)
	->idTag("order_state_id_".$this->item->j2store_order_id)
	->attribs($attr)
	->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
	->hasOne('Orderstatuses')
	->ordering('ordering')
	->setRelations(
		array (
			'fields' => array
			(
				'key'=>'j2store_orderstatus_id',
				'name'=>'orderstatus_name'
			)
		)
	)->getHtml();
?>
<div class="j2-right-top d-flex align-items-center mb-1 justify-content-lg-end">
    <div class="d-flex">
        <div class="status-selector">
            <div class="input-group input-group-sm mb-0">
                <div class="d-flex align-items-center me-lg-2 mb-2 mb-lg-0">
                    <div class="form-check form-switch me-2 me-lg-3 pt-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="notify_customer" id="notify_customer" value="1">
                        <label class="form-check-label cursor-pointer small" for="notify_customer"><?php echo Text::_('J2STORE_NOTIFY_CUSTOMER');?></label>
                    </div>
                    <div class="form-check form-switch me-2 me-lg-3 pt-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="reduce_stock" id="reduce_stock" value="1">
                        <label class="form-check-label cursor-pointer small" for="reduce_stock"><?php echo Text::_('J2STORE_REDUCE_STOCK');?></label>
                    </div>
                    <div class="form-check form-switch me-2 me-lg-3 pt-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="increase_stock" id="increase_stock" value="1">
                        <label class="form-check-label cursor-pointer small" for="increase_stock"><?php echo Text::_('J2STORE_INCREASE_STOCK');?></label>
                    </div>
                </div>
			    <?php echo $this->order_state; ?>
                <button class="btn btn-primary btn-sm" type="submit" onclick="jQuery('#task').attr('value','saveOrderstatus');"><?php echo Text::_('J2STORE_ORDER_STATUS_SAVE'); ?></button>
		    </div>
		</div>
    </div>
</div>

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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$row_class = 'row';
$col_class = 'col-md-';

$image_exists = false;
$imageExtensions = ['jpg', 'png', 'webp'];
$imagePath = '';

foreach ($imageExtensions as $extension) {
	$path = JPATH_SITE . '/media/plg_j2store_'.$this->item->orderpayment_type.'/images/' . $this->item->orderpayment_type . '.' . $extension;
	if (file_exists($path)) {
        $image_exists = true;
		$imagePath = Uri::root(true) . '/media/plg_j2store_' . $this->item->orderpayment_type . '/images/' . $this->item->orderpayment_type . '.' . $extension;
		break;
	}
}
$pay_html = trim(J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'payment'));
?>
<div class="border rounded-3 px-3 py-3 payment-information">
    <div class="d-flex align-items-start min-ht-50">
        <div class="d-lg-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center">
                <?php if($image_exists):?>
                    <img src="<?php echo $imagePath; ?>" class="img-fluid me-2 order-thumb-image" alt="<?php echo Text::_($this->item->orderpayment_type); ?>"/>
		<?php endif; ?>
                <div>
                    <h6 class="mb-0"><?php echo Text::_($this->item->orderpayment_type); ?></h6>
                    <small class="d-block"><?php echo Text::_('J2STORE_ORDER_TRANSACTION_ID'); ?> : <span class="d-inline-block fw-medium text-success fs-6"><?php echo $this->item->transaction_id; ?></span>
				<?php if($pay_html ):?>
                            <?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->item->order_id."&address_type=payment&layout=address&tmpl=component",'',array('class'=>'fa fa-pencil','refresh'=>true,'id'=>'fancybox ms-2','width'=>700,'height'=>600));?>
	                    <?php endif;?>
                    </small>
                </div>
                    </div>
            <div class="mt-4 mt-lg-0">
                <a data-fancybox data-src="#myTransaction" type="button" class="btn btn-outline-primary btn-sm"><?php echo Text::_('J2STORE_VIEW_PRODUCT_DETAILS');?></a>
            </div>
        </div>
    </div>
	<?php if($pay_html ):?>
        <?php echo $pay_html; ?>
    <?php endif;?>
</div>

<!-- Transaction log modal window -->
    <div id="myTransaction" style="display:none;">
        <h3 ><?php echo Text::_('J2STORE_TRANSACTION_LOG_HEADER'); ?><?php echo $this->item->order_id; ?></h3>
        <div class="j2store">
            <div class="<?php echo $row_class ?>">
                <div class="<?php echo $col_class ?>12">
                    <div class="alert alert-info">
                    <?php echo Text::_('J2STORE_TRANSACTION_LOG_HELP_MSG');?>
                    </div>
                    <ul>
                    <li><?php echo Text::_('J2STORE_ORDER_TRANSACTION_STATUS'); ?>
                            <div class="alert alert-warning">
                            <small><?php echo Text::_('J2STORE_ORDER_TRANSACTION_STATUS_HELP_MSG'); ?>
                                </small>
                            </div>
                            <p>
                            <?php echo Text::_($this->item->transaction_status); ?>
                            </p>
                        </li>
                        <li><?php echo Text::_('J2STORE_ORDER_TRANSACTION_DETAILS'); ?> <br>
                            <div class="alert alert-warning">
                            <small><?php echo Text::_('J2STORE_ORDER_TRANSACTION_DETAILS_HELP_MSG'); ?>
                                </small>
                            </div>
                            <p>
                            <?php echo Text::_($this->item->transaction_details); ?>
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

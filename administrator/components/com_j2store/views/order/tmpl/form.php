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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$style = '.input-group-sm>.form-select.form-select-sm{padding-right:1.5rem;background-size: max(100%, 70rem);min-width: 120px;}.cursor-pointer{cursor:pointer;}.status-selector .form-switch .form-check-input{width:32px;min-width:0;}.j2store-order-summary .order-thumb-image{max-height:50px;width:auto;}';
$wa->addInlineStyle($style, [], []);

$this->prefix = 'jform[order]';
$row_class = 'row';
$col_class = 'col-md-';
$btn_small = 'btn-sm';

$labels = ['success', 'info', 'primary', 'warning', 'danger', 'important'];
$foundStatus = null;
foreach ($labels as $label) {
	if (strpos($this->item->orderstatus_cssclass, $label) !== false) {
		if($label == 'important'){
			$foundStatus = 'danger';
		} else {
			$foundStatus = $label;
		}
		break;
	} else {
		$foundStatus = 'secondary';
	}
}
$orders_url = Route::_('index.php?option=com_j2store&view=orders');
$print_order_url = Route::_( "index.php?option=com_j2store&view=orders&task=printOrder&tmpl=component&order_id=".$this->item->order_id);
$print_shipping_url = Route::_( "index.php?option=com_j2store&view=orders&task=printShipping&tmpl=component&order_id=".$this->orderinfo->order_id);
$resend_email_url = Route::_( "index.php?option=com_j2store&view=orders&task=resendEmail&id=".$this->order->j2store_order_id);
?>
<div class="j2store">
	<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<input type="hidden" name="option" value="com_j2store">
		<input type="hidden" name="view" value="order">
		<input type="hidden" id="task" name="task" value="">
		<input type="hidden" id="id" name="id" value="<?php echo $this->item->j2store_order_id; ?>" />
		<input type="hidden" id="j2store_order_id" name="j2store_order_id" value="<?php echo $this->item->j2store_order_id; ?>" />
		<input type="hidden" name="order_id" value="<?php echo $this->item->order_id; ?>" />
		<?php echo HTMLHelper::_( 'form.token' ); ?>

		<div class="<?php echo $row_class ?>">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-1 me-2"><a href="<?php echo $orders_url;?>" title="" class="me-1"><span class="fas fa-solid fa-arrow-left"></span></a></div>
                            <div class="j2-order-bar w-100">
                                <div class="row justify-content-start align-items-center">
                                    <div class="col-md-6 mb-3 mb-lg-0">
                                        <div class="j2-title-top d-flex align-items-center mb-1">
                                            <h2 class="h3 mb-0">#<?php echo $this->item->getInvoiceNumber(); ?></h2>
                                            <div class="ms-2 fw-normal fs-5">(<b><?php echo $this->item->order_id; ?></b>)</div>
                                            <div class="badge rounded-2 px-2 text-bg-<?php echo $foundStatus;?> ms-2"><?php echo Text::_($this->item->orderstatus_name);?></div>
                                        </div>
                                        <div class="j2-title-bottom d-flex align-items-center">
                                            <small><?php echo HTMLHelper::_('date', $this->item->created_on, $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'))); ?></small>
                                            <small class="mx-2">|</small>
                                            <small><?php echo $this->item->get_customer_language(); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
	                                    <?php echo $this->loadTemplate('orderstatus');?>

                                        <div class="j2-right-bottom d-flex align-items-center justify-content-lg-end">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
			</div>
            </div>
		</div>
		<div class="row">
            <div class="col-lg-8 order-2 order-lg-1">
	            <?php //echo $this->loadTemplate('actions');?>
	            <?php echo $this->loadTemplate('summary');?>
	            <?php echo $this->loadTemplate('orderhistory');?>
	            <?php //echo $this->loadAnyTemplate('site:com_j2store/myprofile/ordersummary');?>
            </div>
            <div class="col-lg-4 sticky-lg-top max-content-height z-3 order-1 order-lg-2" style="top: 75px;">
                <div class="order-action-buttons mb-4">
	                <?php echo J2StorePopup::popuplink($print_order_url,'<span class="fas fa-solid fa-print me-2"></span>'.Text::_('J2STORE_PRINT_ORDER'), array('class'=>'btn btn-primary btn-sm mb-2 w-xs-100 w-sm-auto w-lg-100 w-xxl-auto'));?>
	                <?php echo J2StorePopup::popupAdvanced($print_shipping_url,'<span class="fas fa-solid fa-print me-2"></span> '.Text::_('J2STORE_PRINT_SHIPPING_ADDRESS'), array('id'=>'btn btn-primary btn-sm mb-2 w-xs-100 w-sm-auto w-lg-100 w-xxl-auto'));?>
                    <a href="<?php echo $resend_email_url;?>" class="btn btn-primary btn-sm mb-2 w-xs-100 w-sm-auto w-lg-100 w-xxl-auto" ><?php echo '<span class="fas fa-solid fa-envelope me-2"></span> '.Text::_('J2STORE_RESEND_MAIL')?></a>
	                <?php if($this->order->has_downloadable_item()): ?>
                        <button type="button" data-bs-toggle="collapse" data-bs-target="#collapseDownloads" aria-expanded="false" aria-controls="collapseDownloads" class="btn btn-dark btn-sm ms-2 mb-2">
                            <span class="fas fa-solid fa-download me-2" aria-hidden="true"></span><?php echo Text::_('J2STORE_DOWNLOAD').' '.Text::_('J2STORE_ACTIONS');?>
                            <span class="visually-hidden"><?php echo Text::_('J2STORE_DOWNLOAD').' '.Text::_('J2STORE_ACTIONS');?></span>
                        </button>
				<?php endif;?>
	                <?php if($this->order->has_downloadable_item()): ?>
                    <div class="collapse" id="collapseDownloads">
                        <div class="d-flex align-items-center">
                            <div class="form-check form-switch me-2 me-lg-3">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="grant_download_access" id="grant_download_access" value="1">
                                <label class="form-check-label cursor-pointer small" for="grant_download_access"><?php echo Text::_('J2STORE_GRANT_DOWNLOAD_PERMISSION');?></label>
			</div>
                            <div class="form-check form-switch me-2 me-lg-3">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="reset_download_expiry" id="reset_download_expiry" value="1">
                                <label class="form-check-label cursor-pointer small" for="reset_download_expiry"><?php echo Text::_('J2STORE_RESET_DOWNLOAD_EXPIRY');?></label>
		</div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="reset_download_limit" id="reset_download_limit" value="1">
                                <label class="form-check-label cursor-pointer small" for="reset_download_limit"><?php echo Text::_('J2STORE_RESET_DOWNLOAD_LIMIT');?></label>
		</div>
					</div>
				</div>
	                <?php endif;?>
                </div>
                <div class="card mb-4 j2store-customer-notes">
                    <div class="card-header justify-content-between">
                        <h3 class="mb-0"><?php echo Text::_("J2STORE_ORDER_CUSTOMER_NOTE"); ?></h3>
                        <button class="btn <?php echo $btn_small ?> btn-primary btn-sm" type="submit" onclick="jQuery('#task').attr('value','saveOrderCnote');"><?php echo Text::_('J2STORE_ORDER_STATUS_SAVE'); ?></button>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control small" aria-invalid="false" name="customer_note" placeholder="<?php echo Text::_("J2STORE_ORDER_CUSTOMER_NOTE_EMPTY"); ?>"><?php echo $this->item->customer_note; ?></textarea>
					</div>
				</div>
                <div class="j2store-general-order">
		            <?php echo $this->loadTemplate('general');?>
			</div>
			</div>
		</div>
	</form>
</div>

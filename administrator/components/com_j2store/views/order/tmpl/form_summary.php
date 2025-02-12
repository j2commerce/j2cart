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
use Joomla\CMS\Uri\Uri;

$order = $this->order;
$platform = J2Store::platform();
$items = $this->order->getItems();
$currency = J2Store::currency();
$image_path = Uri::root();
?>
<div class="card mb-4 j2store-order-summary">
    <div class="card-header justify-content-between">
        <h3 class="mb-0"><?php echo Text::_('J2STORE_ORDER_SETTINGS').' '.Text::_('J2STORE_ORDER_ITEMS')?></h3>
        <button type="button" class="btn btn-link btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="orderSummary-edit"><span class="fas fa-solid fa-ellipsis-v"></span></button>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orderSummary-edit">
            <a class="dropdown-item" href="<?php echo Route::_('index.php?option=com_j2store&view=orders&task=createOrder&oid='.$this->order->j2store_order_id);?>"><i class="fas fa-solid fa-edit me-2"></i><?php echo Text::_('J2STORE_ORDER_EDIT');?></a>
        </div>
    </div>
    <div class="card-body">

	    <?php foreach ($items as $item):
            $item->params = $platform->getRegistry($item->orderitem_params);
            $thumb_image = $item->params->get('thumb_image', '');
            $back_order_text = $item->params->get('back_order_item', '');
            $thumb_image_raw = $platform->getImagePath($thumb_image);


		    $img = HTMLHelper::_('image', $thumb_image_raw, '', '', true, 1);
		    $path = parse_url($img, PHP_URL_PATH);
	    ?>
            <div class="border mb-4 rounded-3 px-4 py-3 text-subdued">
                <div class="row align-items-lg-start">
                    <?php if($this->params->get('show_thumb_cart', 1)):?>
                        <?php if(file_exists(JPATH_SITE.$path)): ?>
                            <div class="col-lg-1">
                                <div class="admin-cart-thumb-image">
                                    <img src="<?php echo $image_path.$thumb_image; ?>" alt="Image" class="w-auto img-fluid">
                                </div>
                            </div>
	                    <?php endif;?>
                    <?php endif;?>
                    <div class="col-lg-9">
                        <div class="row justify-content-lg-between align-items-lg-start">
                            <div class="col-lg-8">
	                            <?php echo $this->order->get_admin_formatted_lineitem_name($item,'admin');?>

	                            <?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTitleInOrder', array($item, $this->order, $this->params));?>

	                            <?php if(!empty($item->orderitem_sku)): ?>
                                    <div class="small d-flex align-items-center">
                                        <div class="item-option item-option-name"><?php echo Text::_('J2STORE_CART_LINE_ITEM_SKU'); ?>:</div>
                                        <div class="item-option item-option-value fw-bold ms-1"><?php echo $item->orderitem_sku; ?></div>
                                    </div>
	                            <?php endif; ?>
	                            <?php if($back_order_text):?>
                                    <div class="text-start">
                                        <span class="badge rounded-2 px-2 text-bg-warning"><?php echo Text::_($back_order_text);?></span>
                                    </div>
	                            <?php endif;?>
                            </div>
                            <div class="col-lg-4">
	                            <?php if($this->params->get('show_price_field', 1)): ?>
                                <div class="fw-medium fs-6 text-end">
	                                <?php echo $currency->format($this->order->get_formatted_order_lineitem_price($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value);?> <span class="fas fa-solid fa-times small mx-1"></span> <?php echo $item->orderitem_quantity; ?>
                                </div>
	                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="fw-medium fs-5 text-end">
	                        <?php echo $currency->format($this->order->get_formatted_lineitem_total($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value ); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
    </div>
</div>
<?php echo $this->loadTemplate('orderdetails');?>
<div class="card mb-4 j2store-order-summary text-subdued">
    <div class="card-header justify-content-between">
        <h3 class="mb-0"><?php echo Text::_('J2STORE_ORDER_SUMMARY')?></h3>
    </div>
    <div class="card-body">
        <div class="table-responsive fs-5">
            <table class="table" id="j2OrderSummary">
                <caption class="visually-hidden"><?php echo Text::_('J2STORE_ORDER_SUMMARY')?></caption>
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                <?php if($totals = $this->order->get_formatted_order_totals()): ?>
	                <?php foreach($totals as $total): ?>
                        <?php if($total['label'] == 'Total'){ ?>
                            <tr>
                                <th scope="row" colspan="2" class="fs-4"><?php echo $total['label']; ?></th>
                                <td class="text-end fw-bold fs-4"><?php echo $total['value']; ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <th scope="row" colspan="2"><?php echo $total['label']; ?></th>
                                <td class="text-end"><?php echo $total['value']; ?></td>
                            </tr>
                        <?php } ?>
	                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

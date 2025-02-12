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

$orderinfo = $this->orderinfo;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal','a.modal');
$platform->loadExtra('behavior.formvalidator');

$row_class = 'row';
$col_class = 'col-md-';

$customer_link = Route::_('index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$this->item->user_email);
$items = $this->order->getItems();

$currency = J2Store::currency();
$customer_orders = J2Html::getUserOrders($this->item->user_id);
$customer_start_date = J2Html::getCustomerStartDate($customer_orders);
$customer_days_old = J2Html::calculateDaysFromStartDate($customer_start_date);
$customer_sum_orders = 0;
if($this->item->user_id > 0)
    $customer_sum_orders = J2Html::countUserOrders($this->item->user_id);

?>
<div class="card mb-4 j2store-customer-information">
    <div class="card-header justify-content-between">
        <h3 class="mb-0"><?php echo Text::_("J2STORE_CUSTOMER_INFORMATION"); ?></h3>
	    <?php if($this->item->user_id == 0): ?>
            <span class="small">
                <span class="fas fa-solid fa-user-slash text-warning me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('J2STORE_GUEST')?>"></span>
                <span class="muted">(<?php echo Text::_('J2STORE_UNIQUE_TOKEN'); ?>: <?php echo $this->item->token;?>)</span>
            </span>
	    <?php endif;?>
    </div>
    <div class="card-body">
        <div class="customer-information">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <small class="d-block mb-1"><a href="<?php echo $customer_link;?>" target="_blank"><span class="fas fa-solid fa-user me-2 fa-fw small"></span><?php echo $this->orderinfo->billing_first_name.' '.$this->orderinfo->billing_last_name; ?></a></small>
                    <small class="d-block mb-1"><a href="mailto:<?php echo $this->item->user_email;?>" target="_blank"><span class="fas fa-solid fa-envelope me-2 fa-fw small"></span><?php echo $this->item->user_email;?></a></small>
	                <?php if($this->orderinfo->billing_phone_1):?>
                        <small class="d-block mb-1"><a href="tel:<?php echo $this->orderinfo->billing_phone_1;?>" class="" title="Call <?php echo $this->orderinfo->billing_phone_1; ?>"><span class="fas fa-solid fa-phone me-2 fa-fw small"></span><?php echo $this->orderinfo->billing_phone_1; ?></a></small>
	                <?php endif;?>

	                <?php if($this->orderinfo->billing_phone_2):?>
                        <small class="d-block"><a href="tel:<?php echo $this->orderinfo->billing_phone_2;?>" class="" title="Call <?php echo $this->orderinfo->billing_phone_2; ?>"><span class="fas fa-solid fa-phone me-2 fa-fw small"></span><?php echo $this->orderinfo->billing_phone_2; ?></a></small>
	                <?php endif;?>
                </div>
                <div class="col-lg-6">
                    <small class="d-block mb-1"><?php echo Text::sprintf('J2STORE_CUSTOMER_FOR_DAYS',$customer_days_old); ?></small>
                    <small class="d-block mb-1"><?php echo Text::_('J2STORE_CUSTOMER_TOTAL_SALES'); ?><b class="ms-2"><a href="<?php echo $customer_link;?>" target="_blank"><?php echo $customer_sum_orders;?></a></b></small>
                </div>
            </div>
        </div>
        <div class="border mb-4 rounded-3 px-4 py-3">
            <div class="d-flex align-items-start">
                <div class="d-lg-flex align-items-center justify-content-between w-100">
                    <div class="d-flex">
                        <span class="fas fa-solid fa-map-marker-alt"></span>
                        <div class="ms-4">
                            <h5 class="mb-0"><?php echo Text::_('J2STORE_BILLING_ADDRESS');?><?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->item->order_id."&address_type=billing&layout=address&tmpl=component",'',array('class'=>'fas fa-solid fa-edit','refresh'=>true,'id'=>'fancybox ms-2'));?></h5>
                            <small><?php echo $this->orderinfo->billing_first_name.' '.$this->orderinfo->billing_last_name; ?></small>
                        </div>
                    </div>
                    <div class="mt-4 mt-lg-0">
                        <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#collapseBillingAddress" aria-expanded="false" aria-controls="collapseBillingAddress">
                            <?php echo Text::_('J2STORE_VIEW_MORE');?>
                        </a>
                    </div>
                </div>
            </div>
            <div id="collapseBillingAddress" class="collapse">
                <div class="pt-4">
                    <div class="row">
                        <div class="col-12 mb-3 mb-lg-0">
                            <div class="address" itemscope itemtype="https://schema.org/PostalAddress">
                                <p class="small">
			                        <?php if($this->orderinfo->billing_company):?>
                                        <span itemprop="name"><?php echo $this->orderinfo->billing_company;?></span><br>
			                        <?php endif;?>
			                        <?php if($this->orderinfo->billing_address_1):?>
                                        <span itemprop="streetAddress"><?php echo $this->orderinfo->billing_address_1;?></span><br>
			                        <?php endif;?>
			                        <?php if($this->orderinfo->billing_address_2):?>
                                        <span itemprop="streetAddress"><?php echo $this->orderinfo->billing_address_2;?></span><br>
			                        <?php endif;?>
			                        <?php if($this->orderinfo->billing_city):?>
                                        <span itemprop="addressLocality"><?php echo $this->orderinfo->billing_city;?></span>,
			                        <?php endif;?>
			                        <?php if($this->orderinfo->billing_zone_name):?>
                                        <span itemprop="addressRegion"><?php echo $this->orderinfo->billing_zone_name;?></span>
			                        <?php endif;?>
			                        <?php if($this->orderinfo->billing_zip):?>
                                        <span itemprop="postalCode"><?php echo $this->orderinfo->billing_zip;?></span><br>
			                        <?php endif;?>
                                </p>
                            </div>
	                    </div>
                        <div class="col-12">
                            <div>
                                <p class="small">
	                                <?php if($this->orderinfo->billing_tax_number):?>
                                        <span><?php echo $this->orderinfo->billing_tax_number;?></span>
	                                <?php endif;?>

	                                <?php echo J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'billing'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border mb-4 rounded-3 px-4 py-3">
            <div class="d-flex align-items-start">
                <div class="d-lg-flex align-items-center justify-content-between w-100">
                    <div class="d-flex">
                        <span class="fas fa-solid fa-map-marker-alt"></span>
                        <div class="ms-4">
                            <h5 class="mb-0"><?php echo Text::_('J2STORE_SHIPPING_ADDRESS');?>
	                            <?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id='.$this->item->order_id.'&address_type=shipping&layout=address&tmpl=component','',array('class'=>'fas fa-solid fa-edit','refresh'=>true,'id'=>'fancybox ms-2'));?>
							&nbsp;
	                            <?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=orders&task=printShipping&tmpl=component&order_id='.$this->orderinfo->order_id,'', array('class'=>'fas fa-solid fa-print','width'=>800 ,'height'=>600,'id'=>'ms-2'));?>
                                </h5>
                            <small><?php echo $this->orderinfo->shipping_first_name.' '.$this->orderinfo->shipping_last_name; ?></small>
                        </div>
                    </div>
                    <div class="mt-4 mt-lg-0">
                        <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#collapseShippingAddress" aria-expanded="false" aria-controls="collapseShippingAddress">
						    <?php echo Text::_('J2STORE_VIEW_MORE');?>
                        </a>
                    </div>
                </div>
            </div>
            <div id="collapseShippingAddress" class="collapse">
                <div class="pt-4">
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="address" itemscope itemtype="https://schema.org/PostalAddress">
                                <p class="small">
								    <?php if($this->orderinfo->shipping_company):?>
                                        <span itemprop="name"><?php echo $this->orderinfo->shipping_company;?></span><br>
								    <?php endif;?>
								    <?php if($this->orderinfo->shipping_address_1):?>
                                        <span itemprop="streetAddress"><?php echo $this->orderinfo->shipping_address_1;?></span><br>
								    <?php endif;?>
								    <?php if($this->orderinfo->shipping_address_2):?>
                                        <span itemprop="streetAddress"><?php echo $this->orderinfo->shipping_address_2;?></span><br>
								    <?php endif;?>
								    <?php if($this->orderinfo->shipping_city):?>
                                        <span itemprop="addressLocality"><?php echo $this->orderinfo->shipping_city;?></span>,
								    <?php endif;?>
								    <?php if($this->orderinfo->shipping_zone_name):?>
                                        <span itemprop="addressRegion"><?php echo $this->orderinfo->shipping_zone_name;?></span>
								    <?php endif;?>
								    <?php if($this->orderinfo->shipping_zip):?>
                                        <span itemprop="postalCode"><?php echo $this->orderinfo->shipping_zip;?></span><br>
								    <?php endif;?>
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div>
                                <p class="small">
								    <?php if($this->orderinfo->shipping_tax_number):?>
                                        <span><?php echo $this->orderinfo->shipping_tax_number;?></span>
								    <?php endif;?>

								    <?php echo J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'shipping'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</div>
</div>

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

$row_class = 'row';
$col_class = 'col-md-';

$orders = $this->orders;

$customer_start_date = J2Html::getCustomerStartDate($this->orders);
$customer_days_old = J2Html::calculateDaysFromStartDate($customer_start_date);
$currency = J2Store::currency();
$customer_gross_sales = J2Html::getGrossCustomerSales($orders);
$customer_sum_orders = J2Html::getSumCustomerOrders($orders);
$currency_code = $currency->getCode();
$currency_value = $currency->getValue();
?>
<?php if($this->email):?>
<div class="j2store">
    <form class="form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','customer');?>
		<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo J2Html::hidden('email',$this->email ,array('id' =>'customer_email_id'));?>
		<?php echo HTMLHelper::_('form.token'); ?>
	<div class="<?php echo $row_class ?>">

            <div class="col-12 mb-2">
                <div class="row">
                    <div class="col-12 col-lg">
                        <div class="row">
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-xl-0">
                                <div class="alert alert-success">
                                    <div class="stat-box py-2">
                                        <div class="stat-value mb-2">
                                            <h2 class="alert-heading fs-1 fw-medium mb-0"><?php echo $currency->format($customer_gross_sales,$currency_code,$currency_value);?></h2>
                                        </div>
                                        <div class="stat-title">
                                            <h3 class="fs-6 mb-0 fw-medium alert-heading"><?php echo Text::_('J2STORE_CUSTOMER_TOTAL_SALES');?></h3>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-xl-0">
                                <div class="alert alert-primary border-0">
                                    <div class="stat-box py-2">
                                        <div class="stat-value mb-2">
                                            <h2 class="alert-heading fs-1 fw-medium mb-0"><?php echo $customer_sum_orders;?></h2>
                                        </div>
                                        <div class="stat-title">
                                            <h3 class="fs-6 mb-0 fw-medium alert-heading"><?php echo Text::_('J2STORE_CUSTOMER_TOTAL_ORDERS');?></h3>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body text-subdued">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg mb-3 mb-lg-0">
                                <h1 class="mb-0"><?php echo $this->item->first_name.' '.$this->item->last_name;?></h1>
                                <span class="fas fa-solid fa-user me-2"></span><span class="small"><?php echo Text::sprintf('J2STORE_CUSTOMER_FOR_DAYS', $customer_days_old);?></span>
                            </div>
                        </div>
						</div>
                </div>
            </div>
            <div class="col-lg-7">
                <?php echo $this->loadTemplate('orderhistory');?>
            </div>
            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0"><?php echo Text::_('J2STORE_EMAIL');?></h3>
                    </div>
                    <div class="card-body text-subdued">
                        <div class="controls mb-2">
                            <span class="fas fa-solid fa-envelope me-2"></span><?php echo $this->email;?>
                            <a id="customer-email-info" class="btn btn-primary btn-sm ms-2" onclick="jQuery('#customer-email-edit-info').toggle();jQuery('#customer-email-info').toggle();">
			                    <?php echo Text::_('J2STORE_EDIT');?>
								</a>
                        </div>
                        <div class="controls align-self-end" id="customer-email-edit-info" style="display:none;">
                            <div class="input-group">
			                    <?php echo J2Html::text('new_email',$this->email ,array('id'=>'new-email-input','class'=>'form-control form-control-sm'));?>
                                <button id="customer-save-btn" class="btn btn-success btn-sm" type="button" onclick="getUpdatedEmail(this,'changeEmail');"><?php echo Text::_('JAPPLY'); ?></button>
                                <button id="customer-confirm-btn" class="btn btn-warning btn-sm" type="button" onclick="getUpdatedEmail(this,'confirmchangeEmail');" style="display:none;"><?php echo Text::_('J2STORE_CONFIRM_UPDATE'); ?></button>
                                <button class="btn btn-primary btn-sm" type="button" onclick="canUpdate();"><?php echo Text::_('JCANCEL'); ?></button>
						</div>
				</div>
		</div>
	</div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><?php echo Text::_('J2STORE_ADDRESS_LIST');?></h3>
                    </div>
                    <div class="card-body text-subdued">
	                    <?php if($this->addresses && !empty($this->addresses)):
            echo J2Store::plugin()->eventWithHtml('BeforeCustomerAddressList',array($this->addresses));
			foreach($this->addresses as $item):
			$this->item = $item;
		?>
		<?php echo $this->loadTemplate('addresses');?>
		<?php endforeach;?>
		<?php endif;?>
		</div>
                </div>
            </div>
        </div>

	<div class="<?php echo $row_class ?>">

		<h4><?php Text::_('J2STORE_CUSTOMER_DETAILS');?></h4>
		<div class="<?php echo $col_class ?>8">

		</div>
		<div class="<?php echo $col_class ?>4"></div>
	</div>
	<div class="<?php echo $row_class ?>">
		<div class="<?php echo $col_class ?>6">

		</div>
		<div class="<?php echo $col_class ?>6">

		</div>
	</div>
</form>
</div>
<?php endif;?>

<script type="text/javascript">

/** Method to cancel the update option **/
function canUpdate(){
	(function($){
		//empty the task
		$('#task').attr('value','');
		location.reload();
	})(j2store.jQuery);
}
function getUpdatedEmail(element , task){
	(function($){
		$('#task').attr('value',task);
		var form  =$('#adminForm');
		var values = form.serializeArray();
		$.ajax({
				method: 'POST',
				url :'index.php',
				dataType:'json',
				data:values,
				success:function(json){
					if(json['redirect']){
						  window.location.replace(json['redirect']);
					}else{
						if(json['msgType'] !='' ){
							$(element).prop( "disabled", true );
							$('#new-email-input').prop( "readonly", 'readonly' );
							$('#system-message-container').append('<div class="alert alert-warning">'+  json['msg'] +'</div>');
							$('#customer-confirm-btn').show();
						}
					}
				}
			})
	})(j2store.jQuery);
}
</script>

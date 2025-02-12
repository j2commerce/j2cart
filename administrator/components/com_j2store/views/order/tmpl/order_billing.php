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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal','a.modal');
$platform->loadExtra('behavior.formvalidator');

$this->address_type='billing';
$row_class = 'row';
$col_class = 'col-md-';
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$script = "document.addEventListener('DOMContentLoaded',function(){document.getElementById('billing-address-existing').addEventListener('click',function(){document.getElementById('orderinfo-billing-" . $this->order->j2store_order_id . "').style.display='none';document.getElementById('nextlayout').style.display='none';document.getElementById('saveAndNext').style.display='block';document.querySelectorAll('.j2error').forEach(el=>el.remove());});document.getElementById('billing-address-new').addEventListener('click',function(){document.getElementById('orderinfo-billing-" . $this->order->j2store_order_id . "').style.display='block';document.getElementById('nextlayout').style.display='block';document.getElementById('saveAndNext').style.display='none';document.querySelectorAll('.j2error').forEach(el=>el.remove());});const countryElement=document.getElementById('country_id');if(countryElement){countryElement.addEventListener('change',function(){if(this.value==='')return;const loader=document.createElement('span');loader.className='wait';loader.innerHTML='&nbsp;<img src=\"" . Uri::root(true) . "/media/j2store/images/loader.gif\" alt=\"\" />';countryElement.after(loader);fetch('index.php?option=com_j2store&view=orders&task=getCountry&country_id='+this.value,{method:'GET',headers:{'Accept':'application/json'}}).then(response=>{if(!response.ok){throw new Error('Network response was not ok');}return response.json();}).then(json=>{document.querySelectorAll('.wait').forEach(el=>el.remove());const postcodeRequired=document.getElementById('billing-postcode-required');if(postcodeRequired){postcodeRequired.style.display=json['postcode_required']==='1'?'block':'none';}let html='<option value=\"\">" . JText::_('J2STORE_SELECT_OPTION') . "</option>';if(json['zone']&&json['zone'].length>0){json['zone'].forEach(zone=>{html+='<option value=\"'+zone.j2store_zone_id+'\"';if(zone.j2store_zone_id==='" . $this->address->zone_id . "'){html+=' selected=\"selected\"';}html+='>'+zone.zone_name+'</option>';});}else{html+='<option value=\"0\" selected=\"selected\">" . JText::_('J2STORE_CHECKOUT_NONE') . "</option>';}document.getElementById('zone_id').innerHTML=html;}).catch(error=>{console.error('Fetch error:',error);});});countryElement.dispatchEvent(new Event('change'));}});";

//$wa->addInlineScript($script, [], []);
?>
<fieldset class="customer-information options-form">
    <legend><?php echo Text::_('J2STORE_BILLING_ADDRESS');?></legend>
    <div class="mb-4">
        <div class="d-block mb-4">
            <?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->order->order_id."&address_type=billing&layout=address&tmpl=component",Text::_('J2STORE_ADDRESS_EDIT_BTN'),array('refresh'=>true,'id'=>'fancybox btn btn-primary btn-sm','width'=>700,'height'=>600));?>
            <a href="#" class="btn btn-outline-primary btn-sm collapse ms-2 show" data-bs-toggle="collapse" data-bs-target="#collapseChangeBillingAddress" aria-expanded="false" aria-controls="collapseChangeBillingAddress"><?php echo Text::_("J2STORE_CHOOSE_ALTERNATE_ADDRESS");?></a>
        </div>
        <div class="d-block mb-3"><span class="fas fa-solid fa-user me-3 fa-fw"></span><?php echo $this->orderinfo->billing_first_name." ".$this->orderinfo->billing_last_name; ?></div>
		<?php if($this->orderinfo->billing_company):?>
            <div class="d-block mb-3"><span class="fas fa-solid fa-building me-3 fa-fw"></span><?php echo $this->orderinfo->billing_company; ?></div>
		<?php endif;?>
        <div class="d-block mb-3"><span class="fas fa-solid fa-envelope me-3 fa-fw"></span><a href="mailto:<?php echo $this->order->user_email;?>" target="_blank"><?php echo $this->order->user_email;?></a></div>
		<?php if($this->orderinfo->billing_phone_1):?>
            <div class="d-block mb-3"><span class="fas fa-solid fa-phone me-3 fa-fw"></span><?php echo $this->orderinfo->billing_phone_1;?></div>
		<?php endif;?>
		<?php if($this->orderinfo->billing_phone_2):?>
            <div class="d-block mb-3"><span class="fas fa-solid fa-mobile me-3 fa-fw"></span><?php echo $this->orderinfo->billing_phone_2;?></div>
		<?php endif;?>
        <div class="address" itemscope itemtype="https://schema.org/PostalAddress">
            <div class="d-flex align-items-center">
                <div>
                    <span class="fas fa-solid fa-map-marker-alt me-3 fa-fw"></span>
                </div>
                <div>
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
					<?php if($this->orderinfo->billing_country_name):?>
                        <span itemprop="addressCountry"><?php echo $this->orderinfo->billing_country_name;?></span>
					<?php endif;?>
                </div>
            </div>
        </div>
		<?php echo J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'billing'); ?>
    </div>
    <div id="collapseChangeBillingAddress" class="collapse">

        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" name="save_shipping" role="switch" id="flexSwitchsave_shipping" checked>
            <label class="form-check-label" for="flexSwitchsave_shipping"><?php echo JText::_('J2STORE_SAME_AS_SHIPPING');?></label>
        </div>

		<input type="hidden" value="<?php echo $this->address_type;?>" name="address_type" />
		<div class="display_message" id="display_message"></div>
		<div class="billing-infos ">
			<?php if (isset($this->addresses) && count($this->addresses) > 0) : ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="address" id="billing-address-existing" value="existing" checked="checked">
                    <label class="form-check-label" for="billing-address-existing"><?php echo Text::_('J2STORE_ADDRESS_EXISTING'); ?></label>
                </div>
                <select class="form-select mb-4" name="address_id" id="address_id" >
				    <?php foreach ($this->addresses as $address) :  ?>
				    <?php if ($address->j2store_address_id == $this->billing_address_id) : ?>
				    	<option value="<?php echo $address->j2store_address_id; ?>" selected="selected">
				    		<?php echo $address->first_name; ?> 	<?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
				    	</option>
				    <?php else: ?>
				    	<option value="<?php echo $address->j2store_address_id; ?>">
				    		<?php echo $address->first_name; ?> <?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
				    	</option>
				    <?php endif; ?>
				    <?php endforeach; ?>
				  </select>
				<?php endif;?>
		</div>

		<div id="new-address">
			<input name="validate_type" type="hidden" value="billing" id="validate_type">

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="address" id="billing-address-new" value="new">
                <label class="form-check-label" for="billing-address-new"><?php echo Text::_('J2STORE_ADDRESS_NEW'); ?></label>
            </div>

			<div id="orderinfo-billing-<?php echo $this->order->j2store_order_id;?>" style="display:none;">
                <fieldset class="order-general-information options-form">
                    <legend><?php echo Text::_('J2STORE_ADDRESS_EDIT');?></legend>
				<?php
				$html = $this->storeProfile->get('store_billing_layout');
				if(empty($html) || strlen($html) < 5) {
				//we dont have a profile set in the store profile. So use the default one.

				$html = '<div class="'.$row_class.'">
                                    <div class="col-md-6">[first_name]</div>
                                    <div class="col-md-6">[last_name]</div>
                                    <div class="col-md-6">[address_1]</div>
                                    <div class="col-md-6">[address_2]</div>
                                    <div class="col-md-6">[city]</div>
                                    <div class="col-md-6">[zip]</div>
                                    <div class="col-md-6">[country_id]</div>
                                    <div class="col-md-6">[zone_id]</div>
                                    <div class="col-md-6">[phone_1]</div>
                                    <div class="col-md-6">[phone_2]</div>
                                    <div class="col-md-6">[company]</div>
                                    <div class="col-md-6">[tax_number]</div>
		</div>';
			}
			//first find all the checkout fields
			preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);
			$allFields = $this->fields;
			?>
			  	<?php foreach ($this->fields as $fieldName => $oneExtraField):?>
				<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
					<?php
						if(property_exists($this->address, $fieldName)):
							if(($fieldName !='email')){ ?>
                                <?php
						$oneExtraField->display_label = 'yes';?>
                                <?php $html = str_replace('['.$fieldName.']',$this->fieldClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName,$fieldName,false, $options = '', $test = false, $allFields, $allValues = null),$html);
						}
					?>
				<?php endif;?>
			  	<?php endforeach; ?>
			 	<?php
			 	 		$unprocessedFields = array();
						  foreach($this->fields as $fieldName => $oneExtraField):
			  			if(!in_array($fieldName, $checkoutFields[1])):
			  				$unprocessedFields[$fieldName] = $oneExtraField;

			  			endif;
			  		endforeach;

			   //now we have unprocessed fields. remove any other square brackets found.
			  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
			  foreach($removeFields[1] as $fieldName) {
			  	$html = str_replace('['.$fieldName.']', '', $html);
			  }
			  ?>

			  <?php  echo $html; ?>

			  <?php if(count($unprocessedFields)): ?>
				<div class="<?php echo $row_class ?>">
					<div class="<?php echo $col_class ?>12">
				  		<?php $uhtml = '';?>
				 		<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
							<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>

								<?php
								//print_r($this->billing_orderinfo);
								if(property_exists($this->address, $fieldName)): ?>
									<?php

										$oneExtraField->display_label = 'yes';
										if(($fieldName !='email')){

											$uhtml .= $this->fieldClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
										}
										 ?>
								<?php endif;?>
				  			<?php endforeach; ?>
				  		<?php echo $uhtml; ?>
				  	</div>
				  </div>
				<?php endif; ?>
                </fieldset>
		</div>
	</div>
</div>
</fieldset>

<script type="text/javascript">
(function($) {
	$('#change_address').on('click',function(e){
		e.preventDefault();
		$('#select_billing_address').show();
		$('#nextlayout').hide();
		$('#saveAndNext').show();
		$('#baddress-info').hide();
		$('#display_message').after('<button id="close_address" class="btn btn-warning pull-right"><?php echo JText::_('J2STORE_CLOSE');?></button>');
	});

})(j2store.jQuery);

(function($) {
	$('#billing-address-existing').on('click' ,function(){
		$('#orderinfo-billing-<?php echo $this->order->j2store_order_id;?>').slideUp(200);
		$('#nextlayout').hide();
		$('#saveAndNext').show();
		$('.j2error').remove();
	});
	$('#billing-address-new').on('click',function(){
		$('#orderinfo-billing-<?php echo $this->order->j2store_order_id;?>').slideDown(200);
		$('#nextlayout').show();
		$('#saveAndNext').hide();
		$('.j2error').remove();
	});

$('#country_id').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=orders&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#country_id').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#billing-postcode-required').show();
			} else {
				$('#billing-postcode-required').hide();
			}

			html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

			if (json['zone'] != '') {

				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

					if (json['zone'][i]['j2store_zone_id'] == '<?php echo $this->address->zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
			}

			/*$("#<?php echo $this->address_type;?>_zone_id").html(html);*/
			$("#zone_id").html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			/*alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);*/
		}
	});
});
})(j2store.jQuery);

(function($) {
	if($('#country_id').length > 0) {
		$('#country_id').trigger('change');
	}
})(j2store.jQuery);
</script>

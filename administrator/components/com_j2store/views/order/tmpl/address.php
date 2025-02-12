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

$row_class = 'row';
$col_class = 'col-md-';

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$script = "document.addEventListener('DOMContentLoaded',function(){const countryElement=document.querySelector('#address #country_id');if(countryElement){countryElement.addEventListener('change',function(){if(this.value==='')return;const loader=document.createElement('span');loader.className='wait';loader.innerHTML='&nbsp;<img src=\"" . Uri::root(true) . "/media/j2store/images/loader.gif\" alt=\"\" />';countryElement.after(loader);fetch('index.php?option=com_j2store&view=orders&task=getCountry&country_id='+this.value,{method:'GET',headers:{'Accept':'application/json'}}).then(response=>{if(!response.ok){throw new Error('Network response was not ok');}return response.json();}).then(json=>{document.querySelectorAll('.wait').forEach(el=>el.remove());const postcodeRequired=document.getElementById('shipping-postcode-required');if(postcodeRequired){postcodeRequired.style.display=json['postcode_required']==='1'?'block':'none';}let html='<option value=\"\">" . Text::_('J2STORE_SELECT_OPTION') . "</option>';if(json['zone']&&json['zone'].length>0){json['zone'].forEach(zone=>{html+='<option value=\"'+zone.j2store_zone_id+'\"';if(zone.j2store_zone_id===' " . $this->orderinfo->zone_id . " '){html+=' selected=\"selected\"';}html+='>'+zone.zone_name+'</option>';});}else{html+='<option value=\"0\" selected=\"selected\">" . Text::_('J2STORE_CHECKOUT_NONE') . "</option>';}const zoneElement=document.querySelector('#address #" . $this->address_type . "_zone_id');if(zoneElement){zoneElement.innerHTML=html;}}).catch(error=>{console.error('Fetch error:',error);});});}});document.addEventListener('DOMContentLoaded',function(){const countryElement=document.querySelector('#address #country_id');if(countryElement){countryElement.dispatchEvent(new Event('change'));}});";

//$wa->addInlineScript($script, [], []);
?>

<div class="j2store">
    <form id="j2storeaddressForm" name="addressForm" method="post" action="<?php echo 'index.php'; ?>">
        <fieldset class="order-general-information options-form">
            <legend><?php echo Text::_('J2STORE_ADDRESS_EDIT');?></legend>
	<div id="address">
		<div class="j2store-address-alert">
		</div>
                <div class="mb-3">
                    <input type="submit" onclick="document.getElementById('task').setAttribute('value', 'saveOrderinfo');" value="<?php echo Text::_('JAPPLY'); ?>" class="btn btn-success btn-sm" />
	  	</div>
	<?php
	$html ='';
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

	$this->fields =  $this->fieldClass->getFields($this->address_type,$this->orderinfo,'address');

	$allFields = $this->fields;
	?>
	  	<?php foreach ($this->fields as $fieldName => $oneExtraField):?>
		<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
			<?php if(property_exists($this->orderinfo, $fieldName)):
			$fieldName_prefix =$this->address_type.'_'.$fieldName;
			if(($fieldName !='email')){
			 	$html = str_replace('['.$fieldName.']',$this->fieldClass->getFormatedDisplay($oneExtraField,$this->orderinfo->$fieldName,$fieldName_prefix,false, $options = '', $test = false, $allFields, $allValues = null),$html);
			}
			?>
		<?php endif;?>
	  	<?php endforeach; ?>
	 	<?php
	 		 //check for unprocessed fields.
	 		 //If the user forgot to add the
	 		 //fields to the checkout layout in store profile, we probably have some.
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
	  <?php echo $html; ?>

	  <?php if(count($unprocessedFields)): ?>
	 	<div class="<?php echo $row_class ?>">
	  		<div class="<?php echo $col_class ?>12">
		  		<?php $uhtml = '';?>
		 		<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
					<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
						<?php if(property_exists($this->orderinfo, $fieldName)): ?>
							<?php
								if(($fieldName !='email')){
									$uhtml .= $this->fieldClass->getFormatedDisplay($oneExtraField,$this->orderinfo->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
								}
								 ?>
						<?php endif;?>
		  			<?php endforeach; ?>
		  		<?php echo $uhtml; ?>
	  		</div>
	  	</div>
		<?php endif; ?>
	</div>
        </fieldset>
  <input type="hidden" name="option" value="com_j2store" />
  <input type="hidden" name="view" value="orders" />
  <input type="hidden" id="task" name="task" value="" />
  <input type="hidden" name="address_type" value="<?php echo $this->address_type;?>" />
  <input type="hidden" name="order_id" value="<?php echo $this->item->order_id;?>" />
  <input type="hidden" name="j2store_orderinfo_id" value="<?php echo $this->item->j2store_orderinfo_id;?>" />
        <?php echo HTMLHelper::_( 'form.token' ); ?>
  </form>
</div>
<script type="text/javascript">
(function($) {
$('#address #country_id').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=orders&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#address #country_id').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#shipping-postcode-required').show();
			} else {
				$('#shipping-postcode-required').hide();
			}

			html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

			if (json['zone'] != '') {

				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

					if (json['zone'][i]['j2store_zone_id'] == '<?php echo $this->orderinfo->zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
			}

			$("#address #<?php echo $this->address_type;?>_zone_id").html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

(function($) {
	if($('#address #country_id').length > 0) {
		$('#address #country_id').trigger('change');
	}
})(j2store.jQuery);
</script>

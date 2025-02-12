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

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('webcomponent.joomla-tab');

$platform = J2Store::platform();
$platform->loadExtra('jquery.framework');
$platform->loadExtra('bootstrap.framework');

$document = Factory::getApplication()->getDocument();

$ajax_base_url = Route::_('index.php');
//now load them in order
$row_class = 'row';
$col_class = 'col-md-';
$active_class = 'class = "nav-link active"' ;
$tab_class ='class = "nav-link"';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $list_active_class = 'class = "j2store-tab active"' ;
    $list_tab_class = 'class = "j2store-tab"';
}
    ?>
	<?php if(J2Store::isPro() != 1): ?>
	<?php echo J2Html::pro(); ?>
<?php else: ?>

<div class="main-card">
    <form class="form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<?php echo J2Html::input('hidden','option','com_j2store',array('id'=>'option'));?>
		<input type="hidden" value="orders" id="view" name="view" />
		<input type="hidden" value="createOrder" id="task" name="task" />
		<input type="hidden" value="<?php echo $this->layout;?>" id="layout" name="layout" />
		<input type="hidden" value="" id="next_layout" name="next_layout" />
		<?php echo J2Html::input('hidden','oid', $this->order->j2store_order_id);?>
		<?php echo J2Html::input('hidden','id', $this->order->j2store_order_id);?>
		<?php echo J2Html::input('hidden','order_id', $this->order->order_id);?>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
        <div class="<?php echo $row_class ?>">
            <div class="message-div <?php echo $col_class ?>12"></div>
	<div class="<?php echo $col_class ?>12">
                <joomla-tab id="myTab" orientation="horizontal" recall breakpoint="768" view="tabs">
	                <?php $active = isset($list_active_class) && !empty($list_active_class) ? 'true' :'false'; ?>
                    <div role="tablist">
		    	<?php if(empty($this->order->j2store_order_id)):?>
                            <button onclick="getTabcontent(this);" aria-expanded="<?php echo $active;?>" data-layout="billing" role="tab" type="button">
			                    <?php echo Text::_('J2STORE_STORES_GROUP_BASIC');?>
                            </button>
				   <?php else:?>
				    <?php foreach($this->fieldsets as $key => $text):?>
                                <?php $active = ($this->layout  == $key) ? 'true':'false';?>
                                <button onclick='window.location.href="index.php?option=com_j2store&view=orders&task=createOrder&layout=<?php echo $key?>&oid=<?php echo $this->order->j2store_order_id;?>"' aria-expanded="<?php echo $active;?>" type="button" <?php echo ($this->layout  == $key) ? 'true':'false'; ?> data-layout="<?php echo $key;?>" role="tab">
				    		<?php echo $text;?>
                                </button>
					<?php endforeach;?>
				  <?php endif;?>
                    </div>
				<?php if(empty($this->order->j2store_order_id)):?>
                        <button onclick="getTabcontent(this);" aria-expanded="<?php echo $active;?>" data-layout="billing" role="tab" type="button" hidden>
			                <?php echo Text::_('J2STORE_STORES_GROUP_BASIC');?>
                        </button>
                        <joomla-tab-element id="basic" name="<?php echo Text::_('J2STORE_STORES_GROUP_BASIC');?>" role="tabpanel" active="">
						<?php  echo $this->loadTemplate('basic');?>
                        </joomla-tab-element>
				<?php else:?>
					<?php foreach($this->fieldsets as $key => $text):?>
					<?php if($this->layout == $key):?>
				                <?php $active =($this->layout  == $key) ? ' active' : ''; ?>

                                <joomla-tab-element id="<?php echo $key;?>" name="<?php echo $text;?>" role="tabpanel" <?php echo $active;?>>
								<?php  echo $this->loadTemplate($key);?>
                                </joomla-tab-element>
					<?php endif;?>
					<?php endforeach;?>
				<?php endif;?>
	    <?php
		    $keys = array_keys($this->fieldsets);
		    $prev_ordinal = (array_search($this->layout,$keys)-1)%count($keys);
                        $next_ordinal = (array_search($this->layout,$keys)+1)%count($keys);



                    //$wa->addInlineScript($script, [], []);
                    ?>
                    <div class="j2-bottom-bar py-4 px-5">
                        <div class="btn-toolbar d-flex justify-content-center justify-content-lg-end">
	    <?php if(isset($keys[$prev_ordinal])):?>
                                <a class="btn btn-primary me-auto" href="index.php?option=com_j2store&view=orders&task=createOrder&layout=<?php echo $keys[$prev_ordinal];?>&oid=<?php echo $this->order->j2store_order_id;?>" data-layout="<?php echo $key;?>">
				                    <?php echo Text::_('J2STORE_PREV');?>
			</a>
    	<?php endif;?>
		                    <?php if(isset($keys[$next_ordinal])):?>
	    <?php if($keys[$next_ordinal] =='shipping' || $keys[$next_ordinal] == 'items'){ ?>
			<?php if((!isset($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->shipping_country_id) || empty($this->orderinfo->shipping_zone_id))&& $keys[$next_ordinal] =='items'):?>
			<a class="btn btn-success" style="display:none;" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
					                    <?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2"></span>
			</a>
                                    <button class="btn btn-success " id="saveAndNext" >	<?php echo Text::_('J2STORE_SAVE_AND_NEXT');?>	</button>
			<?php elseif((!isset($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->billing_country_id) /*|| empty($this->orderinfo->billing_zone_id)*/)&& $keys[$next_ordinal] =='shipping'):?>
			<a class="btn btn-success" style="display:none;" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
					                    <?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2">
			</a>
                                    <button class="btn btn-success" id="saveAndNext"><?php echo Text::_('J2STORE_SAVE_AND_NEXT');?></button>
			<?php else:?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
					                    <?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2">
			</a>
                                    <button class="btn btn-success" style="display:none;" id="saveAndNext"><?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2"></button>
			<?php endif;?>
			<?php } else if($keys[$next_ordinal] == 'basic'){?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('summary')" data-layout="<?php echo 'summary';?>">
				                    <?php echo Text::_('J2STORE_SAVE_ORDER');?>
			</a>
                                <button class="btn btn-success" style="display:none;" id="saveAndNext"><?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2"></button>
			<?php }else{?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
				                    <?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2">
			</a>
                                <button class="btn btn-success" style="display:none;" id="saveAndNext" ><?php echo Text::_('J2STORE_SAVE_AND_NEXT');?><span class="fas fa-solid fa-arrow-right ms-2"></button>
			<?php }?>
	    <?php endif ;?>
	    </div>
	</div>
            </div>
        </div>
</form>
</div>

<script type="text/javascript">
(function($){
	$('#saveAndNext').on('click', function(e){
		e.preventDefault();
		var address_id = $('#address_id').val();
		if(address_id){
			nextlayout('<?php echo $keys[$next_ordinal];?>');
		}else{
			$('.j2error').remove();
                $('#display_message').html('<span class="j2error"><?php echo Text::_('J2STORE_ADDRESS_SELECTION_ERROR');?></span>');
		}
	});

})(j2store.jQuery);

function nextlayout(layout){
	(function($){

			var new_data = $('#new-address :input').serializeArray();
			var data1 = {
					option: 'com_j2store',
					view: 'orders',
					task: 'validate_address',
					order_id: '<?php echo $this->order->order_id;?>'
			};

			var chk_new = 0;
			$.each( new_data, function( key, value ) {
				if(value.name=="address" && value.value == 'new'){
					chk_new = 1;
				}
				data1[value.name] = value.value;
			});

			if(chk_new ){
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data:data1,
					dataType: 'json',
					success: function(json) {
						if(json['success']){
							$('#task').attr('value','saveAdminOrder');
							$('#next_layout').attr('value',layout );
							$('#adminForm').submit();
						}else if (json['error']) {
							$('.warning, .j2error').remove();
							$.each( json['error'], function( key, value ) {
								if (value) {
									$('#'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
								}
							});
						}

					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}else{
				var current_layout = $('#layout').val();
				if(current_layout=='summary'){
					var c=confirm("<?php echo Text::_('J2STORE_ORDER_EDIT_SUMMARY_SAVE_CONFIRM');?>");
					if (c){
						$('#task').attr('value','saveAdminOrder');
						$('#next_layout').attr('value',layout );
						$('#adminForm').submit();
					}
				}else{
					$('#task').attr('value','saveAdminOrder');
					$('#next_layout').attr('value',layout );
					$('#adminForm').submit();
				}
				//
			}
	})(j2store.jQuery);

}

 function getTabcontent(element){
	(function($){
			var layout  = $(element).data('layout');
			$('#layout').attr('value',layout );
			$('#adminForm').submit();
	})(j2store.jQuery);
}
var orderinfo = jQuery('#orderinfo_id').attr('value');
 function setOrderinfo1(address_type,address_id){
		(function($){
			var oid = <?php echo !empty($this->order->j2store_order_id) ? $this->order->j2store_order_id : 0;?>;
			var j2Ajax = $.ajax({
				url:'index.php',
				type: 'post',
				data: {'option':'com_j2store',
						'view':'orders',
						'task':'orderSetAddress',
						'oid':oid ,
						'address_type': address_type,
						'address_id':address_id,
						'address_type':address_type,
						'j2store_orderinfo_id' : orderinfo
					},
				dataType: 'json'
	 	 });
		 j2Ajax.done(function(json) {
			 if(json!='' ){
				 if(json['html'] !='' ){
					if(address_type == 'billing'){
				 		$('#baddress-info').html(json['html']);
						$('#orderinfo_id').attr('value',json['orderinfo_id']);
					}else{
				 		$('#saddress-info').html(json['html']);
				 		$('#orderinfo_id').attr('value',json['orderinfo_id']);
					}
				 }
			 }
		 });
		})(j2store.jQuery);
	}

</script>
<?php endif;?>

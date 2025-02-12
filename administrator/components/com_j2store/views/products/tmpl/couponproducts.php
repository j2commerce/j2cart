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

$platform = J2Store::platform();
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.framework');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('searchtools');

$app = $platform->application();
$db = Factory::getContainer()->get('DatabaseDriver');
$function  = $app->input->getString('function', 'jSelectProduct');
$field = $app->input->getString('field');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_j2store&view=products&task=setCouponProducts');?>" method="post" name="adminForm" id="productadminForm">
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_J2STORE_PRODUCTS');?></legend>
        <div class="js-stools">
            <button class="btn btn-primary align-self-center text-capitalize" id="setAllProductsBnt" type="button" style="display:none;"><?php echo Text::_('J2STORE_SET_VALUES');?></button>
            <div class="js-stools-container-bar mb-0">
                <div class="btn-toolbar">

                    <div class="filter-search-bar btn-group">
                        <div class="input-group">
                            <?php echo J2Html::text('search',htmlspecialchars($this->state->search),array('id'=>'search','class'=>'form-control','placeholder'=>Text::_('J2STORE_PRODUCT_SKU')));?>
                            <?php echo J2Html::buttontype('go','<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>' ,array('class'=>'btn btn-primary','onclick'=>'this.form.submit();'));?>
                            <?php echo J2Html::buttontype('reset', Text::_('JCLEAR'), array('id' => 'reset-filter-search', 'class' => 'filter-search-actions__button btn btn-primary js-stools-btn-clear')); ?>
                        </div>
					</div>
                    <div class="ordering-select">
                        <div class="js-stools-field-list">
                            <span class="visually-hidden">
                                <label id="limit-lbl" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
                            </span>
					<?php echo $this->pagination->getLimitBox(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table itemList">
			<thead>
				<tr>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
                        <th scope="col" class="w-30 title">
                            <?php echo Text::_('J2STORE_PRODUCT_NAME'); ?>
					</th>
                        <th scope="col" class="center nowrap">
                            <?php echo Text::_('J2STORE_PRODUCT_SKU'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach($this->productitems as $key=>$item):?>
				<?php $canChange  = 1;?>
				<tr>
					<td>
                            <input data-product-title="<?php echo $item->product_name;?>" id="cb<?php echo $item->j2store_product_id;?>" type="checkbox" onclick="Joomla.isChecked(this.checked);" value="<?php echo $item->j2store_product_id;?>" name="cid[]" class="form-check-input">
						<?php echo J2html::hidden('tmp_product_title['.$item->j2store_product_id.']', $item->product_name ,array('class'=>'tmp_product_title')); ?>
					</td>
					<td>
						<a href="javascript:if (window.parent) window.parent.<?php echo $db->escape($function);?>('<?php echo $item->j2store_product_id; ?>','<?php echo $item->product_name;?>' ,'<?php echo $field;?>');">
							<?php echo $item->product_name; ?>
						</a>
					</td>
					<td>
						<?php if($item->product_type == 'variable'):?>
                                <h5 class="fs-5"><?php echo Text::_('J2STORE_HAS_VARIANTS'); ?></h5>
								<?php
										$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
										$variant_model->setState('product_type', $item->product_type);
										$variants = $variant_model->product_id($item->j2store_product_id)
													->is_master(0)
													->getList();
										if(isset($variants) && count($variants)):?>

                                    <?php
                                    $skus = []; // Initialize an array to store SKUs
                                    foreach ($variants as $variant) {
                                        if (!empty($variant->sku)) {
                                            $skus[] = '<small>' . htmlspecialchars($variant->sku) . '</small>';
                                        }
                                    }
                                    echo implode(', ', $skus);
                                    ?>
										<?php endif;?>
						<?php else:?>
                                <small><?php echo $item->sku; ?></small>
						<?php endif;?>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
        </div>
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','products');?>
		<?php echo J2Html::hidden('tmpl','component');?>
		<?php echo J2Html::hidden('task','setCouponProducts',array('id'=>'task'));?>
		<?php echo J2Html::hidden('layout','couponproducts');?>
		<?php echo J2Html::hidden('boxchecked',0);?>
		<?php echo J2Html::hidden('filter_order',$listOrder);?>
		<?php echo J2Html::hidden('filter_order_Dir',$listDirn);?>
		<?php echo J2Html::hidden('field',$field);?>
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
<script>
var newArray = new Array();
var checkedValues;
var product_titles;

Joomla.submitform =function(){
	var pressbutton =  jQuery("#task").val();
	 jQuery('#productadminForm').submit();
}

/**
 * Override Joomlaischecked
 */
Joomla.isChecked=function(a,d){
	if(typeof(d)==="undefined"){
		d=document.getElementById("productadminForm")
	}
	if(a==true){
		d.boxchecked.value++

		(function($){
			//now show the
			$("#setAllProductsBnt").show();

		 		checkedValues =  $('input:checkbox:checked').map(function() {
			    	return this.value ;
				}).get();

					product_titles =$('input:checkbox:checked').map(function(){
				return $(this).data('product-title');
			}).get();

		})(j2store.jQuery);
		}else{
			d.boxchecked.value--

			(function($){
				$("#setAllProductsBnt").hide();
				})(j2store.jQuery);
	}

	var g=true,b,f;
	for(b=0,n=d.elements.length;b<n;b++){
		f=d.elements[b];
	if(f.type=="checkbox"){
		if(f.name!="checkall-toggle"&&f.checked==false){
				g=false;
				break
		}
		}
	}if(d.elements["checkall-toggle"]){
		d.elements["checkall-toggle"].checked= g
	}
};

(function($){
	$("input[name=checkall-toggle]").click(function(){
		$("#setAllProductsBnt").toggle(this.checked);
		checkedValues = $('input:checkbox:checked').map(function() {
	    	return this.value ;
		}).get();
		product_titles = $('.tmp_product_title').map(function(){
			return this.value;
		}).get();

	});
	//$("input[name=checkall-toggle]").trigger('change');
})(j2store.jQuery);

(function($){
	$("#setAllProductsBnt").click(function(){
		var form = $("#adminForm");
		var html ='';
		newArray = mergeArray(checkedValues , product_titles)
		$(newArray).each(function(index,value){
			if($('#jform_product_list' ,window.parent.document).find('#product-row-'+value.id ).length == 0){
				html ='<tr id="product-row-'+ value.id +'"><td><input type="hidden" name="products['+value.id +']" value='+value.id+' />'+value.product_title +'</td><td><button class="btn btn-danger" onclick="jQuery(this).closest(\'tr\').remove();"><i class="icon icon-trash"></button></td></tr>';
				$('#jform_product_list', window.parent.document).append(html);
				window.close();
			}
		});
		checkedValues.length='';
	});

 	function mergeArray(checkedValues , product_titles){
 		checkedValues = cleanArray(checkedValues);
	     for(var i = 0; i < checkedValues.length; i++){
		     newArray.push({'id':checkedValues[i],'product_title': product_titles[i]});
	        }
    	 return newArray;
	 };

	function cleanArray(actual){
	  var tmpArray = new Array();
	  for(var i = 0; i<actual.length; i++){
	      if (actual[i]){
	        tmpArray.push(actual[i]);
	    }
	  }
	  return tmpArray;
	}
})(j2store.jQuery);
</script>

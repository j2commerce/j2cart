<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');
HTMLHelper::_('bootstrap.offcanvas', '[data-bs-toggle="offcanvas"]');

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$this->params = J2Store::config();
$selected = "selected='selected'";
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="btn-toolbar w-100 justify-content-end mb-3">
    <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
        <div class="input-group w-100">
	        <?php echo J2Html::text('search', $this->state->search, array('id' => 'search', 'class' => 'form-control j2store-product-filters','placeholder'=>Text::_( 'J2STORE_FILTER_SEARCH' ))); ?>
            <span class="filter-search-bar__label visually-hidden">
                <label id="search-lbl" for="search"><?php echo Text::_( 'J2STORE_FILTER_SEARCH' ); ?></label>
            </span>
			<?php echo J2Html::buttontype('go','<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>' ,array('class'=>'btn btn-primary','onclick'=>'this.form.submit();'));?>
	        <?php echo J2Html::buttontype('reset', Text::_('JCLEAR'), array('id' => 'reset-filter-search', 'class' => 'btn btn-primary', "onclick" => "jQuery('#search').val('');this.form.submit();")); ?>
        </div>
    </div>
    <div class="ordering-select d-flex gap-2 ms-lg-2 flex-grow-1 flex-lg-grow-0">
        <select name="inventry_stock" onchange="this.form.submit();" class="form-select j2store-product-filters w-100">
            <option value=""><?php echo Text::_ ( 'J2STORE_INVENTRY_FILTER_STOCK' );?></option>
            <option value="out_of_stock" <?php echo isset( $this->state->inventry_stock ) && $this->state->inventry_stock == 'out_of_stock' ? $selected:''; ?>><?php echo Text::_ ( 'J2STORE_OUT_OF_STOCK' );?></option>
            <option value="in_stock" <?php echo isset( $this->state->inventry_stock ) && $this->state->inventry_stock == 'in_stock' ? $selected:''; ?>><?php echo Text::_ ( 'COM_J2STORE_PRODUCT_IN_STOCK' );?></option>
        </select>
		<?php echo $this->pagination->getLimitBox();?>
    </div>
</div>


<table class="table itemList" id="inventoryList">
    <caption class="visually-hidden">
		<?php echo Text::_('J2STORE_INVENTORY_FIELDS'); ?>,
        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
    </caption>
    <thead>
        <tr>
            <td class="w-1 text-center d-none d-lg-table-cell">
                <?php echo HTMLHelper::_('grid.sort',  'J2STORE_PRODUCT_ID', 'variant_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?>
            </td>
            <th scope="col" style="min-width:100px" class="title"><?php echo Text::_('J2STORE_PRODUCT_NAME'); ?></th>
            <th scope="col" class="w-10"><?php echo Text::_('J2STORE_PRODUCT_STOCK_QUANTITY'); ?></th>
            <th scope="col" class="w-10 d-none d-lg-table-cell"><?php echo Text::_('J2STORE_PRODUCT_MANAGE_STOCK'); ?></th>

            <th scope="col" class="d-none d-sm-table-cell"><?php echo Text::_('J2STORE_STOCK_STATUS'); ?></th>
            <th scope="col" class="w-10"><?php echo Text::_('J2STORE_INVENTORY_SAVE');?></th>
        </tr>
    </thead>
    <tbody>
        <?php if($this->products && !empty($this->products)):
            foreach($this->products as $i => $item):
	            $thumbimage='';
	            $platform = J2Store::platform();
	            $thumbimage = $platform->getImagePath($item->product->thumb_image);
            ?>
                <tr>
                    <td class="text-center d-none d-lg-table-cell"><?php echo $item->j2store_product_id;?></td>
                    <td>
                        <div class="d-block d-lg-flex">
		                    <?php if(!empty($thumbimage )): ?>
                                <div class="flex-shrink-0">
                                    <a href="<?php echo $item->product->product_edit_url;?>" class="d-none d-lg-inline-block" title="<?php echo $item->product->product_name;?>">
                                        <img src="<?php echo $thumbimage;?>" class="img-fluid object-fit-cover width-64 height-64" alt="<?php echo $this->escape($item->product->product_name);?>">
                                    </a>
                                </div>
		                    <?php endif;?>
                            <div class="flex-grow-1 ms-lg-3 mt-2 mt-lg-0">
                                <div>
                                    <a href="<?php echo $item->product->product_edit_url;?>" title="<?php echo $this->escape($item->product->product_name);?>"><?php echo $this->escape($item->product->product_name);?></a>
                                </div>
	                            <?php if(isset($item->product->variants->sku) && !empty($item->product->variants->sku)) : ?>
                                    <div class="small text-capitalize"><?php echo Text::_('J2STORE_SKU')?>:<b class="ms-2"><?php echo isset($item->product->variants->sku) ? $item->product->variants->sku: '';?></b></div>
	                            <?php endif; ?>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_SOURCE')?>:<b class="ms-2 text-lowercase"><?php echo $item->product->product_source;?></b></div>
                            </div>
                        </div>
                    </td>
		            <?php if(in_array($item->product->product_type,J2Store::product()->getVariableProductTypes())):?>
                        <td>
                            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasVariant<?php echo $item->j2store_product_id;?>" aria-controls="offcanvasVariant<?php echo $item->j2store_product_id;?>">
		                        <?php echo Text::_('J2STORE_PRODUCT_VIEW_ALL_VARIANTS');?>
                            </button>
                            <div class="offcanvas offcanvas-end j2store-offcanvas" tabindex="-1" id="offcanvasVariant<?php echo $item->j2store_product_id;?>" aria-labelledby="offcanvasVariant<?php echo $item->j2store_product_id;?>Label">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasVariant<?php echo $item->j2store_product_id;?>Label"><?php echo $this->escape($item->product_name).' '.Text::_('J2STORE_PRODUCT_TAB_VARIANTS');?> </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <a id="save_all_inventry_<?php echo $item->j2store_product_id;?>" onclick="saveAllVariant('<?php echo $item->j2store_product_id;?>')" class="btn btn-primary btn-sm d-inline-block mb-2"><?php echo Text::_ ( 'J2STORE_SAVEALL' );?></a>
	                                <?php
	                                $variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
	                                $variant_model->setState('product_type', $item->product->product_type);
	                                $variants = $variant_model->product_id($item->product->j2store_product_id)->is_master(0)->getList();
	                                if(isset($variants) && count($variants)):
	                                    $i = 0;
	                                    foreach($variants as $variant):
                                            $vParams = json_decode($variant->params);
                                            $vImage = $vParams->variant_main_image;
	                                ?>
                                            <div id="variantListTable-<?php echo $item->j2store_product_id;?>" class="list-group">
                                                <div class="list-group-item mb-1">
                                                    <div class="d-flex w-100 justify-content-between mb-2 align-items-center">
                                                        <div class="d-block d-lg-flex align-items-center">
	                                                        <?php if($vImage): ?>
                                                                <div class="flex-shrink-0">
                                                                    <div class="d-inline-block">
                                                                        <img src="<?php echo $platform->getImagePath($vImage);?>" class="img-fluid object-fit-cover width-48 height-48" alt="<?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?>">
                                                                    </div>
                                                                </div>
                                                            <?php elseif($thumbimage) :?>
                                                                <div class="flex-shrink-0">
                                                                    <div class="d-inline-block">
                                                                        <img src="<?php echo $thumbimage;?>" class="img-fluid object-fit-cover width-48 height-48" alt="<?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?>">
                                                                    </div>
                                                                </div>
	                                                        <?php endif;?>
                                                            <div class="flex-grow-1 ms-lg-3 mt-2 mt-lg-0">
                                                                <div class="variant-title">
                                                                    <h5 class="lh-1 mb-0"><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></h5>
                                                                    <div class="small text-capitalize"><?php echo $variant->sku; ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <a class="btn btn-success btn-sm" onclick="j2storesaveinventory(<?php echo $variant->j2store_variant_id;?>)"><?php echo Text::_ ( 'JAPPLY' );?></a>
                                                    </div>

                                                    <div class="accordion j2-store-accordion" id="accordionVariant<?php echo $variant->j2store_variant_id;?>">
                                                        <div class="accordion-item border-0">
                                                            <div class="accordion-header">
	                                                            <?php if($variant->manage_stock==0){
		                                                            $text_color = ' text-danger';
	                                                            } else {
		                                                            $text_color = ' text-success';
	                                                            } ?>
                                                                <button class="accordion-button collapsed rounded-0 pt-1 pb-0 px-1 small" type="button" data-bs-toggle="collapse" data-bs-target="#manageStock<?php echo $variant->j2store_variant_id;?>" aria-expanded="false" aria-controls="manageStock<?php echo $variant->j2store_variant_id;?>">
	                                                                <?php echo Text::_('J2STORE_PRODUCT_MANAGE_STOCK')?> <b class="ms-2<?php echo $text_color;?>"><?php if($variant->manage_stock==0) : echo Text::_ ( 'JNO' ); elseif($variant->manage_stock==1): echo Text::_ ( 'JYES' ); endif;?></b>
                                                                </button>
                                                            </div>
                                                            <div id="manageStock<?php echo $variant->j2store_variant_id;?>" class="accordion-collapse collapse" data-bs-parent="#accordionVariant<?php echo $variant->j2store_variant_id;?>">
                                                                <div class="accordion-body px-0">
                                                                    <select name="list[<?php echo $i;?>][manage_stock]" id="manage_stock_<?php echo $variant->j2store_variant_id;?>" class="form-select">
                                                                        <option value="0" <?php if($variant->manage_stock==0)echo "selected";?>><?php echo Text::_ ( 'JNO' );?></option>
                                                                        <option value="1" <?php if($variant->manage_stock==1)echo "selected";?>><?php echo Text::_ ( 'JYES' );?></option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item border-0">
                                                            <div class="accordion-header">
                                                                <button class="accordion-button collapsed rounded-0 pt-1 pb-0 px-1 small" type="button" data-bs-toggle="collapse" data-bs-target="#quantity<?php echo $variant->j2store_variant_id;?>" aria-expanded="false" aria-controls="quantity<?php echo $variant->j2store_variant_id;?>">
	                                                                <?php echo Text::_('J2STORE_PRODUCT_STOCK_QUANTITY')?> <b class="ms-2"><?php echo $variant->quantity;?></b>
                                                                </button>
                                                            </div>
                                                            <div id="quantity<?php echo $variant->j2store_variant_id;?>" class="accordion-collapse collapse" data-bs-parent="#accordionVariant<?php echo $variant->j2store_variant_id;?>">
                                                                <div class="accordion-body px-0">
                                                                    <input type="number" size="2" id="quantity_<?php echo $variant->j2store_variant_id;?>" name="list[<?php echo $i;?>][quantity]" value="<?php echo $variant->quantity;?>" class="form-control w-100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="accordion-item border-0">
                                                            <div class="accordion-header">
                                                                <?php if($variant->availability==0){
                                                                    $text_color = ' text-danger';
                                                                } else {
	                                                                $text_color = ' text-success';
                                                                } ?>
                                                                <button class="accordion-button collapsed rounded-0 pt-1 pb-0 px-1 small" type="button" data-bs-toggle="collapse" data-bs-target="#stock<?php echo $variant->j2store_variant_id;?>" aria-expanded="false" aria-controls="stock<?php echo $variant->j2store_variant_id;?>">
	                                                                <?php echo Text::_('J2STORE_STOCK_STATUS')?> <b class="ms-2<?php echo $text_color;?>"><?php if($variant->availability==0) : echo Text::_ ( 'J2STORE_OUT_OF_STOCK' ); elseif($variant->availability==1): echo Text::_ ( 'COM_J2STORE_PRODUCT_IN_STOCK' ); endif;?></b>
                                                                </button>
                                                            </div>
                                                            <div id="stock<?php echo $variant->j2store_variant_id;?>" class="accordion-collapse collapse" data-bs-parent="#accordionVariant<?php echo $variant->j2store_variant_id;?>">
                                                                <div class="accordion-body px-0">
                                                                    <select id="availability_<?php echo $variant->j2store_variant_id;?>" name="list[<?php echo $i;?>][availability]" class="form-select">
                                                                        <option value="0" <?php if($variant->availability==0)echo "selected";?>><?php echo Text::_ ( 'J2STORE_OUT_OF_STOCK' );?></option>
                                                                        <option value="1" <?php if($variant->availability==1)echo "selected";?>><?php echo Text::_ ( 'COM_J2STORE_PRODUCT_IN_STOCK' );?></option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="list[<?php echo $i;?>][j2store_variant_id]" value="<?php echo $variant->j2store_variant_id;?>">
                                                </div>
                                            </div>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell"></td>
                        <td class="d-none d-sm-table-cell"></td>
                        <td></td>
		            <?php else:?>
                        <td class="w-10"><input type="number" size="2" id="quantity_<?php echo $item->variant_id;?>" name="quantity[<?php echo $item->variant_id;?>]" value="<?php echo $item->quantity;?>" class="form-control inventory-form-control"></td>
                        <td class="d-none d-lg-table-cell">
                            <select name="manage_stock[<?php echo $item->variant_id;?>]" id="manage_stock_<?php echo $item->variant_id;?>" class="form-select">
                                <option value="0" <?php if($item->manage_stock==0)echo "selected";?>><?php echo Text::_ ( 'JNO' );?></option>
                                <option value="1" <?php if($item->manage_stock==1)echo "selected";?>><?php echo Text::_ ( 'JYES' );?></option>
                            </select>
                        </td>

                        <td class="d-none d-sm-table-cell">
                            <select id="availability_<?php echo $item->variant_id;?>" name="availability[<?php echo $item->variant_id;?>]" class="form-select">
                                <option value="0" <?php if($item->availability==0)echo "selected";?>><?php echo Text::_ ( 'J2STORE_OUT_OF_STOCK' );?></option>
                                <option value="1" <?php if($item->availability==1)echo "selected";?>><?php echo Text::_ ( 'COM_J2STORE_PRODUCT_IN_STOCK' );?></option>
                            </select>
                        </td>
                        <td class="text-end"><a class="btn btn-primary" onclick="j2storesaveinventory(<?php echo $item->variant_id;?>)"><?php echo Text::_ ( 'JAPPLY' );?></a></td>
		            <?php endif;?>
                </tr>

            <?php endforeach;?>
        <?php endif;?>
    </tbody>
</table>
<?php  echo $this->pagination->getListFooter(); ?>



<script type="text/javascript">
function saveAllVariant(product_id) {
	var data = jQuery('#adminForm #variantListTable-'+product_id).find('input,select').serialize();
	jQuery.ajax({
		url: 'index.php?option=com_j2store&view=inventories&task=saveAllVariantInventory',
		type: 'post',
		dataType: 'json',
		data: data,
		cache: false,
		beforeSend: function() {
			jQuery('#save_all_inventry_'+product_id).attr('disabled', true);
			jQuery('#save_all_inventry_'+product_id).html('saving...');
		},
		success: function(json) {
			jQuery('#save_all_inventry_'+product_id).attr('disabled', false);
			jQuery('.text-danger, .text-success').remove();

			if (json['error']) {

			}

			if (json['success']) {
				window.location = json['success'];
			}
		},
		/*error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}*/
	});

}
function j2storesaveinventory(variant){	
	var qty = jQuery('#quantity_'+variant).val();
	var availability = jQuery('#availability_'+variant).val();
	var manage_stock = jQuery('#manage_stock_'+variant).val();
	var search = jQuery('#search').val();
	var inventry_stock = jQuery('#adminForm input[name="inventry_stock"').val();
	jQuery.ajax({
		url: 'index.php?option=com_j2store&view=inventories&task=update_inventory&manage_stock='+manage_stock+'&availability='+availability+'&quantity='+qty+'&variant_id='+variant+'&search='+search+'&inventry_stock='+inventry_stock,
		type: 'post',
		dataType: 'json',		
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function() {
			
		},
		complete: function() {
			
		},
		success: function(json) {
			jQuery('.text-danger, .text-success').remove();

			if (json['error']) {
				
			}

			if (json['success']) {		
				window.location = json['success']; 																														
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});	
}
</script>
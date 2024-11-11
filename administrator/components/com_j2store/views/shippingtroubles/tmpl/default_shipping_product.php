<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$row_class = 'row';
$col_class = 'col-md-';
$info_class = $platform->getLabel('info');
$warning_class = $platform->getLabel('warning');
$success_class = $platform->getLabel('success');
$danger_class = $platform->getLabel('danger');

HTMLHelper::_('bootstrap.offcanvas', '[data-bs-toggle="offcanvas"]');
HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'left']);


$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $info_class = 'label label-info';
    $warning_class = 'label label-warning';
    $success_class = 'label label-success';
}

$search = htmlspecialchars($this->state->search);

?>
<div class="<?php echo $row_class; ?>">
<?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="<?php echo $col_class; ?>2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="<?php echo $col_class; ?>6">
 <?php else : ?> 
	<div class="j2store">
  <?php endif;?>
		<?php include 'default_steps.php';?>



  <form action="index.php" method="post" name="adminForm" id="adminForm">


      <div class="btn-toolbar w-100 justify-content-end mb-3">
          <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
              <div class="input-group w-100 me-lg-2">
	              <?php echo  J2Html::text('search',$search,array('id'=>'search' ,'class'=>'form-control j2store-product-filters','placeholder'=>Text::_('J2STORE_FILTER_SEARCH')));?>
                  <span class="filter-search-bar__label visually-hidden">
                      <label id="search-lbl" for="search"><?php echo Text::_('J2STORE_FILTER_SEARCH');?></label>
                  </span>
	              <?php echo J2Html::buttontype('go','<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>' ,array('class'=>'btn btn-primary','onclick'=>'this.form.submit();'));?>
	              <?php echo J2Html::buttontype('reset',Text::_( 'JCLEAR' ),array('id'=>'reset-filter-search','class'=>'btn btn-primary',"onclick"=>"document.getElementById('search').value = ''; this.form.submit();")); ?>
              </div>
          </div>
          <div class="ordering-select d-flex gap-2 ms-lg-2 flex-grow-1 flex-lg-grow-0">
	          <?php echo $this->pagination->getLimitBox();?>
          </div>
      </div>

  		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','shippingtroubles');?>
		<?php echo J2Html::hidden('layout','default_shipping_product');?>
		<?php echo J2Html::hidden('task','browse',array('id'=>'task'));?>
		<?php echo J2Html::hidden('boxchecked','0');?>
		<?php echo J2Html::hidden('filter_order',$this->state->filter_order);?>
		<?php echo J2Html::hidden('filter_order_Dir',$this->state->filter_order_Dir);?>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
		<div class="j2store-product-filters">
			<div class="j2store-alert-box" style="display:none;"></div>
		</div>
		<?php if($this->shipping_available):?>
          <div class="table-responsive-md">
              <table class="table itemList" id="j2storeList">
				<thead>
					<tr>
                        <th scope="col" class="text-center d-none d-md-table-cell"><?php  echo HTMLHelper::_('grid.sort',  'J2STORE_PRODUCT_ID', 'j2store_product_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
                        <th scope="col" style="min-width:100px" class="title"><?php echo Text::_('J2STORE_PRODUCT_NAME');?></th>
						<th scope="col"><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_ENABLED');?></th>
						<th scope="col"><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_DIMENSION');?></th>
						<th scope="col"><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_WEIGHT');?></th>
						<th scope="col"><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_CLASS');?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->products as $i=>$product):
                        $product_helper = J2Store::product();
                        $product_helper->setId($product->j2store_product_id);
                        $product_data = $product_helper->getProduct();
					?>
						<tr>
							<td class="text-center"><?php echo $product->j2store_product_id;?></td>
							<td>
                                <div class="d-block d-lg-flex">
                                    <div class="flex-grow-1">
                                        <div>
                                            <a href="<?php echo $product_data->product_edit_url;?>" title="<?php echo $product_data->product_name;?>"><?php echo $product_data->product_name;?></a>
                                        </div>
                                        <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_TYPE')?>:<b class="ms-2 text-capitalize"><?php echo $product_data->product_type; ?></b></div>
	                                    <?php if($product_data->product_type !='variable' || $product_data->product_type !='flexivariable' || $product_data->product_type !='advancedvariable'):?>
                                            <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_SKU')?>:<b class="ms-2 text-capitalize"><?php echo $product->sku; ?></b></div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </td>
							<?php if(($product_data->product_type !='variable') || ($product_data->product_type !='flexivariable') || ($product_data->product_type !='advancedvariable')):?>
                                <td>
                                    <?php if($product->shipping):?>
                                        <span class="badge text-bg-<?php echo $success_class ?>"> <?php echo Text::_('JENABLED'); ?> </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-<?php echo $danger_class ?>"> <?php echo Text::_('JDISABLED'); ?> </span>
                                    <?php endif; ?>
                                </td>
							<td>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_LENGTH')?>:
                                    <?php if($product->length < 0.1):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
	                                <?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
	                                <?php endif;?>
                                </div>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_WIDTH')?>:
									<?php if($product->width < 0.1):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
									<?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
									<?php endif;?>
                                </div>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_HEIGHT')?>:
									<?php if($product->height < 0.1):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
									<?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
									<?php endif;?>
                                </div>
							</td>
							<td>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_WEIGHT')?>:
									<?php if($product->weight < 0.1):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
									<?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
									<?php endif;?>
                                </div>
							</td>
                            <td>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_WEIGHT_CLASS')?>:
                                    <?php if($product->weight_class_id == 0):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
                                    <?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
                                    <?php endif;?>
                                </div>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_LENGTH_CLASS')?>:
		                            <?php if($product->length_class_id == 0):?>
                                        <span class="text-danger fw-bold"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </span>
		                            <?php else:?>
                                        <span class="text-success fw-bold text-uppercase"> <?php echo Text::_('J2STORE_OK'); ?> </span>
		                            <?php endif;?>
                                </div>
                            </td>
							<?php else:?>
							<td colspan="4">
								<?php echo Text::_('J2STORE_HAS_VARIANTS'); ?>
								<button type="button" class="btn btn-small btn-warning"
										id="showvariantbtn-<?php echo $product->j2store_product_id;?>"
										href="javascript:void(0);"
										onclick="jQuery('#hide-icon-<?php echo $product->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $product->j2store_product_id;?>').toggle('click');jQuery('#variantListTable-<?php echo $product->j2store_product_id;?>').toggle('click');">
									<?php echo Text::_('J2STORE_OPEN_CLOSE'); ?>
									<i id="show-icon-<?php echo $product->j2store_product_id;?>"
									   class="icon icon-plus"></i> <i
										id="hide-icon-<?php echo $product->j2store_product_id;?>"
										class="icon icon-minus" style="display: none;"></i>
								</button>
								<table id="variantListTable-<?php echo $product->j2store_product_id;?>"
									   class="table table-condensed table-bordered hide">
									<thead>
									<th><?php echo Text::_('J2STORE_VARIANT_NAME'); ?></th>
									<th><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_ENABLED'); ?></th>
									<th><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_DIMENSION'); ?></th>
									<th><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_WEIGHT'); ?></th>
									<th><?php echo Text::_('J2STORE_PRODUCT_SHIPPING_CLASS'); ?></th>
									</thead>
								<tbody>
								<?php
								$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
								$variant_model->setState('product_type', $product->product_type);
								$variants = $variant_model->product_id($product->j2store_product_id)
									->is_master(0)
									->getList();
								if(isset($variants) && count($variants)):
									?>

										<?php
									foreach($variants as $variant):
										?>
										<tr>
											<td><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></td>
										<td>
											<?php if($variant->shipping):?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_ENABLED'); ?> </label>
											<?php else: ?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_DISABLED'); ?> </label>
											<?php endif; ?>
										</td>
										<td>
											<?php echo Text::_('J2STORE_LENGTH').":";
											if($variant->length < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
											<?php echo Text::_('J2STORE_WIDTH').":";
											if($variant->width < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
											<?php echo Text::_('J2STORE_HEIGHT').":";
											if($variant->height < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
										</td>
										<td><?php echo Text::_('J2STORE_PRODUCT_WEIGHT').":";
											if($variant->weight < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
										</td>
										<td>
											<?php echo Text::_('J2STORE_PRODUCT_WEIGHT_CLASS').":";
											if($variant->weight_class_id == 0):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
											<?php echo Text::_('J2STORE_PRODUCT_LENGTH_CLASS').":";
											if($variant->length_class_id == 0):?>
												<label class="<?php echo $warning_class ?>"> <?php echo Text::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br>
										</td>
										</tr>
									<?php endforeach;?>
								<?php endif;?>
								</table>
							</td>
							<?php endif;?>
						</tr>
					<?php endforeach;?>
				</tbody>
			</table>
            </div>
            <?php echo $this->pagination->getListFooter(); ?>
		<?php else:?>
		<div class="alert alert-message"><?php echo Text::sprintf('J2STORE_SHIPPING_TROUBLESHOOT_NOTE_MESSAGE','index.php?option=com_j2store&view=shippings',J2Store::buildHelpLink('support/user-guide/standard-shipping.html', 'shipping'));?></div>
		<?php endif;?>
  </form>
  </div>
        <div class="text-center mt-3">
            <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping'); ?>">
                <span class="fas fa-solid fa-arrow-left me-2"></span><?php echo Text::_('JPREV');?>
            </a>
        </div>
  
            <?php if (!empty($sidebar)): ?>
         </div>
            <?php else: ?>
        </div>
    <?php endif; ?>
</div>

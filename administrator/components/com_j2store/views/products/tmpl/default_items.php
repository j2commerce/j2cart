<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$this->params = J2Store::config();
$label_class = $platform->getLabel();
$info_class = $platform->getLabel('info');
$warning_class = $platform->getLabel('warning');
$success_class = $platform->getLabel('success');
$danger_class = $platform->getLabel('danger');

HTMLHelper::_('bootstrap.offcanvas', '[data-bs-toggle="offcanvas"]');
HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'left']);


$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

    ?>
<table class="table itemList" id="productList">
    <caption class="visually-hidden">
		<?php echo Text::_('J2STORE_PRODUCTS'); ?>,
        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
    </caption>
	<thead>
		<tr>
			<td class="w-1 text-center"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></td>
			<th scope="col" class="text-center d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort', 'J2STORE_PRODUCT_ID', 'j2store_product_id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
            </th>
            <th scope="col" style="min-width:100px" class="title">
                <?php echo Text::_('J2STORE_PRODUCT_NAME'); ?>
            </th>

			<th scope="col" class="d-none d-sm-table-cell"><?php echo Text::_('J2STORE_PRODUCT_SKU'); ?></th>
			<th scope="col" class="d-none d-lg-table-cell"><?php echo Text::_('J2STORE_PRODUCT_PRICE'); ?></th>
			<th scope="col" class="text-center d-none d-xxl-table-cell"><?php  echo Text::_('J2STORE_SHIPPING'); ?></th>
            <?php if($this->params->get('enable_inventory', 0)):?>
                <th scope="col" class="d-none d-xxl-table-cell"><?php  echo Text::_('J2STORE_CURRENT_STOCK'); ?></th>
            <?php endif;?>
            <th scope="col" class="d-none d-xxl-table-cell"><?php echo HTMLHelper::_('grid.sort',  'J2STORE_SOURCE', 'product_source', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
			<th scope="col" class="text-center d-none d-xxl-table-cell"><?php  echo HTMLHelper::_('grid.sort',  'J2STORE_SOURCE_ID', 'product_source_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
		</tr>
	</thead>
	<tbody>
        <?php if($this->products && !empty($this->products)):
            $r = 0;
            foreach($this->products as $i => $item):
                $checked = HTMLHelper::_('grid.id', $i, $item->j2store_product_id );
                $r++;
            ?>
            <tr class="row<?php echo $r;?>">
                <td><?php echo $checked; ?></td>
                <td class="text-center d-none d-md-table-cell"><?php echo $item->j2store_product_id;?></td>
                <td><?php
                    $thumbimage='';
                    $platform = J2Store::platform();
                    $thumbimage = $platform->getImagePath($item->thumb_image);
                   ?>
                    <div class="d-block d-lg-flex">
                        <?php if(!empty($thumbimage )): ?>
                            <div class="flex-shrink-0">
                                <a href="<?php echo $item->product_edit_url;?>" class="d-none d-lg-inline-block">
                                    <img src="<?php echo $thumbimage;?>" class="img-fluid j2store-product-thumb-image" alt="<?php echo $this->escape($item->product_name);?>">
                                </a>
                            </div>
                        <?php endif;?>
                        <div class="flex-grow-1 ms-lg-3 mt-2 mt-lg-0">
                            <div>
                                <a href="<?php echo $item->product_edit_url;?>" title="<?php echo $this->escape($item->product_name);?>"><?php echo $this->escape($item->product_name);?></a>
                            </div>
                            <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_TYPE')?>:<b class="ms-2 text-capitalize"><?php echo $item->product_type; ?></b></div>
                            <div class="small text-capitalize"><?php echo Text::_('J2STORE_PRODUCT_VISIBILITY')?>:<b class="ms-2 text-<?php echo $item->visibility ? 'success':'danger'; ?>"><?php echo $item->visibility ? Text::_('JYES'):Text::_('JNO'); ?></b></div>
                            <div class="small text-capitalize"><?php echo Text::_('J2STORE_ARTICLE_STATUS')?>:
                                <?php $state_array = array (
                                    '-2' => array('danger', 'JTRASHED'),
                                    '0' => array('danger', 'JUNPUBLISHED'),
                                    '1' => array('success', 'JPUBLISHED')
                                );
                                ?>
                                <b class="ms-2 text-capitalize text-<?php echo $state_array[$item->source->state][0]; ?>"><?php echo Text::_($state_array[$item->source->state][1]);?></b>
                            </div>
                            <?php if($item->taxprofile_id):?>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_TAXPROFILE')?>:<b class="ms-2 text-capitalize"><?php echo $item->taxprofile_name; ?></b></div>
                            <?php else: ?>
                                <div class="small text-capitalize"><?php echo Text::_('J2STORE_TAXPROFILE')?>:<b class="ms-2 text-capitalize text-secondary"><?php echo Text::_('J2STORE_NOT_TAXABLE'); ?></b></div>
                            <?php endif; ?>

                        </div>
                    </div>
                </td>

                <?php if(!in_array($item->product_type,J2Store::product()->getVariableProductTypes())):?>
                    <td class="small d-none d-sm-table-cell"><?php echo $this->escape($item->sku); ?></td>
                    <td class="small d-none d-lg-table-cell"><?php echo J2store::currency()->format($item->price); ?></td>
                    <td class="text-center d-none d-xxl-table-cell">
                        <?php if($item->shipping):?>
                            <label class="<?php echo $success_class ?>"> <?php echo Text::_('J2STORE_ENABLED'); ?> </label>
                        <?php else: ?>
                            <label class="<?php echo $danger_class ?>"> <?php echo Text::_('J2STORE_DISABLED'); ?> </label>
                        <?php endif; ?>
                    </td>
                    <?php if($this->params->get('enable_inventory')):?>
                        <td class="small d-none d-xxl-table-cell">
                            <?php if($item->manage_stock == 1): ?>
                                <?php echo $item->quantity; ?>
                            <?php else : ?>
                                <?php echo Text::_('J2STORE_NO_STOCK_MANAGEMENT'); ?>
                            <?php endif; ?>
                        </td>
                    <?php endif;?>
                <?php else:?>
                    <?php $enable_inventory = $this->params->get('enable_inventory'); ?>
                    <?php $colspan = (isset($enable_inventory)) && !empty($enable_inventory) ? 4 : 3 ; ?>
                    <td class="d-none d-sm-table-cell">
                        <?php if(in_array($item->product_type,J2Store::product()->getVariableProductTypes())):?>
                            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasVariant<?php echo $item->j2store_product_id;?>" aria-controls="offcanvasVariant<?php echo $item->j2store_product_id;?>">
                                <?php echo Text::_('J2STORE_PRODUCT_VIEW_ALL_VARIANTS');?>
                            </button>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasVariant<?php echo $item->j2store_product_id;?>" aria-labelledby="offcanvasVariant<?php echo $item->j2store_product_id;?>Label">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasVariant<?php echo $item->j2store_product_id;?>Label"><?php echo $this->escape($item->product_name).' '.Text::_('J2STORE_PRODUCT_TAB_VARIANTS');?> </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="small">
                                            <i class="fa-solid fa-box text-danger"></i> = <?php echo Text::_('J2STORE_PRODUCT_SHIPPING_ENABLED_NO');?>
                                        </div>
                                        <div class="small">
                                            <i class="fa-solid fa-box text-success"></i> = <?php echo Text::_('J2STORE_PRODUCT_SHIPPING_ENABLED_YES');?>
                                        </div>
                                    </div>
                                    <?php
                                    $variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
                                    $variant_model->setState('product_type', $item->product_type);
                                    $variants = $variant_model->product_id($item->j2store_product_id)->is_master(0)->getList();
                                    if(isset($variants) && count($variants)):
                                        foreach($variants as $variant):?>
                                            <div class="list-group">
                                                <div class="list-group-item mb-1">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1"><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></h5>
                                                        <?php echo (isset($variant->shipping) && ($variant->shipping)) ? '<i class="fa-solid fa-box text-success"></i>' : '<i class="fa-solid fa-box text-danger"></i>'?>
                                                    </div>
                                                    <div class="small text-capitalize"><?php echo Text::_('J2STORE_VARIANT_PRICE')?>:<b class="ms-2 text-capitalize"><?php echo J2store::currency()->format($variant->price); ?></b></div>
                                                    <div class="small text-capitalize"><?php echo Text::_('J2STORE_VARIANT_SKU')?>:<b class="ms-2 text-capitalize"><?php echo $variant->sku; ?></b></div>
                                                    <div class="small text-capitalize"><?php echo Text::_('J2STORE_CURRENT_STOCK')?>:<b class="ms-2 text-capitalize"><?php echo $variant->quantity;?></b></div>
                                                </div>
                                            </div>
                                        <?php endforeach;?>
                                    <?php else:?>
                                        <div class="list-group-item mb-1">
                                            <h5 class="mb-0 text-center"><?php echo Text::_('J2STORE_NO_ITEMS_FOUND')?></h5>
                                        </div>
                                    <?php endif;?>
                                </div>
                            </div>
                        <?php endif;?>
                    </td>

                    <td class="small d-none d-lg-table-cell"><?php echo J2store::currency()->format($item->price); ?></td>
                    <td class="text-center d-none d-xxl-table-cell">
	                <?php if($this->params->get('enable_inventory')):?>
                        <td class="small d-none d-xxl-table-cell"></td>
	                <?php endif;?>
                <?php endif;?>
                <td class="small d-none d-xxl-table-cell">
                    <?php echo $item->product_source;?>
                </td>
                <td class="text-center d-none d-xxl-table-cell">
                    <?php echo $item->product_source_id;?>
                </td>
            </tr>
        <?php endforeach;?>
    <?php else:?>
        <tr>
            <td colspan="10"><?php  echo Text::_('J2STORE_NO_ITEMS_FOUND');?></td>
        </tr>
    <?php endif;?>
</tbody>
</table>
<?php  echo $this->pagination->getListFooter(); ?>

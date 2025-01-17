<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_flexivariable
 *
 * @copyright Copyright (C) 2018 J2Store. All rights reserved.
 * @copyright Copyright (C) 2024 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php';
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
$product_type_class = 'badge bg-success';
$btn_class = 'btn-sm';
$star_icon = 'far fa-regular fa-star';

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$style='.variant-item .variant-button{color:inherit;position: relative;}.variant-item .variant-button:focus,.variant-item .variant-button:active,.variant-button:not(.collapsed){box-shadow:none;background-color:transparent;}.variant-item .variant-button:hover,.variant-item .variant-button:focus,.variant-button:not(.collapsed){color:var(--accordion-active-color);}.variant-item .variant-button:after{width:1rem;height:1rem;background-size:1rem;margin-left: auto;margin-right:1rem;color:var(--accordion-active-color);position: absolute;left: 0.5rem;}.variant-item .control-group .control-label{width:160px;font-size: 0.825rem;font-weight: 500;}.j2commerce-variant-general .input-group>.input-group-text{border-radius: var(--border-radius-sm);padding: .25rem .5rem;font-size: .8rem;}.j2commerce-variant-general .input-group>.form-control{border-radius: var(--border-radius-sm);padding: .25rem .5rem;font-size: .8rem;}';
$wa->addInlineStyle($style, [], []);
$enable_inventory = J2Store::config()->get ( 'enable_inventory', 1 );

$variant_list = isset($vars->product->variants) ? $vars->product->variants : new stdClass();
$this->weights = isset($vars->product->weights) ? $vars->product->weights : new stdClass();
$this->lengths = isset($vars->product->lengths) ? $vars->product->lengths : new stdClass();
$this->variant_pagination = isset($vars->product->variant_pagination) ? $vars->product->variant_pagination : new stdClass();


?>

<div class="j2store-flexivariant-settings">
    <div class="accordion" id="accordion">
        <?php if(isset($variant_list)): ?>
        <?php $this->i = 0; ?>
        <?php $this->canChange = 1; ?>
            <?php foreach ($variant_list as $variant): ?>
                <?php $this->variant = $variant;
                if ($variant->is_master == 1) {
                    continue;
                }
                $prefix = $vars->form_prefix.'[variable]['.$this->variant->j2store_variant_id.']';
                $param_data = $platform->getRegistry($variant->params);
                $variant_main_image = $param_data->get('variant_main_image','');
                $is_main_as_thum = $param_data->get('is_main_as_thum',0);
                $variant_names = $this->escape(J2Store::product()->getVariantNamesByCSV($this->variant->variant_name));
                $parts = preg_split('/,(?!\d{3})/', $variant_names);
                $boldParts = array_map(function($part) {
                    return "<b>" . trim($part) . "</b>";
                }, $parts);
                $variantNames = implode(' - ', $boldParts);


                ?>
                <div class="variant-item border mb-3 rounded-3 px-3 py-2 text-subdued" data-variant-id="<?php echo $this->variant->j2store_variant_id;?>">
                    <div class="accordion-header d-flex align-items-center justify-content-start">
                        <?php echo J2Html::hidden($prefix.'[isdefault_variant]',  (isset($this->variant->isdefault_variant))?$this->variant->isdefault_variant : '',array('class'=>'input','id' => 'isdefault_'.$this->variant->j2store_variant_id)); ?>
                        <input id="cid<?php echo $this->variant->j2store_variant_id;?>" class="me-2" type="checkbox" name="vid[]" value="<?php echo $this->variant->j2store_variant_id;?>" />
                        <button class="accordion-button variant-button collapsed p-0 small ps-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $this->variant->j2store_variant_id;?>" aria-expanded="false" aria-controls="collapse<?php echo $this->variant->j2store_variant_id;?>">
                            <span class="variant__id fw-bold me-1 ms-4">(#<?php echo $this->variant->j2store_variant_id;?>)</span>
                            <?php echo $variantNames; ?>
                            <?php if($this->variant->sku):?><span class="variant__sku ms-2">(<?php echo $this->variant->sku;?>)</span><?php endif;?>
                        </button>
                        <?php if( $this->variant->isdefault_variant):?>
                            <a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn hasTooltip <?php echo $btn_class; ?> me-2" title="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_UNSET_DEFAULT');?>" onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id;?>,'unsetDefault',<?php echo $this->variant->product_id;?>)" href="javascript:void(0);" data-original-title="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_UNSET_DEFAULT');?>">
                                <span class="icon-featured"></span>
                            </a>
                        <?php else:?>
                            <a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn hasTooltip <?php echo $btn_class; ?> me-2" title="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_SET_DEFAULT');?>" onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id;?>,'setDefault',<?php echo $this->variant->product_id;?>)" href="javascript:void(0);" data-original-title="<?php echo Text::_('J2STORE_PRODUCT_VARIANT_SET_DEFAULT');?>">
                                <span class="<?php echo $star_icon; ?>"></span>
                            </a>
                        <?php endif;?>
                        <a class="btn btn-danger <?php echo $btn_class; ?>" onclick="deleteVariant(<?php echo $this->variant->j2store_variant_id;?>)" href="javascript:void(0);">
                            <span class="icon icon-trash"></span>
                        </a>
                    </div>
                    <div id="collapse<?php echo $this->variant->j2store_variant_id;?>" class="collapse">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-lg-6 j2commerce-variant-general">
                                            <fieldset class="options-form px-3">
                                                <legend class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_TAB_GENERAL');?></legend>
                                                <?php echo J2Html::hidden($prefix.'[j2store_variant_id]', $this->variant->j2store_variant_id,array('class'=>'input-small','id'=>'variant_'.$this->variant->j2store_variant_id)); ?>

                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_SKU'), 'sku'); ?></div>
                                                    <div class="controls">
                                                        <?php echo J2Html::text($prefix.'[sku]', $this->variant->sku,array('class'=>'form-control form-control-sm','id'=>'sku_'.$this->variant->j2store_variant_id)); ?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_UPC'), 'upc'); ?></div>
                                                    <div class="controls">
                                                        <?php echo J2Html::text($prefix.'[upc]', $this->variant->upc,array('class'=>'form-control form-control-sm','id'=>'upc_'.$this->variant->j2store_variant_id)); ?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_REGULAR_PRICE'), 'price'); ?></div>
                                                    <div class="controls">
                                                        <?php echo J2Html::price($prefix.'[price]', $this->variant->price,array('class'=>'form-control form-control-sm','id'=>'price_'.$this->variant->j2store_variant_id)); ?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_SET_ADVANCED_PRICING'), 'sale_price'); ?></div>
                                                    <div class="controls">
                                                        <?php
                                                        $base_path = rtrim(Uri::root(),'/').'/administrator';
                                                        $url = $base_path."/index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$this->variant->j2store_variant_id."&layout=productpricing&tmpl=component";?>
                                                        <?php echo J2StorePopup::popup($url , Text::_( "J2STORE_PRODUCT_SET_PRICES" ), array('class'=>'btn btn-success btn-sm'));?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_PRICING_CALCULATOR'), 'pricing_calculator'); ?></div>
                                                    <div class="controls">
                                                        <?php
                                                        echo J2Html::select()->clearState()
                                                            ->type('genericlist')
                                                            ->name($prefix.'[pricing_calculator]')
                                                            ->value($this->variant->pricing_calculator)
                                                            ->attribs(array('id' =>'pricing_calculator_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                            ->setPlaceHolders(J2Store::product()->getPricingCalculators())
                                                            ->getHtml();
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php echo J2Store::plugin()->eventWithHtml('AfterDisplayVariableProductForm',array(&$this->variant,$prefix));?>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-6 j2commerce-variant-shipping">
                                            <fieldset class="options-form px-3">
                                                <legend class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_TAB_SHIPPING');?></legend>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_ENABLE_SHIPPING'), 'shipping'); ?></div>
                                                    <div class="controls">
                                                        <?php
                                                        echo J2Html::select()->clearState()
                                                            ->type('genericlist')
                                                            ->name($prefix.'[shipping]')
                                                            ->value($this->variant->shipping)
                                                            ->attribs(array('id' =>'shipping_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                            ->setPlaceHolders(array(1 => Text::_('J2STORE_YES'),0 => Text::_('J2STORE_NO')))
                                                            ->getHtml();
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_DIMENSIONS'), 'dimensions'); ?></div>
                                                    <div class="controls">
                                                        <div class="input-group">
                                                            <?php echo J2Html::text($prefix.'[length]',$this->variant->length,array('class'=>'form-control form-control-sm'));?>
                                                            <?php echo J2Html::text($prefix.'[width]',$this->variant->width,array('class'=>'form-control form-control-sm'));?>
                                                            <?php echo J2Html::text($prefix.'[height]',$this->variant->height,array('class'=>'form-control form-control-sm'));?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'length_class'); ?></div>
                                                    <div class="controls">
                                                        <?php $default_length = empty($this->variant->length_class_id) ? J2Store::config()->get('config_length_class_id') : $this->variant->length_class_id;
                                                        echo J2Html::select()->clearState()
                                                            ->type('genericlist')
                                                            ->name($prefix.'[length_class_id]')
                                                            ->value($default_length)
                                                            ->attribs(array('id' =>'length_class_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                            ->setPlaceHolders($this->lengths)
                                                            ->getHtml();
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_WEIGHT'), 'weight'); ?></div>
                                                    <div class="controls">
                                                        <?php echo J2Html::text($prefix.'[weight]',$this->variant->weight ,array('class'=>'form-control form-control-sm'));?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'weight_class'); ?></div>
                                                    <div class="controls">
                                                        <?php $default_weight = empty($this->variant->weight_class_id) ? J2Store::config()->get('config_weight_class_id') : $this->variant->weight_class_id;
                                                        echo J2Html::select()->clearState()
                                                            ->type('genericlist')
                                                            ->name($prefix.'[weight_class_id]')
                                                            ->value($default_weight)
                                                            ->attribs(array('id' =>'weight_class_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                            ->setPlaceHolders($this->weights)
                                                            ->getHtml();
                                                        ?>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-lg-6 j2commerce-variant-main-image">
                                            <fieldset class="options-form px-3">
                                                <legend class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_MAIN_IMAGE');?></legend>
                                                <div class="control-group mb-0">
                                                    <div class="controls">
                                                        <?php echo J2Html::media($prefix.'[params][variant_main_image]' ,$variant_main_image,array('id'=>'variant_main_image'.$this->variant->j2store_variant_id ,'image_id'=>'input-variant-main-image'.$this->variant->j2store_variant_id));?>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <div class="controls">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="variant_thum_<?php echo $this->variant->j2store_variant_id;?>" name="<?php echo $prefix.'[params][is_main_as_thum]';?>" <?php echo (isset($is_main_as_thum) && $is_main_as_thum) ? 'checked=""' : '';?> value="1" />
                                                            <label class="form-check-label small" for="variant_thum_<?php echo $this->variant->j2store_variant_id;?>"><?php echo Text::_('J2STORE_PRODUCT_IS_MAIN_IMAGE_AS_THUM'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 j2commerce-variant-inventory">
                                    <fieldset class="options-form px-3">
                                        <legend class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_TAB_INVENTORY');?></legend>
                                        <?php if(J2Store::isPro() == 1) : ?>
                                            <?php if($enable_inventory == 0):?>
                                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                <span class="fas fa-solid fa-exclamation-triangle flex-shrink-0 me-2"></span>
                                                <div><?php echo Text::sprintf('J2STORE_PRODUCT_INVENTORY_WARNING',Route::_('index.php?option=com_j2store&view=configuration'));?></div>
                                            </div>
                                        <?php endif;?>
                                            <div class="control-group">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MANAGE_STOCK'), 'manage_stock'); ?></div>
                                                <div class="controls">
                                                    <?php echo J2Html::select()->clearState()
                                                        ->type('genericlist')
                                                        ->name($prefix.'[manage_stock]')
                                                        ->value($this->variant->manage_stock)
                                                        ->attribs(array('id' =>'manage_stock_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                        ->setPlaceHolders(array(0 => Text::_('J2STORE_NO'), 1 => Text::_('J2STORE_YES')))
                                                        ->getHtml();
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_QUANTITY'), 'quantity');?></div>
                                                <div class="controls">
                                                    <?php echo J2Html::hidden($prefix.'[quantity][j2store_productquantity_id]', $this->variant->j2store_productquantity_id,array('class'=>'input','id' => 'productquantity_'.$this->variant->j2store_variant_id)); ?>
                                                    <?php echo J2Html::text($prefix.'[quantity][quantity]', $this->variant->quantity,array('class'=>'form-control form-control-sm' ,'id' => 'quantity_'.$this->variant->j2store_variant_id)); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_ALLOW_BACK_ORDERS'), 'allow_backorder');?></div>
                                                <div class="controls">
                                                    <?php echo  J2Html::select()->clearState()
                                                        ->type('genericlist')
                                                        ->name($prefix.'[allow_backorder]')
                                                        ->attribs(array('id' =>'allowbackorder_'.$this->variant->j2store_variant_id,'class'=>'form-select form-select-sm'))
                                                        ->value($this->variant->allow_backorder)
                                                        ->setPlaceHolders(
                                                            array('0' => Text::_('COM_J2STORE_DO_NOT_ALLOW_BACKORDER'),
                                                                  '1' => Text::_('COM_J2STORE_DO_ALLOW_BACKORDER'),
                                                                  '2' => Text::_('COM_J2STORE_ALLOW_BUT_NOTIFY_CUSTOMER')
                                                            ))
                                                        ->getHtml(); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_STOCK_STATUS'), 'availability'); ?></div>
                                                <div class="controls">
                                                    <?php echo J2Html::select()->clearState()
                                                        ->type('genericlist')
                                                        ->name($prefix.'[availability]')
                                                        ->attribs(array('class'=>'form-select form-select-sm'))
                                                        ->value($this->variant->availability)
                                                        ->setPlaceHolders(
                                                            array('0' => Text::_('COM_J2STORE_PRODUCT_OUT_OF_STOCK') ,
                                                                  '1'=> Text::_('COM_J2STORE_PRODUCT_IN_STOCK'))
                                                        )
                                                        ->getHtml();
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="control-group mt-3 mb-0">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_NOTIFY_QUANTITY'), 'notify_qty'); ?></div>
                                                <div class="controls">
                                                    <?php
                                                    $attribs = (isset($this->variant->use_store_config_notify_qty) && !empty($this->variant->use_store_config_notify_qty)) ? array('id' =>'notify_qty_'.$this->variant->j2store_variant_id,'disabled'=>'','class'=>'form-control form-control-sm') : array('id' =>'notify_qty_'.$this->variant->j2store_variant_id,'class'=>'form-control form-control-sm');
                                                    echo J2Html::text($prefix.'[notify_qty]', $this->variant->notify_qty ,$attribs); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"></div>
                                                <div class="controls">
                                                    <div class="qty_restriction">
                                                        <div class="form-check form-switch">
                                                            <input id="variant_config_notify_qty_<?php echo $this->variant->j2store_variant_id;?>" type="checkbox" name="<?php echo $prefix.'[use_store_config_notify_qty]';?>" class="storeconfig form-check-input" role="switch" <?php echo (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? 'checked' : ''; ?> />
                                                            <label class="form-check-label small" for="variant_config_notify_qty_<?php echo $this->variant->j2store_variant_id;?>"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_QUANTITY_RESTRICTION'), 'quantity_restriction'); ?></div>
                                                <div class="controls">
                                                    <?php echo J2Html::select()->clearState()
                                                        ->type('genericlist')
                                                        ->name($prefix.'[quantity_restriction]')
                                                        ->value($this->variant->quantity_restriction)
                                                        ->attribs(array('id' =>'quantity_restriction_'.$this->variant->j2store_variant_id ,'class'=>'form-select form-select-sm'))
                                                        ->setPlaceHolders(array(1 => Text::_('J2STORE_YES'),0 => Text::_('J2STORE_NO')))
                                                        ->getHtml();
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="control-group mb-0">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MAX_SALE_QUANTITY'), 'max_sale_qty'); ?></div>
                                                <div class="controls">
                                                    <?php
                                                    $attribs = (isset($this->variant->use_store_config_max_sale_qty) && !empty($this->variant->use_store_config_max_sale_qty) ) ? array('id'=>'max_sale_qty_'.$this->variant->j2store_variant_id, 'disabled'=>'','class'=>'form-control form-control-sm'): array('id'=>'max_sale_qty_'.$this->variant->j2store_variant_id,'class'=>'form-control form-control-sm');
                                                    echo J2Html::text($prefix.'[max_sale_qty]', $this->variant->max_sale_qty,$attribs); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"></div>
                                                <div class="controls">
                                                    <div class="store_config_max_sale_qty">
                                                        <div class="form-check form-switch">
                                                            <input id="store_config_max_sale_qty_<?php echo $this->variant->j2store_variant_id;?>" type="checkbox" name="<?php echo $prefix.'[use_store_config_max_sale_qty]';?>" class="storeconfig form-check-input" role="switch" <?php echo isset($this->variant->use_store_config_max_sale_qty) && !empty($this->variant->use_store_config_max_sale_qty)  ? 'checked=""' : '';?> />
                                                            <label class="form-check-label small" for="store_config_max_sale_qty_<?php echo $this->variant->j2store_variant_id;?>"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group mb-0">
                                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MIN_SALE_QUANTITY'), 'min_sale_qty'); ?></div>
                                                <div class="controls">
                                                    <?php
                                                    $attribs = (isset($this->variant->use_store_config_min_sale_qty) && !empty($this->variant->use_store_config_min_sale_qty)) ? array('id' =>'min_sale_qty','disabled'=>'','class'=>'form-control form-control-sm'): array('id'=>'min_sale_qty_'.$this->variant->j2store_variant_id,'class'=>'form-control form-control-sm');
                                                    echo J2Html::text($prefix.'[min_sale_qty]', $this->variant->min_sale_qty,$attribs); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="control-label"></div>
                                                <div class="controls">
                                                    <div class="store_config_min_sale_qty">
                                                        <div class="form-check form-switch">
                                                            <input id="store_config_min_sale_qty_<?php echo $this->variant->j2store_variant_id;?>" type="checkbox" name="<?php echo $prefix.'[use_store_config_min_sale_qty]';?>" class="storeconfig form-check-input" role="switch" <?php echo isset($this->variant->use_store_config_min_sale_qty) && !empty($this->variant->use_store_config_min_sale_qty)  ? 'checked=""': ''; ?> />
                                                            <label class="form-check-label small" for="store_config_min_sale_qty_<?php echo $this->variant->j2store_variant_id;?>"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <script type="text/javascript">
                                                document.addEventListener("DOMContentLoaded", function () {
                                                    // Variant Config Notify Quantity
                                                    var variantNotifyQtyCheckbox = document.getElementById("variant_config_notify_qty_<?php echo $this->variant->j2store_variant_id; ?>");
                                                    var notifyQtyInput = document.getElementById("notify_qty_<?php echo $this->variant->j2store_variant_id; ?>");

                                                    if (variantNotifyQtyCheckbox) {
                                                        variantNotifyQtyCheckbox.addEventListener("click", function () {
                                                            if (this.checked) {
                                                                this.checked = true;
                                                            } else {
                                                                this.checked = false;
                                                            }
                                                            notifyQtyInput.disabled = this.checked;
                                                        });
                                                    }

                                                    // Store Config Max Sale Quantity
                                                    var maxSaleQtyCheckbox = document.getElementById("store_config_max_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>");
                                                    var maxSaleQtyInput = document.getElementById("max_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>");

                                                    if (maxSaleQtyCheckbox) {
                                                        maxSaleQtyCheckbox.addEventListener("click", function () {
                                                            if (!this.checked) {
                                                                this.checked = false;
                                                            }
                                                            maxSaleQtyInput.disabled = this.checked;
                                                        });
                                                    }

                                                    // Store Config Min Sale Quantity
                                                    var minSaleQtyCheckbox = document.getElementById("store_config_min_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>");
                                                    var minSaleQtyInput = document.getElementById("min_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>");

                                                    if (minSaleQtyCheckbox) {
                                                        minSaleQtyCheckbox.addEventListener("click", function () {
                                                            if (!this.checked) {
                                                                this.checked = false;
                                                            }
                                                            minSaleQtyInput.disabled = this.checked;
                                                        });
                                                    }
                                                });
                                            </script>
                                        <?php else:?>
                                            <?php echo J2Html::pro(); ?>
                                        <?php endif;?>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $this->i++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?php echo Text::_('J2STORE_NO_RESULTS_FOUND'); ?>
        <?php endif; ?>
    </div>
</div>



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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$enable_inventory = J2Store::config()->get ( 'enable_inventory', 1 );
?>
<?php if(J2Store::isPro() == 1) : ?>
    <div class="j2store-product-inventory">
        <fieldset class="options-form">
            <legend><?php echo Text::_('J2STORE_PRODUCT_TAB_INVENTORY');?></legend>
            <?php if($enable_inventory == 0):?>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <span class="fas fa-solid fa-exclamation-triangle flex-shrink-0 me-2"></span>
                    <div><?php echo Text::sprintf('J2STORE_PRODUCT_INVENTORY_WARNING',Route::_('index.php?option=com_j2store&view=configuration'));?></div>
                </div>
            <?php endif;?>
            <div class="form-grid">
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MANAGE_STOCK'),'manage_stock'); ?></div>
		            <?php echo J2Html::radioBooleanList($this->form_prefix.'[manage_stock]', (isset($this->variant->manage_stock))?$this->variant->manage_stock:''); ?>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_QUANTITY'), 'quantity');?></div>
                    <div class="controls">
	                    <?php echo J2Html::hidden($this->form_prefix.'[quantity][j2store_productquantity_id]', (isset($this->variant->j2store_productquantity_id)) ? $this->variant->j2store_productquantity_id:'',array('class'=>'input')); ?>
	                    <?php echo J2Html::text($this->form_prefix.'[quantity][quantity]', (isset($this->variant->quantity))?$this->variant->quantity:'',array('class'=>'form-control','field_type'=>'integer')); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_ALLOW_BACK_ORDERS'), 'allow_backorder');?></div>
                    <div class="controls"><?php echo str_replace('<select', '<select class="form-select"', $this->allow_backorder); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_STOCK_STATUS'), 'availability'); ?></div>
                    <div class="controls"><?php echo str_replace('<select', '<select class="form-select"', $this->availability); ?></div>
                </div>
                <div class="control-group mb-0">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_NOTIFY_QUANTITY'), 'notify_qty'); ?></div>
                    <div class="controls">
                        <div class="input-group align-items-center">
                            <?php $attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id'=>'notify_qty' ,'disabled'=>'disabled','field_type'=>'integer','class'=>'form-control') :array('id'=>'notify_qty','field_type'=>'integer','class'=>'form-control');
	                    echo J2Html::text($this->form_prefix.'[notify_qty]',(isset($this->variant->notify_qty)) ? $this->variant->notify_qty: '' ,$attribs); ?>
                            <div class="form-check form-switch pt-0 qty_restriction ms-3">
                                <input class="form-check-input storeconfig" type="checkbox" role="switch" id="config_notify_qty" value="<?php echo $this->variant->use_store_config_notify_qty;?>" name="<?php echo $this->form_prefix; ?>[use_store_config_notify_qty]">
                                <label class="form-check-label" for="config_notify_qty"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_QUANTITY_RESTRICTION'), 'quantity_restriction'); ?></div>
		            <?php echo J2Html::radioBooleanList($this->form_prefix.'[quantity_restriction]',(isset($this->variant->quantity_restriction))? $this->variant->quantity_restriction : '' ); ?>
                </div>
                <div class="control-group mb-0">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MAX_SALE_QUANTITY'), 'max_sale_qty'); ?></div>
                    <div class="controls">
                        <div class="input-group align-items-center">
	                        <?php $attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id' =>'max_sale_qty','disabled'=>'','field_type'=>'integer','class'=>'form-control') : array('id' =>'max_sale_qty','field_type'=>'integer','class'=>'form-control');
	                        echo J2Html::text($this->form_prefix.'[max_sale_qty]',(isset($this->variant->max_sale_qty))?$this->variant->max_sale_qty:'' ,$attribs); ?>

                            <div class="form-check form-switch pt-0 qty_restriction ms-3">
                                <input class="form-check-input storeconfig" type="checkbox" role="switch" id="store_config_max_sale_qty" value="<?php echo $this->variant->use_store_config_max_sale_qty;?>" name="<?php echo $this->form_prefix; ?>[use_store_config_max_sale_qty]" <?php echo (isset($this->variant->use_store_config_max_sale_qty) && $this->variant->use_store_config_max_sale_qty) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="store_config_max_sale_qty"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group mb-0">
                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_MIN_SALE_QUANTITY'), 'min_sale_qty'); ?></div>
                    <div class="controls">
                        <div class="input-group align-items-center">
                        <?php $attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id'=>'min_sale_qty' ,'disabled'=>'','field_type'=>'integer','class'=>'form-control') :array('id'=>'min_sale_qty','field_type'=>'integer','class'=>'form-control');
                            echo J2Html::text($this->form_prefix.'[min_sale_qty]', (isset($this->variant->min_sale_qty))?$this->variant->min_sale_qty:'',$attribs); ?>
                            <div class="form-check form-switch pt-0 qty_restriction ms-3">
                                <input class="form-check-input storeconfig" type="checkbox" role="switch" id="store_config_min_sale_qty" value="<?php echo $this->variant->use_store_config_min_sale_qty;?>" name="<?php echo $this->form_prefix; ?>[use_store_config_min_sale_qty]" <?php echo (isset($this->variant->use_store_config_min_sale_qty) && $this->variant->use_store_config_min_sale_qty) ? 'checked' : ''; ?> />
                                <label class="form-check-label" for="store_config_min_sale_qty"><?php echo Text::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("config_notify_qty").addEventListener("click", function () {
                this.setAttribute("value", this.checked ? 1 : 0);
                document.getElementById("notify_qty").disabled = this.checked;
            });

            document.getElementById("store_config_max_sale_qty").addEventListener("click", function () {
                this.setAttribute("value", this.checked ? 1 : 0);
                document.getElementById("max_sale_qty").disabled = this.checked;
            });

            document.getElementById("store_config_min_sale_qty").addEventListener("click", function () {
                this.setAttribute("value", this.checked ? 1 : 0);
                document.getElementById("min_sale_qty").disabled = this.checked;
            });
        });
    </script>
    <?php else:?>
        <?php echo J2Html::pro(); ?>
    <?php endif;?>

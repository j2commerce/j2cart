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

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/input.php');

$platform = J2Store::platform();
$app = Factory::getApplication();
$option = $app->input->getString('option');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
$product_type_class = 'badge bg-success';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.Text::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$style = '.j2store-product-edit-form .input-group .form-check.form-switch .form-check-input{min-width:0;}';
$wa->addInlineStyle($style, [], []);
?>
<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if(pressbutton == 'article.cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
        }else if(pressbutton == 'article.apply') {
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                if(document.getElementById('submit_button') != null) {
                    document.getElementById('submit_button').onclick = function () {
                        this.disabled = true;
                    }
                }
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }else{
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }
    }
</script>

<div class="j2store">
    <div class="j2store-product-edit-form">
        <div class="<?php echo $row_class;?>">
            <div class="<?php echo $col_class;?><?php echo ($this->item->j2store_product_id) ?'4':'12'; ?> mb-4">
                <div class="card j2store-product-information">
                    <div class="card-header justify-content-between">
                        <h3 class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_INFORMATION'); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="control-group" id="j2store-product-enable">
                                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_TREAT_AS_PRODUCT'), 'enabled',array());?></div>
		                        <?php echo J2Html::radioBooleanList($this->form_prefix.'[enabled]', $this->item->enabled, array('id'=>'j2store-product-enabled-radio-group', 'class'=>'form-check form-check-inline','hide_label'=>true));?>
                            </div>
                            <div class="control-group" id="j2store-product-type">
		                        <?php if(!empty($this->item->product_type)): ?>
                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_TYPE'), 'product_type',array()); ?></div>
                                    <div class="controls">
                                        <span class="<?php echo $product_type_class;?>"><?php echo Text::_('J2STORE_PRODUCT_TYPE_'.strtoupper($this->item->product_type)) ?></span>
                                    </div>
			                        <?php echo J2Html::hidden($this->form_prefix.'[product_type]', $this->item->product_type); ?>
		                        <?php else: ?>
                                    <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_TYPE'), 'product_type',array()); ?></div>
                                    <div class="controls"><?php echo str_replace('<select', '<select class="form-select"', $this->product_types); ?></div>
		                        <?php endif; ?>
                            </div>
	                        <?php if(!$this->item->enabled): ?>
                                <!-- Show this only when this was not a product -->
		                        <?php if($option == 'com_content' && J2Store::platform()->isClient('administrator')): ?>
                                    <div class="control-group">
                                        <div class="controls">
                                            <input type="button" id="submit_button" onclick="Joomla.submitbutton('article.apply')" class="btn btn-primary" value="<?php echo Text::_('J2STORE_SAVE_AND_CONTINUE'); ?>" />
                                        </div>
                                    </div>
		                        <?php endif; ?>
	                        <?php endif; ?>

	                        <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
                                <div class="control-group">
                                    <div class="control-label"></div>
                                    <div class="controls">
                                        <div class="j2store-confirm-cont">
                                            <a data-fancybox data-src="#j2storeConfirmChange" type="button" class="btn btn-sm btn-outline-danger" ><?php echo  Text::_('J2STORE_CHANGE_PRODUCT_TYPE');?></a>
                                            <?php echo $this->loadTemplate('confirm_change'); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
                <div class="<?php echo $col_class;?>8 mb-4">
                    <div class="card j2store-product-shortcodes text-bg-primary text-white">
                        <div class="card-header justify-content-between fs-3">
                            <h3 class="mb-0 text-white"><?php echo Text::_('J2STORE_PRODUCT_ID'); ?>: <?php echo $this->item->j2store_product_id; ?></h3>
                            <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShortcodes" aria-expanded="false" aria-controls="collapseShortcodes"><?php echo Text::_('J2STORE_PRODUCT_VIEW_SHORTCODES'); ?></button>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-2 text-white fs-5"><?php echo Text::_('J2STORE_PLUGIN_SHORTCODE')?></h4>
                            <p class="shortcode">
                                {j2store}<?php echo $this->item->j2store_product_id; ?>|cart{/j2store}
                            </p>
                            <small>
		                        <?php echo Text::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT');?>
                            </small>
                            <div class="collapse additional-short-code" id="collapseShortcodes">
                                <h4 class="mb-2 text-white fs-5 mt-4"><?php echo Text::_('J2STORE_PLUGIN_SHORTCODE_ADDITIONAL')?></h4>
                                <p class="small">
		                            <?php echo Text::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT_ADDITIONAL');?> <b> {j2store}<?php echo $this->item->j2store_product_id; ?>|upsells|crosssells{/j2store}</b>
                                </p>
                                <p class="shortcode small">price|thumbnail|mainimage|mainadditional|upsells|crosssells</p>
                                <p class="small">
		                            <?php echo Text::_('J2STORE_PLUGIN_SHORTCODE_FOOTER_WARNING');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
        <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_product_id]'?>" value="<?php echo $this->item->j2store_product_id; ?>" />

        <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
            <div class="card j2store-product-shortcodes">
                <div class="card-header justify-content-between">
                    <h3 class="mb-0"><?php echo Text::_('J2STORE_PRODUCT_TYPE_'.strtoupper($this->item->product_type)); ?></h3>
                </div>
                <div class="card-body">
                    <?php echo $this->loadTemplate($this->item->product_type); ?>
                    <input type="hidden" name="<?php echo $this->form_prefix.'[product_type]'?>" value="<?php echo $this->item->product_type; ?>" />
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
    <script type="text/javascript">
        (function($) {
            $(document).on("click","#j2storeConfirmChange #changeTypeBtn", function(e) {
                $.ajax({
                    url :'index.php?option=com_j2store&view=products&task=changeProductType',
                    type: 'post',
                    data:{'product_id' :<?php echo $this->item->j2store_product_id; ?> ,'product_type' : '<?php echo $this->item->product_type; ?>' },
                    dataType: 'json',
                    beforeSend:function(){
                        $('#changeTypeBtn').html('<i class="icon-spin icon-refresh glyphicon glyphicon-refresh glyphicon-spin"></i> Changing type...');
                    },
                    success: function(json) {
                        if(json['success']){
                            location.reload();
                        }
                    }
                });

            });
            $(document).on("click","#j2storeConfirmChange #closeTypeBtn", function(e) {
                $.fancybox.close();
            });
        })(jQuery);
    </script>
<?php endif;?>
<script type="text/javascript">
    (function($) {
        $(document).ready(function() {
            $("div.j2store-tab-menu>div.list-group>a").click(function(e) {
                e.preventDefault();
                $(this).siblings('a.active').removeClass("active");
                $(this).addClass("active");
                var index = $(this).index();
                $("div.j2store-tab>div.j2store-tab-content").removeClass("active");
                $("div.j2store-tab>div.j2store-tab-content").eq(index).addClass("active");
            });
        });
    })(jQuery);

    (function($) {
        $('#j2store-product-enable').bind('change', function() {
            var enabled = $('#j2store-product-enable input[type=radio]:checked').val();
            if(enabled == 1) {
                $("#j2store-product-type").show();
            }else {
                $("#j2store-product-type").hide();
            }
        });

        $('#j2store-product-enable').trigger('change');

    })(jQuery);
</script>

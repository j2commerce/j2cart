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

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$script = 'document.addEventListener("DOMContentLoaded", function () {
        var checkAllShipping = document.getElementById("checkAllShipping");
        if (checkAllShipping) {
            checkAllShipping.addEventListener("click", function () {
                this.value = 0;
                if (this.checked === true) {
                    this.value = 1;
                }
                var shippingInput = document.getElementById("shippingInput");
                if (shippingInput) {
                    shippingInput.disabled = this.checked;
                }
            });
        }
    });';

$wa->addInlineScript($script, [], []);
?>
<div class="j2store-product-shipping">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_TAB_SHIPPING');?></legend>
        <div class="form-grid">
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_ENABLE_SHIPPING'), 'shipping'); ?></div>
				<?php echo J2Html::radioBooleanList($this->form_prefix.'[shipping]',(isset($this->variant->shipping)) ? $this->variant->shipping:''); ?>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_DIMENSIONS'), 'dimensions'); ?></div>
                <div class="controls">
                    <div class="input-group">
	                    <?php echo J2Html::text($this->form_prefix.'[length]',(isset($this->variant->length))?$this->variant->length:'',array('class'=>'form-control', 'placeholder'=>Text::_('J2STORE_LENGTH'),'field_type'=>'integer'));?>
	                    <?php echo J2Html::text($this->form_prefix.'[width]',(isset($this->variant->width)) ? $this->variant->width:'',array('class'=>'form-control', 'placeholder'=>Text::_('J2STORE_WIDTH'),'field_type'=>'integer'));?>
	                    <?php echo J2Html::text($this->form_prefix.'[height]',(isset($this->variant->height)) ? $this->variant->height : '',array('class'=>'form-control', 'placeholder'=>Text::_('J2STORE_HEIGHT'),'field_type'=>'integer'));?>
                    </div>
	            </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'length_class'); ?></div>
                <div class="controls">
			    <?php echo $this->lengths ;?>
		        </div>
		    </div>
	        <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_WEIGHT'), 'weight'); ?></div>
                <div class="controls">
	                <?php echo J2Html::text($this->form_prefix.'[weight]',(isset($this->variant->weight))?$this->variant->weight:'',array('class'=>'form-control','field_type'=>'integer'));?>
                </div>
	        </div>
            <div class="control-group">
                <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'weight_class'); ?></div>
                <div class="controls">
	                <?php echo $this->weights; ?>
                </div>
            </div>
        </div>
    </fieldset>
</div>
<!--<script type="text/javascript">
(function($){
	$("#checkAllShipping").click(function(){
		$(this).attr('value',0);
		if(this.checked == true){
			$(this).attr('value',1);
		}
		$("#shippingInput").attr('disabled',this.checked);
	});
})(j2store.jQuery);
</script>-->

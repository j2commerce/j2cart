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
?>
<style>
    .j2store-bs .modal{
        position: absolute;
    }
    .j2storeRegenerateVariant{
        margin-top:100px;
        -moz-border-radius: 0 0 8px 8px;
        -webkit-border-radius: 0 0 8px 8px;
        border-radius: 0 0 8px 8px;
        border-width: 0 8px 8px 8px;
        border:1px solid #000000;
    }

    .j2storeRegenerateVariant .modal-header{
        border:1px solid #faa732;
        background-color:#faa732;
    }
</style>
<div class="j2store-product-variants">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_VARIANT_OPTIONS');?></legend>
        <table id="attribute_options_table" class="table itemList align-middle j2store">
            <thead>
            <tr>
                <th><?php echo Text::_('J2STORE_VARIANT_OPTION');?></th>
                <th><?php echo Text::_('J2STORE_OPTION_ORDERING');?></th>
                <th><?php echo Text::_('J2STORE_REMOVE'); ?> </th>
            </tr>
            </thead>
            <tbody>
                <?php if(isset($this->item->product_options ) && !empty($this->item->product_options)):?>
                    <?php foreach($this->item->product_options as $poption ):?>
                        <tr id="pao_current_option_<?php echo $poption->j2store_productoption_id;?>">
                            <td>
                                <?php echo $this->escape($poption->option_name);?>
                                <?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
                                <?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
                                <small>(<?php  echo $this->escape($poption->option_unique_name);?>)</small>
                                <small><?php Text::_('J2STORE_OPTION_TYPE');?><?php echo Text::_('J2STORE_'.strtoupper($poption->type))?></small>
                            </td>
                            <td><?php echo J2Html::text($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][ordering]',$poption->ordering,array('id'=>'ordering' ,'class'=>'form-control form-control-sm'));?></td>
                            <td>
                                <span class="optionRemove" onClick="removePAOption(<?php echo $poption->j2store_productoption_id;?>,'<?php echo $this->item->product_type;?>')"><span class="icon icon-trash"></span></span>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php endif;?>
                <tr class="j2store_a_options">
                    <td colspan="2">
                        <div class="control-group mt-4">
                            <div class="control-label"><?php echo J2Html::label(Text::_('J2STORE_SEARCH_AND_ADD_VARIANT_OPTION')); ?></div>
                            <div class="controls">
                                <div class="input-group">
                                <select name="option_select_id" id="option_select_id" class="form-select">
                                    <?php foreach ($this->product_option_list as $option_list):?>
                                        <option value="<?php echo $option_list->j2store_option_id?>"><?php echo $this->escape($option_list->option_name) .' ('.$this->escape($option_list->option_unique_name).')';?></option>
                                    <?php endforeach; ?>
                                </select>
                                <a onclick="addOption()" class="btn btn-success"> <?php echo Text::_('J2STORE_ADD_OPTIONS')?></a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="alert alert-info d-flex align-items-center my-3" role="alert">
        <span class="fas fa-solid fa-exclamation-circle me-3"></span>
        <div><?php echo Text::_('J2STORE_FLEXIVARIANT_GENERATION_HELP_TEXT'); ?></div>
    </div>
</div>
<script type="text/javascript">
    function addOption() {
        (function ($) {
            var option_value = $('#option_select_id').val();
            var option_name = $('#option_select_id option[value='+option_value+']').html();
            $('<tr><td class=\"addedOption\">' + option_name+ '</td><td><input class=\"form-control form-control-sm\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ option_value+'][ordering]\" value=\"0\"></td><td><span class=\"optionRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\"><span class=\"icon icon-trash\"></span></span><input type=\"hidden\" value=\"' + option_value+ '\" name=\"<?php echo $this->form_prefix; ?>[item_options]['+ option_value+'][option_id]\" /><input type=\"hidden\" value="" name=\"<?php echo $this->form_prefix; ?>[item_options]['+ option_value+'][j2store_productoption_id]\" /></td></tr>').insertBefore('.j2store_a_options');
        })(j2store.jQuery);

    }
</script>

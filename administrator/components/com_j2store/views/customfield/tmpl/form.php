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
use Joomla\CMS\HTML\HTMLHelper;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
$row_class = 'row';
$col_class = 'col-md-';

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->addInlineScript("
    document.addEventListener('DOMContentLoaded', function() {
        // Add 'form-select' class to #fieldtype
        document.getElementById('fieldtype').classList.add('form-select');

        // Remove 'inputbox' and add 'form-select' to #field_optionszone_type
        var element = document.getElementById('field_optionszone_type');
        if (element) {
            element.classList.remove('inputbox');
            element.classList.add('form-select');
        }

        // Check for a select field inside the .default class and add 'form-select' to it
        var defaultSelect = document.querySelectorAll('.default input, .default select');
        defaultSelect.forEach(function(field) {
            if (field.tagName.toLowerCase() === 'select') {
                field.classList.add('form-select');
            } else if (field.tagName.toLowerCase() === 'input') {
                field.classList.add('form-control', 'w-100');
}
        });
        // Check for fields inside .preview-form and add appropriate classes
        var previewFields = document.querySelectorAll('.preview-form input, .preview-form select');
        previewFields.forEach(function(field) {
            if (field.tagName.toLowerCase() === 'select') {
                field.classList.add('form-select');
            } else if (field.tagName.toLowerCase() === 'input') {
                field.classList.add('form-control', 'w-100');
            }
        });
    });
");
?>
<div class="j2store j2store-fields">
    <div class="main-card card">
        <div class="card-body">
    <form name="adminForm" id="adminForm" method="post"
          class="form-validate" enctype="multipart/form-data"
          action="index.php">
        <div class="<?php echo $row_class ?>">
                    <div class="<?php echo $col_class ?>7 col-lg-8">
                        <fieldset class="options-form">
                            <legend><?php echo Text::_('J2STORE_ADD_CUSTOM_FIELD');?></legend>
                            <div class="table-responsive">
                                <table class="table align-middle">
                        <tr>
                                        <td class="key"><label><?php echo Text::_('J2STORE_CUSTOM_FIELDS_NAME');?></label></td>
                                        <td><?php echo J2Html::text('data[field][field_name]' ,$this->item->field_name,array('class'=>'form-control w-100','id'=>'field_name'));?></td>
                        </tr>
                        <tr>
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELDS_TABLE' ); ?></td>
                            <td><?php echo $this->item->field_table ?>
                                <?php echo J2html::hidden('data[field][field_table]', $this->item->field_table);?>
                            </td>
                        </tr>
                        <tr class="columnname">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELDS_COLUMN' ); ?></td>
                            <td>
                                <?php if(empty($this->item->j2store_customfield_id)): ?>
                                                <?php echo J2Html::text('data[field][field_namekey]' ,$this->item->field_namekey,array('class'=>'form-control w-100','id'=>'field_namekey'));?>
                                <?php else: ?>
                                    <?php echo $this->item->field_namekey; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_TYPE' ); ?></td>
                            <td>
                                <?php
                                if(!empty($this->field->field_type) && $this->field->field_type=='customtext'){
                                    $this->fieldtype->addJS();
                                    echo $this->field->field_type.'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->field->field_type.'" />';
                                }else{
                                    echo $this->fieldtype->display('data[field][field_type]',@$this->field->field_type,@$this->field->field_table);
                                }?>
                            </td>
                        </tr>
                        <tr class="required">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELDS_REQUIRED' ); ?></td>

                            <td><?php
                                echo J2Html::select()->clearState()
                                    ->type('genericlist')
                                    ->name('data[field][field_required]')
                                    ->value($this->item->field_required)
                                    ->attribs(array('class'=>'form-select'))
                                    ->setPlaceholders(
                                        array(
                                                        '0' => Text::_('JNO'),
                                                        '1' => Text::_('JYES')
                                        ))
                                    ->getHtml();
                                ?></td>
                        </tr>
                        <tr class="required">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_ERROR' ); ?></td>
                                        <td><?php echo J2Html::text('field_options[errormessage]',@$this->escape($this->item->field_options['errormessage']),array('class'=>'form-control w-100','id'=>'errormessage'));?></td>
                        </tr>
                        <tr class="default">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_DEFAULT' ); ?></td>
                            <td><?php echo $this->fieldClass->display(@$this->field,@$this->field->field_default,'data[field][field_default]',false,'',true,$this->allFields); ?></td>
                        </tr>
                        <tr class="multivalues">
                                        <td class="key">
                                            <?php echo Text::_( 'J2STORE_CUSTOM_FIELD_VALUES' ); ?>
                            </td>
                            <td>
                                            <table id="j2store_field_values_table" class="table align-middle">
                                    <tbody id="tablevalues">
                                    <tr>
                                        <td colspan="3">
                                                        <button type="button" onclick="addLine();return false;" class="btn btn-primary btn-sm" title="<?php echo $this->escape(Text::_('J2STORE_CUSTOM_FIELD_ADDVALUE')); ?>"><?php echo Text::_('J2STORE_CUSTOM_FIELD_ADDVALUE'); ?></button>
                                        </td>
                                    </tr>
                                    <tr>
                                                    <td><?php echo Text::_('J2STORE_CUSTOM_FIELD_VALUE')?></td>
                                                    <td><?php echo Text::_('J2STORE_CUSTOM_FIELD_TITLE'); ?></td>
                                                    <td><?php echo Text::_('J2STORE_CUSTOM_FIELD_DISABLED'); ?></td>
                                    </tr>
                                    <?php
                                    if(!empty($this->field->field_value) && is_array($this->field->field_value) AND $this->field->field_type!='zone'){
                                        foreach($this->field->field_value as $title => $value){
                                            $no_selected = 'selected="selected"';
                                            $yes_selected = '';
                                            if((int)$value->disabled){
                                                $no_selected = '';
                                                $yes_selected = 'selected="selected"';
                                            }
                                            ?>
                                            <tr>
                                                            <td>
                                                                <input type="text" name="field_values[title][]" class="form-control" value="<?php echo $this->escape($title); ?>" />
                                                            </td>
                                                            <td>
                                                                <input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" />
                                                            </td>
                                                            <td><select name="field_values[disabled][]" class="form-select">
                                                                    <option <?php echo $no_selected; ?> value="0"><?php echo Text::_('JNO'); ?></option>
                                                                    <option <?php echo $yes_selected; ?> value="1"><?php echo Text::_('JYES'); ?></option>
                                                    </select></td>
                                            </tr>
                                        <?php } }?>
                                    <tr>
                                                    <td><?php echo J2Html::text('field_values[title][]' ,'',array('class' =>'form-control'));?></td>
                                                    <td><?php echo J2Html::text('field_values[value][]' ,'',array('class' =>'form-control'));?></td>
                                        <td><?php echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name('field_values[disabled][]')
                                                ->value(0)
                                                ->setPlaceholders(
                                                    array(
                                                                    '0' => Text::_('JNO'),
                                                                    '1' => Text::_('JYES')
                                                    ))
                                                ->getHtml();
                                            ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr class="filtering">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_INPUT_FILTERING' ); ?></td>
                            <td>
                                <?php  $input_filtering  =  (isset($this->item->field_options['filtering']) ? (int)$this->item->field_options['filtering'] : ""); ?>
                                            <?php echo HTMLHelper::_('select.booleanlist', "field_options[filtering]", '',  $input_filtering); ?>
                            </td>
                        </tr>
                        <tr class="maxlength">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_MAXLENGTH' ); ?></td>
                            <td>
                                <?php $maxlength =  (isset($this->item->field_options['maxlength']) ? (int)$this->item->field_options['maxlength'] : ""); ?>
                                            <?php echo J2Html::text('field_options[maxlength]',$maxlength,array('id' =>'maxlength','class'=>'form-control'));?>
                            </td>
                        </tr>

                        <tr class="place_holder">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_PLACEHOLDER' ); ?></td>
                            <td>
                                <?php $placeholder =  (isset($this->item->field_options['placeholder']) ? $this->item->field_options['placeholder'] : ""); ?>
                                            <?php echo J2Html::text('field_options[placeholder]',$placeholder,array('id' =>'placeholder','class'=>'form-control'));?>
                            </td>
                        </tr>
                        <tr class="size">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_SIZE' ); ?></td>
                                        <td><?php echo J2Html::text('field_options[size]',@$this->item->field_options['size'],array('id' =>'size','class'=>'form-control'));?></td>
                        </tr>
                        <tr class="rows">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_ROWS' ); ?></td>
                                        <td><?php echo J2Html::text('field_options[size]',$this->item->field_options['size'],array('id' =>'size','class'=>'form-control'));?>
                            </td>
                        </tr>

                        <tr class="cols">
                            <td class="key">
                                            <?php echo Text::_( 'J2STORE_CUSTOM_FIELD_COLUMNS' ); ?>
                            </td>
                            <td>
                                            <input type="text" name="field_options[cols]" id="cols" class="form-control" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
                            </td>
                        </tr>
                        <tr class="zone">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_ZONE' ); ?></td>
                            <td><?php echo $this->zoneType->display("field_options[zone_type]",@$this->field->field_options['zone_type'],true);?></td>
                        </tr>

                        <tr class="format">
                                        <td class="key"><?php echo Text::_( 'J2STORE_CUSTOM_FIELD_FORMAT' ); ?></td>
                                        <td><input type="text" id="format" name="field_options[format]" value="<?php echo $this->escape(@$this->field->field_options['format']); ?>" class="form-control"/></td>
                        </tr>
                        <tr class="customtext">
                            <td class="key">
                                            <?php echo Text::_( 'J2STORE_CUSTOM_TEXT' ); ?>
                            </td>
                                        <td><textarea cols="50" rows="4" name="fieldcustomtext" class="form-control"><?php echo @$this->field->field_options['customtext']; ?></textarea></td>
                        </tr>

                        <tr class="readonly">
                            <td class="key">
                                            <?php echo Text::_( 'J2STORE_CUSTOM_FIELD_READONLY' ); ?>
                            </td>
                            <td>
                                            <?php // echo HTMLHelper::_('select.booleanlist', "field_options[readonly]" , ''); ?>
                                            <?php echo HTMLHelper::_('select.booleanlist', "field_options[readonly]" , '',@$this->field->field_options['readonly']); ?>
                            </td>
                        </tr>
                    </table>
                            </div>
                </fieldset>
            </div>
                    <div class="<?php echo $col_class ?>5 col-lg-4">
                        <fieldset class="field-status options-form mb-3">
                            <legend><?php echo Text::_('J2STORE_STATUS')?></legend>
                            <div class="form-grid">
                                <div class="control-group">
                                    <div class="control-label">
                                        <label><?php echo Text::_('J2STORE_PUBLISH');?></label>
                                    </div>
                                    <?php echo HTMLHelper::_('select.booleanlist', 'data[field][enabled]', '', $this->item->enabled); ?>
                                </div>
                            </div>
                </fieldset>

                        <fieldset class="field-display options-form mb-3">
                            <legend><?php echo Text::_('J2STORE_CUSTOM_FIELD_CHECKOUT_DISPLAY_SETTINGS')?></legend>
                            <span class="muted"> <small><?php echo Text::_('J2STORE_CUSTOM_FIELD_DISPLAY_HELP');?></small></span>
                            <div class="form-grid">
                                <div class="control-group">
                                    <div class="control-label text-capitalize w-100">
                                        <label><?php echo Text::_( 'J2STORE_STORE_BILLING_LAYOUT_LABEL' ); ?></label>
                                    </div>
                                    <?php echo HTMLHelper::_('select.booleanlist', "data[field][field_display_billing]", '', $this->field->field_display_billing); ?>
                                </div>
                                <div class="control-group">
                                    <div class="control-label text-capitalize w-100">
                                        <label><?php echo Text::_( 'J2STORE_STORE_SHIPPING_LAYOUT_LABEL' ); ?></label>
                                    </div>
                                    <?php echo HTMLHelper::_('select.booleanlist', "data[field][field_display_shipping]", '', $this->item->field_display_shipping); ?>
                                </div>
                                <div class="control-group">
                                    <div class="control-label text-capitalize w-100">
                                        <label><?php echo Text::_( 'J2STORE_STORE_PAYMENT_LAYOUT_LABEL' ); ?></label>
                                    </div>
                                    <?php echo HTMLHelper::_('select.booleanlist', "data[field][field_display_payment]" , '', $this->item->field_display_payment); ?>
                                </div>
                            </div>
                </fieldset>
                <?php if(!empty($this->field->j2store_customfield_id)) : ?>
                            <fieldset class="adminform options-form preview-form">
                                <legend><?php echo Text::_('PREVIEW'); ?></legend>
                                <table class="admintable table align-middle">
                            <tr>
                                <td class="key">
                                    <?php $this->fieldClass->suffix='_preview';
                                    echo $this->fieldClass->getFieldName($this->field); ?>
                                </td>
                                <td><?php
                                    $field_options = '';
                                    if($placeholder){
                                        $field_options .= ' placeholder="'.$placeholder.'" ';
                                    }

                                    echo $this->fieldClass->display($this->field,$this->field->field_default, $this->field->field_namekey, false,$field_options,true,$this->allFields); ?></td>
                            </tr>
                        </table>
                    </fieldset>
                <?php endif; ?>
                    </div>
                </div>
                <input type="hidden" name="option" value="com_j2store" />
                <input type="hidden" name="view" value="customfields" />
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="j2store_customfield_id" value="<?php echo $this->item->j2store_customfield_id ?>" />
                        <input type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken();?>" value="1" />
            </form>
        </div>
    </div>
</div>

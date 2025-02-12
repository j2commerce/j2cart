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
use Joomla\CMS\Uri\Uri;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
jimport('joomla.filesystem.file');
$this->loadHelper('select');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.Text::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.Text::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}
$config = Factory::getApplication()->getConfig();
$asset_id = $config->get('asset_id');

$optionvalues=array();
if(isset($this->optionvalues))
{
 $optionvalues = $this->optionvalues;
}
$this->item->option_params = $platform->getRegistry($this->item->option_params);


?>
<script  type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton == 'cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
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
    <div class="main-card card">
        <div class="card-body">
            <form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
                <input type="hidden" name="option" value="com_j2store">
                <input type="hidden" name="view" value="option">
                <input type="hidden" name="task" value="">
                <input type="hidden" id="option_id" name="j2store_option_id" value="<?php echo $this->item->j2store_option_id; ?>" />
		        <?php echo HTMLHelper::_('form.token'); ?>

                <fieldset class="options-form">
                    <legend><?php echo Text::_('J2STORE_OPTION_DETAILS'); ?> </legend>
                    <div class="form-grid">
                        <div class="control-group">
                            <div class="control-label">
                                <label for="option_name"><?php echo Text::_( 'J2STORE_OPTION_DISPLAY_NAME' ); ?></label>
                            </div>
                            <div class="controls">
                                <input type="text" name="option_name" id="option_name" class="form-control required" value="<?php echo htmlentities($this->item->option_name);?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="option_unique_name"><?php echo Text::_( 'J2STORE_OPTION_UNIQUE_NAME' ); ?></label>
                            </div>
                            <div class="controls">
                                <input type="text" name="option_unique_name" id="option_unique_name" class="form-control required" value="<?php echo htmlentities($this->item->option_unique_name);?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="type"><?php echo Text::_( 'J2STORE_OPTION_TYPE' ); ?></label>
                            </div>
                            <div class="controls">
	                            <?php echo J2StoreHelperSelect::getOptionTypesList('type', 'option-type', $this->item); ?>
                            </div>
                        </div>

	                    <?php if($this->item->type == 'text') :?>
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="option_unique_name"><?php echo Text::_( 'J2STORE_OPTION_PLACEHOLDER' ); ?></label>
                                </div>
                                <div class="controls">
	                                <?php echo J2Html::text('option_params[place_holder]', $this->item->option_params->get('place_holder', '' ),array('class' => 'form-control')); ?>
                                </div>
                            </div>
	                    <?php endif;?>

                        <div class="control-group">
                            <div class="control-label">
                                <label for="enabled"><?php echo Text::_( 'J2STORE_OPTION_STATE' ); ?></label>
                            </div>
                            <div class="controls">
			                    <?php echo J2StoreHelperSelect::publish('enabled',$this->item->enabled); ?>
                            </div>
                        </div>
	                    <?php if($this->item->type == 'date' || $this->item->type == 'datetime'  ):?>
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="option_params[hide_pastdates]"><?php echo Text::_( 'J2STORE_DATE_HIDE_PAST_DATES' ); ?></label>
                                </div>
                                <div class="controls">
	                                <?php echo J2Html::radio('option_params[hide_pastdates]', $this->item->option_params->get('hide_pastdates')); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="option_params[date_format]"><?php echo Text::_( 'J2STORE_CONF_DATE_FORMAT_LABEL' ); ?></label>
                                </div>
                                <div class="controls">
	                                <?php echo J2Html::text('option_params[date_format]', $this->item->option_params->get('date_format', 'yy-mm-dd'),array('class' => 'form-control')); ?>
                                </div>
                            </div>
		                    <?php if($this->item->type == 'datetime'): ?>
                                <div class="control-group">
                                    <div class="control-label">
                                        <label for="option_params[time_format]"><?php echo Text::_( 'J2STORE_CONF_TIME_FORMAT_LABEL' ); ?></label>
                                    </div>
                                    <div class="controls">
	                                    <?php echo J2Html::text('option_params[time_format]', $this->item->option_params->get('time_format', 'HH:mm' ),array('class' => 'form-control')); ?>
                                    </div>
                                </div>
		                    <?php endif; ?>
                        <?php endif;?>
                    </div>
                </fieldset>
                <fieldset class="options-form" id="option-value">
                    <legend><?php echo Text::_('J2STORE_OV_ADD_NEW_OPTION_VALUES');?></legend>
                    <div class="alert alert-info mt-0" role="alert">
                        <p class="m-0"><b><?php echo Text::_('J2STORE_OPTION_VALUE_IMAGE');?></b>: <?php echo Text::_('J2STORE_OPTION_VALUE_IMAGE_HELP');?></p>
                    </div>

                    <table class="table itemList options-list">
                        <thead>
                        <tr>
                            <td class="w-1 text-center text-uppercase d-none d-md-table-cell"><?php echo Text::_('J2STORE_COUPON_ID');?></td>
                            <td><?php echo Text::_('J2STORE_OPTION_VALUE_NAME'); ?></td>
                            <td class="d-none d-lg-table-cell"><?php echo Text::_('J2STORE_OPTION_VALUE_IMAGE');?></td>
                            <td class="d-none d-md-table-cell"><?php echo Text::_('JGRID_HEADING_ORDERING'); ?></td>
                            <td class="d-none d-md-table-cell"><?php //echo Text::_('J2STORE_REMOVE'); ?></td>
                        </tr>
                        </thead>
		                <?php $option_value_row = 0; ?>
		                <?php if(isset($this->item->optionvalues) && !empty($this->item->optionvalues)):?>
			                <?php foreach($this->item->optionvalues as $option_value):?>

                                <tbody id="option-value-row<?php echo $option_value_row; ?>">
                                <tr>
                                    <td class="w-1 text-center border-bottom d-none d-md-table-cell">
                                        <?php echo $option_value->j2store_optionvalue_id;?>
                                    </td>
                                    <td class="border-bottom">
                                        <input type="hidden"  name="option_value[<?php echo $option_value_row; ?>][j2store_optionvalue_id]" value="<?php echo $option_value->j2store_optionvalue_id	; ?>" />
                                        <input type="text" class="form-control required w-100"   name="option_value[<?php echo $option_value_row; ?>][optionvalue_name]" value="<?php echo isset($option_value->optionvalue_name) ? htmlentities($option_value->optionvalue_name): ''; ?>" />
                                    </td>
                                    <td class="border-bottom d-none d-lg-table-cell">
                                        <div class="input-prepend input-append">
							                <?php echo J2Html::media('option_value['.$option_value_row.'][optionvalue_image]', $option_value->optionvalue_image, array('id' => 'jform_optionvalue_image_'.$option_value->j2store_optionvalue_id, 'image_id' => 'input-optionvalue-image-'.$option_value->j2store_optionvalue_id, 'no_hide' => '')); ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell border-bottom">
                                        <input class="form-control required text-center" type="text" name="option_value[<?php echo $option_value_row; ?>][ordering]" value="<?php echo (!empty($option_value->ordering) ? $option_value->ordering: 0); ?>" size="1" />
                                    </td>
                                    <td class="border-bottom d-none d-md-table-cell">
                                        <a class="btn btn-danger btn-sm" onclick="DeleteOptionValue(<?php echo $option_value->j2store_optionvalue_id	; ?>,<?php echo $option_value_row; ?>)"><?php echo Text::_('J2STORE_REMOVE'); ?></a>
                                    </td>
                                </tr>
                                </tbody>
				                <?php $option_value_row++; ?>
			                <?php endforeach; ?>
		                <?php endif;?>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-center text-lg-end">
                                <a href="javascript:void(0)" onclick="j2storeAddOptionValue();" class="btn btn-primary"><?php echo Text::_('J2STORE_OPTION_VALUE_ADD'); ?></a>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </fieldset>
            </form>
        </div>
    </div>


    <script>
        function removeImage(id) {
            var no_preview = "<?php echo Uri::root().'media/j2store/images/common/no_image-100x100.jpg'?>";
            document.getElementById(id).value = "";
            document.getElementById("optimage-" + id).src = no_preview;

            window.scrollTo({
                top: document.getElementById(id).getBoundingClientRect().top + window.pageYOffset,
                behavior: "smooth"
            });
        }

    function previewImage(value,id) {

        value='<?php echo Uri::root();?>'+value;
        jQuery("#optimage-"+id).attr('src',value);

    }


    function jInsertFieldValue(value, id) {

        var old_id = document.id(id).value;
    if (old_id != id) {
        var elem = document.id(id)
        elem.value = value;
        elem.fireEvent("change");
        previewImage(value,id);
    }

    }
    </script>

    <script type="text/javascript">
        var thumb_image = "<?php echo Uri::root().'media/j2store/images/common/no_image-100x100.jpg'?>";
        var vhref = "<?php echo "index.php?option=com_media&view=images&tmpl=component&asset=".$asset_id."&author=".Factory::getApplication()->getIdentity()->id."&fieldid=jform_main_image_";?>";

        var selectElement = document.querySelector("select[name='type']");
        var optionValueElement = document.getElementById('option-value');
        var placeHolderElement = document.getElementById('place_holder');

        if (selectElement) {
            selectElement.addEventListener('change', function() {
                if (optionValueElement && placeHolderElement) {
                    if (this.value === 'select' || this.value === 'radio' || this.value === 'checkbox' || this.value === 'image') {
                        optionValueElement.style.display = 'block';
                        placeHolderElement.style.display = 'none';
                    } else if (this.value === 'text') {
                        optionValueElement.style.display = 'none';
                        placeHolderElement.style.display = 'block';
                    } else {
                        optionValueElement.style.display = 'none';
                        placeHolderElement.style.display = 'none';
                    }
                }
            });

            // Trigger the change event to initialize
            selectElement.dispatchEvent(new Event('change'));
        }

    var option_value_row = <?php echo $option_value_row; ?>;

    function j2storeAddOptionValue() {
        var html = '';
        html += '<tbody id="option-value-row' + option_value_row + '">';
        html += '<tr>';
        html += '<td class="d-none d-md-table-cell border-bottom"></td>';
        html += '<td class="border-bottom"><input type="hidden" name="option_value[' + option_value_row + '][j2store_optionvalue_id]" value="" />';
        html += '<input type="text" class="form-control w-100 required" name="option_value[' + option_value_row + '][optionvalue_name]" value="" />';
        html += '</td>';
        html += '<td class="d-none d-lg-table-cell text-center border-bottom">';
        html += '<input type="hidden" name="option_value[' + option_value_row + '][optionvalue_image]" value="" />';
        html += '<span class="text text-info"><?php echo addslashes(Text::_('J2STORE_OPTIONVALUE_INSERT_IMAGE_HELP'));?></span>';
        html += '<td class="border-bottom"><input class="form-control text-center" type="text" name="option_value[' + option_value_row + '][ordering]" value="0" size="1" /></td>';
        html += '<td class="border-bottom"><a onclick="document.getElementById(\'option-value-row' + option_value_row + '\').remove();" class="btn btn-danger btn-sm"><?php echo Text::_('J2STORE_REMOVE'); ?></a></td>';
        html += '</tr>';
        html += '</tbody>';

        // Insert the HTML before the <tfoot> element inside #option-value
        var optionValueTable = document.querySelector('#option-value tfoot');
        if (optionValueTable) {
            optionValueTable.insertAdjacentHTML('beforebegin', html);
        }

        option_value_row++;
    }

    function DeleteOptionValue(optionvalue_id, option_value_row) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_j2store&view=option&task=deleteoptionvalue&optionvalue_id=' + optionvalue_id, true);
        xhr.responseType = 'json';

        xhr.onload = function() {
            if (xhr.status === 200) {
                var json = xhr.response;
                if (json) {
                    document.getElementById("system-message-container").innerHTML = json['html'];
                    if (json['success']) {
                        var rowElement = document.getElementById('option-value-row' + option_value_row);
                        if (rowElement) {
                            rowElement.remove();
                        }
                    }
                }
            }
        };

        xhr.send();
    }
    </script>
</div>



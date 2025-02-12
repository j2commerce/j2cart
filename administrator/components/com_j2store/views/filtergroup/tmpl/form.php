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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
<div class="main-card card">
    <div class="card-body">
        <div class="j2store">
            <div id="j2store-system-message-container"></div>
            <form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post"
                  action="<?php echo Route::_('index.php?option=com_j2store&view=filtergroup&task=edit&id=' . $this->item->j2store_filtergroup_id); ?>">
                <input type="hidden" name="option" value="com_j2store">
                <input type="hidden" name="view" value="filtergroup">
                <input type="hidden" name="task" value="edit">
                <input type="hidden" name="id" value="<?php echo $this->item->j2store_filtergroup_id; ?>">
                <input type="hidden" id="j2store_filtergroup_id" name="j2store_filtergroup_id"
                       value="<?php echo $this->item->j2store_filtergroup_id; ?>"/>
                <input type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken(); ?>" value="1"/>
                <fieldset class="options-form">
                    <legend><?php echo Text::_('J2STORE_PRODUCT_FILTER_GROUPS_DETAILS'); ?> </legend>
                    <div class="form-grid">
                        <div class="control-group">
                            <div class="control-label">
                                <label for="group_name"><?php echo Text::_('J2STORE_PRODUCT_FILTER_NAME'); ?></label>
                            </div>
                            <div class="controls">
	                            <?php echo J2Html::text('group_name', $this->item->group_name, array('class' => 'required form-control')); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="enabled"><?php echo Text::_('J2STORE_OPTION_STATE'); ?></label>
                            </div>
                            <div class="controls">
	                            <?php echo J2StoreHelperSelect::publish('enabled', $this->item->enabled); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="ordering"><?php echo Text::_('JGRID_HEADING_ORDERING'); ?></label>
                            </div>
                            <div class="controls">
	                            <?php echo J2Html::text('ordering', $this->item->ordering, array('class' => 'required form-control')); ?>
                            </div>
                        </div>

                    </div>
                </fieldset>
                <fieldset id="filter-value" class="options-form">
                    <legend><?php echo Text::_('J2STORE_ADD_NEW_PRODUCT_FILTER_VALUES'); ?></legend>
                    <div class="btn-toolbar w-100 justify-content-end">
	                    <?php echo $this->filter_pagination->getLimitBox(); ?>
                    </div>

                    <table id="pFilerValue" class="table itemList align-middle">
                        <thead>
                        <tr>
                            <th class="w-1 text-center"><?php echo Text::_('J2STORE_PRODUCT_FILTER_ID'); ?></th>
                            <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILTER_VALUE'); ?></th>
                            <th scope="col"><?php echo Text::_('JGRID_HEADING_ORDERING'); ?></th>
                            <td></td>
                        </tr>
                        </thead>
                        <?php $product_filter_value_row = 0; ?>
                        <?php if (isset($this->filtervalues) && !empty($this->filtervalues)): ?>
                            <?php foreach ($this->filtervalues as $filter_value): ?>
                                <tbody id="filter-value-row<?php echo $product_filter_value_row; ?>">
                                <tr>
                                    <td class="text-center border-bottom">
		                                <?php echo $filter_value->j2store_filter_id; ?>
                                    </td>
                                    <td class="border-bottom">
                                        <?php echo J2Html::hidden('filter_value[' . $filter_value->j2store_filter_id . '][j2store_filter_id]', $filter_value->j2store_filter_id); ?>
                                        <?php echo J2Html::text('filter_value[' . $filter_value->j2store_filter_id . '][filter_name]', $filter_value->filter_name,array('class' => 'form-control')); ?>
                                    </td>
                                    <td class="border-bottom">
                                        <?php echo J2Html::text('filter_value[' . $filter_value->j2store_filter_id . '][ordering]', $filter_value->ordering, array('class' => 'form-control text-center', 'size'=>'1')); ?>
                                    </td>

                                    <td class="border-bottom text-end">
                                        <?php echo J2html::button('delete', Text::_('J2STORE_REMOVE'), array('class' => 'btn btn-danger btn-sm', "id" => "filterValueDeleteBtn-$filter_value->j2store_filter_id", 'onclick' => 'DeleteFilterValue(' . $filter_value->j2store_filter_id . ',' . $product_filter_value_row . ')')); ?>
                                    </td>
                                </tr>
                                </tbody>
                                <?php $product_filter_value_row++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tfoot>
                        <tr>
                            <td colspan="4" class="text-start">
                                <a href="javascript:void(0)" onclick="j2storeAddFilterToGroup();" class="btn btn-primary"><span class="icon-save-new me-2" aria-hidden="true"></span> <?php echo Text::_('J2STORE_ADD'); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <?php echo $this->filter_pagination->getListFooter(); ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </fieldset>
            </form>

            <?php
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $script = "
                var filter_value_row = ".json_encode($product_filter_value_row).";

                function j2storeAddFilterToGroup() {
                    var html = '';
                    html += '<tbody id=\"filter-value-row' + filter_value_row + '\">';
                    html += '<tr>';
                    html += '<td class=\"text-center border-bottom\"></td>';
                    html += '<td class=\"border-bottom\"><input type=\"hidden\" name=\"filter_value[' + filter_value_row + '][j2store_filter_id]\" value=\"\" />';
                    html += '<input type=\"text\" class=\"form-control\" name=\"filter_value[' + filter_value_row + '][filter_name]\" value=\"\" />';
                    html += '</td>';
                    html += '<td class=\"w-1 border-bottom\"><input class=\"form-control text-center\" type=\"text\" name=\"filter_value[' + filter_value_row + '][ordering]\" value=\"\" size=\"1\" /></td>';
                    html += '<td class=\"border-bottom text-end\"><a class=\"btn btn-danger btn-sm\" onclick=\"document.getElementById(\'filter-value-row' + filter_value_row + '\').remove();\">".Text::_('J2STORE_REMOVE')."</a></td>';
                    html += '</tr>';
                    html += '</tbody>';

                    var pFilerValueTable = document.querySelector('#pFilerValue tfoot');
                    if (pFilerValueTable) {
                        pFilerValueTable.insertAdjacentHTML('beforebegin', html);
                    }

                    filter_value_row++;
                }
                ";
            $wa->addInlineScript($script, [], []);

            $script2 = "
                function DeleteFilterValue(productfiltervalue_id, filter_value_row) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'index.php?option=com_j2store&view=filtergroups&task=deleteproductfiltervalues&productfiltervalue_id=' + productfiltervalue_id, true);
                    xhr.responseType = 'json';

                    xhr.onload = function() {
                        var json = xhr.response;
                        var html = '';
                        if (xhr.status === 200 && json) {
                            if (json['success']) {
                                var rowElement = document.getElementById('filter-value-row' + filter_value_row);
                                if (rowElement) {
                                    rowElement.remove();
                                }
                                html = '<div class=\"alert alert-success alert-block\"><p>' + json['msg'] + '</p></div>';
                                document.getElementById('j2store-system-message-container').innerHTML = html;
                            } else {
                                html = '<div class=\"alert alert-warning alert-block\"><p>' + json['msg'] + '</p></div>';
                                document.getElementById('filterValueDeleteBtn-' + productfiltervalue_id).value = '".Text::_('J2STORE_REMOVE')."';
                                document.getElementById('j2store-system-message-container').innerHTML = html;
                            }
                        }
                    };

                    xhr.onerror = function() {
                        console.error('An error occurred while processing the request.');
                    };

                    // Set 'beforeSend' equivalent
                    document.getElementById('filterValueDeleteBtn-' + productfiltervalue_id).value = '".Text::_('J2STORE_REMOVE_CONTINUE')."';

                    xhr.send();
                }
                ";
            $wa->addInlineScript($script2, [], []);
            ?>
        </div>
    </div>
</div>

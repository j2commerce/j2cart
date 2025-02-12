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

$this->loadHelper('select');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
?>
<div class="j2store">
    <div class="main-card card">
        <div class="card-body">
            <form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
                <input type="hidden" name="option" value="com_j2store">
                <input type="hidden" name="view" value="taxprofile">
                <input type="hidden" name="task" value="">
                <input type="hidden"  name="j2store_taxprofile_id" value="<?php echo $this->item->j2store_taxprofile_id	; ?>">
                <input type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken();?>" value="1" />

                <fieldset id="taxprofile_edit" class="options-form mb-4">
                    <legend><?php echo Text::_('J2STORE_TAXPROFILE'); ?></legend>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <span class="fas fa-solid fa-info-circle flex-shrink-0 me-2"></span>
                        <div><?php echo Text::_('J2STORE_TAXPROFILES_HELP_TEXT'); ?></div>
                    </div>
                    <div class="form-grid">
                        <div class="control-group">
                            <div class="control-label">
                                <label for="taxprofile_name"><?php echo Text::_('J2STORE_TAXPROFILE_NAME'); ?></label>
                            </div>
                            <div class="controls">
                                <?php echo J2Html::text('taxprofile_name', $this->item->taxprofile_name, array('class'=>'form-control','id'=>'taxprofile_name','required'=>'')); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="taxprofile_name"><?php echo Text::_('J2STORE_ENABLED'); ?></label>
                            </div>
                            <div class="controls">
                                <?php echo J2StoreHelperSelect::publish('enabled',$this->item->enabled);?>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset id="taxprofile_rates" class="options-form mb-4">
                    <legend><?php echo Text::_('COM_J2STORE_TITLE_TAXRATES'); ?></legend>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <span class="fas fa-solid fa-info-circle flex-shrink-0 me-2"></span>
                        <div><?php echo Text::_('J2STORE_TAXPROFILE_TAXRATE_MAP_HELP'); ?></div>
                    </div>
                    <div class="table-responsive">
                        <table id="taxprofile_rule_table" class="table itemList align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-1"><?php echo Text::_('J2STORE_NUM'); ?></th>
                                    <th scope="col" colspan="1"><?php echo Text::_('J2STORE_TAXPROFILE_TAXRATE'); ?></th>
                                    <th scope="col"><?php echo Text::_('J2STORE_TAXPROFILE_ADDRESS'); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <?php $taxrule_row = 0;?>
                            <?php if(isset($this->item->taxrules) && count($this->item->taxrules)): ?>
                                <?php foreach ($this->item->taxrules as  $i => $taxrule): ?>
                                    <tbody id="tax-to-taxrule-row<?php echo $taxrule_row; ?>">
                                        <tr>
                                            <td class="w-1"><?php echo $i+1; ?></td>
                                            <td>
                                                <select name="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][taxrate_id]" class="form-select">
                                                    <?php foreach ($this->item->taxrates as $taxrate) : ?>
                                                        <?php  if ($taxrate->j2store_taxrate_id == $taxrule->taxrate_id) : ?>
                                                            <option value="<?php echo $taxrate->j2store_taxrate_id; ?>" selected="selected"><?php echo $taxrate->taxrate_name; ?></option>
                                                        <?php else: ?>
                                                            <option value="<?php echo $taxrate->j2store_taxrate_id; ?>"><?php echo $taxrate->taxrate_name; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][address]" class="form-select">
                                                    <?php  if ($taxrule->address == 'billing') { ?>
                                                        <option value="billing" selected="selected"><?php echo Text::_('J2STORE_BILLING_ADDRESS'); ?></option>
                                                    <?php } else { ?>
                                                        <option value="billing"><?php echo Text::_('J2STORE_BILLING_ADDRESS'); ?></option>
                                                    <?php } ?>
                                                    <?php  if ($taxrule->address == 'shipping') { ?>
                                                        <option value="shipping" selected="selected"><?php echo Text::_('J2STORE_SHIPPING_ADDRESS'); ?></option>
	                                                <?php } else { ?>
                                                        <option value="shipping"><?php echo Text::_('J2STORE_SHIPPING_ADDRESS'); ?></option>
	                                                <?php } ?>
                                                </select>
                                            </td>
                                            <input type="hidden" name ="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][j2store_taxrule_id]" value="<?php echo $taxrule->j2store_taxrule_id; ?>"/>
                                            <td class="text-end"><a onclick="j2storeRemoveTax(<?php echo $taxrule->j2store_taxrule_id; ?>, <?php echo $taxrule_row; ?>);" class="btn btn-danger"><span class="icon icon-trash me-2"></span><?php echo Text::_('J2STORE_REMOVE'); ?></a></td>
                                        </tr>
                                        <?php $taxrule_row++; ?>
                                    </tbody>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-start">
                                        <a class="btn btn-primary" onclick="j2storeAddTaxProfile();" class="btn btn-primary"><span class="icon-save-new me-2" aria-hidden="true"></span><?php echo Text::_('J2STORE_ADD'); ?></a>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    let taxrule_row = <?php echo $taxrule_row; ?>;

    function j2storeAddTaxProfile() {
                // Construct the HTML dynamically
                let html = '<tbody id="tax-to-taxrule-row' + taxrule_row + '">';
            html += '  <tr>';
            html +='<td></td>';
                html += '<td><select name="tax-to-taxrule-row[' + taxrule_row + '][taxrate_id]" class="form-select">';
            <?php foreach ($this->item->taxrates as $taxrate) : ?>
            html += '      <option value="<?php echo $taxrate->j2store_taxrate_id; ?>"><?php echo addslashes($taxrate->taxrate_name); ?></option>';
                <?php endforeach; ?>
            html += '    </select></td>';
                html += '<td><select name="tax-to-taxrule-row[' + taxrule_row + '][address]" class="form-select">';
                html += '<option value="billing"><?php echo Text::_('J2STORE_BILLING_ADDRESS'); ?></option>';
                html += '<option value="shipping"><?php echo Text::_('J2STORE_SHIPPING_ADDRESS'); ?></option>';
            html += '    </select></td>';
            html += '<input type="hidden" name="tax-to-taxrule-row['+ taxrule_row + '][j2store_taxrule_id]" value="" /></td>';
                html += '<td class="text-end"><a onclick="document.getElementById(\'tax-to-taxrule-row' + taxrule_row + '\').remove();" class="btn btn-danger"><span class="icon icon-trash me-2"></span><?php echo Text::_('J2STORE_REMOVE'); ?></a></td>';
            html += '  </tr>';
            html += '</tbody>';

                // Add the new HTML before the table's <tfoot>
                const taxProfileRuleTable = document.getElementById('taxprofile_rule_table');
                const tableFoot = taxProfileRuleTable.querySelector('tfoot');
                if (tableFoot) {
                    tableFoot.insertAdjacentHTML('beforebegin', html);
                }

                // Increment the tax rule row counter
            taxrule_row++;
            }

    function j2storeRemoveTax(taxrule_id, taxrule_row) {
        (function($) {
            $('.j2storealert').remove();
            $.ajax({
                method:'post',
                url:'index.php?option=com_j2store&view=taxprofile&task=deleteTaxRule',
                data:{'taxrule_id':taxrule_id},
                dataType:'json'
            }).done(function(response) {
                if(response.success) {
                    $('#tax-to-taxrule-row'+taxrule_row).remove();
                            $('#taxprofile_rule_table').before('<div class="j2storealert alert alert-success d-flex align-items-center"><span class="fas fa-solid fa-check-circle flex-shrink-0 me-2"></span><div>'+response.success+'</div></div>');
                } else {
                            $('#taxprofile_rule_table').before('<div class="j2storealert alert alert-danger d-flex align-items-center"><i class="fas fa-solid fa-times-circle flex-shrink-0 me-2"></i><div>'+response.error+'</div></div>');
                }
            });
        })(j2store.jQuery);
    }
</script>

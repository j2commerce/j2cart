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
use Joomla\CMS\Uri\Uri;

$base_path = rtrim(Uri::root(),'/').'/administrator';
?>
<div class="j2store-product-configuration-options">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_OPTIONS');?></legend>
        <div class="table-responsive">
            <table id="attribute_options_table" class="table itemList align-middle j2store">
				<thead>
					<tr>
                        <th scope="col"><?php echo Text::_('J2STORE_OPTION_NAME');?></th>
                        <th scope="col"><?php echo Text::_('J2STORE_PARENT_OPTION');?></th>
                        <th scope="col"><?php echo Text::_('J2STORE_OPTION_REQUIRED');?></th>
                        <th scope="col"><?php echo Text::_('J2STORE_OPTION_ORDERING');?></th>
                        <th class="text-end"></th>
					</tr>
			</thead>
			<tbody>
				<?php if(isset($this->item->product_options ) && !empty($this->item->product_options)):
					$key = 0;
				?>
                    <?php foreach($this->item->product_options as $poption):?>

				<tr id="pao_current_option_<?php echo $poption->j2store_productoption_id;?>">
						<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
						<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
					<td>
						<?php echo $this->escape($poption->option_name);?>
                                <?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
                                <?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
						<small>(<?php  echo $this->escape($poption->option_unique_name);?>)</small>
                                <small><?php Text::_('J2STORE_OPTION_TYPE');?><?php echo Text::_('J2STORE_'.strtoupper($poption->type))?></small>
						<?php if(isset($poption->type) && ($poption->type =='select' || $poption->type =='radio' || $poption->type =='checkbox')):?>
                                    <a class="small d-block" data-fancybox data-type="iframe" data-src="<?php echo $base_path."/index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$this->item->j2store_product_id."&productoption_id=".$poption->j2store_productoption_id."&layout=productoptionvalues&tmpl=component";?>" href="javascript:;">
                                        <?php echo Text::_( "J2STORE_OPTION_SET_VALUES" );?>
                                    </a>
						<?php endif;?>
					</td>
					<td>
						<?php
						$parent_options  = J2StoreHelperSelect::getParentOption($poption->j2store_productoption_id,$poption->parent_id,$poption->option_id);
						echo J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][parent_id]')
						->value($poption->parent_id)
						->setPlaceHolders($parent_options)
                                    ->attribs(array('class'=>'form-select'))
						->getHtml();

						?>
						</td>
					<td>
					<?php echo J2Html::select()->clearState()
											   ->type('genericlist')
												->name($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][required]')
												->value($poption->required)
                                    ->setPlaceHolders(array('0' => Text::_('J2STORE_NO') ,'1' => Text::_('J2STORE_YES')))
                                    ->attribs(array('class'=>'form-select'))
												->getHtml();
					?>
					</td>
                            <td><?php echo J2Html::text($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][ordering]',$poption->ordering,array('id'=>'ordering' ,'class'=>'form-control'));?></td>
                            <td class="text-end">
                                <span class="optionRemove" onClick="removePAOption(<?php echo $poption->j2store_productoption_id;?>,'<?php echo $this->item->product_type;?>')"><span class="icon icon-trash"></span></span>
					</td>
				</tr>
				<?php $key++;?>
				<?php endforeach;?>
				<?php endif;?>
				<tr class="j2store_a_options">
                    <td colspan="3">
                        <div class="control-group align-items-center mt-4">
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
					<tfoot>
						<tr>
                    <td colspan="5">
                        <?php echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setpaimport&product_type=".$this->item->product_type."&product_id=".$this->item->j2store_product_id."&layout=paimport&tmpl=component", Text::_('J2STORE_IMPORT_PRODUCT_OPTIONS'), array('class'=>'btn btn-primary btn-sm text-capitalize','width'=>800 , 'height'=>500));?>
							</td>
						</tr>
					</tfoot>
			</table>
		</div>
    </fieldset>
</div>
<script type="text/javascript">
function addOption() {
        var optionSelect = document.getElementById('option_select_id');
        var optionValue = optionSelect.value;
        var optionName = optionSelect.options[optionSelect.selectedIndex].text;

        var html = '<span class="j2error"><?php echo Text::_('J2STORE_PARENT_OPTION_MESSAGE'); ?></span>';

        var tr = document.createElement('tr');

        // Create and append the option name cell
        var tdOptionName = document.createElement('td');
        tdOptionName.className = 'addedOption';
        tdOptionName.textContent = optionName;
        tr.appendChild(tdOptionName);

        // Create and append the HTML content cell
        var tdHtml = document.createElement('td');
        tdHtml.innerHTML = html;
        tr.appendChild(tdHtml);

        // Create and append the select input cell
        var tdSelect = document.createElement('td');
        var select = document.createElement('select');
        select.className = 'form-select';
        select.name = `<?php echo $this->form_prefix . '[item_options]'; ?>[${optionValue}][required]`;

        var optionNo = document.createElement('option');
        optionNo.value = '0';
        optionNo.textContent = '<?php echo Text::_('J2STORE_NO'); ?>';
        select.appendChild(optionNo);

        var optionYes = document.createElement('option');
        optionYes.value = '1';
        optionYes.textContent = '<?php echo Text::_('J2STORE_YES'); ?>';
        select.appendChild(optionYes);

        tdSelect.appendChild(select);
        tr.appendChild(tdSelect);

        // Create and append the ordering input field cell
        var tdInputs = document.createElement('td');
        var inputOrdering = document.createElement('input');
        inputOrdering.className = 'form-control';
        inputOrdering.name = `<?php echo $this->form_prefix . '[item_options]'; ?>[${optionValue}][ordering]`;
        inputOrdering.value = '0';
        tdInputs.appendChild(inputOrdering);

        var hiddenOptionId = document.createElement('input');
        hiddenOptionId.type = 'hidden';
        hiddenOptionId.value = optionValue;
        hiddenOptionId.name = `<?php echo $this->form_prefix . '[item_options]'; ?>[${optionValue}][option_id]`;
        tdInputs.appendChild(hiddenOptionId);

        var hiddenProductOptionId = document.createElement('input');
        hiddenProductOptionId.type = 'hidden';
        hiddenProductOptionId.value = '';
        hiddenProductOptionId.name = `<?php echo $this->form_prefix . '[item_options]'; ?>[${optionValue}][j2store_productoption_id]`;
        tdInputs.appendChild(hiddenProductOptionId);

        tr.appendChild(tdInputs);

        // Create and append the remove button cell
        var tdRemove = document.createElement('td');
        tdRemove.classList.add('text-end');
        var spanRemove = document.createElement('span');
        spanRemove.className = 'optionRemove';
        var trashIcon = document.createElement('span');
        trashIcon.className = 'icon icon-trash';
        spanRemove.appendChild(trashIcon);
        spanRemove.onclick = function () {
            tr.remove();
        };
        tdRemove.appendChild(spanRemove);
        tr.appendChild(tdRemove);

        // Insert the row before the `.j2store_a_options` element
        var optionsTable = document.querySelector('.j2store_a_options');
        if (optionsTable) {
            optionsTable.parentNode.insertBefore(tr, optionsTable);
        }
}
</script>

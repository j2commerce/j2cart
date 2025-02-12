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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.formvalidator');
$platform->loadExtra('behavior.multiselect');

$row_class = 'row';
$col_class = 'col-lg-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.Text::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$script = 'function jSelectProduct(product_id, product_name, field_id) {
                var form = document.querySelector("#adminForm");
                var fieldContainer = form.querySelector("#" + field_id + " tbody");
                var existingRow = fieldContainer.querySelector("#product-row-" + product_id);

                if (!existingRow) {
                    var newRow = document.createElement("tr");
                    newRow.classList.add("j2store-product-list-tr");
                    newRow.id = "product-row-" + product_id;

                    var productCell = document.createElement("td");
                    productCell.innerHTML = `<input type="hidden" name="products[`+product_id+`]" value="`+product_id+`" /><small>`+product_name+`</small>`;

                    var actionCell = document.createElement("td");
                    var deleteButton = document.createElement("button");
                    deleteButton.className = "btn btn-link btn-sm";
                    deleteButton.innerHTML = "<span class=\"icon icon-trash text-danger\"></span>";
                    deleteButton.addEventListener("click", function () {
                        newRow.remove();
                    });
                    actionCell.classList.add("w-5");
                    actionCell.appendChild(deleteButton);

                    newRow.appendChild(actionCell);
                    newRow.appendChild(productCell);
                    fieldContainer.appendChild(newRow);
                    alert("'.Text::_('J2STORE_PRODUCT_ADDED').'");
                } else {
                    alert("'.Text::_('J2STORE_PRODUCT_ADDED_ALREADY').'");
                }
            }';
$wa->addInlineScript($script, [], []);
?>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">
        <div class="j2store_<?php echo $vars->view;?>_edit">
            <form id="adminForm" class="form-horizontal form-validate" action="<?php echo $vars->action_url?>" method="post" name="adminForm">
                <?php echo J2Html::hidden('option','com_j2store');?>
                <?php echo J2Html::hidden('view',$vars->view);?>
                <?php if(isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                    <?php echo J2Html::hidden($vars->primary_key,$vars->id);?>
                <?php endif; ?>
                <?php echo J2Html::hidden('task', '', array('id'=>'task'));?>
                <?php echo HTMLHelper::_( 'form.token' ); ?>
                    <?php if(isset($vars->field_sets) && !empty($vars->field_sets)):?>
		            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic_options', 'recall' => true, 'breakpoint' => 768]); ?>
                        <?php foreach ($vars->field_sets as $field_set):?>
                            <?php if(isset($field_set['fields']) && !empty($field_set['fields'])):?>
				            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $field_set['id'], Text::_($field_set['label'])); ?>
                            <div <?php echo isset($field_set['id']) && $field_set['id'] ? 'id="'.$field_set['id'].'"': '';?>
					            <?php echo isset($field_set['class']) && is_array($field_set['class']) ? 'class="'.implode(' ',$field_set['class']).'"': '';?>>
                                <fieldset class="options-form">
	                                <?php if(isset($field_set['label']) && !empty($field_set['label'])):?>
                                        <legend><?php echo Text::_($field_set['label']);?></legend>
                                <?php endif; ?>
                                    <div class="form-grid">
                                    <?php if(isset($field_set['is_pro']) && $field_set['is_pro'] && J2Store::isPro() != 1):?>
                                        <?php echo J2Html::pro();?>
                                    <?php else: ?>
                                        <?php foreach ($field_set['fields'] as $field_name => $field):?>
                                            <?php $is_required = isset($field['options']['required']) && !empty($field['options']['required']) ? true:false;?>
                                            <div class="control-group">
                                                    <?php if(isset($field['label']) && !empty($field['label'])):?>
                                                        <div class="control-label">
                                                            <?php echo Text::_($field['label']);?><?php echo $is_required ? "<span>*</span>": '';?>
                                                        </div>
                                                    <?php endif; ?>
                                                <div class="controls">
	                                                    <?php
	                                                    if($field['type'] === 'select') :
		                                                    $field['options']['class'] = isset($field['options']['class']) ? $field['options']['class'] . ' form-select' : 'form-select';
		                                                    echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);
                                                        elseif($field['type'] === 'radio'):
	                                                        $field['options']['class'] = isset($field['options']['class']) ? $field['options']['class'] . '' : '';
	                                                        echo J2Html::radioBooleanList($field['name'],$field['value'],$field['options']);
	                                                    elseif(isset($field['type']) && in_array($field['type'], array('number', 'text', 'email', 'password', 'textarea', 'file'))) :
		                                                    $field['options']['class'] = isset($field['options']['class']) ? $field['options']['class'] . ' form-control' : 'form-control';
		                                                    echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);
                                                        elseif(isset($field['type']) && in_array($field['type'], array('radio', 'checkbox', 'button', 'submit', 'hidden'))) :
		                                                    echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);
                                                        else:
		                                                    echo J2Html::custom($field['type'], $field['name'], $field['value'], $field['options']);
	                                                    endif;
	                                                    ?>
                                                    <?php if(isset($field['desc']) && !empty($field['desc'])):?>
                                                            <small class="form-text"><?php echo Text::_($field['desc']);?></small>
                                                    <?php endif; ?>
                                                    <?php if($field_name == 'product_links'):?>
                                                            <div class="alert alert-success">
                                                                <?php echo Text::_('J2STORE_COUPON_ADDING_PRODUCT_HELP');?>
                                                            </div>
                                                                <div class="table-responsive">
                                                                <table class="table itemList align-middle" id="jform_product_list">
                                                                        <tbody>
                                                                        <?php if(!empty($vars->item->products)):?>
                                                                        <tr>
                                                                                <td colspan="3" class="px-0 text-end">
                                                                                    <button type="button" class="btn btn-danger btn-sm" onclick="document.querySelectorAll('.j2store-product-list-tr').forEach(element => element.remove());">
                                                                                        <span class="icon icon-trash me-1"></span>
                                                                                        <?php echo Text::_('J2STORE_DELETE_ALL_PRODUCTS'); ?>
                                                                                    </button>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $product_ids = explode(',',$vars->item->products);
                                                                        $i =1;
                                                                                foreach($product_ids as $pid):
                                                                                    $product = J2Store::fof()->getModel('Products','J2StoreModel')->getItem($pid);
                                                                        ?>
                                                                            <tr class="j2store-product-list-tr" id="product-row-<?php echo $pid?>">
                                                                                        <td class="w-5">
                                                                                            <button type="button" class="btn btn-link btn-sm" onclick="this.closest('tr').remove();"><span class="icon icon-trash text-danger"></span></button>
                                                                                        </td>
                                                                                        <td>
                                                                                            <input type="hidden" name="products[<?php echo $pid;?>]" value='<?php echo $pid;?>' />
                                                                                            <small class="fw-bold"><?php echo $product->product_name;?></small>
                                                                                        </td>
                                                                            </tr>
                                                                            <?php
                                                                            $i++;
                                                                        endforeach;?>
                                                                            <?php endif;?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                </fieldset>
                            </div>
                          <?php echo HTMLHelper::_('uitab.endTab'); ?>
                      <?php endif; ?>
                  <?php endforeach;?>
		              <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    var value_type = '<?php echo $vars->item->value_type?>';
    document.querySelector('select[name=value_type]').addEventListener('change', function () {
      value_type = this.value;
      var maxQuantityElement = document.getElementById('max_quantity');
      if (maxQuantityElement) {
        var controlGroup = maxQuantityElement.closest('.control-group');
        if (controlGroup) {
          controlGroup.style.display = 'none';
          if (value_type === 'percentage_product' || value_type === 'fixed_product') {
            controlGroup.style.display = ''; // Show if conditions are met
          }
        }
      }
    });
    document.querySelector('select[name=value_type]').dispatchEvent(new Event('change'));
</script>

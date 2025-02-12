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

$key = 0;
$base_path = rtrim(Uri::root(),'/').'/administrator';
?>
<div class="j2store-product-options">
    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PRODUCT_OPTIONS');?></legend>
        <table id="attribute_options_table" class="table itemList align-middle j2store">
					<thead>
						<tr>
                    <th scope="col"><?php echo Text::_('J2STORE_OPTION_NAME');?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_OPTION_REQUIRED');?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_OPTION_ORDERING');?></th>
                    <th scope="col" class="text-end"><?php echo Text::_('J2STORE_OPTION_REMOVE');?></th>
						</tr>
				</thead>
				<tbody>
					<?php if(isset($this->item->product_options ) && !empty($this->item->product_options)):

					?>
					<?php foreach($this->item->product_options as  $poption ):?>
					<tr id="pao_current_option_<?php echo $poption->j2store_productoption_id;?>">
						<td>
							<?php echo $this->escape($poption->option_name);?>
							<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
							<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
							<small>(<?php  echo $this->escape($poption->option_unique_name);?>)</small>
                            <small><?php Text::_('J2STORE_OPTION_TYPE');?><?php echo Text::_('J2STORE_'.strtoupper($poption->type))?></small>
							<?php if(isset($poption->type) && ($poption->type =='select' || $poption->type =='radio' || $poption->type =='checkbox')):?>
                                <a class="small ms-2" data-fancybox data-type="iframe" data-src="<?php echo $base_path."/index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$this->item->j2store_product_id."&productoption_id=".$poption->j2store_productoption_id."&layout=productoptionvalues&tmpl=component";?>" href="javascript:;">
						            <?php echo Text::_( "J2STORE_OPTION_SET_VALUES" );?>
                                </a>
							<?php endif;?>
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
</div>

<script type="text/javascript">
var key =<?php echo $key;?>;
function addOption() {
    (function ($) {
        var option_value = $('#option_select_id').val();
        var option_name = $('#option_select_id option[value='+option_value+']').html();
        console.log(option_value);
        console.log(option_name);
        $('<tr id=\"j2store-op-tr-'+key+'\"><td class=\"addedOption\">' + option_name + '</td><td><select name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][required]\" ><option value=\"0\"><?php echo Text::_('J2STORE_NO');?></option><option value=\"1\"><?php echo Text::_('J2STORE_YES'); ?></option></select></td><td><input class=\"input-small\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][ordering]\" value=\"0\"></td><td><span class=\"optionRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\">x</span><input type=\"hidden\" value=\"' + option_value+ '\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][option_id]\" /><input type=\"hidden\" value="" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key +'][j2store_productoption_id]\" /> </td></tr>').insertBefore('.j2store_a_options');
        key++;
    })(j2store.jQuery);
}
</script>

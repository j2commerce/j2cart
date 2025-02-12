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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php';
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
$this->prefix = 'jform[prices]';
$row_class = 'row';
$col_class = 'col-md-';

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);
?>
<div class="j2store px-lg-4">
	<?php if(isset($this->variant_id) && $this->variant_id > 0): ?>
	<form class="form-horizontal form-validate" id="adminForm" 	name="adminForm" method="post" action="index.php">
		<?php echo  J2Html::hidden('option','com_j2store');?>
		<?php echo  J2Html::hidden('view','products');?>
		<?php echo  J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo  J2Html::hidden('variant_id', $this->variant_id, array('id'=>'variant_id'));?>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
	<div class="note <?php echo $row_class;?> mb-3">
        <fieldset class="options-form">
            <legend><?php echo Text::_('J2STORE_PRODUCT_ADD_PRICING');?></legend>
            <table class="adminlist table itemList">
			<thead>
				<tr>
                        <th scope="col" class="w-40"><?php echo Text::_('J2STORE_PRODUCT_PRICE_DATE_RANGE');?><span class="fas fa-solid fa-exclamation-circle ms-1" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_OPTIONAL');?>"></span></th>
                        <th scope="col" class="w-20"><?php echo Text::_('J2STORE_PRODUCT_PRICE_QUANTITY_RANGE');?><span class="fas fa-solid fa-exclamation-circle ms-1" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_OPTIONAL');?>"></span></th>
                        <th scope="col" class="w-10"><?php echo Text::_('J2STORE_PRODUCT_PRICE_GROUP_RANGE');?></th>
                        <th scope="col" class="w-20"><?php echo Text::_('J2STORE_PRODUCT_PRICE_VALUE');?></th>
                        <th scope="col" class="w-10"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
                        <div class="input-group">
					        <?php echo J2Html::calendar('date_from','',array('class'=>'form-control','id'=>'price_date_from','format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
                            <span class="input-group-text mx-2"><?php echo Text::_('J2STORE_TO');?></span>
					        <?php echo J2Html::calendar('date_to','',array('class'=>'form-control','id'=>'price_date_to','format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
                        </div>
					</td>
					<td>
                        <div class="input-group">
					        <?php echo J2Html::text('quantity_from', '',array('class'=>'form-control')); ?>
                            <span class="input-group-text"><?php echo Text::_('J2STORE_QUANTITY_AND_ABOVE');?></span>
                        </div>
					</td>
					<td>
				        <?php echo HTMLHelper::_('select.genericlist', $this->groups, 'customer_group_id', array('class'=>'form-select'), 'value', 'text',''); ?>
					</td>
					<td>
				        <?php echo J2Html::price('price','',array('class'=>'form-control')); ?>
					</td>
                    <td class="text-end">
                        <button class="btn btn-success" onclick="document.getElementById('task').value='createproductprice'; document.adminForm.submit();">
					        <?php echo Text::_('J2STORE_PRODUCT_CREATE_PRICE'); ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
        </fieldset>
	</div>

	<div class="note_green <?php echo $row_class;?>">
        <fieldset class="options-form">
            <legend><?php echo Text::_('J2STORE_PRODUCT_CURRENT_PRICES');?></legend>
            <div class="text-start">
                <button class="btn btn-success btn-sm" onclick="document.getElementById('task').value='saveproductprices'; document.adminForm.submit();">
			        <?php echo Text::_('J2STORE_PRODUCT_SAVE_ALL_PRICES'); ?>
							</button>
			</div>
            <table class="table itemList">
				<thead>
					<tr>
                        <th scope="col" class="w-40"><?php echo Text::_('J2STORE_PRODUCT_PRICE_DATE_RANGE');?><span class="fas fa-solid fa-exclamation-circle ms-1" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_OPTIONAL');?>"></span></th>
                        <th scope="col" class="w-20"><?php echo Text::_('J2STORE_PRODUCT_PRICE_QUANTITY_RANGE');?><span class="fas fa-solid fa-exclamation-circle ms-1" data-bs-toggle="tooltip" title="<?php echo Text::_('J2STORE_OPTIONAL');?>"></span></th>
                        <th scope="col" class="w-10"><?php echo Text::_('J2STORE_PRODUCT_PRICE_GROUP_RANGE');?></th>
                        <th scope="col" class="w-20"><?php echo Text::_('J2STORE_PRODUCT_PRICE_VALUE');?></th>
                        <th scope="col" class="w-10"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if(isset($this->prices) && !empty($this->prices)):
                            $utility = J2Store::utilities();
					foreach($this->prices as $key => $pricing):?>
					<tr class="row<?php echo $key%2;?>" id="productprice-row-<?php echo $pricing->j2store_productprice_id;?>">
						<td>
                                <div class="input-group">
	                                <?php echo J2Html::calendar($this->prefix."[$pricing->j2store_productprice_id][date_from]",$utility->convert_utc_current($pricing->date_from),array('class'=>'form-control','id'=>"price_date_from_$key",'format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
                                    <span class="input-group-text mx-2"><?php echo Text::_('J2STORE_TO');?></span>
	                                <?php echo J2Html::calendar($this->prefix."[$pricing->j2store_productprice_id][date_to]",$utility->convert_utc_current($pricing->date_to),array('class'=>'form-control','id'=>"price_date_to_$key",'format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
                                </div>
						</td>
						<td>
                                <div class="input-group">
	                                <?php echo J2Html::text($this->prefix."[$pricing->j2store_productprice_id][quantity_from]",$pricing->quantity_from,array('class'=>'form-control')); ?>
                                    <span class="input-group-text"><?php echo Text::_('J2STORE_QUANTITY_AND_ABOVE');?></span>
                                </div>
						</td>
						<td>
                                <?php echo HTMLHelper::_('select.genericlist', $this->groups, $this->prefix."[$pricing->j2store_productprice_id][customer_group_id]", array('class'=>'form-select'), 'value', 'text',$pricing->customer_group_id);?>
						</td>
						<td>
                                <?php echo J2Html::price_with_data($this->prefix, $pricing->j2store_productprice_id, "[$pricing->j2store_productprice_id][price]",$pricing->price,array('class'=>'form-control'), $pricing); ?>
							<?php echo J2Html::hidden($this->prefix."[$pricing->j2store_productprice_id][j2store_productprice_id]",$pricing->j2store_productprice_id,array('id'=>"product_price_id_$pricing->j2store_productprice_id"));?>
							<?php echo J2Html::hidden($this->prefix."[$pricing->j2store_productprice_id][variant_id]",$pricing->variant_id,array('id'=>"variant_id_$pricing->j2store_productprice_id"));?>
						</td>
                            <td class="text-end">
                                <a class="btn btn-danger" href="index.php?option=com_j2store&view=products&task=removeproductprice&variant_id=<?php echo $pricing->variant_id;?>&productprice_id=<?php echo $pricing->j2store_productprice_id; ?>&cid[]=<?php echo $pricing->j2store_productprice_id;?>">
                                    <?php echo Text::_('J2STORE_REMOVE');?>
							</a>
						</td>
					</tr>
					<?php endforeach;?>
					<?php endif;?>
				</tbody>
			</table>
        </fieldset>
		</div>
	</form>
	<?php else: ?>
	    <?php echo JText::_('J2STORE_NO_VARIANT_FOUND'); ?>
	<?php endif;?>
</div>

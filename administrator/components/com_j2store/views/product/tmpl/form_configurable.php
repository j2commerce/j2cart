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

$this->variant = $this->item->variants;

//lengths
$this->lengths = J2Html::select()->clearState()
    ->type('genericlist')
    ->name($this->form_prefix.'[length_class_id]')
    ->value($this->variant->length_class_id)
    ->attribs(array('class'=>'form-select'))
    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
    ->hasOne('Lengths')
    ->setRelations(
        array (
            'fields' => array (
                'key'=>'j2store_length_id',
                'name'=>'length_title'
            )
        )
    )->getHtml();

//weights

$this->weights = J2Html::select()->clearState()
    ->type('genericlist')
    ->name($this->form_prefix.'[weight_class_id]')
    ->value($this->variant->weight_class_id)
    ->attribs(array('class'=>'form-select'))
    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
    ->hasOne('Weights')
    ->setRelations(
        array (
            'fields' => array (
                'key'=>'j2store_weight_id',
                'name'=>'weight_title'
            )
        )
    )->getHtml();

//backorder
$this->allow_backorder = J2Html::select()->clearState()
    ->type('genericlist')
    ->name($this->form_prefix.'[allow_backorder]')
    ->value($this->variant->allow_backorder)
    ->attribs(array('class'=>'form-select'))
    ->setPlaceHolders(
        array('0' => Text::_('COM_J2STORE_DO_NOT_ALLOW_BACKORDER'),
            '1' => Text::_('COM_J2STORE_DO_ALLOW_BACKORDER'),
            '2' => Text::_('COM_J2STORE_ALLOW_BUT_NOTIFY_CUSTOMER')
        ))
    ->getHtml();

$this->availability =J2Html::select()->clearState()
    ->type('genericlist')
    ->name($this->form_prefix.'[availability]')
    ->attribs(array('class'=>'form-select'))
    ->value($this->variant->availability)
    ->default(1)
    ->setPlaceHolders(
        array('0' => Text::_('COM_J2STORE_PRODUCT_OUT_OF_STOCK') ,
            '1'=> Text::_('COM_J2STORE_PRODUCT_IN_STOCK') ,
        )
    )
    ->getHtml();
$row_class = 'row';
$col_class = 'col-md-';

?>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'j2storetab', ['active' => 'generalTab', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'generalTab', Text::_('J2STORE_PRODUCT_TAB_GENERAL')); ?>
                    <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo isset($this->variant->j2store_variant_id) && !empty($this->variant->j2store_variant_id) ? $this->variant->j2store_variant_id: 0; ?>" />
                    <?php echo $this->loadTemplate('general');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'pricingTab', Text::_('J2STORE_PRODUCT_TAB_PRICE')); ?>
                    <?php  echo $this->loadTemplate('pricing');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'inventoryTab', Text::_('J2STORE_PRODUCT_TAB_INVENTORY')); ?>
                    <?php  echo $this->loadTemplate('inventory');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'imagesTab', Text::_('J2STORE_PRODUCT_TAB_IMAGES')); ?>
                    <?php  echo $this->loadTemplate('images');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'shippingTab', Text::_('J2STORE_PRODUCT_TAB_SHIPPING')); ?>
                    <?php  echo $this->loadTemplate('shipping');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'optionsTab', Text::_('J2STORE_PRODUCT_TAB_OPTIONS')); ?>
                    <?php  echo $this->loadTemplate('configoptions');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'filterTab', Text::_('J2STORE_PRODUCT_TAB_FILTER')); ?>
                    <?php  echo $this->loadTemplate('filters');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'relationsTab', Text::_('J2STORE_PRODUCT_TAB_RELATIONS')); ?>
                    <?php  echo $this->loadTemplate('relations');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'j2storetab', 'appsTab', Text::_('J2STORE_PRODUCT_TAB_APPS')); ?>
                    <?php  echo $this->loadTemplate('apps');?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
</div>

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

$this->variant = $this->item->variants;

//lengths
$this->lengths = J2Html::select()->clearState()
    ->type('genericlist')
    ->name($this->form_prefix.'[length_class_id]')
    ->value(isset($this->variant->length_class_id) && !empty($this->variant->length_class_id) ? $this->variant->length_class_id: 0)
    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
    ->hasOne('Lengths')
	->attribs(array('class'=>'form-select'))
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
    ->value(isset($this->variant->weight_class_id) && !empty($this->variant->weight_class_id) ? $this->variant->weight_class_id: 0)
    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
    ->hasOne('Weights')
	->attribs(array('class'=>'form-select'))
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
    ->value(isset($this->variant->allow_backorder) && !empty($this->variant->allow_backorder) ? $this->variant->allow_backorder: 0)
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
    ->value(isset($this->variant->availability) && !empty($this->variant->availability) ? $this->variant->availability: 0)
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
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.startTabSet', 'j2storetab', ['active' => 'generalTab', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'generalTab', Text::_('J2STORE_PRODUCT_TAB_GENERAL')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo isset($this->variant->j2store_variant_id) && !empty($this->variant->j2store_variant_id) ? $this->variant->j2store_variant_id: 0; ?>" />
                    <?php echo $this->loadTemplate('general');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'pricingTab', Text::_('J2STORE_PRODUCT_TAB_PRICE')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('pricing');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'inventoryTab', Text::_('J2STORE_PRODUCT_TAB_INVENTORY')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('inventory');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'imagesTab', Text::_('J2STORE_PRODUCT_TAB_IMAGES')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('images');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'shippingTab', Text::_('J2STORE_PRODUCT_TAB_SHIPPING')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('shipping');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'optionsTab', Text::_('J2STORE_PRODUCT_TAB_OPTIONS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('options');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'filterTab', Text::_('J2STORE_PRODUCT_TAB_FILTER')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('filters');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'relationsTab', Text::_('J2STORE_PRODUCT_TAB_RELATIONS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('relations');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
        <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'appsTab', Text::_('J2STORE_PRODUCT_TAB_APPS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('apps');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
</div>

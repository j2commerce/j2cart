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


$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}

$j2params = J2Store::config();
$default_currency = $j2params->get('config_currency','USD');

$currency = J2Store::currency();


?>
<div class="px-2 pt-2 pb-0 mb-5">
    <div class="<?php echo $row_class;?>"  id="advanced-search-controls">
        <div class="col-lg-2 col-md-4 mb-2">
		    <?php echo J2Html::select()->clearState()
			    ->type('genericlist')
			    ->name('manufacturer_id')
			    ->value($this->state->manufacturer_id)
			    ->attribs(array('class'=>'form-select j2store-product-filters','onchange'=>'this.form.submit();'))
			    ->setPlaceHolders(
				    array(''=>Text::_('J2STORE_PRODUCT_MANUFACTURER'))
			    )
			    ->hasOne('Manufacturers')
			    ->setRelations( array(
					    'fields' => array (
						    'key' => 'j2store_manufacturer_id',
						    'name' => array('company')
					    )
				    )
			    )->getHtml();
		    ?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
		    <?php echo J2Html::select()->clearState()
			    ->type('genericlist')
			    ->name('vendor_id')
			    ->value($this->state->vendor_id)
			    ->setPlaceHolders(array(''=>Text::_('J2STORE_PRODUCT_VENDOR')))
			    ->attribs(array('class'=>'form-select j2store-product-filters','onchange'=>'this.form.submit();'))
			    ->hasOne('Vendors')
			    ->setRelations(
				    array (
					    'fields' => array
					    (
						    'key'=>'j2store_vendor_id',
						    'name'=>array('first_name','last_name')
					    )
				    )
			    )->getHtml();

		    ?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::select()->clearState()
		        ->type('genericlist')
		        ->name('taxprofile_id')
		        ->value($this->state->taxprofile_id)
		        ->attribs(array('class'=>'form-select j2store-product-filters','onchange'=>'this.form.submit();'))
		        ->setPlaceHolders(array('' => Text::_('J2STORE_PRODUCT_TAX_PROFILE')))
		        ->hasOne('Taxprofiles')
		        ->setRelations(
			        array (
				        'fields' => array (
					        'key'=>'j2store_taxprofile_id',
					        'name'=>'taxprofile_name'
				        )
			        )
		        )->getHtml();

	        ?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::select()->clearState()
		        ->type('genericlist')
		        ->name('visible')
		        ->value($this->state->visible)
		        ->attribs(array('class'=>'form-select j2store-product-filters','onchange'=>'this.form.submit();'))
		        ->setPlaceHolders(array(
			        '' => Text::_('J2STORE_PRODUCT_VISIBILITY'),
			        1 => Text::_('J2STORE_YES'),
			        0 => Text::_('J2STORE_NO')
		        ))
		        ->getHtml();
	        ?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2html::calendar('since',$this->state->since,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_FROM')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2html::calendar('until',$this->state->until,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_TO')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2html::text('productid_from',$this->state->productid_from,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_PRODUCT_ID_FROM')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2html::text('productid_to',$this->state->productid_to,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_PRODUCT_ID_TO')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <div class="input-group">
                <span class="input-group-text"><?php echo $currency->getSymbol();?></span>
	            <?php echo J2html::text('pricefrom',$this->state->pricefrom,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_PRODUCT_REGULAR_PRICE_FROM')));?>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <div class="input-group">
                <span class="input-group-text"><?php echo $currency->getSymbol();?></span>
	            <?php echo J2html::text('priceto',$this->state->priceto,array('class'=>'form-control j2store-product-filters', 'placeholder'=>Text::_('J2STORE_PRODUCT_REGULAR_PRICE_TO')));?>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-2 text-center text-lg-start">
            <div class="advanced-filter-search-actions btn-group">
	            <?php echo J2Html::buttontype('advanced_search',Text::_('J2STORE_APPLY_FILTER'),array('class'=>'btn btn-success' ,'onclick'=>'this.form.submit();'));?>
	            <?php echo J2Html::buttontype('reset_advanced_filters',Text::_('JCLEAR'),array('class'=>'btn btn-primary' ,'onclick'=>'resetAdvancedFilters()'));?>
            </div>
        </div>
    </div>
</div>

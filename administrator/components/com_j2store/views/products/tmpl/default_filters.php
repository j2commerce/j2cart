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

$search = htmlspecialchars($this->state->search);

HTMLHelper::_('bootstrap.collapse', '[data-bs-toggle="collapse"]');

$this->product_types[0] = Text::_('J2STORE_PRODUCT_TYPE');
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$script = "function j2storeResetAllFilters(){document.querySelectorAll('.j2store-product-filters').forEach(function(e){e.value=''});document.getElementById('search').value='';document.getElementById('j2store_product_type').value='';document.getElementById('adminForm').submit()}function resetAdvancedFilters(){document.querySelectorAll('#advanced-search-controls .j2store-product-filters').forEach(function(e){e.value=''});document.getElementById('adminForm').submit()}";
$wa->addInlineScript($script, [], []);

?>
<div class="btn-toolbar w-100 justify-content-end mb-3">
    <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
        <div class="input-group w-100">
	        <?php echo J2Html::text('search',$search,array('id'=>'search' ,'class'=>'form-control j2store-product-filters','placeholder'=>Text::_( 'J2STORE_FILTER_SEARCH' )));?>
            <span class="filter-search-bar__label visually-hidden">
                <label id="search-lbl" for="search"><?php echo Text::_( 'J2STORE_FILTER_SEARCH' ); ?></label>
            </span>
	        <?php echo J2Html::buttontype('go','<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>' ,array('class'=>'btn btn-primary','onclick'=>'this.form.submit();'));?>
        </div>
    </div>

    <div class="filter-search-actions btn-group ms-lg-2 flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
        <button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-filter w-100" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
            <?php echo Text::_('JFILTER_OPTIONS');?><span class="icon-angle-down ms-1" aria-hidden="true"></span>
        </button>
        <?php echo J2Html::buttontype('reset',Text::_( 'JCLEAR' ),array('id'=>'reset-all-filter','class'=>'btn btn-primary' ,'onclick'=>'j2storeResetAllFilters();'));?>
    </div>
    <div class="ordering-select d-flex gap-2 ms-lg-2  flex-grow-1 flex-lg-grow-0">
	    <?php echo J2Html::select()->clearState()
		    ->type('genericlist')
		    ->name('product_type')
		    ->attribs(array('class'=>'form-select j2store-product-filters w-100','onchange'=>'this.form.submit();'))
		    ->value($this->state->product_type)
		    ->setPlaceHolders($this->product_types)
		    ->getHtml();
	    ?>
	    <?php echo $this->pagination->getLimitBox();?>
    </div>
</div>



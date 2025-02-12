<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;


$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$secondary_button = 'btn btn-dark';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
$secondary_button = 'btn btn-inverse';
}
$search = htmlspecialchars($this->state->search);
?>
<div class="btn-toolbar w-100 justify-content-end mb-3">
    <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
        <div class="input-group w-100">
		    <?php echo J2Html::text('search',$search,array('id'=>'search' ,'class'=>'form-control j2store-product-filters','placeholder'=>Text::_( 'J2STORE_FILTER_SEARCH' )));?>
            <span class="filter-search-bar__label visually-hidden">
                <label id="search-lbl" for="search"><?php echo Text::_( 'J2STORE_FILTER_SEARCH' ); ?></label>
            </span>
		    <?php echo J2Html::buttontype('go','<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>' ,array('class'=>'btn btn-primary','onclick'=>'this.form.submit();'));?>
	        <?php echo J2Html::buttontype('reset', Text::_('JCLEAR'), array('id' => 'reset-filter-search', 'class' => 'btn btn-primary d-inline-block d-lg-none')); ?>
        </div>
    </div>
    <div class="filter-search-actions btn-group ms-lg-2 flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
        <button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-filter w-100" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
            <?php echo Text::_('JFILTER_OPTIONS');?><span class="icon-angle-down ms-1" aria-hidden="true"></span>
        </button>
		<?php echo J2Html::buttontype('reset',Text::_( 'JCLEAR' ),array('id'=>'reset-filter','class'=>'btn btn-primary'));?>
    </div>
    <div class="ordering-select d-flex gap-2 ms-lg-2 flex-grow-1 flex-lg-grow-0">
	    <?php echo J2Html::select()
		    ->type('genericlist')
		    ->name('orderstate')
		    ->value($this->state->orderstate)
		    ->attribs(array('onchange' => 'this.form.submit();', 'class' => 'form-select j2store-product-filters w-100'))
		    ->setPlaceHolders(array('' => Text::_('J2STORE_SELECT_STATUS')))
		    ->hasOne('Orderstatuses')
		    ->ordering('ordering')
		    ->setRelations(
			    array(
				    'fields' => array
				    (
					    'key' => 'j2store_orderstatus_id',
					    'name' => 'orderstatus_name'
				    )
			    )
		    )->getHtml();
	    ?>
		<?php echo $this->pagination->getLimitBox(); ?>
    </div>
</div>

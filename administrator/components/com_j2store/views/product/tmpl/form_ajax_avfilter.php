<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="alert alert-info alert-block mb-3">
    <strong><?php echo Text::_('J2STORE_NOTE'); ?></strong> <?php echo Text::_('J2STORE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS'); ?>
</div>
<div class="table-responsive">
    <table id="product_filters_table" class="table itemList j2store">
        <thead>
        <tr>
            <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILTER_VALUE');?></th>
            <th scope="col" class="w-1 text-center"><?php echo Text::_('J2STORE_REMOVE');?></th>
        </tr>
        </thead>
        <tbody>
            <?php if(isset($this->product_filters) && count($this->product_filters)): ?>
                <?php foreach($this->product_filters as $group_id=>$filters):?>
                    <tr>
                        <td colspan="2"><h4 class="mb-0"><?php echo Text::_($this->escape($filters['group_name'])); ?></h4></td>
                    </tr>
                    <?php foreach($filters['filters'] as $filter):
                        ?>
                        <tr id="product_filter_current_option_<?php echo $filter->filter_id;?>">
                            <td class="addedFilter">
                                <?php echo $this->escape($filter->filter_name) ;?>
                            </td>
                            <td class="text-center">
                                <span class="filterRemove" onclick="removeFilter(<?php echo $filter->filter_id; ?>, <?php echo $this->item->j2store_product_id; ?>);">
                                    <span class="icon icon-trash text-danger"></span>
                                </span>
                                <input type="hidden" value="<?php echo $filter->filter_id;?>" name="<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]" />
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php endforeach;?>
            <?php endif;?>
            <tr class="j2store_a_filter">
                <td colspan="2">
                    <small><strong><?php echo Text::_('J2STORE_SEARCH_AND_PRODUCT_FILTERS');?></strong></small>
	                <?php echo J2Html::text('productfilter' ,'' ,array('id' =>'J2StoreproductFilter','class'=>'form-control ms-2'));?>
                </td>
            </tr>
        </tbody>
    </table>
</div>


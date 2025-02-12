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
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')->useScript('multiselect');

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);

$style = '.input-group-sm>.form-select.form-select-sm{padding-right:1.5rem;background-size: max(100%, 70rem);max-width: 120px;min-width: 120px;}.cursor-pointer{cursor:pointer;}';
$wa->addInlineStyle($style, [], []);
?>
<div class="table-responsive">
    <table class="table itemList align-middle" id="orderList">
        <caption class="visually-hidden">
		    <?php echo Text::_('J2STORE_ORDERS'); ?>,
            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
        </caption>
        <thead>
            <tr>
                <td class="w-1 text-center"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></td>
                <th scope="col" class="d-none"><?php echo HTMLHelper::_('grid.sort','J2STORE_INVOICE_TITLE', 'invoice',$this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col" class="title"><?php echo HTMLHelper::_('grid.sort','J2STORE_ORDER_SETTINGS', 'order_id', $this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col"><?php echo HTMLHelper::_('grid.sort','J2STORE_ORDER_DATE', 'created_on',$this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col" class="title"><?php echo HTMLHelper::_('grid.sort','J2STORE_CUSTOMER', 'billing_first_name',$this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col"><?php echo HTMLHelper::_('grid.sort','J2STORE_TOTAL', 'order_total',$this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col"><?php echo HTMLHelper::_('grid.sort','J2STORE_PAYMENT', 'orderpayment_type', $this->state->filter_order_Dir,$this->state->filter_order); ?>
                </th>
                <th scope="col"><?php echo Text::_('J2STORE_ORDER_STATUS'); ?></th>
                <?php echo J2Store::plugin()->eventWithHtml('AdminOrderListTab', array($this->state))?>
                <th scope="col"><?php echo Text::_('J2STORE_ACTIONS');?></th>
            </tr>
        </thead>
        <tbody>
            <?php if($this->items && !empty($this->items)):
                foreach($this->items as $i=> $row):
                $link 	= Route::_( 'index.php?option=com_j2store&view=order&id='.$row->j2store_order_id  );
                $checked = HTMLHelper::_('grid.id', $i, $row->j2store_order_id );
                $order = F0FTable::getInstance('Order', 'J2StoreTable');
                $order->load(array('order_id'=>$row->order_id));
            ?>
                <tr>
                    <td class="w-1 text-center"><?php echo $checked; ?></td>
                    <td class="small">
                        <a href="<?php echo $link ?>" title="<?php echo Text::_( 'J2STORE_ORDER_VIEW' );?>::<?php echo $this->escape($row->order_id); ?>"><?php echo $this->escape($row->invoice); ?></a>
                    </td>
                    <td class="small">
                        <a href="<?php echo $link ?>" title="<?php echo Text::_( 'J2STORE_ORDER_VIEW');?>::<?php echo $this->escape($row->order_id); ?>"> <?php echo $this->escape($row->order_id); ?></a>
                    </td>

                    <td class="small"><?php  echo HTMLHelper::_('date',$row->created_on, $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'))); ?></td>
                    <td class="small">
                        <span class="me-1"><?php echo $row->billing_first_name .' '.$row->billing_last_name; ?></span>
                        <span>(<?php echo $row->user_email;?>)</span>
                        <?php if($row->user_id == 0): ?>
                            <span class="fas fa-solid fa-user-slash text-warning ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('J2STORE_GUEST')?>"></span>
                        <?php endif;?>
                        <?php if($row->discount_code):?>
                            <span class="fas fa-solid fa-scissors fa-cut ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('J2STORE_COUPON_CODE');?>:<?php echo $row->discount_code;?>"></span>
                        <?php endif;?>
                    </td>

                    <td class="small"><?php echo $this->currency->format( $order->get_formatted_grandtotal(), $row->currency_code, $row->currency_value ); ?></td>
                    <td class="small"><?php echo Text::_($row->orderpayment_type); ?></td>
                    <td class="small">
                        <?php
                        $keywords = ['success', 'info', 'primary', 'warning', 'danger', 'important'];
                        $foundKeyword = null;
                        foreach ($keywords as $keyword) {
                            if (str_contains($row->orderstatus_cssclass, $keyword)) {
                                if($keyword == 'important'){
                                    $foundKeyword = 'danger';
                                } else {
                                    $foundKeyword = $keyword;
                                }
                                break;
                            } else {
                                $foundKeyword = 'secondary';
                            }
                        }
                        ?>
                        <a href="<?php echo $link ?>" title="<?php echo Text::_( 'J2STORE_ORDER_STATUS');?>" class="badge rounded-2 px-2 text-bg-<?php echo $foundKeyword;?>"> <?php echo Text::_($row->orderstatus_name); ?></a>
                    </td>
                    <?php echo J2Store::plugin ()->eventWithHtml ( 'AdminOrderListTabContent', array($row))?>
                    <td class="small">
                        <?php $print_url = Route::_('index.php?option=com_j2store&view=orders&task=printOrder&tmpl=component&order_id='.$row->order_id);?>
                        <?php $edit_url = Route::_('index.php?option=com_j2store&view=orders&task=createOrder&oid='.$row->j2store_order_id);?>
                        <input type="hidden" name="return" value="orders" />
                        <div class="d-flex">
                            <div class="status-selector">
                                <div class="input-group input-group-sm mb-0">
                                    <?php $attr = array("class"=>"form-select form-select-sm form-select-border-".$foundKeyword , "id"=>"order_state_id_".$row->j2store_order_id);?>
                                    <?php echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name('order_state_id')
                                        ->value($row->order_state_id)
                                        ->idTag('order_state_id_'.$row->j2store_order_id)
                                        ->attribs($attr)
                                        ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
                                        ->hasOne('Orderstatuses')
                                        ->ordering('ordering')
                                        ->setRelations(
                                            array (
                                                'fields' => array
                                                (
                                                    'key'=>'j2store_orderstatus_id',
                                                    'name'=>'orderstatus_name'
                                                )
                                            )
                                        )->getHtml();

                                    ?>
                                    <button class="btn btn-primary btn-sm" id="order-list-save_<?php echo $row->j2store_order_id;?>" type="button" onclick="submitOrderState('<?php echo $row->j2store_order_id; ?>','<?php echo $row->order_id; ?>')"><?php echo Text::_('J2STORE_ORDER_STATUS_SAVE'); ?></button>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="notify_customer" id="notify_customer_<?php echo $row->j2store_order_id;?>" value="1">
                                    <label class="form-check-label cursor-pointer" for="notify_customer_<?php echo $row->j2store_order_id;?>"><?php echo Text::_('J2STORE_NOTIFY_CUSTOMER');?><span class="fas fa-solid fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('J2STORE_NOTIFY_CUSTOMER_TOOLTIP')?>"></span></label>
                                </div>
                            </div>
                            <div class="action-buttons ms-auto">
                                <a class="btn btn-link btn-sm" href="<?php echo $print_url;?>" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('J2STORE_PRINT_INVOICE')?>"><span class="fas fa-solid fa-print"></span></a>
                                <a class="btn btn-link btn-sm" href="<?php echo $edit_url;?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo Text::_('JGLOBAL_EDIT')?>"><span class="icon icon-pencil"></span></a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach;?>
            <?php else:?>
            <tr>
                <td colspan="10">
                    <?php echo Text::_('J2STORE_NO_RESULTS_FOUND');?>
                </td>
            </tr>
            <?php endif;?>
        </tbody>
    </table>
	<?php echo $this->pagination->getListFooter(); ?>
</div>

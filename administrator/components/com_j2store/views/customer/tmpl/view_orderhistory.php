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

$platform = J2Store::platform();
$j2params = J2Store::config();
$dateFormat = $j2params->get('date_format');
?>
<div class="col-12 mb-4">
    <div class="card">
        <div class="card-header">
            <h3 class="mb-0"><?php echo Text::_('J2STORE_ORDER_HISTORY');?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table itemList align-middle" id="orderList">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_("J2STORE_ORDER_DATE"); ?></th>
                            <th scope="col"><?php echo Text::_("J2STORE_ORDER_SETTINGS"); ?></th>
                            <th scope="col"><?php echo Text::_("J2STORE_TOTAL"); ?></th>
                            <th scope="col"><?php echo Text::_("J2STORE_ORDER_STATUS"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
	                <?php if($this->orders && !empty($this->orders)):
			            foreach($this->orders as $order):?>
	                        <tr>
                                <td class="text-subdued">
	                                <?php echo HTMLHelper::_('date',$order->created_on, $dateFormat); ?>
		                        </td>
		                        <td>
			                        <a href="<?php echo 'index.php?option=com_j2store&view=order&id='.$order->j2store_order_id;?>">
					                    <?php echo $order->order_id;?>
                                    </a>
                                </td>
                                <td class="text-subdued">
                                    <?php echo $this->currency->format($order->order_total,$order->currency_code,$order->currency_value);?>
                                </td>
                                <td>
                                    <?php echo J2Html::getOrderStatusHtml($order->order_state_id);?>
                                </td>
	                        </tr>
	                    <?php endforeach;?>
	                <?php endif;?>
                    </tbody>
                </table>
            </div>
	        <?php echo J2Store::plugin()->eventWithHtml('BeforeCustomerOrderList',array($this->orders)); ?>
        </div>
    </div>
</div>



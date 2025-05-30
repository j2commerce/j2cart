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
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.framework',true);

$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-md-';
?>
<?php if (!empty($sidebar)): ?>
    <div id="j2c-menu" class="mb-4">
        <?php echo $sidebar; ?>
    </div>
<?php endif; ?>
<div class="j2store j2store-voucher-history">
    <div class="js-stools mt-4 mb-3">
        <div class="js-stools-container-bar">
            <div class="btn-toolbar gap-2 align-items-center">
                <h2><?php echo Text::_('J2STORE_VOUCHER_HISTORY'); ?> : <?php echo $this->voucher->voucher_code?></h2>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table itemList align-middle">
            <thead>
                <tr>
                    <th scope="col"><?php echo Text::_('J2STORE_INVOICE')?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_ORDER_ID')?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_CUSTOMER')?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_AMOUNT')?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_DATE')?></th>
                </tr>
	        </thead>
	        <tbody>
                <?php if(count($this->vouchers)): ?>
                    <?php foreach($this->vouchers as $item): ?>
                        <?php $link = 'index.php?option=com_j2store&view=order&id='.$item->order->j2store_order_id; ?>
                        <tr>
                            <td>
                                <a href="<?php echo $link; ?>" target="_blank">
                                    <?php echo $item->order->getInvoiceNumber(); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo $link; ?>" target="_blank">
                                    <?php echo $item->order_id; ?>
                                </a>
                            </td>
                            <td><?php echo $item->order->user_email; ?></td>
                            <td><?php echo $item->discount_amount; ?></td>
                            <td><?php echo HTMLHelper::_('date', $item->order->created_on, $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'))); ?></td>
                        </tr>
                    <?php endforeach;?>
	            <?php else:?>
                    <tr>
                        <td colspan="5">
                            <?php echo Text::_('J2STORE_NO_RESULTS_FOUND');?>
                        </td>
                    </tr>
	            <?php endif;?>
	        </tbody>
        </table>
    </div>
</div>

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

$firstKey = array_key_first($this->orderhistory);
$lastKey = array_key_last($this->orderhistory);
?>
<div class="card mb-4 j2store-order-history border-0 shadow-none bg-transparent">
    <div class="card-header justify-content-between">
        <h3 class="mb-0"><?php echo Text::_('J2STORE_ORDER_HISTORY');?></h3>
    </div>
    <div class="p-3">
		<?php foreach($this->orderhistory as $key => $history):
			$item = F0FModel::getTmpInstance('OrderStatuses', 'J2StoreModel')->getItem($history->order_state_id);
			$keywords = ['success', 'info', 'primary', 'warning', 'danger', 'important'];
			$foundKeyword = null;
			foreach ($keywords as $keyword) {
				if (strpos($item->orderstatus_cssclass, $keyword) !== false) {
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
            <?php if($key == $firstKey) {
                $col1 = '';
                $col2 = ' border-end';
                $icon = 'fas fa-solid fa-cart-plus fa-fw text-'.$foundKeyword;
            } elseif($key == $lastKey) {
                $col1 = ' border-end';
                $col2 = '';
                $icon = 'fas fa-solid border border-2 rounded-circle border-white fa-circle fa-fw text-'.$foundKeyword;
            } else {
                $col1 = ' border-end';
                $col2 = ' border-end';
			    $icon = 'fas fa-solid border border-2 rounded-circle border-white fa-circle fa-fw text-'.$foundKeyword;
            }
			if (strpos($history->comment, 'notified with') !== false) {
				$icon = 'fas fa-solid fa-envelope fa-fw text-' . $foundKeyword;
			} elseif (strpos($history->comment, 'item removed') !== false) {
			    $icon = 'fas fa-solid fa-trash fa-fw text-'.$foundKeyword;
            } else {
				$icon = $icon;
            }
            ?>
            <div class="row">
                <div class="col-auto text-center flex-column d-none d-lg-flex">
                    <div class="row h-50 mb-n1">
                        <div class="col<?php echo $col1;?>"></div>
                        <div class="col"></div>
                    </div>
                    <h5 class="m-2">
                        <i class="<?php echo $icon;?>"></i>
                    </h5>
                    <div class="row h-50">
                        <div class="col<?php echo $col2;?>"></div>
                        <div class="col"></div>
                    </div>
                </div>
                <div class="col py-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="float-end text-subdued small fw-bold"><?php echo HTMLHelper::_('date', $history->created_on, $this->params->get('date_format', Text::_('DATE_FORMAT_LC1'))); ?></div>
                            <?php if($history->order_state_id):?>
                                <h4 class="card-title text-subdued small"><div class="badge rounded-2 px-2 text-bg-<?php echo $foundKeyword;?>"><?php echo Text::_($item->orderstatus_name);?></div><?php //echo J2Html::getOrderStatusHtml($history->order_state_id);?></h4>
                            <?php endif;?>

                            <p class="card-text text-subdued small"><?php echo Text::_($history->comment);?></p>
                        </div>
                    </div>
	            </div>
            </div>
	    <?php endforeach;?>
    </div>
</div>

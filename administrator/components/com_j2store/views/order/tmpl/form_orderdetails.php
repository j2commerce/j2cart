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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);

$row_class = 'row';
$col_class = 'col-md-';

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$style = '.j2store-order-summary .min-ht-50 {min-height: 50px;}';
$wa->addInlineStyle($style, [], []);
?>
<div class="card mb-4 j2store-order-summary text-subdued">
    <div class="card-header justify-content-between">
        <h3 class="mb-0"><?php echo Text::_('J2STORE_ORDER_DETAILS');?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6 mb-4 "><?php echo $this->loadTemplate('payment');?></div>
            <div class="col-lg-6 mb-4 "><?php echo $this->loadTemplate('shipping');?></div>
			<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterPaymentInformation', array($this)); ?>
			<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterShippingInformation', array($this)); ?>
        </div>
    </div>
</div>

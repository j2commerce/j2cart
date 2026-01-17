<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$currency = J2Store::currency();
$order_status = $params->get('order_status', array('*'));
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
  $row_class = 'row-fluid';
  $col_class = 'span';
}
?>
<div class="card mb-5 mt-3">
  <div class="card-body">
    <nav class="quick-icons" aria-label="J2Commerce Mini Stats Notifications">
      <div class="row flex-wrap">
        <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
          <?php
          $tz = new DateTimeZone(Factory::getApplication()->getConfig()->get('offset'));
          $today = (new DateTime('now', $tz))->format('Y-m-d');
          $tommorow = (new DateTime('now +1 days', $tz))->format('Y-m-d');
          //$tz = Factory::getApplication()->getConfig()->get('offset');
          //$today = Factory::getDate('now', $tz)->format('Y-m-d'); // DOES NOT WORK PROPERLY WITH TIME ZONES
          //$tommorow = Factory::getDate('now +1 days', $tz)->format('Y-m-d');
          ?>
          <div class="alert alert-success my-0 w-100 border-0">
            <div class="quickicon-info">
              <div class="quickicon-value display-6 mb-3">

                <?php echo $currency->format(
                  F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since($today)
                    ->until($tommorow)
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
                );
                ?>
              </div>
            </div>
            <div class="quickicon-name d-flex align-items-center">
                            <span class="j-links-link">
                                <div class="text-capitalize"><?php echo Text::_('J2STORE_TOTAL_CONFIRMED_ORDERS_TODAY'); ?></div>
                            </span>
            </div>
          </div>
        </div>
        <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
          <div class="alert alert-warning my-0 w-100 border-0">
            <div class="quickicon-info">
              <div class="quickicon-value display-6 mb-3">
                <?php
                $yesterday = (new DateTime('now -1 days', $tz))->format('Y-m-d');
                echo $currency->format(
                  F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since($yesterday)
                    ->until($yesterday . ' 23:59:59')
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
                );
                ?>
              </div>
            </div>
            <div class="quickicon-name d-flex align-items-center">
                            <span class="j-links-link">
                                <div class="text-capitalize"><?php echo Text::_('J2STORE_TOTAL_CONFIRMED_ORDERS_YESTERDAY'); ?></div>
                            </span>
            </div>
          </div>
        </div>
        <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
          <div class="alert alert-primary my-0 w-100 border-0">
            <div class="quickicon-info">
              <div class="quickicon-value display-6 mb-3">
                <?php
                switch (gmdate('m')) {
                  case 1:
                  case 3:
                  case 5:
                  case 7:
                  case 8:
                  case 10:
                  case 12:
                    $lmday = 31;
                    break;
                  case 4:
                  case 6:
                  case 9:
                  case 11:
                    $lmday = 30;
                    break;
                  case 2:
                    $y = gmdate('Y');
                    if (!($y % 4) && ($y % 400)) {
                      $lmday = 29;
                    } else {
                      $lmday = 28;
                    }
                }
                if ($lmday < 1) $lmday = 28;
                ?>
                <?php echo $currency->format(
                  F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since(gmdate('Y') . '-' . gmdate('m') . '-01')
                    ->until(gmdate('Y') . '-' . gmdate('m') . '-' . $lmday . ' 23:59:59')
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
                );
                ?>
              </div>
            </div>
            <div class="quickicon-name d-flex align-items-center">
                            <span class="j-links-link">
                                <div class="text-capitalize"><?php echo Text::_('J2STORE_TOTAL_CONFIRMED_ORDERS_THIS_MONTH'); ?></div>
                            </span>
            </div>
          </div>
        </div>
        <div class="quickicon quickicon-single col-12 col-md-6 col-lg-3 mb-3 mb-lg-0 border-0">
          <div class="alert alert-danger my-0 w-100 border-0">
            <div class="quickicon-info">
              <div class="quickicon-value display-6 mb-3">
                <?php echo $currency->format(F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()->orderstatus($order_status)->nozero(1)->moneysum(1)->getOrdersTotal()); ?>
              </div>
            </div>
            <div class="quickicon-name d-flex align-items-center">
                            <span class="j-links-link">
                                <div class="text-capitalize"><?php echo Text::_('J2STORE_ALL_TIME'); ?></div>
                            </span>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </div>
</div>

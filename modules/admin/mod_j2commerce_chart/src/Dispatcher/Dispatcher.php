<?php
/**
 * @package     Joomla.Module
 * @subpackage  J2Commerce.mod_j2store_chart
 *
 * @copyright Copyright (C) 2017 J2Store. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

namespace J2Commerce\Module\Chart\Administrator\Dispatcher;

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use J2Commerce\Module\Chart\Administrator\Helper\ChartHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/models/orders.php' );
/**
 * Dispatcher class for mod_j2commerce_chart
 *
 * @since  5.2.3
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.2.3
     */
    protected function getLayoutData()
    {
        $user = Factory::getApplication()->getIdentity();

        if(!$user->authorise('j2store.vieworder', 'com_j2store')) {
            return '';
        }
        $data = parent::getLayoutData();
        Factory::getApplication()->getLanguage()->load('com_j2store', JPATH_SITE);
        $order_model = \J2Store::fof()->getModel('Orders' ,'J2StoreModel');

        $helper = new ChartHelper;

        $chartArray = [
            0 => 'daily',
            1 => 'monthly',
            2 => 'yearly'
        ];
        $data['link_type'] = $data['params']->get('link_type','link');
	    $data['chart_type'] = $data['params']->get('chart_type',$chartArray);
	    $data['order_status'] = $data['params']->get('order_status',array());
        $data['orders'] = $helper->getOrders($data['order_status']);
        $data['years'] = $helper->getYear($data['order_status']);
        $data['months'] = $helper->getMonth($data['order_status']);
        $data['days'] = $helper->getDay($data['order_status']);

        return $data;
    }
}

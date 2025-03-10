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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

class J2StoreModelOrders extends F0FModel
{
	protected $_order = null;

	protected $_orders = [];

	protected function onAfterGetItem(&$record)
    {
		$status = J2Store::fof()->getModel('Orderstatuses', 'J2StoreModel')->getItem($record->order_state_id);
		$record->orderstatus_name = $status->orderstatus_name;
		$record->orderstatus_cssclass = $status->orderstatus_cssclass;
	}

	/**
	 * Method to preprocess the Orders list
	 * @param   array  &$order_list  An array of objects, each row representing a record
	 * @return  void
	 */
	protected function onProcessList(&$order_list)
	{
		// pre process the order list via plugins
		J2Store::plugin()->event('ProcessOrderList', array(&$order_list));
	}

	public function populateOrder($cartitems = array(), $order_id = null)
    {
		$orderTable = J2Store::fof()->loadTable('Order', 'J2StoreTable')->getClone();

		if ( $order_id > 0 && ( $orderTable->load(array('order_id'=>$order_id))) && $orderTable->has_status( array( 5 ) ) ) {
			$order = $orderTable;
			//Customer is resuming an order. So delete the children. We have to re-initialise the order object
			$order->updateOrder();
		}else{
			$order = J2Store::fof()->loadTable('Order', 'J2StoreTable')->getClone();
			$order->is_update = 0;
		}

		//get the cart items
		if(is_null($this->_order)) {
			if(!$cartitems) {
				$cart_model = J2Store::fof()->getModel('Carts', 'J2StoreModel');
				$cart_model->setCartType('cart');
				$cartitems = $cart_model->getItems();
			}
			$items = J2Store::fof()->getModel('OrderItems', 'J2StoreModel')->setItems($cartitems)->getItems();

			$order->setItems($items);
			$order->getTotals();
			$this->_order = $order;
		}

		return $this;
	}

	function getOrder()
    {
		return $this->_order;
	}

	function initOrder($order_id = null)
    {
		$cart_model = J2Store::fof()->getModel('Carts', 'J2StoreModel');
		$cart_model->setCartType('cart');
		$items = $cart_model->getItems();
		$this->populateOrder($items, $order_id);

		return $this;
	}

	function validateOrder(&$order)
    {
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$session = $app->getSession();

		$address_model = J2Store::fof()->getModel('Addresses', 'J2StoreModel');

		//check if items are in cart
		if($order->getItemCount() < 1) {
			throw new \Exception(Text::_('J2STORE_CART_NO_ITEMS'));
			return false;
		}

		//validate shipping

		//set shipping address
		if($user->id && $session->has('shipping_address_id', 'j2store')) {
			$shipping_address = $address_model->getItem($session->get('shipping_address_id', '', 'j2store'));
		} elseif($session->has('guest', 'j2store')) {
			$guest = $session->get('guest', array(), 'j2store');
			if(isset($guest['shipping'])) {
				$shipping_address = $guest['shipping'];
			}
		}else{
			$shipping_address = array();
		}

		$showShipping = false;
		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			if (empty($shipping_address)) {
				throw new \Exception(Text::_('J2STORE_CHECKOUT_NO_SHIPPING_ADDRESS_FOUND'));
				return false;
			}
		}else {
			$session->clear('shipping_method', 'j2store');
			$session->clear('shipping_values', 'j2store');
		}

		// Validate if billing address has been set.

		if ($user->id && $session->has('billing_address_id', 'j2store')) {
			$billing_address = $address_model->getItem($session->get('billing_address_id', '', 'j2store'));
		} elseif ($session->has('guest', 'j2store')) {
			$guest = $session->get('guest', array(), 'j2store');
			$billing_address = $guest['billing'];
		}

		if (empty($billing_address)) {
			throw new \Exception(Text::_('J2STORE_CHECKOUT_NO_BILLING_ADDRESS_FOUND'));
			return false;
		}

		return true;
	}

	function loadItemsTemplate($order,$receiver_type = '*')
    {
		static $sets;
		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$order->order_id] ) )
		{

			$app = Factory::getApplication();
			$html = ' ';

			if(!empty($order->customer_language)) {
				$lang = $app->getLanguage();
				$lang->load('com_j2store', JPATH_ADMINISTRATOR, $order->customer_language);
				$lang->load('com_j2store', JPATH_SITE, $order->customer_language);
			}
			$view = J2Store::view();

			$view->set( 'order', $order);
			$view->set( 'params', J2Store::config());
            $view->set( 'email_receiver', $receiver_type);
			$view->setDefaultViewPath(JPATH_SITE.'/components/com_j2store/views/myprofile/tmpl');
			$view->setTemplateOverridePath(JPATH_SITE.'/templates/'.$view->getTemplate().'/html/com_j2store/myprofile');
			//if templates are assigned to menu, then we got to fetch it.
			if(J2Store::platform()->isClient('site')) {
				$view->setTemplateOverridePath( JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/com_j2store/myprofile' );
			}
			$html = $view->getOutput('orderitems');
			$sets[$order->order_id] = $html;
		}

		return $sets[$order->order_id];
	}

	public function buildQuery($overrideLimites = false)
    {
        $db = $this->_db;
		$query = parent::buildQuery($overrideLimites);
		$query->select($db->quoteName('#__j2store_orderstatuses.orderstatus_name'));
		$query->select($db->quoteName('#__j2store_orderstatuses.orderstatus_cssclass'));
		$query->select("CASE WHEN #__j2store_orders.invoice_prefix IS NULL or #__j2store_orders.invoice_number = 0 THEN
						#__j2store_orders.j2store_order_id
  					ELSE
						CONCAT(#__j2store_orders.invoice_prefix, #__j2store_orders.invoice_number)
					END
				 	AS invoice");
		$query->join('LEFT OUTER', '#__j2store_orderstatuses ON #__j2store_orders.order_state_id = #__j2store_orderstatuses.j2store_orderstatus_id');
		$query->select($db->quoteName('#__j2store_orderinfos.billing_first_name'));
		$query->select($db->quoteName('#__j2store_orderinfos.billing_last_name'));
		$query->join('LEFT OUTER', '#__j2store_orderinfos ON #__j2store_orders.order_id = #__j2store_orderinfos.order_id');

		$limit_orderstatuses = $this->getState('orderstatuses', '*');
		$limit_orderstatuses = explode(',', $limit_orderstatuses);

		if(!in_array('*', $limit_orderstatuses)) {
			$query->where('#__j2store_orders.order_state_id IN ('.implode(',', $limit_orderstatuses).')');
		}

		return $query;
	}

	function getOrderList($overrideLimits = false, $group = '')
    {
		if (empty($this->_orders))
		{
			$query = $this->getOrderListQuery($overrideLimits);

			if (!$overrideLimits)
			{
				$limitstart = $this->getState('limitstart');
				$limit = $this->getState('limit');
                try {
                    $this->_orders = $this->_getList((string) $query, $limitstart, $limit, $group);
                } catch (\Exception $e) {

                }
			}
			else
			{
                try {
                    $this->_orders = $this->_getList((string) $query, 0, 0, $group);
                } catch (\Exception $e) {

                }
			}
		}

		return $this->_orders;
	}

	function getOrderListQuery($overrideLimits = false, $group = '')
    {
		$db = $this->_db;

		$query = $db->getQuery(true)->select('#__j2store_orders.*')->from('#__j2store_orders');

		$query->select($this->_db->quoteName('#__j2store_orderstatuses.orderstatus_name'));

		$query->select($this->_db->quoteName('#__j2store_orderstatuses.orderstatus_cssclass'));

		$query->select("CASE WHEN #__j2store_orders.invoice_prefix IS NULL or #__j2store_orders.invoice_number = 0 THEN
				#__j2store_orders.j2store_order_id
				ELSE
				CONCAT(#__j2store_orders.invoice_prefix, #__j2store_orders.invoice_number)
				END
				AS invoice");
		$query->join('LEFT OUTER', '#__j2store_orderstatuses ON #__j2store_orders.order_state_id = #__j2store_orderstatuses.j2store_orderstatus_id');

		//get orderinfo table columns.
		$fields = $db->gettableColumns('#__j2store_orderinfos');
		unset($fields['order_id']);
		unset($fields['j2store_orderinfo_id']);

		foreach (array_keys($fields) as $field) {
			$query->select('#__j2store_orderinfos.'.$field);
		}
		$query->join('LEFT OUTER', '#__j2store_orderinfos ON #__j2store_orders.order_id = #__j2store_orderinfos.order_id');

		$query->select(' ( SELECT #__j2store_countries.country_name FROM #__j2store_countries WHERE #__j2store_countries.j2store_country_id = #__j2store_orderinfos.billing_country_id ) as billingcountry_name');
		$query->select(' ( SELECT #__j2store_countries.country_name FROM #__j2store_countries WHERE #__j2store_countries.j2store_country_id = #__j2store_orderinfos.shipping_country_id ) as shippingcountry_name');
		$query->select(' ( SELECT #__j2store_zones.zone_name FROM #__j2store_zones WHERE #__j2store_zones.j2store_zone_id = #__j2store_orderinfos.billing_zone_id ) as billingzone_name');
		$query->select(' ( SELECT #__j2store_zones.zone_name FROM #__j2store_zones WHERE #__j2store_zones.j2store_zone_id = #__j2store_orderinfos.shipping_zone_id ) as shippingzone_name');

		$query->select($this->_db->quoteName('#__j2store_orderdiscounts.discount_code'));
		$query->join('LEFT OUTER', '#__j2store_orderdiscounts ON #__j2store_orders.order_id = #__j2store_orderdiscounts.order_id AND #__j2store_orderdiscounts.discount_type = '.$db->quote('coupon'));

		$query->select($this->_db->quoteName('#__j2store_ordershippings.ordershipping_name'));
		$query->select($this->_db->quoteName('#__j2store_ordershippings.ordershipping_tracking_id'));
		$query->join('LEFT OUTER', '#__j2store_ordershippings ON #__j2store_orders.order_id = #__j2store_ordershippings.order_id');

		$this->_buildTotalQueryWhere($query);
		$this->_buildQueryOrderBy($query);
        J2Store::plugin()->event('AfterOrderListQuery',array(&$query));

		return $query;
	}

	function buildCountQuery()
    {
		$subquery = $this->getOrderListQuery();
		$subquery->clear('order');
		$query = $this->_db->getQuery(true)
		->select('COUNT(*)')
		->from("(" . (string) $subquery . ") AS a");

		return $query;
	}

	function getOrdersTotal()
    {
		//run some basic ACL checks
		$user = Factory::getApplication()->getIdentity();
		if(!$user->authorise('j2store.vieworder', 'com_j2store')) {
			return '';
		}

        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$state = $this->getFilterValues();

		if($state->moneysum == 1) {
			$query->select('SUM(#__j2store_orders.order_total)');
		} else {
			$query->select('COUNT(*)');
		}
		$query->from('#__j2store_orders');
		$this->_buildTotalQueryWhere($query);
		//echo $query;
		$db->setQuery($query);

		return $db->loadResult();
	}

	protected function _buildQueryOrderBy(&$query)
    {
		$db = $this->_db;
		if(!empty($this->state->filter_order) && in_array($this->state->filter_order,array('invoice','order_id','created_on','order_total','orderpayment_type'))) {
            if(!in_array(strtolower($this->state->filter_order_Dir),array('asc','desc'))){
                $this->state->filter_order_Dir = 'desc';
            }
            $query->order($db->quoteName($this->state->filter_order).' '.$this->state->filter_order_Dir);
			//$query->order($this->state->filter_order.' '.$this->state->filter_order_Dir);
		}
		$query->order('#__j2store_orders.created_on DESC');
	}

	private function getFilterValues()
	{
		return (object)array(
				'search'		=> $this->getState('search','','string'),
				'title'			=> $this->getState('title','','string'),
				'user_id'		=> $this->getState('user_id',0,'int'),
				'order_id'		=> $this->getState('order_id',0,'int'),
				'orderstate'	=> $this->getState('orderstate',0,'int'),
				'processor'		=> $this->getState('processor','','string'),
				'paykey'		=> $this->getState('paykey','','string'),
				'since'			=> $this->getState('since',0,'string'),
				'until'			=> $this->getState('until',0,'string'),
				'groupbydate'	=> $this->getState('groupbydate',0,'int'),
				'groupbylevel'	=> $this->getState('groupbylevel',0,'int'),
				'moneysum'		=> $this->getState('moneysum',0,'float'),
				'coupon_id'		=> $this->getState('coupon_id',0,'int'),
				'coupon_code'	=> $this->getState('coupon_code',0,'string'),
				'nozero'		=> $this->getState('nozero',0,'int'),
				'frominvoice'	=> $this->getState('frominvoice',0,'int'),
				'toinvoice'		=> $this->getState('toinvoice',0,'int'),
				'orderstatus'	=> $this->getState('orderstatus',array()),
                'token'		    => $this->getState('token',''),
                'user_email'	=> $this->getState('user_email',''),
		);
	}

	function _buildTotalQueryWhere(&$query)
    {
		$app = Factory::getApplication();
		$db = $this->_db;
		$state = $this->getFilterValues();

		$loadChildOrders = $app->input->getInt('parent');
		if($loadChildOrders){
			$query->where(
				$db->quoteName('#__j2store_orders').'.'.$db->quoteName('parent_id').'='.$db->quote($loadChildOrders)
			);
		} else {
			$query->where(
				$db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_type').'='.$db->quote('normal')
			);
		}

		if(isset($state->orderstatus) && !empty($state->orderstatus) && is_array($state->orderstatus)) {
			if(!in_array('*' ,$state->orderstatus)){
				$query->where($db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_state_id').' IN ('.
					implode(',',$state->orderstatus).')');
			}
		}

		//order status
		if($state->orderstate ) {
			$states_temp = explode(',', $state->orderstate);

			$states = array();
			foreach($states_temp as $s) {
				$s = strtoupper($s);
				//5=incomplete, 4=pending, 3=failed, 1=confirmed
			//	if(!in_array($s, array(1,3,4,5))) continue;
				$states[] = $db->quote($s);
			}

			if(!empty($states)) {

				$query->where(
						$db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_state_id').' IN ('.
						implode(',',$states).')'
				);
			}
		}

		if($state->paykey) {
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('orderpayment_type').' LIKE '.
					$db->quote('%'.$state->paykey.'%')
			);
		}

		if($state->user_id) {
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('user_id').'='.$db->quote($state->user_id)
			);
		}
		if($state->token){
            $query->where(
                $db->quoteName('#__j2store_orders').'.'.$db->quoteName('token').'='.$db->quote($state->token)
            );
        }
		if($state->user_email){
            $query->where(
                $db->quoteName('#__j2store_orders').'.'.$db->quoteName('user_email').'='.$db->quote($state->user_email)
            );
        }
        $tz = Factory::getConfig()->get('offset');
		//since
        $since = trim($state->since);

		if(empty($since) || ($since == '0000-00-00') || ($since == '0000-00-00 00:00:00')) {
			$since = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $since)) {
				$since = '2001-01-01';
			}
            $since = $this->convert_time_to_utc($since);
			// Filter from-to dates
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('created_on').' >= '.
					$db->quote($since)
			);
		}

		// "Until" queries
        $until = trim($state->until);

		if(empty($until) || ($until == '0000-00-00') || ($until == '0000-00-00 00:00:00')) {
			$until = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $until)) {
				$until = '2037-01-01';
			}
            $until = $this->convert_time_to_utc($until);
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('created_on').' <= '.
					$db->quote($until)
			);
		}
		// No-zero toggle
		if(!empty($state->nozero)) {
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_total').' > '.
					$db->quote('0')
			);
		}

	/* 	if(!empty($state->moneysum)) {
			$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_total').' = '.
					$db->quote($state->moneysum)
			);
		} */

		//from invoice number
		if($state->frominvoice) {
		//CASE
		//	WHEN id<800 THEN success=1
         // ELSE 1=1
      //END
			$query->where('CASE WHEN '.
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('invoice_number').' = 0 THEN '.$db->quoteName('#__j2store_orders').'.'.$db->quoteName('j2store_order_id').' >= '.$db->quote($state->frominvoice).
					' ELSE ' .$db->quoteName('#__j2store_orders').'.'.$db->quoteName('invoice_number').' >= '.$db->quote($state->frominvoice) .' END '
			);
		}

		//to invoice number
		if($state->toinvoice) {
			$query->where('CASE WHEN '.
				$db->quoteName('#__j2store_orders').'.'.$db->quoteName('invoice_number').' = 0 THEN '.$db->quoteName('#__j2store_orders').'.'.$db->quoteName('j2store_order_id').' <= '.$db->quote($state->toinvoice).
				' ELSE ' .$db->quoteName('#__j2store_orders').'.'.$db->quoteName('invoice_number').' <= '.$db->quote($state->toinvoice) .' END '
			);

			/*$query->where(
					$db->quoteName('#__j2store_orders').'.'.$db->quoteName('invoice_number').' <= '.$db->quote($state->toinvoice)
			);*/
		}

		if($state->search){
			$search = '%'.trim($state->search).'%';
			$subquery = '( select order_id from #__j2store_orderitems where #__j2store_orderitems.orderitem_sku LIKE '.$db->quote($search).' AND #__j2store_orderitems.order_id = '.$db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_id').' Group by #__j2store_orderitems.order_id )';
			$query->where('('.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_id').' LIKE '.$db->quote($search).' OR '.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_id').' = '.$subquery.' OR '.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('j2store_order_id').' LIKE '.$db->quote($search).'OR '.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('user_email').' LIKE '.$db->quote($search).'OR '.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('order_state').' LIKE '.$db->quote($search).'OR '.
                    $db->quoteName('#__j2store_orders').'.'.$db->quoteName('orderpayment_type').' LIKE '.$db->quote($search).'OR'.
                    ' CONCAT ('.$db->quoteName('#__j2store_orderinfos').'.'.$db->quoteName('billing_first_name').', " ", '.$db->quoteName('#__j2store_orderinfos').'.'.$db->quoteName('billing_last_name').') LIKE '.$db->quote($search).'OR '.
                    $db->quoteName('#__j2store_orderinfos').'.'.$db->quoteName('billing_first_name').' LIKE '.$db->quote($search).'OR'.
                    $db->quoteName('#__j2store_orderinfos').'.'.$db->quoteName('billing_last_name').' LIKE '.$db->quote($search)
                .')'
            ) ;
		}

		if($state->coupon_code){
			$query->where(
					$db->quoteName('#__j2store_orderdiscounts').'.'.$db->quoteName('discount_code').' LIKE '.
					$db->quote('%'.$state->coupon_code.'%')
			);
			//set the type to coupon
			$query->where($db->quoteName('#__j2store_orderdiscounts').'.'.$db->quoteName('discount_type').' = '.$db->quote('coupon'));
		}
	}

    function convert_time_to_utc($datetime, $format = 'Y-m-d H:i:s', $modify = '')
    {
        $tz = Factory::getConfig()->get('offset');
        $from_date = Factory::getDate($datetime,$tz);
        $from_date->format($format);
        $timezone = new DateTimeZone('UTC');
        $from_date->setTimezone($timezone);

        return $from_date->format($format);
    }

	public function export($data=array(), $auto=false)
    {
		$app = Factory::getApplication();
		PluginHelper::importPlugin ('j2store');
		$currency = J2Store::currency();

        $j2params = J2Store::config();

		if($auto) {
			//trigger the plugin event
			$results = $app->triggerEvent( "onJ2StoreBeforeOrderExport", array() );

			if (is_array($results) && count($results))
			{
				$data = array_merge($data, $results[0]);
			}
		}

		$paystate = '';
		//order status filter
		if(isset($data['export_status']) && is_array($data['export_status'])) {
			$paystate = implode(',' ,$data['export_status']);
		}

	/* 	$rows = $this->clearState()
		->frominvoice($data['export_from'])
		->toinvoice($data['export_to'])
		->since($data['export_from_date'])
		->until($data['export_to_date'])
		->paystate($paystate)
		->getOrdersExport(); */

		$rows = $this->getOrderList();
		if(count($rows) > 0) {
			//process the totals
			$max = 1;
            $platform = J2Store::platform();
			$new_orders =array();

            // Create product option columns when requested
            // Needs a first pass to get options for all products
            // May significantly slow down the export on a large amount of orders
            if ($j2params->get('export_column_per_product_option', 0))
            {
                $option_columns = [];

                foreach ($rows as $key => $order)
                {
                    $orderTable = J2Store::fof()->loadTable('Order', 'J2StoreTable')->getClone();
                    $orderTable->load($order->j2store_order_id);
                    $orderitems = $orderTable->getItems();

                    foreach ($orderitems as $item)
                    {
                        $columns_from_options_item = $this->gatherColumnsFromProductOptions($item);
                        $option_columns            = array_merge($option_columns, $columns_from_options_item);
                    }
                    $option_columns = array_unique($option_columns);
                }
            }

			foreach ($rows as $key => $order) {
				$orderTable = J2Store::fof()->loadTable('Order','J2StoreTable')->getClone();
				$orderTable->load($order->j2store_order_id);
				$orderitems = $orderTable->getItems();
				$new_order = array();
				$all_values = $platform->fromObject($order);
				$new_order = array_merge($new_order, $all_values );
				$new_order['billing_country_name'] = $order->billingcountry_name;
				$new_order['shipping_country_name'] = $order->shippingcountry_name;

				$new_order['billing_zone_name'] = $order->billingzone_name;
				$new_order['shipping_zone_name'] = $order->shippingzone_name;

				//$new_order = array();

				$new_order['invoice'] = $order->invoice;
				$new_order['order_id'] = $order->order_id;
				$new_order['created_on'] = $order->created_on;
				$new_order['customer_name'] = $order->billing_first_name .' '.$order->billing_last_name;
				$new_order['customer_email'] = $order->user_email;
				$new_order['currency_code'] = $order->currency_code;

				$order_info = $orderTable->getOrderInformation();
				$billing_country_table = $this->getCountryById($order_info->billing_country_id);
				if($order_info->shipping_country_id > 0) {
					$shipping_country_table = $this->getCountryById($order_info->shipping_country_id);
				}else {
					$shipping_country_table = $this->getCountryById($order_info->billing_country_id);
				}

				$new_order['billing_country_code_2'] = $billing_country_table->country_isocode_2;
				$new_order['billing_country_code_3'] = $billing_country_table->country_isocode_3;
				$new_order['shipping_country_code_2'] = $shipping_country_table->country_isocode_2;
				$new_order['shipping_country_code_3'] = $shipping_country_table->country_isocode_3;

				$new_order['order_subtotal'] = $currency->format( $order->order_subtotal, $order->currency_code, $order->currency_value, false);
				$new_order['order_tax'] = $currency->format( $order->order_tax, $order->currency_code, $order->currency_value, false);
				$new_order['order_shipping'] = $currency->format( $order->order_shipping, $order->currency_code, $order->currency_value, false);
				$new_order['order_shipping_tax'] = $currency->format( $order->order_shipping_tax, $order->currency_code, $order->currency_value, false);
				$new_order['order_surcharge'] = $currency->format( $order->order_surcharge, $order->currency_code, $order->currency_value, false);
				$new_order['order_discount'] = $currency->format( $order->order_discount, $order->currency_code, $order->currency_value, false);
				$new_order['order_total'] = $currency->format( $order->order_total, $order->currency_code, $order->currency_value, false);

				//$new_order = array_merge($new_order, $new_order);

				$new_order['orderstatus_name'] = Text::_($order->orderstatus_name);
				$new_order['orderpayment_type'] = Text::_($order->orderpayment_type);

				//now process order items
				$i = 1;
				//$new_order['orderitems'] = $orderitems;
				foreach ($orderitems as $item)
				{
					//prepare the array
					$new_order['product_id_'.$i] =$item->product_id;
					//$new_order['product_type_'.$i] =$item->product_type;
					$new_order['product_sku_'.$i] =$item->orderitem_sku;
					$new_order['product_name_'.$i] =$item->orderitem_name;

                    if (!$j2params->get('export_column_per_product_option', 0)) {
                        // product options in the same column
                        $new_order['product_options_' . $i] = $this->getItemDescription($item);
                    } else {
                        // product options each have their own column
                        if (!empty($option_columns)) {
                            foreach ($option_columns as $column) {
                                $new_order['product_options_' . $i . ' ' . $column] = $this->getValuesForProductOptionsColumns($item, $column);
                            }
                        } else {
                            $new_order['product_options_'.$i] = '';
                        }
                    }

					$new_order['product_quantity_'.$i] =$item->orderitem_quantity;
					$new_order['product_tax_'.$i] =$currency->format($item->orderitem_tax, $order->currency_code,$order->currency_value, false);
					$new_order['product_total_with_tax_'.$i] =$currency->format($item->orderitem_finalprice_with_tax, $order->currency_code,$order->currency_value, false);
					$new_order['product_total_without_tax_'.$i] =$currency->format($item->orderitem_finalprice_without_tax, $order->currency_code,$order->currency_value, false);
					$i++;
				}

				//unset variables
				$unset_variables = array(
						'billingcountry_name',
						'billingzone_name',
						'shippingcountry_name',
						'shippingzone_name',
						'invoice_prefix',
						'invoice_number',
						'order_state',
						'orderstatus_cssclass',
						'all_billing',
						'all_shipping',
						'all_payment',
						'j2store_order_id',
						'user_email'
				);

				$this->formatCustomFields('billing', $new_order['all_billing'], $new_order);
				$this->formatCustomFields('shipping', $new_order['all_shipping'], $new_order);
				$this->formatCustomFields('payment', $new_order['all_payment'], $new_order);

				foreach($unset_variables as $var) {
					unset($new_order[$var]);
				}
				$new_orders[] = $platform->toObject($new_order);
			}
			return $new_orders;
		}

		return true;
	}

    function gatherColumnsFromProductOptions($item)
    {
        $columns = [];

        if (!empty($item->orderitemattributes) && count($item->orderitemattributes) > 0) {
            foreach ($item->orderitemattributes as $option) {
                if (!in_array($option->orderitemattribute_name, $columns)) {
                    $columns[] = $option->orderitemattribute_name;
                }
            }
        }

        return $columns;
    }

    function getValuesForProductOptionsColumns($item, $column)
    {
        if (!empty($item->orderitemattributes) && count($item->orderitemattributes) > 0) {
            foreach ($item->orderitemattributes as $option) {
                if ($option->orderitemattribute_name === $column) {
                    return $option->orderitemattribute_value;
                }
            }
        }

        return '';
    }

	function formatCustomFields($type, $data_field, &$order)
    {
		$address = J2Store::fof()->loadTable('Address', 'J2StoreTable');
		$fields = J2Store::getSelectableBase()->getFields($type, $address, 'address', '', true);
		foreach($fields as $field) {
			$order[$type.'_'.strtolower($field->field_namekey)] = '';
		}
        $custom_fields = array();
		try{
            $registry = J2Store::platform()->getRegistry(stripslashes($data_field));
            $custom_fields = $registry->toObject();
        }catch (\Exception $e){
            // do nothing
        }

		$row = J2Store::fof()->loadTable('Orderinfo','J2StoreTable');
		if(isset($custom_fields) && $custom_fields) {
			foreach($custom_fields as $namekey=>$field) {
				if(!property_exists($row, $type.'_'.strtolower($namekey)) && !property_exists($row, 'user_'.$namekey) && $namekey !='country_id' && $namekey != 'zone_id' && $namekey != 'option' && $namekey !='task' && $namekey != 'view' && $namekey !='email' ) {
					if(is_object($field)) {
						$string = '';
						if(is_array($field->value)) {
							$k = count($field->value); $i = 1;
							foreach($field->value as $value) {
								$string .=Text::_($value);
								if($i != $k) {
									$string .='|';
								}
								$i++;
							}

						}elseif(is_object($field->value)) {
                            //convert the object into an array
                            $obj_array = ArrayHelper::fromObject($field->value);
                            $k = count($obj_array); $i = 1;
                            foreach($obj_array as $value) {
                                $string .=Text::_($value);
                                if($i != $k) {
                                    $string .='|';
                                }
                                $i++;
                            }

						}elseif(J2Store::utilities()->isJson(stripslashes($field->value))) {
                            $json_values = array();
                            try{
                                $json_values = json_decode(stripslashes($field->value));
                            }catch (\Exception $e){
                                // do nothing
                            }


							if(is_array($json_values)) {
								$k = count($json_values ); $i = 1;
								foreach($json_values as $value){
									$string .= Text::_($value);
									if($i != $k) {
										$string .='|';
									}
									$i++;
								}
							} else {
								$string .= Text::_($field->value);
							}

						} else {
							$string = Text::_($field->value);
						}
						if(!empty($string)) {
							$order[$type.'_'.strtolower($namekey)] = $string;
						}
					}
				}
			}
		}
	}

	function getItemDescription($item)
    {
		$desc = '';

		//productoptions
		if (!empty($item->orderitemattributes)) {
			//first convert from JSON to array

			/* $registry = new JRegistry;
			$registry->loadString(stripslashes($item->orderitem_attribute_names), 'JSON'); */
			$product_options =$item->orderitemattributes;
			if(count($product_options) >0 ) {
				$first = true;
				foreach ($product_options as $option) {

					if($first) {
						$desc .= '';
					} else {
						$desc .= ' | ';
					}
					$desc .=$option->orderitemattribute_name.':'.$option->orderitemattribute_value;

					$first = false;
				}
			}
		}

		return $desc;
	}

	/**
	 * Method to cancel unpaid orders
	 */
	public function cancel_unpaid_orders()
    {
		$config = J2Store::config();

		// Get today's date
		$jNow	 = new Date();
		$now	 = $jNow->toUnix();

		$held_duration = $config->get('hold_stock');

		if ( $held_duration < 1 || $config->get('enable_inventory', 0) != 1 ) 	return;

		$date = date( "Y-m-d H:i:s", strtotime( '-' . abs( intval( $held_duration )) . ' MINUTES', $now) );

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->select('order_id')->from('#__j2store_orders')->where('modified_on <'.$db->quote($date))
            ->where('order_type ='.$db->quote('normal'))
            ->where('order_state_id IN (4,5)');
		$db->setQuery($query);
		$unpaid_orders = $db->loadObjectList();
		if ( $unpaid_orders ) {
			foreach ( $unpaid_orders as $unpaid_order ) {

				$order = J2Store::fof()->loadTable('Order', 'J2StoreTable')->getClone();
				if($order->load(array('order_id'=>$unpaid_order->order_id)) ) {

					if ( !empty($order->order_id) ) {
						//set order status as cancelled
						//first restore order stock

						$old_status = $order->order_state_id;

						$order->update_status(6);
						$order->notify_customer(true);

						//if status is new, then stock may not got reduced.
						if($old_status == 4) {
							$order->restore_order_stock();
						}
						$order->add_history(Text::_('J2STORE_ORDER_CANCELLED_TIME_LIMIT_EXPIRED'));
					}
				}
			}
		}
	}

	public function getCountryById($country_id)
    {
		$country = J2Store::fof()->loadTable('Country', 'J2StoreTable')->getClone();
		$country->load($country_id);

		return $country;
	}
}

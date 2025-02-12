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

class J2Cart
{
	public static $instance = null;
	protected $state;

	public function __construct($properties=null)
  {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	public function getSubtotal($items)
  {
		$subtotal = 0;
		if (! isset ( $items ) && count ( $items ) < 1)
			return $subtotal;

		foreach($items as $item) {
			$subtotal += $item->product_subtotal;
		}
		return $subtotal;
	}

	public function getCartTaxTotal($items)
  {
		$taxtotal = 0;
		if (! isset ( $items ) && count ( $items ) < 1)
			return $taxtotal;

		foreach($items as $item) {
			if(isset($item->taxes) && isset($item->taxes->taxtotal)) {
				$taxtotal += $item->taxes->taxtotal;
			}
		}
		return $taxtotal;
	}

	public static function getTaxes($items)
  {
		$tax_data = array();

		foreach ($items as $item) {
			if ($item->orderitem_taxprofile_id) {

				$tax_rates = $item->taxes->taxes;

				foreach ($tax_rates as $taxrate_id=>$tax_rate) {
					if (!isset($tax_data[$taxrate_id])) {
						$tax_data[$taxrate_id]['name'] = $tax_rate['name'];
						$tax_data[$taxrate_id]['rate'] = $tax_rate['rate'];
						$tax_data[$taxrate_id]['total'] = ($tax_rate['amount'] * $item->orderitem_quantity);
					} else {
						$tax_data[$taxrate_id]['name'] = $tax_rate['name'];
						$tax_data[$taxrate_id]['rate'] = $tax_rate['rate'];
						$tax_data[$taxrate_id]['total'] += ($tax_rate['amount'] * $item->orderitem_quantity);
					}
				}
			}
		}
		return $tax_data;
	}

	public function getCartTotalWeight($items)
  {
		$weight_total = 0;
		if (! isset ( $items ) && count ( $items ) < 1) return $weight_total;

		foreach($items as $item) {
			//only when shipping is enabled
			if(isset($item->shipping) && $item->shipping == 1) {
				$weight_total += $item->weight_total;
			}
		}
		return $weight_total;
	}

	public function removeCartItem($cart_id)
  {

	}

	public function getImage($type, $product_id)
  {

	}

	function resetCart( $session_id, $user_id )
	{
		$session = Factory::getApplication()->getSession();
		$user = Factory::getApplication()->getIdentity();

		//get cart items based on old session id
		$model = J2Store::fof()->getModel('Carts', 'J2StoreModel');
		$model->setState( 'filter_session', $session_id );
		$model->setState( 'filter_cart_type', 'cart');
		$cart  = $model->loadCart();

		J2Store::plugin()->event('BeforeResetCart', array($session_id, $user_id));
		//get wishlist items
		//delete the items with old session id
		$this->deleteSessionCartItems( $session_id );
		$this->resetCartTable($cart, $session_id, $user_id, 'cart');

		J2Store::plugin()->event('AfterResetCart', array($session_id, $user_id));

	}

	public function resetCartTable($cart, $session_id, $user_id, $cart_type='cart') {
		$session = Factory::getApplication()->getSession();
		if (!empty($cart))
		{
			F0FTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );

			$keynames = array();
			$keynames['user_id'] = $user_id;
			$keynames['cart_type'] = $cart_type;

			$table = J2Store::fof()->loadTable( 'Carts', 'J2StoreTable' )->getClone();

			if (!$table->load($keynames))
			{
				foreach($cart as $key=>$value)
				{
					if(property_exists($table, $key))
					{
						$table->set($key, $value);
					}
				}
				// this is a new cartitem, so set cart_id = 0
				$table->j2store_cart_id = '0';
			}
			//table loaded.

			$table->user_id = $user_id;
			$table->session_id = $session->getId();

			if (!$table->store())
			{
                Factory::getApplication()->enqueueMessage($table->getError(),'notice');
			}else {

				//now we got the cart id.
				$this->updateCartitemEntry($cart, $table);
			}
		}
	}

	function updateCartitemEntry($current_cart, $existing_cart)
  {
		//also load the cart items
		$cartitem_model = J2Store::fof()->getModel('Cartitems', 'J2StoreModel');
		$cartitem_model->setState('filter_cart', $current_cart->j2store_cart_id);
		$items = $cartitem_model->getList();

		foreach($items as $item) {

			//first delete. And then proceed.
			$this->deleteCartItem($item->j2store_cartitem_id);

			$cartitem = J2Store::fof()->loadTable('Cartitem', 'J2StoreTable');

			$keys = array();
			$keys['product_id'] = $item->product_id;
			$keys['vendor_id'] = $item->vendor_id;
			$keys['variant_id'] = $item->variant_id;
			$keys['product_type'] = $item->product_type;
			$keys['product_options'] = $item->product_options;
			$keys['cart_id'] = $existing_cart->j2store_cart_id;

            J2Store::plugin()->event("BeforeUpdateCartItemEntry", array( &$keys, $item ) );

			if($cartitem->load($keys)) {
				//already has the item. So just add the quantity
				$cartitem->product_qty = $cartitem->product_qty + $item->product_qty;
			}else {
				//new item
				$item->cart_id = $existing_cart->j2store_cart_id;
				$item->j2store_cartitem_id = 0;
				$cartitem->bind($item);
			}
			//save item
			$cartitem->store();

		}

		return true;
	}

	function deleteCartItem($cartitem_id)
  {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery ( true );

		$query->delete( "#__j2store_cartitems" );
		$query->where ( $db->qn ( 'j2store_cartitem_id' ) . " = " . $db->q ( $cartitem_id) );
		$db->setQuery ( $query );
		try {
			$db->execute ();
		} catch (\Exception $e) {
			$this->setError ( $e->getMessage () );
			return false;
		}
		return true;
	}

	function updateSession( $user_id, $session_id )
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->update( "#__j2store_carts" );
		$query->set( $db->qn('session_id')." = ".$db->q($session_id) );
		$query->where( $db->qn('user_id')." = ".$db->q($user_id) );
		$db->setQuery( (string) $query );
		try{
			$db->execute();
		}catch (\Exception $e){
			$this->setError( $e->getMessage () );
			return false;
		}

		return true;
	}

	function deleteSessionCartItems( $session_id )
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->delete();
		$query->from( "#__j2store_carts" );
		$query->where( $db->qn('session_id')." = ".$db->q($session_id) );
		$query->where( $db->qn('user_id')." <= 0 ");
		$db->setQuery( (string) $query );
		if (!$db->execute())
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}
		return true;
	}

	public static function emptyCart( $order_id )
	{
		$app = Factory::getApplication();
		$cart = J2Store::fof()->loadTable( 'Cart', 'J2StoreTable' );
		$order = J2Store::fof()->loadTable('Order', 'J2StoreTable');
		$order->load(array('order_id'=>$order_id));
		if (!empty($order->order_id))
		{
			if($cart->load($order->cart_id)) {
				$item = $cart;
				J2Store::plugin()->event('BeforeEmptyCart', array($item));
				$cart->delete();
				J2Store::plugin()->event('AfterEmptyCart', array($item));
			}
		}
	}
}

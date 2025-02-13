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

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

class J2StoreControllerMyProfile extends F0FController
{
	protected $cacheableTasks = [];

	public function execute($task)
    {
		if(in_array($task, array('add', 'edit', 'read'))) {
			$task = 'browse';
		}
		parent::execute($task);
	}

	protected function onBeforeGenericTask($task)
	{
        $input = Factory::getApplication()->input;
		$format = $input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeGenericTask($task);
	}

	protected function onBeforeBrowse()
    {
        $input = Factory::getApplication()->input;
		$format = $input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeBrowse();
	}

	public function display($cachable = false, $url = false, $tpl = NULL)
    {
		$app = Factory::getApplication();
		$session = $app->getSession();
        $user = $app->getIdentity();
		$document = F0FPlatform::getInstance()->getDocument();
		$params = J2Store::config();
		if ($document instanceof Document)
		{
			$viewType = $document->getType();
		}
		else
		{
			$viewType = $this->input->getCmd('format', 'html');
		}

		$view = $this->getThisView();

		// Get/Create the model

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);

		// Load the model
		$order_model = J2Store::fof()->getModel('Orders', 'J2StoreModel');
		$limit_orderstatuses = $params->get('limit_orderstatuses', '*');

		$guest_token = $session->get('guest_order_token', '', 'j2store');
		$guest_order_email = $session->get('guest_order_email', '', 'j2store');
		$orders = [];
        $limit_start = $app->input->get('limitstart',0);
        $limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->get('list_limit'), 'int' );

		if (empty($user->id) && (empty($guest_token) || empty($guest_order_email)) )
		{
			$view->setLayout('default_login');
		} elseif ($user->id)  {
			if(isset($guest_token)) {
				$session->clear('guest_order_token', 'j2store');
			}
			// Assign data to the view
			$order_model->clearState()->clearInput();
			$order_model->setState('filter_order', 'created_on');
			$order_model->setState('filter_order_Dir', 'DESC');
            $order_model->setState('limitstart', $limit_start);
            $order_model->setState('limit', $limit);
			$orders = $order_model->user_id($user->id)->order_type('normal')->orderstatuses($limit_orderstatuses)->getItemList();
			$pagination = $order_model->getPagination();
            $view->set('order_pagination', $pagination);
			$view->set('orders', $orders);
			$view->set('beforedisplayprofile' , J2Store::plugin()->eventWithHtml('BeforeDisplayMyProfile',array($orders)));
			$orderinfos = J2Store::fof()->getModel('Myprofiles','J2StoreModel')->getAddress();
			$view->set('orderinfos',$orderinfos);
			$view->set('fieldClass',J2Store::getSelectableBase());
			$view->set('guest', false);
			if($this->getTask()!='editAddress'){
                $layout = $app->input->get('layout','default');
				$view->setLayout($layout);
			}
			// if its guest
		} elseif ($guest_token && $guest_order_email) {

			$order_model->clearState()->clearInput();
			$order_model->setState('filter_order', 'created_on');
			$order_model->setState('filter_order_Dir', 'DESC');
            $order_model->setState('limitstart', $limit_start);
            $order_model->setState('limit', $limit);
			$orders = $order_model->token($guest_token)->order_type('normal')->user_email($guest_order_email)->orderstatuses($limit_orderstatuses)->getItemList();
            $pagination = $order_model->getPagination();
            $view->set('order_pagination', $pagination);
			$view->set('guest', true);
			if($this->getTask()!='editAddress'){
                $layout = $app->input->get('layout','default');
				$view->setLayout($layout);
			}
		}

 		//trigger after display order event
        foreach($orders as $order){
            // results as html
            $result = J2Store::plugin()->eventWithHtml('AfterDisplayOrder', array( $order ) );
            if(!empty($result)){
                $order->after_display_order = $result;
            }
        }

        $view->set('orders', $orders);

		$view->set('params', $params);
		$view->set('user', $user);
		$view->display();
	}

	public function editAddress()
    {
		$address_id = $this->input->getInt('address_id');
		$address = J2Store::fof()->loadTable('Address' ,'J2StoreTable');
		$address->load($address_id);
        $app = Factory::getApplication();
        $user = $app->getIdentity();

        if(!empty( $address->user_id ) && $user->id != $address->user_id){
			$app->redirect ('index.php?option=com_j2store&view=myprofile',Text::_('J2STORE_MYPROFILE_ADDRESS_INVALID'),'error');
		}
		$address_type = $this->input->getString('address_type');
		$model = $this->getModel('Myprofile' ,'J2StoreModel');
		$view = $this->getThisView();
		//$this->storeProfile

		$view->setModel($model,true);
		$view->set('address_type' ,$address_type );
		$view->set('address' ,$address );
		$fieldClass  = J2Store::getSelectableBase();
		$view->set('fieldClass' , $fieldClass);
		$view->setLayout('address');
		$view->display();
	}

	function deleteAddress()
    {
        $app = Factory::getApplication();
		$o_id = $app->input->getInt('address_id');
		$table = J2Store::fof()->loadTable('Address','J2StoreTable');
		$url = J2Store::platform()->getMyprofileUrl();
        $json = [];
		if($table->load($o_id)){
            $user = $app->getIdentity();
			if($user->id == $table->user_id && $table->user_id > 0){
				if(!$table->delete($o_id)){
                    $json['success'] = false;
                    $json['message'] = Text::_('J2STORE_MYPROFILE_ADDRESS_DELETE_ERROR');
				}else{
                    $json['success'] = true;
                    $json['message'] = Text::_('J2STORE_MYPROFILE_ADDRESS_DELETED_SUCCESSFULLY');
                    $json['url'] = $url;
                    J2Store::plugin()->event('AfterMyProfileAddressDelete',array($table));
                }
			}else{
                $json['success'] = false;
                $json['message'] = Text::_('J2STORE_MYPROFILE_ADDRESS_INVALID');
			}
		}else{
            $json['success'] = false;
            $json['message'] = Text::_('J2STORE_MYPROFILE_ADDRESS_INVALID');
        }
        echo json_encode($json);
        $app->close();
	}

	/**
	 * Method to save Address
	 * edit / new address will be saved
	 * @param post data
	 * @return result
	 */
	function saveAddress()
    {
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$values = $app->input->getArray($_POST);
		$values['id'] = $values['address_id'];
		unset( $values['j2store_address_id'] );
		$values['user_id'] = (isset($values['user_id'])  && $values['user_id']) ? $values['user_id'] : $user->id;
		$values['email'] = $user->email;
		$model = $this->getModel('myprofile');
		$selectableBase = J2Store::getSelectableBase();
        if(!in_array($values['type'],array('billing','shipping'))){
            $values['type'] = 'billing';
        }
		$json = $selectableBase->validate($values, $values['type'], 'address');
        if($values['user_id'] != $user->id){
            $json['error']['message'] = Text::_('J2STORE_MYPROFILE_INVALID_USER_ID');
            $json['error']['msgType']='error';
        }
        J2Store::plugin()->event('BeforeMyProfileAddressSave',array(&$json));
		if(empty($json['error'])){
			$table = J2Store::fof()->loadTable('Address','J2StoreTable');
			$table->load ($values['address_id']);
			$status = true;
			if($table->j2store_address_id){
				$status = false;
				if($user->id == $table->user_id){
					$status = true;
				}
			}
			$values['user_id'] = isset($values['user_id']) ? $values['user_id'] : $user->id;
			$values['email'] = isset($values['user_id']) ? $values['email'] : $user->email;
			if($status){

				if(!$table->bind ( $values )){
					$json['error']['message'] = $table->getError ();
					$json['error']['msgType']='error';
				}
				if($table->store ()){
                    $platform = J2Store::platform();
					$json['success']['url'] = $platform->getMyprofileUrl();
                    $json['success']['apply_url'] = J2Store::platform()->getMyprofileUrl(array('task' => 'editAddress','layout' => 'address','address_id' => $table->j2store_address_id));//JRoute::_('index.php?option=com_j2store&view=myprofile');//&task=editAddress&layout=address&address_id='.$table->j2store_address_id
					$json['success']['msg'] = Text::_('J2STORE_'.strtoupper($table->type).'_ADDRESS_SAVED_SUCCESSFULLY');
					$json['success']['address_id'] = $table->j2store_address_id;
					$json['success']['msgType']='success';
					J2Store::plugin()->event('AfterMyProfileAddressSave',array($table));
				}else{
					$json['error']['message'] = $table->getError ();
					$json['error']['msgType']='error';
				}
			}else{
				$json['error']['message'] = Text::_('J2STORE_MYPROFILE_ADDRESS_INVALID');
				$json['error']['msgType']='error';
			}
		}
		echo json_encode($json);
		$app->close();
	}

	public  function vieworder()
    {
		$app = Factory::getApplication();
		$order_id = $this->input->getString('order_id');
		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$order = J2Store::fof()->loadTable('Order' ,'J2StoreTable')->getClone();
		$order->load(array('order_id' => $order_id));

		if($this->validate($order)) {
			$error = false;
			$view->set('order' ,$order );
		}else {
			$msg = Text::_('J2STORE_ORDER_MISMATCH_OR_NOT_FOUND');
			$error = true;
			$view->set('errormsg' , $msg);
		}
		$view->set('error', $error);
		$view->setLayout('view');
		$view->display();
	}

	public function printOrder()
    {
		$app = Factory::getApplication();
		$order_id = $this->input->getString('order_id');
		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$order = J2Store::fof()->loadTable('Order' ,'J2StoreTable')->getClone();
		$order->load(array('order_id' => $order_id));
		if($this->validate($order)) {
			$error = false;
			$view->set('order' ,$order );
		}else {
			$msg = Text::_('J2STORE_ORDER_MISMATCH_OR_NOT_FOUND');
			$error = true;
			$view->set('errormsg' , $msg);
		}
		$view->set('error', $error);
		$view->setLayout('view');
		$view->display();
	}

	public function reOrder()
    {
		//order
		$app = Factory::getApplication();
		$order_id = $app->input->get('order_id',0);
		$url = 'index.php?option=com_j2store&view=myprofile';

		if(!$order_id || $app->getIdentity()->id < 1 || !Session::checkToken('get') ){
			$app->redirect ( $url, Text::_('J2STORE_INVALID_ORDER_PROFILE') );
		}

		$order = J2Store::fof()->loadTable('Order' ,'J2StoreTable')->getClone();
		$order->load(array('order_id' => $order_id));
		$user = $app->getIdentity();

		if($order->load(array('order_id' => $order_id)) && $order->order_state_id == 5 ){
			// variant check
			// validate stock
			// option available
			$items = $order->getItems();
			if(count ( $items ) > 0){
				$cart_table = J2Store::fof()->loadTable( 'Cart', 'J2StoreTable' )->getClone ();
				$cart_table->load(array('user_id'=>$user->id));
                $db = Factory::getContainer()->get('DatabaseDriver');
				if(!empty($cart_table->j2store_cart_id)){
					$db->getQuery(true);
					$query = 'DELETE FROM #__j2store_cartitems where cart_id ='.$db->q($cart_table->j2store_cart_id);
					$db->setQuery ( $query );
					$db->execute ();
				}
				$db->getQuery ( true );
				// delete the old cart items for that user
				$del_qry = 'DELETE FROM #__j2store_carts WHERE user_id=' . $db->q ( $user->id );
				$db->setQuery ( $del_qry );
				$db->execute ();

				// set new cart
				$cart = J2Store::fof()->loadTable( 'Cart', 'J2StoreTable' )->getClone ();
				$cart->j2store_cart_id = 0;
				$cart->user_id = $user->id;
				$session = $app->getSession();
				$cart->session_id = $session->getId();
				$cart->cart_type = 'cart';
				$cart->created_on = Factory::getDate ()->toSql (true);
				$cart->modified_on = Factory::getDate ()->toSql (true);
				$cart->customer_ip = $_SERVER['REMOTE_ADDR'];

				$browser = Browser::getInstance();
				$cart->cart_browser = $browser->getBrowser();
				$analytics = [];
				$analytics['is_mobile'] = $browser->isMobile();
				$cart->cart_analytics = json_encode($analytics);
				if($cart->store()){
					//product_id
					//product_qty
					//product_option
					foreach ($items as $key=>$item){
						// change table to table transfer
						$cartitem = J2Store::fof()->loadTable( 'Cartitem', 'J2StoreTable' )->getClone ();
						$cartitem->cart_id = $cart->j2store_cart_id;
						$cartitem->product_id = $item->product_id ;
						$cartitem->variant_id = $item->variant_id;
						$cartitem->vendor_id = $item->vendor_id;
						$cartitem->product_type = $item->product_type;
						$cartitem->cartitem_params = $item->orderitem_params;
						$cartitem->product_qty = $item->orderitem_quantity ;
						$cartitem->product_options = $item->orderitem_attributes;
						$cartitem->store ();
					}
					$session->set('payment_method',$order->orderpayment_type, 'j2store');
					$order_info = $order->getOrderInformation();
					$model = $this->getThisModel();
					//find shipping and billing id
					$billing_id = $model->getBillingAddress($order_info,$order->user_email)->j2store_address_id;
					$shipping_id = $model->getShippingAddress($order_info,$order->user_email)->j2store_address_id;
					$session->set('billing_address_id',$billing_id, 'j2store');
					$session->set('shipping_address_id',$shipping_id, 'j2store');

					$update_order = J2Store::fof()->getModel('Orders', 'J2StoreModel');
					$order = $update_order->initOrder($order_id)->getOrder();
					$order->saveOrder();
					$session->set ( 'profile_order_id',$order->order_id,'j2store' );
					$app->setUserState( 'j2store.order_id', $order->order_id );
					// do ajax call for shipping_payment_method
					// do ajax call for validation
					// do ajax call for confirm
					// thats it

					$view = $this->getThisView();

					if ($model = $this->getThisModel())
					{
						// Push the model into the view (as default)
						$view->setModel($model, true);
					}
					$params = J2Store::config ();
					$showShipping = false;
					if($params->get('show_shipping_address', 0)) {
						$showShipping = true;
					}

					if ($isShippingEnabled = $order->isShippingEnabled())
					{
						$showShipping = true;
					}

					$view->showShipping = $showShipping;
					$view->set('order' ,$order );
					$view->setLayout('reorder');
					$view->display();
				}
			}
		}else{
			$app->redirect ( $url, Text::_('J2STORE_INVALID_ORDER_PROFILE') );
		}
	}

	public function getCountry()
    {
		$app = Factory::getApplication();
		$country_id = $app->input->getInt('country_id');
		$country_info = J2Store::fof()->getModel('Countries', 'J2StoreModel')->getItem($country_id);
		$json = [];
		if ($country_info) {
			$model = J2Store::fof()->getModel('Zones', 'J2StoreModel')
				->enabled(1)
				->country_id($country_id);

			$model->setState('filter_order',"zone_name");
			$model->setState('filter_order_Dir',"ASC");
            try {
                $zones = $model->getList();
            } catch (Exception $e) {
                $zones = [];
            }

			foreach($zones as &$zone) {
				$zone->zone_name = Text::_($zone->zone_name);
			}
			if(isset($zones) && is_array($zones)) {
				$json = array(
					'country_id'        => $country_info->j2store_country_id,
					'name'              => $country_info->country_name,
					'iso_code_2'        => $country_info->country_isocode_2,
					'iso_code_3'        => $country_info->country_isocode_3,
					'zone'              => $zones
				);
			}
		}
		echo json_encode($json);
		$app->close();
	}

	public function validate($order)
    {
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		$session = $app->getSession();
		$guest_token = $session->get('guest_order_token', '', 'j2store');
		$guest_order_email = $session->get('guest_order_email', '', 'j2store');

		$status = false;

		if(!isset($order->order_id) || empty($order->order_id) || $order->order_id === 0) {
			return $status;
		}

		if ($user->id)  {
			if($order->user_id == $user->id) {
				$status = true;
			}
			// if its guest
		} elseif($guest_token && $guest_order_email) {
			if((trim($order->user_email) === $guest_order_email) && (trim($order->token) === $guest_token)) {
				$status = true;
			}
		}
		J2Store::plugin ()->event ( 'AfterViewOrderValidate', array($order,&$status) );
		return $status;
	}

	function guestentry()
    {
		//check token
        Session::checkToken() or jexit('Invalid Token');
		$app = Factory::getApplication();
        $platform= J2Store::platform();
		$email = $this->input->getString('email', '');
		$token = $this->input->getString('order_token', '');
        $link = J2Store::platform()->getMyprofileUrl();
		if(empty($email) || empty($token)) {
			$msg = Text::_('J2STORE_ORDERS_GUEST_VALUES_REQUIRED');
            $platform->redirect($link, $msg);
		}

		//checks
		if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
			$session = $app->getSession();
			$session->set('guest_order_email', $email, 'j2store');

		} else {
			$msg = Text::_('J2STORE_ORDERS_GUEST_INVALID_EMAIL');
            $platform->redirect($link, $msg);
		}

		if(J2Store::fof()->loadTable('Order', 'J2StoreTable')->load(array('token'=>$token, 'user_email'=>$email))) {
			$session->set('guest_order_token', $token, 'j2store');
		} else {
			$msg = Text::_('J2STORE_ORDERS_GUEST_INVALID_TOKEN');
            $platform->redirect($link, $msg);
		}
		$this->setRedirect($link);
		return;
	}

	function download()
    {
		$model = J2Store::fof()->getModel('Orderdownloads', 'J2StoreModel');
		if($model->getDownloads() === false ) {
			$msg = $model->getError();
			$url = J2Store::platform()->getMyprofileUrl();
			$this->setRedirect($url, $msg, 'warning');
		}
	}

	function updateHitCount()
    {
		$app = Factory::getApplication();
		$post = $app->input->getArray($_REQUEST);
		$json = [];
		$order = J2Store::fof()->loadTable('Order', 'J2StoreTable')->getClone();
		$order->load(array('order_id'=>$post['order_id']));
		if(isset($post['orderdownload_id']) && isset($post['productfile_id']) && isset($post['token']) && $post['orderdownload_id'] > 0 && $post['productfile_id'] > 0 && ($order->token==$post['token'])){
			$table = J2Store::fof()->loadTable('Orderdownload', 'J2StoreTable');
			$table->load($post['orderdownload_id']);
			$table->limit_count = $table->limit_count + 1;
			$table->store();
			$productfile = J2Store::fof()->loadTable('Productfile', 'J2StoreTable');
			$productfile->load($post['productfile_id']);
			$productfile->download_total = $productfile->download_total +1;
			$productfile->store();
			$json['success']=1;
		}else{
			$json['error'] = 1;
		}
		echo json_encode($json);
		$app->close();
	}
}

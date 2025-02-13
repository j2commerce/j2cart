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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class J2StoreControllerCarts extends F0FController
{
	protected $cacheableTasks = array();

	public function execute($task)
    {
		if(in_array($task, array('add', 'edit', 'read'))) {
			$task = 'browse';
		}
		parent::execute($task);
	}

	protected function onBeforeGenericTask($task)
	{
		$format = Factory::getApplication()->input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeGenericTask($task);
	}

	protected function onBeforeBrowse()
    {
		$format = Factory::getApplication()->input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeBrowse();
	}

	public function addItem()
    {
        $platform = J2Store::platform();
		$app = $platform->application();
		$model = $this->getModel('Carts', 'J2StoreModel');
		$result = $model->addCartItem();
		$registry = $platform->getRegistry('{}');
		if(is_object($result)) {
			$registry->loadObject($result);
			$json = $registry->toArray();
		} elseif(is_array($result)) {
			$json = $result;
		}else {
			$json = $result;
		}

		$config = J2Store::config();
		$cart_url = $model->getCartUrl();

		//if javascript submissions is not enabled
		$ajax = $app->input->getInt('ajax', 0);
		if($ajax) {
			if(isset($json['success'])) {
				if($config->get('addtocart_action', 3) == 3 ) {
					$json['redirect'] = $cart_url;
				}
			}
            $platform = J2Store::platform();
			$json['product_redirect'] = $platform->getProductUrl(array('task' => 'view','id' => $this->input->getInt('product_id')));
            //Route::_('index.php?option=com_j2store&view=product&id='.$this->input->getInt('product_id'));
			echo json_encode($json);
			$app->close();
		} else {
			$return = $app->input->getBase64('return');
			if(!is_null($return)) {
				$return_url = base64_decode($return);
			} else {
				$return_url = $cart_url;
			}

			if($json['success']) {
				$this->setRedirect($cart_url, Text::_('J2STORE_ITEM_ADDED_TO_CART'), 'success');
			} elseif($json['error']) {
				$error = J2Store::utilities()->errors_to_string($json['error']);
				$this->setRedirect($return_url , $error, 'error');
			}else {
				$this->setRedirect($return_url);
			}
		}
	}

	/**
	 * force shipping
	 *   */
	function forceshipping()
    {
		$json = array();
		$app = Factory::getApplication();
		$json = J2Store::plugin()->eventWithArray('ValidateShipping');
		echo json_encode($json);
		$app->close();
	}

	function update()
    {
		//first clear cache
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();

		$model = $this->getModel('Carts');
		$result = $model->update();
		if(isset($result['error'])) {
			$msg = $result['error'];
		} else {
			$msg = Text::_('J2STORE_CART_UPDATED_SUCCESSFULLY');
		}
		$url = $model->getCartUrl();
		$this->setRedirect($url, $msg, 'notice');
	}

	function clearCart()
    {
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();
		$model = $this->getModel('Carts' ,'J2StoreModel');
		$items = $model->getItems();
		foreach ($items as $item){
			$cartitem = J2Store::fof()->loadTable( 'Cartitem', 'J2StoreTable' )->getClone();
            if ($cartitem->delete ( $item->j2store_cartitem_id )) {
                J2Store::plugin()->event('RemoveFromCart', array(
                    $item
                ));
            }
		}
		$msg = Text::_('J2STORE_CART_CLEAR_SUCCESSFULLY');
		$url = $model->getCartUrl();
		$this->setRedirect($url, $msg, 'notice');
	}

	function remove()
    {
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();

		$model = $this->getModel('Carts' ,'J2StoreModel');
		if($model->deleteItem()) {
			$msg = Text::_('J2STORE_CART_UPDATED_SUCCESSFULLY');
		}else {
			$msg = $model->getError();
		}
		$url = $model->getCartUrl();
		$this->setRedirect($url, $msg, 'notice');
	}

	function ajaxmini()
    {
		J2Store::utilities()->nocache();
		//initialise system objects
		$app = Factory::getApplication();
		$document = Factory::getApplication()->getDocument();
        $db = Factory::getContainer()->get('DatabaseDriver');
		$language = Factory::getApplication()->getLanguage()->getTag();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_cart'))->where('published=1')
            ->where('(language="*" OR language='.$db->q($language).')');
		$db->setQuery($query);
		$modules = $db->loadObjectList();
		if(count($modules) < 1) {
			$query = $db->getQuery(true);
			$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_cart'))->where('published=1')
			->where('(language="*" OR language="en-GB")');
			$db->setQuery($query);
			$modules = $db->loadObjectList();
		}

		$renderer	= $document->loadRenderer('module');
		$json = array();
		if (count($modules) < 1)
		{
			$json['response'] = ' ';
		} else {
			foreach($modules as $module) {
				$app->setUserState( 'mod_j2store_mini_cart.isAjax', '1' );
				$json['response'][$module->id] = $renderer->render($module);
			}
			echo json_encode($json);
			$app->close();

		}
		$app->close();
	}

	function setcurrency()
    {
		//no cache
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();

		$app = Factory::getApplication();
		$currency = J2Store::currency();
		$post = $app->input->getArray($_POST);
		if(isset($post['currency_code'])) {
			$currency->set($post['currency_code']);
		}

		//get the redirect
		if(isset($post['redirect'])) {
			$url = base64_decode($post['redirect']);
		} else {
			$url = 'index.php';
		}

		$app->redirect($url);
	}

	function applyCoupon()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$model = J2Store::fof()->getModel('Carts', 'J2StoreModel');
		//coupon
		$post_coupon = $this->input->getString('coupon', '');
		//first time applying? then set coupon to session
		if (isset($post_coupon) && !empty($post_coupon)) {
			J2Store::fof()->getModel('Coupons', 'J2StoreModel')->set_coupon($post_coupon);
		}

		//check if we have a redirect
		$redirect = Factory::getApplication()->input->getBase64('redirect', '');
		if(!empty($redirect)) {
			$url = Route::_(base64_decode($redirect));
		}else {
			$url = $model->getCartUrl();
		}

		$this->setRedirect($url);
	}

	function removeCoupon()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$model = $this->getModel('Carts' ,'J2StoreModel');
		//coupon
		$coupon_model = J2Store::fof()->getModel ( 'Coupons', 'J2StoreModel' );
		if($coupon_model->has_coupon()) {
			$coupon_model->remove_coupon();
			$msg = Text::_('J2STORE_COUPON_REMOVED_SUCCESSFULLY');
			$msgType = 'success';
		}else {
			$msg = Text::_('J2STORE_PROBLEM_REMOVING_COUPON');
			$msgType = 'notice';
		}
		$url = $model->getCartUrl();
		$this->setRedirect($url, $msg, $msgType);
	}

	function applyVoucher()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$model = J2Store::fof()->getModel('Carts', 'J2StoreModel');
		//coupon
		$voucher = $this->input->getString('voucher', '');

		//first time applying? then set coupon to session
		if (isset($voucher) && !empty($voucher)) {
			J2Store::fof()->getModel ( 'Vouchers', 'J2StoreModel' )->set_voucher($voucher);
		}

        //check if we have a redirect
        $redirect = Factory::getApplication()->input->getBase64('redirect', '');
        if(!empty($redirect)) {
            $url = Route::_(base64_decode($redirect));
        }else {
            $url = $model->getCartUrl();
        }
		$this->setRedirect($url);
	}

	function removeVoucher()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		J2Store::plugin()->event('BeforeRemoveVoucher');
		$model = $this->getModel('Carts' ,'J2StoreModel');
		//coupon
		$session = Factory::getApplication()->getSession();
		$voucher_model = J2Store::fof()->getModel ( 'Vouchers', 'J2StoreModel' );
		if($voucher_model->has_voucher()) {
			$voucher_model->remove_voucher();
			$msg = Text::_('J2STORE_VOUCHER_REMOVED_SUCCESSFULLY');
			$msgType = 'success';
		}else {
			$msg = Text::_('J2STORE_PROBLEM_REMOVING_VOUCHER');
			$msgType = 'notice';
		}
		$url = $model->getCartUrl();
		$this->setRedirect($url, $msg, $msgType);
	}

	function estimate()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$model = $this->getModel('Carts' ,'J2StoreModel');
		$app = Factory::getApplication();
		$session = Factory::getApplication()->getSession();
		$country_id = $this->input->getInt('country_id', 0);
		$zone_id = $this->input->getInt('zone_id', 0);
		$postcode  = $this->input->getString('postcode', 0);
		$country_required = $this->input->getString('country_required', 1);
		$zone_required = $this->input->getString('zone_required', 1);
		$postal_required = $this->input->getString('postal_required', 0);

		$json = array();
		if(!$country_id && $country_required) $json['error']['country_id'] = Text::_('J2STORE_ESTIMATE_COUNTRY_REQUIRED');
		if(!$zone_id && $zone_required) $json['error']['zone_id'] = Text::_('J2STORE_ESTIMATE_ZONE_REQUIRED');

		$params = J2Store::config();
		if(	($postal_required ==1 || $params->get('postalcode_required', 0) ) && empty($postcode)){
			$json['error']['postcode'] = Text::_('J2STORE_ESTIMATE_POSTALCODE_REQUIRED');
		}

		//run a validation plugin event.
		J2Store::plugin()->event('BeforeShippingEstimate', array(&$json));

		if(!$json) {

			if($country_id || $zone_id) {
				if($country_id) {
					$session->set('billing_country_id', $country_id, 'j2store');
					$session->set('shipping_country_id', $country_id, 'j2store');
				}

				if($zone_id) {
					$session->set('billing_zone_id', $zone_id, 'j2store');
					$session->set('shipping_zone_id', $zone_id, 'j2store');
				}

				$session->set('force_calculate_shipping', 1, 'j2store');
			}

			if($postcode) {
				$session->set('shipping_postcode', $postcode, 'j2store');
				$session->set('billing_postcode', $postcode, 'j2store');
			}
			$url = $model->getCartUrl();
			$json['redirect'] = $url;
		}

		//run after validation plugin event.
		J2Store::plugin()->event('AfterShippingEstimate', array(&$json));

		echo json_encode($json);
		$app->close();

	}

	function shippingUpdate()
    {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$json = array();

		$model = $this->getModel('Carts' ,'J2StoreModel');
		$app = Factory::getApplication();
		$session = $app->getSession();
		$values = $this->input->getArray($_REQUEST);
		$shipping_values = array();
		$shipping_values['shipping_price']    = isset($values['shipping_price']) ? $values['shipping_price'] : 0;
		$shipping_values['shipping_extra']   = isset($values['shipping_extra']) ? $values['shipping_extra'] : 0;
		$shipping_values['shipping_code']     = isset($values['shipping_code']) ? $values['shipping_code'] : '';
		$shipping_values['shipping_name']     = isset($values['shipping_name']) ? $values['shipping_name'] : '';
		$shipping_values['shipping_tax']      = isset($values['shipping_tax']) ? $values['shipping_tax'] : 0;
		$shipping_values['shipping_plugin']     = isset($values['shipping_plugin']) ? $values['shipping_plugin'] : '';
		$session->set('shipping_values', $shipping_values, 'j2store');

		$redirect = $model->getCartUrl();
		$json['redirect'] = $redirect;

		//allow plugins to modify the output
		J2Store::plugin()->event('AfterShippingUpdate', array(&$json));

		echo json_encode($json);
		$app->close();
	}

	public function getCountry()
    {
        $app = Factory::getApplication();
        $session = $app->getSession();
        $set = $session->get('j2store_country_zone',array(),'j2store');
        $country_id = $app->input->getInt('country_id');
        if (!isset($set[$country_id])) {

            $country_info = J2Store::fof()->getModel('Countries', 'J2StoreModel')->getItem($country_id);
            $json = array();
            if ($country_info) {

                $db = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select('a.*')->from('#__j2store_zones AS a');
                $query->where('a.enabled=1')
                    ->order('a.zone_name ASC');
                $query->where('a.country_id='.$db->q($country_id));
                $db->setQuery($query);
                try {
                    $zones = $db->loadObjectList();
                } catch (Exception $e) {
                    $zones = array();
                }
            }

            foreach ($zones as &$zone) {
                $zone->zone_name = Text::_($zone->zone_name);
            }
            if (isset($zones) && is_array($zones)) {
                $json = array(
                    'country_id' => $country_info->j2store_country_id,
                    'name' => $country_info->country_name,
                    'iso_code_2' => $country_info->country_isocode_2,
                    'iso_code_3' => $country_info->country_isocode_3,
                    'zone' => $zones
                );
            }

            $set[$country_id] = $json;
            $session->set('j2store_country_zone',$set,'j2store');
        }

		echo json_encode($set[$country_id]);
		$app->close();
	}

	/**
	 * Method to check file upload
	 *
	 */
	public function upload()
    {
		$files = $this->input->files->get('file');
		$json = array();
		if($files) {
			$model = $this->getModel('Carts');
			$json = $model->validate_files($files);
		}
		echo json_encode($json);
		Factory::getApplication()->close();
	}

	public function addtowishlist()
    {
		$app = Factory::getApplication();
		$model = $this->getModel('Carts', 'J2StoreModel');
		$model->setCartType('wishlist');
		$result = $model->addCartItem();
		$json = J2Store::plugin()->eventWithArray('AfterAddingToWishlist', array($result));
		echo json_encode($json);
		$app->close();
	}
}

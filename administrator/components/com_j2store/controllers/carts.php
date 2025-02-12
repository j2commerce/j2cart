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

class J2StoreControllerCarts extends F0FController
{
	/**
	 * add product to order item
	 */
	function addOrderitems(){
		$app = Factory::getApplication();
		$model = $this->getModel('Cartadmins', 'J2StoreModel')->getClone();
		$result = $model->addAdminCartItem();

		if(isset($result['success']) && $result['success']){
			$result['message'] = Text::_("J2STORE_ITEM_ADDED_SUCCESS");
		}
		echo json_encode($result);
		$app->close();

	}

	/**
	 * apply coupon
	 */
	function applyCoupon()
  {
		$json = [];
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = Factory::getApplication();
		$id = $app->input->getInt('oid', '');
		//coupon
		$post_coupon = $app->input->getString('coupon', '');
		//first time applying? then set coupon to session
		if (isset($post_coupon) && !empty($post_coupon)) {
			J2Store::fof()->getModel( 'Coupons', 'J2StoreModel' )->set_coupon($post_coupon);
		}
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=summary&oid='.$id;
		$json['success']=1;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * remove coupon
	 *   */
	function removeCoupon()
  {
		$json = [];
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = Factory::getApplication();

		//coupon
		$id = $app->input->getInt('oid', '');
		$order_id = $app->input->getInt('order_id', '');
		J2Store::fof()->getModel( 'Coupons', 'J2StoreModel' )->remove_coupon();

		$discount_table = J2Store::fof()->loadTable('Orderdiscount', 'J2StoreTable')->getClone();
		$discount_table->load(array(
				'order_id' => $order_id,
				'discount_type' => "coupon"
		));
		if($discount_table->j2store_orderdiscount_id){
			$discount_table->delete();
		}
		$json['success']=1;
        $url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=summary&oid='.$id;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * apply voucher
	 *   */
	function applyVoucher()
  {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = Factory::getApplication();

		$voucher = $app->input->getString('voucher', '');
		//first time applying? then set coupon to session
		if (isset($voucher) && !empty($voucher)) {
			J2Store::fof()->getModel( 'Vouchers', 'J2StoreModel' )->set_voucher($voucher);
		}

        $id = $app->input->getInt('oid', '');
        $url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=summary&oid='.$id;
		$json = [];
		$json['success']=1;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * remove voucher
	 *   */
	function removeVoucher()
  {
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = Factory::getApplication();

		J2Store::fof()->getModel( 'Vouchers', 'J2StoreModel' )->remove_voucher();

		$id = $app->input->getInt('oid', '');
		$order_id = $app->input->getInt('order_id', '');
		$discount_table = J2Store::fof()->loadTable('Orderdiscount', 'J2StoreTable')->getClone();
		$discount_table->load(array(
				'order_id' => $order_id,
				'discount_type' => "voucher"
		));
		if($discount_table->j2store_orderdiscount_id){
			$discount_table->delete();
		}
        $url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=summary&oid='.$id;
		$json = [];
		$json['redirect']= $url;
		$json['success']=1;
		echo json_encode($json);
		$app->close();

	}

	function update()
  {
		//first clear cache
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();
		$app = Factory::getApplication();
		$model = $this->getModel('Cartadmins','J2StoreModel');
		$result = $model->update();
		$json = [];
		if(!empty($result['error'])) {
			$json['error'] = $result['error'];
		} else {
			$json['success'] = Text::_('J2STORE_CART_UPDATED_SUCCESSFULLY');
		}
		$id = $app->input->getInt('oid', '');
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=items&next_layout=items&oid='.$id;
		echo json_encode($json);
		$app->close();
		//$this->setRedirect($url, $msg, 'notice');
	}
}

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

class J2storeControllerShippingtroubles extends F0FController
{
	public function browse()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
		$layout = $app->input->getString('layout','default');
		if($layout === 'default_shipping'){
			//before check shipping enable
			$model = $this->getModel('Shippingtroubles');
			$state = $this->getFilterStates();
			foreach($state as $key => $value){
				$model->setState($key,$value);
			}
			$messages = array();
			$shippings = $model->getShippingMethods();
            $shipping_message = array();
            J2Store::plugin()->event('ShippingParamsValidate',array(&$shipping_message));
            if(!empty($shippings)){
                foreach ($shippings as &$shipping){
                    if(isset($shipping_message[$shipping->element]) && !empty($shipping_message[$shipping->element])){
                        $shipping->messages = $shipping_message[$shipping->element];
                    }
                }
            }
            //echo "<pre>";print_r($shipping_message);exit;
			if($shippings){
				$messages = $model->getShippingValidate();
			}
			$view = $this->getThisView();
			$view->setModel($model);
			$view->set('shipping_available',$shipping);
			$view->set('shipping_messages',$messages);
			$view->setLayout($layout);
		}elseif ($layout === 'default_shipping_product'){
			//before check shipping enable
			$model = $this->getModel('Shippingtroubles');
			$state = $this->getFilterStates();
			foreach($state as $key => $value){
				$model->setState($key,$value);
			}
			$products = array();
			$shipping = $model->getShippingDetails();
			if($shipping){
				$products = $model->getList();
			}else{
				$app->redirect('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping');
			}
			$view = $this->getThisView();
			$view->setModel($model);
			$view->set('shipping_available',$shipping);
			$view->set('products',$products);
			$view->set('state', $model->getState());
			$view->setLayout($layout);
		}
		return parent::browse();
	}

	public function getFilterStates() {
        $platform = J2Store::platform();
        $app = $platform->application();
		$state = array();
		$state['search'] = $app->input->getString('search','');
		$state['product_type']= $app->input->getString('product_type','');
		$state['filter_order']= $app->input->getString('filter_order','j2store_product_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
		$state['sku']= $app->input->getString('sku','');

		return $state;
	}
}

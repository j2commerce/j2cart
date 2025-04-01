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
use Joomla\CMS\Response\JsonResponse;

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/productbase.php');

class J2StoreControllerProducts extends J2StoreControllerProductsBase
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->cacheableTasks = array();
	}

	public function execute($task)
	{
		return parent::execute($task);
	}

	public function create()
    {
		$url = 'index.php?option=com_content&view=article&layout=edit';
		$this->setRedirect($url);
	}

	public function browse()
	{
        $app = Factory::getApplication();
        $model = $this->getThisModel();
        $state = $this->getFilterStates();
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $product_types  = $model->getProductTypes();
        array_unshift($product_types, Text::_('J2STORE_SELECT_OPTION'));

        $products = $model->getProductList();

        $view = $this->getThisView();
        $view->setModel($model);
        $view->set('products',$products);
        $view->set('state', $model->getState());
        $view->set('product_types',$product_types);
        return parent::browse();
	}

	function setCouponProducts()
    {
		//get variant id
		$model = J2Store::fof()->getModel('Products', 'J2StoreModel');
		$limit = $this->input->getInt('limit',20);
		$limitstart = $this->input->getInt('limitstart',0);

		//sku search
		$search = $this->input->getString('search','');
		$model->setState('search',$search);
		$model->setState('limit',$limit);
		$model->setState('limitstart',$limitstart);
		$model->setState('enabled',1);
		$items = $model->getProductList();
		$layout = $this->input->getString('layout','couponproducts');
		$view = $this->getThisView('Products');
		$view->setModel($model, true);
		$view->set('state',$model->getState());
		$view->set('pagination',$model->getPagination());
		$view->set('total',$model->getTotal());
		$view->set('productitems',$items);
		$view->setLayout($layout);
		$view->display();
	}

    /**
     * The number of products to show in the dashboard quickicon.
     * @return void
     */
    public function getQuickiconContent()
    {
        $model = F0FModel::getTmpInstance('Products', 'J2StoreModel');

        $amount = (int) $model->getProductPageTotal();

        $result = [];

        $result['amount'] = $amount;

        echo new JsonResponse($result);
    }
}

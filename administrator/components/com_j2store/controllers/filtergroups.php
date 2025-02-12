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

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

class J2StoreControllerFiltergroups extends F0FController
{
    use list_view;

    public function browse()
    {
        $app = Factory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['group_name'] = $app->input->getString('group_name','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_filtergroup_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_filtergroup_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_PRODUCTFILTER_ID'
            ),
            'group_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=filtergroup&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_filtergroup_id',
                'label' => 'J2STORE_PRODUCTFILTER_GROUP_NAME'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

	/**
	 * Method to delete product filter vales
	 * @params int filtervalueid
	 * @return array json
	 */
	function deleteproductfiltervalues()
  {
		$o_id = $this->input->getInt('productfiltervalue_id');
		$productfilter = J2Store::fof()->loadTable('filter','J2StoreTable');
		$json = array();
		$json['success'] = true;
		$json['msg'] = Text::_('J2STORE_PRODUCT_FILTER_VALUE_DELETE_SUCCESS');
		if(!$productfilter->delete($o_id)){
			$json['success'] = false;
			$json['msg'] = Text::_('J2STORE_PRODUCT_FILTER_VALUE_DELETE_ERROR');
		}
		echo json_encode($json);
		Factory::getApplication()->close();
	}
}

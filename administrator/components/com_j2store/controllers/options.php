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

class J2StoreControllerOptions extends F0FController
{
    use list_view;

    public function deleteoptionvalue()
    {
        $app = Factory::getApplication();
        $option_value_id = $app->input->getInt('optionvalue_id');

        $product_optionValue = J2Store::fof()->loadTable('Productoptionvalue','J2StoreTable')->getClone();
        $product_optionValue->load(array(
            'optionvalue_id' => $option_value_id
        ));

        $json  =array();

        $delete_status = true;
        if($product_optionValue->j2store_product_optionvalue_id > 0){
            $delete_status = false;
        }
        if($delete_status){
            $optionValue = J2Store::fof()->loadTable('Optionvalue','J2StoreTable')->getClone();
            $optionValue->load($option_value_id);
            $msg_type = "success";
            $msg_header ='Message';
            $msg = Text::_('J2STORE_OPTION_VALUE_DELETED_SUCCESSFULLY');
            $json['success'] = true;
            if(!$optionValue->delete()){
                $json['success'] = false;
                $msg_type = "warning";
                $msg = Text::_('J2STORE_OPTION_VALUE_DELETE_ERROR');
                $msg_header ='Warning';
            }
        }else{
            $json['success'] = false;
            $msg_type = "warning";
            $msg = Text::_('J2STORE_OPTION_VALUE_USED_IN_SOME_PRODUCT');
            $msg_header ='Warning';
        }

        $html = "<div class='alert alert-$msg_type'>";
        $html .="<h4 class='alert-heading'>". $msg_header."</h4>";
        $html .="<p>" .$msg."</p></div>";
        $json['html'] = $html;
        echo json_encode($json);
        $app->close();
    }

    public function getOptions()
    {
        $app = Factory::getApplication();
        $q = $app->input->post->get('q', '','string');
        $json = array();
        $model = $this->getThisModel('options');
        $result = $model->getOptions($q);
        $product_type = $app->input->getString('product_type');
        if($product_type =='configurable'){
            $json['options'] = $result;
            //get parent
            $json['pa_options']= $model->getParent($q);
        }else{
            $json = $result;
        }
        echo json_encode($json);
        $app->close();
    }

    public function browse()
    {
        $app = J2Store::platform()->application();
        $model = $this->getThisModel();
        //Toolbar
        $this->addBrowseToolBar();
        $state = array();
        $state['option_name'] = $app->input->getString('option_name','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_option_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $header = array(
            'j2store_option_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_OPTION_ID'
            ),
            'option_unique_name' => array(
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=option&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_option_id',
                'label' => 'J2STORE_OPTION_UNIQUE_NAME'
            ),
            'option_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_OPTION_DISPLAY_NAME'
            ),
            'type' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_OPTION_TYPE'
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
}

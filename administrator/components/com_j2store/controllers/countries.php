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

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerCountries extends F0FController
{
    use list_view;
    public function execute($task) {
        if(in_array($task, array('edit', 'add'))) {
            $task = 'add';
        }
        return parent::execute($task);
    }
    function add()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $vars = $this->getBaseVars();
        $this->editToolBar();
        $vars->primary_key = 'j2store_country_id';
        $vars->id = $this->getPageId();
        $country_table = J2Store::fof()->loadTable('Country', 'J2StoreTable')->getClone ();
        $country_table->load($vars->id);
        $vars->item = $country_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';

        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_COUNTRY',
            'fields' => array(
                'country_name' => array(
                    'label' => 'J2STORE_COUNTRY_NAME',
                    'type' => 'text',
                    'name' => 'country_name',
                    'value' => $country_table->country_name,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'country_isocode_2' => array(
                    'label' => 'J2STORE_COUNTRY_CODE2',
                    'type' => 'text',
                    'name' => 'country_isocode_2',
                    'value' => $country_table->country_isocode_2,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'country_isocode_3' => array(
                    'label' => 'J2STORE_COUNTRY_CODE3',
                    'type' => 'text',
                    'name' => 'country_isocode_3',
                    'value' => $country_table->country_isocode_3,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $country_table->enabled,
                    'options' => array('class' => '')
                ),
            )
        );
        echo $this->_getLayout('form', $vars,'edit');
    }
    public function browse()
    {
        $app = Factory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['country_name'] = $app->input->getString('country_name','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_country_id');
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
            'j2store_country_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_COUNTRY_ID'
            ),
            'country_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=country&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_country_id',
                'label' => 'J2STORE_COUNTRY_NAME'
            ),
            'country_isocode_2' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_COUNTRY_CODE2'
            ),
            'country_isocode_3' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_COUNTRY_CODE3'
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
	 * Method
	 * @return boolean
	 */
	function elements(){
		$geozone_id = $this->input->getInt('geozone_id');
		$model = J2Store::fof()->getModel('Countries','J2StoreModel');
		$filter = [];
		$filter['limit'] = $this->input->getInt('limit',20);
		$filter['limitstart'] = $this->input->getInt('limitstart');
		$filter['search'] = $this->input->getString('search','');
		$filter['country_name'] = $this->input->getString('country_name','');
		foreach($filter as $key => $value){
			$model->setState($key,$value);
		}

		if(isset($geozone_id) && $geozone_id){
			$view = $this->getThisView();
			$state = $model->getState();
			$view->setModel($this->getThisModel(),true);
			$view->set('countries',$model->enabled(1)->country_name($filter['country_name'])->getItemList());
			$view->set('pagination',$model->getPagination());
			$view->set('geozone_id',$geozone_id);
			$view->set('state',$model->getState());
			$view->setLayout('modal');
			$view->display();
		}else{
			return false;
		}
	}
}

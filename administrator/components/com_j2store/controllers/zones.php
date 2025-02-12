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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Select;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.controllerform');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

class J2StoreControllerZones extends F0FController
{
    use list_view;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->registerTask('apply', 'save');
    }

    public function execute($task)
    {
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
        $vars->primary_key = 'j2store_zone_id';
        $vars->id = $this->getPageId();
        $zone_table = J2Store::fof()->loadTable('Zone', 'J2StoreTable')->getClone ();
        $zone_table->load($vars->id);
        $vars->item = $zone_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_ZONE',
            'fields' => array(
                'zone_name' => array(
                    'label' => 'J2STORE_ZONE_NAME',
                    'type' => 'text',
                    'name' => 'zone_name',
                    'value' => $zone_table->zone_name,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'zone_code' => array(
                    'label' => 'J2STORE_ZONE_CODE',
                    'type' => 'text',
                    'name' => 'zone_code',
                    'value' => $zone_table->zone_code,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'country_id' => array(
                    'label' => 'J2STORE_ADDRESS_COUNTRY',
                    'type' => 'country',
                    'name' => 'country_id',
                    'value' => $zone_table->country_id,
                    'options' => array('class' => 'form-control','id' => 'country_id')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $zone_table->enabled,
                    'options' => array('')
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
        $state['zone_name'] = $app->input->getString('zone_name','');
        $state['zone_code'] = $app->input->getString('zone_code','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_zone_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $this->addBrowseToolBar();
        $vars->state = $model->getState();
        $header = array(
            'j2store_zone_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_ZONE_ID'
            ),
            'zone_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=zone&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_zone_id',
                'label' => 'J2STORE_ZONE_NAME'
            ),
            'zone_code' => array(
                'type' => 'fieldsearchable',
                'filterclass'=>"input-small",
                'sortable' => 'true',
                'label' => 'J2STORE_ZONE_CODE'
            ),
            'country_id' => array(
                'type' => 'fieldsql',
                'query' => 'SELECT * FROM #__j2store_countries',
                'key_field' => 'j2store_country_id',
                'value_field' => 'country_name',
                'sortable' => 'true',
                'label' => 'J2STORE_COUNTRY_NAME'
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

	function getZoneList(){
		$app = Factory::getApplication();
		$post = $app->input->getArray($_POST);
		if($post['country_id']) {
			$model = J2Store::fof()->getModel('Zones', 'J2storeModel');
			$model->setState('country_id', $post['country_id']);
			$zones = $model->getList(true);
		}
		$model->clearState();
		$options = array();
		if (!empty($zones))
		{
			foreach ($zones as $zone)
			{
				$options[] = HTMLHelper::_('select.option',
						$zone->j2store_zone_id, $zone->zone_name);
			}
		}
		$default = $post['zone_id'];
		echo Select::genericlist($options, $post['field_name'], '', 'value', 'text', $default, $post['field_id']);
		$app->close();
	}

	public function getCountry()
  {
		$app = Factory::getApplication();
		$country_id = $this->input->getInt('country_id');
		$zone_id = $this->input->getInt('zone_id');
		$json = array();
		if($country_id) {
			$zones = J2Store::fof()->getModel('Zones', 'J2storeModel')->country_id($country_id)->getList();
			$json['zone'] = $zones ;
		}
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Method
	 * @return boolean
	 */
	function elements()
  {
		$geozone_id = $this->input->getInt('geozone_id');
		$model = J2Store::fof()->getModel('Zones','J2StoreModel');
		$filter =array();
		$filter['limit'] = $this->input->getInt('limit',20);
		$filter['limitstart'] = $this->input->getInt('limitstart');
		$filter['search'] = $this->input->getString('search','');
		$filter['country_id'] = $this->input->getInt('country_id',1);
		foreach($filter as $key => $value){
			$model->setState($key,$value);
		}
		if(isset($geozone_id) && $geozone_id){
			$view = $this->getThisView();
			$view->setModel($this->getThisModel(),true);
			$zones =$model->enabled(1)->getItemList();
			$view->set('zones',$zones);
			$view->set('pagination',$model->getPagination());
			$view->set('geozone_id',$geozone_id);
			$view->set('state',$model->getState());
			$view->set('modal');
			$view->display();
		}else{
			return false;
		}
	}

    /**
     * Save zone
     *
     * @return bool|void
     * @throws Exception
     */
    function save()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $post = $app->input->getArray($_REQUEST);
        $zone_id = isset($post['j2store_zone_id']) && !empty($post['j2store_zone_id']) ? $post['j2store_zone_id']: 0;
        $zone_table = J2Store::fof()->loadTable('Zone' ,'J2StoreTable');
        try{
            $zone_table->load($zone_id);
            $zone_table->bind($post);
            $zone_table->store();
            $zone_id = $zone_table->j2store_zone_id;
            $msg = Text::_('J2STORE_SAVE_SUCCESS');
            $type = '';
        }catch (Exception $e){
            $msg = $e->getMessage();
            $type = 'error';
        }
        if(isset($post['task']) && $post['task'] == 'apply'){
            $url = 'index.php?option=com_j2store&view=zone&id='.$zone_id;
        }else{
            $url = 'index.php?option=com_j2store&view=zones';
        }

        $platform->redirect($url,$msg,$type);
    }
}

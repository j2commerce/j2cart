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

class J2StoreControllerManufacturers extends F0FController
{
    use list_view;

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
        $vars->primary_key = 'j2store_manufacturer_id';
        $vars->id = $this->getPageId();
        $manufacturer_table = J2Store::fof()->loadTable('Manufacturer', 'J2StoreTable')->getClone ();
        $manufacturer_table->load($vars->id);
        $vars->item = $manufacturer_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $col_class = 'span';
        }
        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_MANUFACTURER_GENERAL_INFORMATION',
            'fields' => array(
                'company' => array(
                    'label' => 'J2STORE_ADDRESS_COMPANY_NAME',
                    'type' => 'text',
                    'name' => 'company',
                    'value' => $manufacturer_table->company,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'tax_number' => array(
                    'label' => 'J2STORE_ADDRESS_TAX_NUMBER',
                    'type' => 'text',
                    'name' => 'tax_number',
                    'value' => $manufacturer_table->tax_number,
                    'options' => array('class' => 'form-control')
                ),
                'address_1' => array(
                    'label' => 'J2STORE_ADDRESS_LINE1',
                    'type' => 'text',
                    'name' => 'address_1',
                    'value' => $manufacturer_table->address_1,
                    'options' => array('class' => 'form-control')
                ),
                'address_2' => array(
                    'label' => 'J2STORE_ADDRESS_LINE2',
                    'type' => 'text',
                    'name' => 'address_2',
                    'value' => $manufacturer_table->address_2,
                    'options' => array('class' => 'form-control')
                ),
                'city' => array(
                    'label' => 'J2STORE_ADDRESS_CITY',
                    'type' => 'text',
                    'name' => 'city',
                    'value' => $manufacturer_table->city,
                    'options' => array('class' => 'form-control')
                ),
                'zip' => array(
                    'label' => 'J2STORE_ADDRESS_ZIP',
                    'type' => 'text',
                    'name' => 'zip',
                    'value' => $manufacturer_table->zip,
                    'options' => array('class' => 'form-control')
                ),
                'phone_1' => array(
                    'label' => 'J2STORE_ADDRESS_PHONE',
                    'type' => 'text',
                    'name' => 'phone_1',
                    'value' => $manufacturer_table->phone_1,
                    'options' => array('class' => 'form-control')
                ),
                'phone_2' => array(
                    'label' => 'J2STORE_ADDRESS_MOBILE',
                    'type' => 'text',
                    'name' => 'phone_2',
                    'value' => $manufacturer_table->phone_2,
                    'options' => array('class' => 'form-control')
                ),
                'email' => array(
                    'label' => 'J2STORE_EMAIL',
                    'type' => 'email',
                    'name' => 'email',
                    'value' => $manufacturer_table->email,
                    'options' => array('class' => 'form-control')
                ),
            ),
        );
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'class' => array(
	            $col_class.'6'
            ),
            'label' => 'J2STORE_ADDITIONAL_INFORMATION',
            'fields' => array(
                'country_id' => array(
                    'label' => 'J2STORE_ADDRESS_COUNTRY',
                    'type' => 'country',
                    'name' => 'country_id',
                    'value' => $manufacturer_table->country_id,
                    'options' => array('class' => 'input-xlarge','id' => 'country_id','zone_id' => 'zone_id','zone_value' => empty($manufacturer_table->zone_id) ? 1:$manufacturer_table->zone_id)
                ),
                'zone_id' => array(
                    'label' => 'J2STORE_ADDRESS_ZONE',
                    'type' => 'zone',
                    'name' => 'zone_id',
                    'value' => empty($manufacturer_table->zone_id) ? 1:$manufacturer_table->zone_id,
                    'options' => array('class' => 'form-select','id' => 'zone_id')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $manufacturer_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
                'brand_desc_id' => array(
                    'label' => 'J2STORE_MANUFACTURER_ARTICLE_ID',
                    'type' => 'modal_article',
                    'name' => 'brand_desc_id',
                    'value' => $manufacturer_table->brand_desc_id,
                    'options' => array('class' => '')
                ),

            )
        );
        echo $this->_getLayout('form', $vars,'edit');

	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
	    $script = "
			document.addEventListener('DOMContentLoaded', function() {
			    var countryElement = document.getElementById('country_id');
			    var zoneElement = document.getElementById('zone_id');
			    if (countryElement) {
			        countryElement.classList.add('form-select');
			        countryElement.dispatchEvent(new Event('change'));
			    }
			    if (zoneElement) {
			        zoneElement.classList.add('form-select');
			        zoneElement.dispatchEvent(new Event('liszt:updated'));
			    }
			});
			";
	    $wa->addInlineScript($script, [], []);
    }

    public function browse()
    {
        $app = Factory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['company'] = $app->input->getString('company','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_manufacturer_id');
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
            'j2store_manufacturer_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_MANUFACTURER_ID'
            ),
            'company' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=manufacturer&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_manufacturer_id',
                'label' => 'J2STORE_MANUFACTURER_NAME'
            ),
            'city' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_CITY'
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

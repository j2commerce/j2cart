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

class J2StoreControllerVendors extends F0FController
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
        $vars->primary_key = 'j2store_vendor_id';
        $vars->id = $this->getPageId();
        $vendor_table = J2Store::fof()->loadTable('Vendor', 'J2StoreTable')->getClone ();
        $vendor_table->load($vars->id);
        $vars->item = $vendor_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';

        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_VENDOR_GENERAL_INFORMATION',
            'fields' => array(
                'first_name' => array(
                    'label' => 'J2STORE_ADDRESS_FIRSTNAME',
                    'type' => 'text',
                    'name' => 'first_name',
                    'value' => $vendor_table->first_name,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'last_name' => array(
                    'label' => 'J2STORE_ADDRESS_LASTNAME',
                    'type' => 'text',
                    'name' => 'last_name',
                    'value' => $vendor_table->last_name,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'user_id' => array(
                    'label' => 'J2STORE_LINKED_USER',
                    'type' => 'user',
                    'name' => 'user_id',
                    'value' => $vendor_table->user_id,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'address_1' => array(
                    'label' => 'J2STORE_ADDRESS_LINE1',
                    'type' => 'text',
                    'name' => 'address_1',
                    'value' => $vendor_table->address_1,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'address_2' => array(
                    'label' => 'J2STORE_ADDRESS_LINE2',
                    'type' => 'text',
                    'name' => 'address_2',
                    'value' => $vendor_table->address_2,
                    'options' => array('class' => 'form-control')
                ),
                'city' => array(
                    'label' => 'J2STORE_ADDRESS_CITY',
                    'type' => 'text',
                    'name' => 'city',
                    'value' => $vendor_table->city,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'zip' => array(
                    'label' => 'J2STORE_ADDRESS_ZIP',
                    'type' => 'text',
                    'name' => 'zip',
                    'value' => $vendor_table->zip,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'phone_1' => array(
                    'label' => 'J2STORE_ADDRESS_PHONE',
                    'type' => 'text',
                    'name' => 'phone_1',
                    'value' => $vendor_table->phone_1,
                    'options' => array('class' => 'form-control')
                ),
                'phone_2' => array(
                    'label' => 'J2STORE_ADDRESS_MOBILE',
                    'type' => 'text',
                    'name' => 'phone_2',
                    'value' => $vendor_table->phone_2,
                    'options' => array('class' => 'form-control')
                ),
                'email' => array(
                    'label' => 'J2STORE_EMAIL',
                    'type' => 'email',
                    'name' => 'email',
                    'value' => $vendor_table->email,
                    'options' => array('class' => 'form-control')
                ),
            ),
        );
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_VENDOR_ADVANCED_INFORMATION',
            'fields' => array(
	            'vendor_id' => array(
		            'label' => 'J2STORE_VENDOR_ID',
		            'type' => 'text',
		            'name' => 'vendor_id',
		            'value' => $vendor_table->j2store_vendor_id,
		            'options' => array('class' => 'form-control','disabled' => '','readonly' => '')
	            ),
                'company' => array(
                    'label' => 'J2STORE_ADDRESS_COMPANY_NAME',
                    'type' => 'text',
                    'name' => 'company',
                    'value' => $vendor_table->company,
                    'options' => array('class' => 'form-control')
                ),
                'tax_number' => array(
                    'label' => 'J2STORE_ADDRESS_TAX_NUMBER',
                    'type' => 'text',
                    'name' => 'tax_number',
                    'value' => $vendor_table->tax_number,
                    'options' => array('class' => 'form-control')
                ),
                'country_id' => array(
                    'label' => 'J2STORE_ADDRESS_COUNTRY',
                    'type' => 'country',
                    'name' => 'country_id',
                    'value' => $vendor_table->country_id,
                    'options' => array('class' => 'form-select','id' => 'country_id','zone_id' => 'zone_id','zone_value' => empty($vendor_table->zone_id) ? 1:$vendor_table->zone_id)
                ),
                'zone_id' => array(
                    'label' => 'J2STORE_ADDRESS_ZONE',
                    'type' => 'zone',
                    'name' => 'zone_id',
                    'value' => empty($vendor_table->zone_id) ? 1:$vendor_table->zone_id,
                    'options' => array('class' => 'form-select','id' => 'zone_id')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $vendor_table->enabled,
                    'options' => array('class' => 'input-xlarge')
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
        $state['first_name'] = $app->input->getString('first_name', '');
        $state['filter_order'] = $app->input->getString('filter_order', 'j2store_vendor_id');
        $state['filter_order_Dir'] = $app->input->getString('filter_order_Dir', 'ASC');
        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_vendor_id' => array(
	            'type' => 'rowselect',
                'label' => 'J2STORE_VENDOR_ID'

            ),
            'first_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=vendor&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_vendor_id',
                'label' => 'J2STORE_VENDOR_NAME_LABEL'
            ),
            'city' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_CITY',
                'class' => 'd-none d-md-table-cell'
            ),
            'country_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_COUNTRY',
                'class' => 'd-none d-md-table-cell'
            ),
            'zone_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_ZONE',
                'class' => 'd-none d-md-table-cell'
            ),
            'enabled' => array(
	            'type' => 'published',
	            'sortable' => 'true',
	            'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default', $vars);
    }
}

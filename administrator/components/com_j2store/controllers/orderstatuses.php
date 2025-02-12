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
use Joomla\CMS\Toolbar\ToolbarHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

class J2StoreControllerOrderstatuses extends F0FController
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
        $vars->primary_key = 'j2store_orderstatus_id';
        $vars->id = $this->getPageId();
        $orderstatus_table = J2Store::fof()->loadTable('Orderstatus', 'J2StoreTable')->getClone ();
        $orderstatus_table->load($vars->id);
        $vars->item = $orderstatus_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'label' => 'COM_J2STORE_TITLE_ORDERSTATUSES_EDIT',
            'class' => array(
                $col_class.'12'
            ),
            'fields' => array(
                'orderstatus_name' => array(
                    'label' => 'J2STORE_ORDERSTATUS_NAME',
                    'type' => 'text',
                    'name' => 'orderstatus_name',
                    'value' => $orderstatus_table->orderstatus_name,
                    'options' => array('required' => 'true','class' => 'form-control')
                ),
                'orderstatus_cssclass' => array(
                    'label' => 'J2STORE_ORDERSTATUS_LABEL',
                    'type' => 'text',
                    'name' => 'orderstatus_cssclass',
                    'value' => $orderstatus_table->orderstatus_cssclass,
                    'options' => array('class' => 'form-control')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $orderstatus_table->enabled,
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
        $state['orderstatus_name'] = $app->input->getString('orderstatus_name','');
        $state['orderstatus_cssclass'] = $app->input->getString('orderstatus_cssclass','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_orderstatus_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->edit_view = 'orderstatus';
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $option = $app->input->getCmd('option', 'com_foobar');
        $subtitle_key = strtoupper($option . '_TITLE_' . $app->input->getCmd('view', 'cpanel'));
        ToolbarHelper::title(Text::_(strtoupper($option)) . ': ' . Text::_($subtitle_key), str_replace('com_', '', $option));
        ToolbarHelper::addNew();
        ToolbarHelper::editList();
        $msg = Text::_($option . '_CONFIRM_DELETE');
        ToolbarHelper::deleteList(strtoupper($msg));
        $header = array(
            'j2store_orderstatus_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '10',
                'label' => 'J2STORE_ORDERSTATUS_ID'
            ),
            'orderstatus_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=orderstatus&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_orderstatus_id',
                'label' => 'J2STORE_ORDERSTATUS_NAME',
                'translate' => false
            ),
            'orderstatus_cssclass' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'tdwidth' => '8%',
                'label' => 'J2STORE_ORDERSTATUS_LABEL'
            ),
            'orderstatus_core' => array(
                'type' => 'corefieldtypes',
                'sortable' => 'true',
                'label' => 'J2STORE_ORDERSTATUS_CORE'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }
}

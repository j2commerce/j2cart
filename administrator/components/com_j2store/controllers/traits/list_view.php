<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

trait list_view {
    function addBrowseToolBar(){
        $app = J2Store::platform()->application();
        $option = $app->input->getCmd('option', 'com_foobar');
        $subtitle_key = strtoupper($option . '_TITLE_' . $app->input->getCmd('view', 'cpanel'));
        ToolBarHelper::title(Text::_(strtoupper($option)) . ': ' . Text::_($subtitle_key), str_replace('com_', '', $option));
        ToolBarHelper::addNew();
        ToolBarHelper::editList();
        ToolBarHelper::publishList();
        ToolBarHelper::unpublishList();
        $msg = Text::_($option . '_CONFIRM_DELETE');
        ToolBarHelper::deleteList(strtoupper($msg));
    }

    private function noToolbar() {
        $app = J2Store::platform()->application();
        $option = $app->input->getCmd('option', 'com_foobar');
        $componentName = str_replace('com_', '', $option);

        // Set toolbar title
        $subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
        ToolBarHelper::title(Text::_(strtoupper($option)) . ': ' . Text::_($subtitle_key), $componentName);
    }

    function editToolBar(){
        $app = J2Store::platform()->application();
        $option = $app->input->getCmd('option', 'com_foobar');
        $componentName = str_replace('com_', '', $option);

        // Set toolbar title
        $subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
        ToolBarHelper::title(Text::_(strtoupper($option)) . ': ' . Text::_($subtitle_key), $componentName);
        ToolBarHelper::apply();
        ToolBarHelper::save();
        ToolBarHelper::custom('savenew', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        ToolBarHelper::cancel();
    }
    public function toolbarBacktodashboard(){
        $app = J2Store::platform()->application();
        $option = $app->input->getCmd('option', 'com_foobar');
        $componentName = str_replace('com_', '', $option);
        // Set toolbar title
        $subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
        ToolBarHelper::title(Text::_(strtoupper($option)) . ': ' . Text::_($subtitle_key), $componentName);
        // Set toolbar icons
        ToolBarHelper::back(Text::_('J2STORE_BACK_TO_DASHBOARD'), 'index.php?option=com_j2store&view=cpanel');
    }
    function getBaseVars(){
        $platform = J2Store::platform();
        $app = $platform->application();
        $vars = new stdClass();
        $vars->option = 'com_j2store';
        $vars->view = $app->input->get('view','');
        $vars->edit_view = \F0FInflector::singularize($vars->view);
        $vars->action_url = 'index.php?option='.$vars->option;
        return $vars;
    }

    function setHeader($header,&$vars){
        if(empty($header)){
            $header = array();
        }
        $vars->header = $header;
    }

    function setItemsFormat($item_format,&$vars){
        $vars->items_format = $item_format;
    }

    function _getLayout($layout, $vars,$layout_type = 'list'){

        ob_start();
        $layout = $this->_getLayoutPath( $layout,$layout_type );
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
    function _getLayoutPath($layout = 'default',$layout_type = 'list')
    {
        $app = J2Store::platform()->application();
        $view = $app->input->get('view','');
        // get the template and default paths for the layout
        $templatePath = JPATH_ADMINISTRATOR.'/templates/'.$app->getTemplate().'/html/com_j2store/'.$view.'/'.$layout.'.php';
        $defaultPath = JPATH_ADMINISTRATOR.'/components/com_j2store/layouts/'.$layout_type.'/'.$layout.'.php';
        $additional_path = JPATH_ADMINISTRATOR.'/components/com_j2store/views/'.$view.'/tmpl/'.$layout.'.php';
        // if the site template has a layout override, use it

        if (file_exists($templatePath))
        {
            return $templatePath;
        }elseif (file_exists( $defaultPath ))
        {
            return $defaultPath;
        }
        else
        {
            return $additional_path ;
        }
    }
    function getPageId(){
        $app = J2Store::platform()->application();
        $id = $app->input->get('id',0);
        $task = $app->input->get('task','');
        if ($task == 'add') {
            $id = $app->input->get('id',0);
        }else{
            if (empty($id)) {
                $cid = $app->input->get('cid', array());
                $id = isset($cid[0]) && !empty($cid[0]) ? $cid[0] : 0;
            }
        }
        return $id;
    }

    protected function exportButton($view = 'orders') {
        if(!isset($view) || empty($view)) return;
        //$bar = JToolBar::getInstance('toolbar');
        $bar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');
        // Add "Export to CSV"
        $link = URI::getInstance();
        $query = $link->getQuery(true);
        $query['format'] = 'csv';
        $query['option'] = 'com_j2store';
        $query['view'] = $view;
        $query['task'] = 'browse';
        $link->setQuery($query);

        ToolBarHelper::divider();
        $icon = 'download';
        $bar->appendButton('Link', $icon, Text::_('J2STORE_EXPORTCSV'), $link->toString());
    }
}
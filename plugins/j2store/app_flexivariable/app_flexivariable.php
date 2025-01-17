<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_flexivariable
 *
 * @copyright Copyright (C) 2018 J2Store. All rights reserved.
 * @copyright Copyright (C) 2024 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
defined('_JEXEC') or die ('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/app.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/j2store.php');

class plgJ2StoreApp_flexivariable extends J2StoreAppPlugin
{

    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_flexivariable';

    /**
     * Overriding
     *
     * @param $row
     * @return string
     */
    function onJ2StoreGetAppView($row)
    {
        if (!$this->_isMe($row)) {
            return null;
        }
        return $this->viewList();
    }

    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        $id = $app->input->getInt('id', '0');
        ToolBarHelper::title(Text::_('J2STORE_APP') . '-' . Text::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        ToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        ToolBarHelper::back('PLG_J2STORE_BACK_TO_APPS', 'index.php?option=com_j2store&view=apps');
        ToolBarHelper::apply('apply');
        ToolBarHelper::save();

        $vars = new \stdClass();
        $fof_helper = J2Store::fof();
        $model = $fof_helper->getModel('AppFlexiVariables', 'J2StoreModel');
        $data = $this->params->toArray();
        $new_data = array();
        $new_data['params'] = $data;
        $form = $model->getForm($new_data);
        $vars->form = $form;
        $vars->id = $id;
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        return $this->_getLayout('default', $vars);
    }

    public function onJ2StoreGetProductTypes(&$types)
    {
        $is_pro = J2Store::isPro();
        if ($is_pro) {
            $types['flexivariable'] = Text::_('J2STORE_PRODUCT_TYPE_FLEXIVARIABLE');
        }
    }

    public function onJ2StoreAfterAddJS()
    {
        $is_pro = J2Store::isPro();
        if ($is_pro) {
	        $wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
	        $wa->registerAndUseScript('flexivariable-script', Uri::base().'media/plg_j2store_app_flexivariable/js/flexivariable.js', [], [], []);

        }
    }

    public function onJ2StoreAfterProcessUpSellItem($upsell_product, &$show)
    {
        if (isset($upsell_product->product_type) && $upsell_product->product_type == 'flexivariable') {
            $show = true;
        }
    }

    public function onJ2StoreAfterProcessCrossSellItem($cross_sell_product, &$show)
    {
        if (isset($cross_sell_product->product_type) && $cross_sell_product->product_type == 'flexivariable') {
            $show = true;
        }
    }

    public function onJ2StoreAfterVariantListAjax(&$view, &$item)
    {
        if ($item->product_type == 'flexivariable') {
            $item->app_detail = $this->getAppDetails();
            $view->assign('item', $item);
        }
    }

    protected function getAppDetails()
    {
	    $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__extensions')
            ->where('folder=' . $db->q('j2store'))
            ->where('element=' . $db->q('app_flexivariable'))
            ->where('type=' . $db->q('plugin'));
        $db->setQuery($query);
        return $db->loadObject();
    }
}

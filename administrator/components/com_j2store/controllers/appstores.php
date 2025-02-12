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

class J2StoreControllerAppStores extends F0FController
{
    protected $cacheableTasks = [];

    public function execute($task)
    {
        if($task !== 'browse'){
            $task = 'browse';
        }
        parent::execute($task);
    }

    function browse()
    {
        $model = $this->getThisModel();
        $app = Factory::getApplication();

        $option = 'com_j2store';
        $ns = $option.'pluginsearch';
        $limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->get('list_limit'), 'int' );
        $limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );
        $limitstart = ($limit !== 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $filter_order_Dir =  $app->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',	'word' );
        $filter_order	= $app->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'tbl.user_id',	'cmd' );

        $plugin_type = $app->input->getString('plugin_type',  $model->getState('plugin_type', ''));
        $search = $app->input->getString('search',  $model->getState('search', ''));
        $plugin_types = array(
            '' => Text::_('J2STORE_ALL'),
            'payment' => Text::_('J2STORE_PAYMENT'),
            'shipping' => Text::_('J2STORE_SHIPPING'),
            'app' => Text::_('J2STORE_APP'),
            'report' => Text::_('J2STORE_REPORT'),
            'other' => Text::_('J2STORE_OTHER')
        );
        $final_list = [];
        $plugin_version = [];
        $this->getInstalledPlugin($final_list,$plugin_version);

        $model->setState('limit', $limit);
        $model->setState('limitstart', $limitstart);
        $model->setState('filter_order_Dir', $filter_order_Dir);
        $model->setState('filter_order', $filter_order);
        $model->setState('search',$search);
        $model->setState('plugin_type',$plugin_type);
        $current_page = $app->input->getString('page','popular');

        $model->setState('current_page',$current_page);
        $view   = $this->getThisView('Appstore');
        try {
            $items = $model->getList();
        } catch (Exception $e) {
            $items = [];
        }


        $pagination = $model->getPagination();
        $view->set('items',$items);
        $view->set('plugin_types',$plugin_types);
        $view->set('installed_plugin',$final_list);
        $view->set('plugin_version',$plugin_version);
        $view->set('pagination',$pagination);
        $view->set('state',$model->getState());
        $view->setModel( $model, true );
        $view->setLayout( 'default' );
        $view->display();
    }

    public function getInstalledPlugin(&$final_list,&$version_list)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('element,manifest_cache')->from('#__extensions')
            ->where('folder='.$db->q('j2store'));
        $db->setQuery($query);
        $installed_list = $db->loadObjectList();
        $platform = J2Store::platform();
        foreach ($installed_list as $install){
            $params = $platform->getRegistry($install->manifest_cache);
            $version = $params->get('version');
            $final_list[] = $install->element;
            $version_list[$install->element] = $version;
        }
    }
}

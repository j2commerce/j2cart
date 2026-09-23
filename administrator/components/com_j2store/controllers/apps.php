<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;


class J2StoreControllerApps extends F0FController
{
	protected $cacheableTasks = array();

	public function execute($task)
	{
		$app = JFactory::getApplication();
		$appTask = $app->input->getCmd('appTask', '');
		$values = $app->input->getArray($_POST);
        $this->uninstall_plugin();
		// Check if we are in a report method view. If it is so,
		// Try lo load the report plugin controller (if any)
		if ( $task  == "view" && $appTask != '' )
		{
			// FOF's per-task ACL in fof.xml does not cover delegated appTask calls, so we
			// enforce authorization here explicitly:
			//  - always require an authenticated user (blocks anonymous appTask calls)
			//  - in the backend, additionally require core.manage on com_j2store, since
			//    the admin-only appTasks (e.g. changeSubscriptionStatus,
			//    updateSubscriptionOrderItem*) only guard themselves with
			//    isClient('administrator') and have no per-record ownership check of
			//    their own
			// Frontend appTasks that mutate data (e.g. cancelSubscription) already
			// enforce their own per-record ownership check, and admin-only appTasks
			// already refuse to run outside the backend client, so no further gate is
			// needed here for an authenticated frontend user.
			$user = JFactory::getUser();
			if ($user->guest)
			{
				throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}
			if ($app->isClient('administrator') && !$user->authorise('core.manage', 'com_j2store'))
			{
				throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			$model = $this->getModel('Apps');

			$id = $app->input->getInt('id', '0');

			if(!$id)
				parent::execute($task);

			$model->setId($id);

			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
			$element = $row->element;

			// The name of the App Controller should be the same of the $_element name,
			// without the tool_ prefix and with the first letter Uppercase, and should
			// be placed into a controller.php file inside the root of the plugin
			// Ex: tool_standard => J2StoreControllerToolStandard in tool_standard/controller.php
			$controllerName = str_ireplace('app_', '', $element);
			$controllerName = ucfirst($controllerName);
			$path = JPATH_SITE.'/plugins/j2store/';
			$controllerPath = $path.$element.'/'.$element.'/controller.php';
			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}
			$className    = 'J2StoreControllerApp'.$controllerName;
			if ($controllerName != '' && class_exists($className)){
				// Create the controller
				$controller   = new $className();
				// Add the view Path
				$controller->addViewPath($path);
				// Perform the requested task
				$controller->execute( $appTask );
				// Redirect if set by the controller
				$controller->redirect();
			} else{
				parent::execute($task);
			}
		} else{

			parent::execute($task);
		}
	}

	function uninstall_plugin(){
        $uninstall_plugins = array(
            'app_campaignrabbit' => 'j2store',
            'campaignrabbit' => 'system',
            'app_retainfulcoupon' => 'j2store'
        );
        $db = JFactory::getDbo();
        foreach ($uninstall_plugins as $plugin => $folder)
        {
            $sql = $db->getQuery(true)
                ->select($db->qn('extension_id'))
                ->from($db->qn('#__extensions'))
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('element') . ' = ' . $db->q($plugin))
                ->where($db->qn('folder') . ' = ' . $db->q($folder));
            $db->setQuery($sql);

            try
            {
                $id = $db->loadResult();
            }
            catch (Exception $exc)
            {
                $id = 0;
            }

            if ($id)
            {
                try{
                    $installer = new JInstaller;
                    $installer->uninstall('plugin', $id, 1);
                }catch (Exception $e){

                }

            }
        }
    }

	function view(){
		$model = $this->getThisModel();
		$id = $this->input->getInt('id');
		$row = $model->getItem($id);
		$view   = $this->getThisView('App');
		$view->setModel( $model, true );
		$view->set('row', $row );
		$view->setLayout( 'view' );
		$view->display();
	}



}
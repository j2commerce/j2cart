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
use Joomla\CMS\Installer\Installer;

class J2StoreControllerApps extends F0FController
{
	protected $cacheableTasks = [];

	public function execute($task)
	{
		$app = Factory::getApplication();
		$appTask = $app->input->getCmd('appTask', '');
		$values = $app->input->getArray($_POST);
        $this->uninstall_plugin();
		// Check if we are in a report method view. If it is so,
		// Try to load the report plugin controller (if any)
		if ($task  === "view" && $appTask !== '' )
		{
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

	function uninstall_plugin()
  {
        $uninstall_plugins =  [
            'app_campaignrabbit' => 'j2store',
            'campaignrabbit' => 'system',
            'app_retainfulcoupon' => 'j2store',
        ];
        $db = Factory::getContainer()->get('DatabaseDriver');
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
                    $installer = new Installer;
                    $installer->uninstall('plugin', $id, 1);
                }catch (Exception $e){

                }

            }
        }
    }

	function view()
  {
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

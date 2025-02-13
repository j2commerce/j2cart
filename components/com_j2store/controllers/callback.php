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

class J2StoreControllerCallback extends F0FController
{
	protected $cacheableTasks = array();

	function __construct()
    {
		$config['csrfProtection'] = 0;
		parent::__construct($config);
		$this->cacheableTasks = array();
	}

	function execute($task)
    {
		$this->read();
	}

	function read()
    {
		// Makes sure SiteGround's SuperCache doesn't cache the subscription page
		J2Store::utilities()->nocache();

		$app = Factory::getApplication();
		$app->setHeader('X-Cache-Control', 'False', true);
		$method = $app->input->getCmd('method', 'none');
		$model = $this->getModel('Callback');
		$result = $model->runCallback($method);
		echo $result ? 'OK' : 'FAILED';
		$app->close();
	}
}

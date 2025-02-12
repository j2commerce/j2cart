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

class J2StoreControllerCrons extends F0FController
{

	protected $cacheableTasks = [];

	function __construct()
  {
		$config['csrfProtection'] = 0;
		parent::__construct($config);
		$this->cacheableTasks = [];
	}

	function execute($task)
  {
		$this->cron();
	}

	public function cron()
  {
		// Makes sure SiteGround's SuperCache doesn't cache the CRON view
		$app = Factory::getApplication();
		$app->setHeader('X-Cache-Control', 'False', true);
		$cron_key = J2Store::config ()->get ( 'queue_key','' );

		if (empty($cron_key))
		{
			header('HTTP/1.1 503 Service unavailable due to configuration');
			$app->close (503);
		}
		$secret = $app->input->get('cron_secret', null, 'raw');
		if ($secret != $cron_key)
		{
			header('HTTP/1.1 403 Forbidden');
			$app->close (403);
		}
		$command = $app->input->get('command', null, 'raw');
		$command = trim(strtolower($command));
		if (empty($command))
		{
			header('HTTP/1.1 501 Not implemented');
			$app->close (501);
		}
        $tz = Factory::getApplication()->getConfig()->get('offset');
        $now_date = Factory::getDate('now', $tz);
        $last_trigger = array(
            'date' => $now_date->toSql (),
            'url' => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI']: '',
            'ip' => $_SERVER['REMOTE_ADDR']
        );
        J2Store::config ()->saveOne('cron_last_trigger',json_encode ( $last_trigger ));

		J2Store::plugin ()->event ( 'ProcessCron',array($command) );
		echo "$command OK";
		$app->close ();
	}

}

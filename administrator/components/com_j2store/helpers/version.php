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

use Joomla\CMS\Date\Date;

class J2StoreVersion
{
  /**
	 * Populates global constants holding the j2store component version
	 */
	public static function load_version_defines()
	{
		if(file_exists(JPATH_ADMINISTRATOR.'/components/com_j2store/version.php')) {
			require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
		}

		if(!defined('J2STORE_VERSION')) define("J2STORE_VERSION", "svn");
		if(!defined('J2STORE_PRO')) define('J2STORE_PRO', false);
		if(!defined('J2STORE_DATE')) {
			jimport('joomla.utilities.date');
			$date = new Date();
			define( "J2STORE_DATE", $date->format('Y-m-d') );
		}
	}
}

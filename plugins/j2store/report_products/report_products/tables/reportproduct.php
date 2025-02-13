<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.report_products
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

class J2StoreTableReportProducts extends F0FTable
{
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__j2store_orders','j2store_order_id', $db, $config);
	}
}

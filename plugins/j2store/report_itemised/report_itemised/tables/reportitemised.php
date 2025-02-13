<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.report_itemized
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

class J2StoreTableReportItemised extends F0FTable
{
	/**
	 * Class Constructor.
	 *
	 * @param   string           $table   Name of the database table to model.
	 * @param   string           $key     Name of the primary key field in the table.
	 * @param   JDatabaseDriver  &$db     Database driver
	 * @param   array            $config  The configuration parameters array
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__j2store_orderitems', 'j2store_orderitem_id', $db);
	}
}

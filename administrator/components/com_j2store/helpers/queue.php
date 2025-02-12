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

class J2Queue extends JObject
{

	public static $instance;

	public function __construct ( $properties = null )
	{
		parent::__construct ( $properties );
	}

	public static function getInstance ( $properties = null )
	{
		if ( !self::$instance ) {
			self::$instance = new self( $properties );
		}

		return self::$instance;
	}

	public function deleteQueue($list)
  {
		if(isset($list->j2store_queue_id) && !empty($list->j2store_queue_id)) {
			$queue_table = J2Store::fof()->loadTable( 'Queue', 'J2StoreTable' )->getClone ();
			$queue_table->load ( $list->j2store_queue_id );
			$queue_table->delete ();
		}
	}

	function resetQueue($list,$day = 7)
  {
		if(isset($list->j2store_queue_id) && !empty($list->j2store_queue_id)){
			$queue_table = J2Store::fof()->loadTable('Queue', 'J2StoreTable')->getClone();
			$queue_table->load($list->j2store_queue_id);
			$new_table = clone $queue_table;

			//delete the current queue
			$queue_table->delete();

			$new_table->j2store_queue_id = '';
			$tz = Factory::getApplication()->getConfig()->get('offset');
			$current_date = Factory::getDate('now', $tz)->toSql(true);
			$date_string = 'now +'.$day.' day';
			$date = Factory::getDate($date_string, $tz)->toSql(true);
			$new_table->status = 'Requeue';
			$new_table->expired = $date;
			$new_table->repeat_count += 1;
			$new_table->modified_on = $current_date;
			$new_table->store();
		}
	}
}

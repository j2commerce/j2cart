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

class J2StoreModelAddresses extends F0FModel
{
	public function buildQuery($overrideLimits=false)
    {
		$query = parent::buildQuery($overrideLimits);
		$query->select('#__j2store_countries.country_name as country_name');
		$query->join('LEFT OUTER', '#__j2store_countries ON #__j2store_addresses.country_id = #__j2store_countries.j2store_country_id');

		$query->select('#__j2store_zones.zone_name as zone_name');
		$query->join('LEFT OUTER', '#__j2store_zones ON #__j2store_addresses.zone_id = #__j2store_zones.j2store_zone_id');
		return $query;
	}

	function addAddress($type = 'billing', $data = array())
    {
		$app = Factory::getApplication();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$user = Factory::getApplication()->getIdentity();

		if (isset ( $data ) && count ( $data )) {
			$post = $data;
		} else {
			$post = $app->input->getArray ( $_POST );
		}

		foreach ( $post as $key => $value ) {
			// in case the value is an array, store as a json encoded message
			if (is_array ( $value )) {
				$post [$key] = $db->escape ( json_encode ( $value ) );
			}
		}

		// first save data to the address table
		$row = J2Store::fof()->loadTable( 'Address', 'J2StoreTable' );

		// set the id so that it updates the record rather than changing
		if (! $row->bind ( $post )) {
			$this->setError ( $row->getError () );
			return false;
		}

		J2Store::plugin()->event('BeforeSaveAddress', array(&$row, $post));
		if ($user->id && (empty($row->user_id) || empty($row->email))) {
			$row->user_id = $user->id;
			$row->email = $user->email;
		}

		$row->type = $type;

		if (! $row->store ()) {
			$this->setError ( $row->getError () );
			return false;
		}
		J2Store::plugin()->event('AfterSaveAddress', array(&$row, $post));

		return $row->j2store_address_id;
	}

	function getAddresses($key='')
    {
		$user = Factory::getApplication()->getIdentity();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$where = array();
		$where[] = 'tbl.user_id='.$db->q((int) $user->id);
		$query = $this->getAddressQuery($where);
		$db->setQuery($query);
		return $db->loadAssocList($key);
	}

	function getAddressQuery($where)
    {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('tbl.*,c.country_name,z.zone_name');
		$query->from('#__j2store_addresses AS tbl');
		$query->leftJoin('#__j2store_countries AS c ON tbl.country_id=c.j2store_country_id');
		$query->leftJoin('#__j2store_zones AS z ON tbl.zone_id=z.j2store_zone_id');
		foreach($where as $condition){
			$query->where($condition);
		}
		return $query;
	}

	public function getAddressById($address_id)
    {
		static $sets;
		if ( !is_array( $sets) )
		{
			$sets= array( );
		}
		if(!isset($sets[$address_id])) {
			$address_table = J2Store::fof()->loadTable('Address', 'J2StoreTable');
			$address_table->load($address_id);
			$sets[$address_id] = $address_table;
		}
		return $sets[$address_id];
	}
}

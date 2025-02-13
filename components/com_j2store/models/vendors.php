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

class J2StoreModelVendors extends F0FModel
{
	public function &getItem($id = null)
	{
        $user = Factory::getApplication()->getIdentity();
		$this->record = J2Store::fof()->loadTable('Vendoruser','J2StoreTable');
		$this->record->load($user->id);

		$this->record->products = J2Store::fof()->getModel('Products' ,'J2StoreModel')
		->vendor_id($this->record->j2store_vendor_id)
		->enabled(1)
		->getList();
		return $this->record;
	}

	public function buildQuery($overrideLimits = false)
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query  = $db->getQuery(true);
		$query->select('#__j2store_vendors.*')->from("#__j2store_vendors as #__j2store_vendors");
		$query->select($db->qn('#__j2store_addresses').'.j2store_address_id')
		->select($db->qn('#__j2store_addresses').'.first_name')
		->select($db->qn('#__j2store_addresses').'.last_name')
		->select($db->qn('#__j2store_addresses').'.address_1')
		->select($db->qn('#__j2store_addresses').'.address_2')
		->select($db->qn('#__j2store_addresses').'.user_id')
		->select($db->qn('#__j2store_addresses').'.email')
		->select($db->qn('#__j2store_addresses').'.city')
		->select($db->qn('#__j2store_addresses').'.zip')
		->select($db->qn('#__j2store_addresses').'.zone_id')
		->select($db->qn('#__j2store_addresses').'.country_id')
		->select($db->qn('#__j2store_addresses').'.phone_1')
		->select($db->qn('#__j2store_addresses').'.phone_2')
		->select($db->qn('#__j2store_addresses').'.fax')
		->select($db->qn('#__j2store_addresses').'.type')
		->select($db->qn('#__j2store_addresses').'.company')
		->select($db->qn('#__j2store_addresses').'.tax_number')
		->select($db->qn('#__j2store_countries').'.country_name')
		->select($db->qn('#__j2store_zones').'.zone_name')
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_vendors.address_id')
		->leftJoin('#__j2store_countries ON #__j2store_countries.j2store_country_id = #__j2store_addresses.country_id')
		->leftJoin('#__j2store_zones ON #__j2store_zones.j2store_zone_id = #__j2store_addresses.zone_id');
		return $query;
	}

	public function buildOrderbyQuery(&$query)
    {
		$state = $this->getState();
		$app = Factory::getApplication();
		$filter_order_Dir = $app->input->getString('filter_order_Dir','asc');
		$filter_order = $app->input->getString('filter_order','filter_name');
        if(!in_array(strtolower($filter_order_Dir),array('asc','desc'))){
            $filter_order_Dir = 'desc';
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
		//check filter
		if($filter_order ==='j2store_vendor_id' || $filter_order ==='enabled' ){
			$query->order('#__j2store_vendors.'.$filter_order.' '.$filter_order_Dir);
		}else if($filter_order ==='country_name' ){
			$query->order('#__j2store_countries.'.$filter_order.' '.$filter_order_Dir);
		}else if($filter_order ==='zone_name' ){
			$query->order('#__j2store_zones.'.$filter_order.' '.$filter_order_Dir);
		}else{
            $query->order($db->qn('#__j2store_addresses').'.'.$db->qn($filter_order).' '.$filter_order_Dir);
		}
	}
}

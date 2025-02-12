<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

class J2StoreModelConfigurations extends F0FModel {

	public function &getItemList($overrideLimits = false, $group = '')
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__j2store_configurations'));
		$db->setQuery($query);
		$items = $db->loadObjectList('config_meta_key');
		return $items;
	}

 	public function onBeforeLoadForm(&$name, &$source, &$options) {
		$app = Factory::getApplication();
		$data1 = $this->_formData;
		$data = $this->getItemList();

		$params = array();
		foreach($data as $namekey=>$singleton) {
			if ($namekey == 'limit_orderstatuses') {
				$params[$namekey] = explode(',', $singleton->config_meta_value);
			}else {
				$params[$namekey] = $singleton->config_meta_value;
			}
		}
		$this->_formData = $params;
	}


}

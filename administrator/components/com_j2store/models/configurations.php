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

class J2StoreModelConfigurations extends F0FModel
{
	public function &getItemList($overrideLimits = false, $group = '')
	{
		$query = $this->_db->getQuery(true)
            ->select('*')
            ->from($this->_db->quoteName('#__j2store_configurations'));
		$this->_db->setQuery($query);
		$items = $this->_db->loadObjectList('config_meta_key');

        return $items;
	}

 	public function onBeforeLoadForm(&$name, &$source, &$options)
    {
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

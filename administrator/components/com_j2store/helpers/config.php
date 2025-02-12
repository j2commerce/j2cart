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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Filter\InputFilter;

class J2Config extends CMSObject
{
	public static $instance = null;
	var $_data;

	public function __construct($properties=null)
  {
		if(!isset($this->_data) && !is_array($this->_data)) {
            $db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true)->select('*')->from('#__j2store_configurations');
			$db->setQuery($query);
			$this->_data = $db->loadObjectList('config_meta_key');
		}
	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	public function set($namekey,$value='')
  {
		if(!isset($this->_data[$namekey]) || !is_object($this->_data[$namekey])) $this->_data[$namekey] = new stdClass();
		$this->_data[$namekey]->config_meta_value=$value;
		$this->_data[$namekey]->config_meta_key=$namekey;
		return true;
	}

	public function get($property, $default='')
  {
		if(isset($this->_data[$property])) {
			return $this->_data[$property]->config_meta_value;
		}
		return $default;
	}

	public function toArray()
  {
		$params = array ();
		if (count ( $this->_data )) {
			foreach ( $this->_data as $param ) {
				$params [$param->config_meta_key] = $param->config_meta_value;
			}
		}
		return $params;
	}

	public function saveOne($metakey, $value)
  {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$app = Factory::getApplication();
		$config = J2Store::config ();
		$query = 'REPLACE INTO #__j2store_configurations (config_meta_key,config_meta_value) VALUES ';

		jimport ( 'joomla.filter.filterinput' );
		$filter = InputFilter::getInstance ( array(), array(), 1, 1 );
		$conditions = array ();

		if (is_array ( $value )) {
			$value = implode ( ',', $value );
		}
		// now clean up the value
		if ($metakey === 'store_billing_layout' || $metakey === 'store_shipping_layout' || $metakey === 'store_payment_layout') {
			$value = $app->input->get ( $metakey, '', 'raw' );
			$clean_value = $filter->clean ( $value, 'html' );
		} else {
			$clean_value = $filter->clean ( $value, 'string' );
		}
		$config->set ( $metakey, $clean_value );
		$conditions [] = '(' . $db->q ( strip_tags ( $metakey ) ) . ',' . $db->q ( $clean_value ) . ')';

		$query .= implode ( ',', $conditions );

		try {
			$db->setQuery ( $query );
			$db->execute ();
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}
}

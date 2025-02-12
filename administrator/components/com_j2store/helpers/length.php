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

class J2Length
{
	private $lengths = [];
	protected static $instance;

	public function __construct()
  {
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)->select('*')
					->from('#__j2store_lengths');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
      		$this->lengths[$row->j2store_length_id] = array(
        		'length_class_id' => $row->j2store_length_id,
        		'title'           => $row->length_title,
				'unit'            => $row->length_unit,
				'value'           => $row->length_value
      		);
    	}
  	}

  	public static function getInstance()
  	{
  		if (!is_object(self::$instance))
  		{
  			self::$instance = new self();
  		}

  		return self::$instance;
  	}

  	public function convert($value, $from, $to)
    {
		if ($from == $to) {
      		return $value;
		}

		if (isset($this->lengths[$from])) {
			$from = $this->lengths[$from]['value'];
		} else {
			$from = 1;
		}

		if (isset($this->lengths[$to])) {
			$to = $this->lengths[$to]['value'];
		} else {
			$to = 1;
		}

		return $value * ($to / $from);
  	}

	public function format($value, $length_class_id, $decimal_point = '.', $thousand_point = ',')
  {
		if (isset($this->lengths[$length_class_id])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$length_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	public function getUnit($length_class_id)
  {
		if (isset($this->lengths[$length_class_id])) {
    		return $this->lengths[$length_class_id]['unit'];
		} else {
			return '';
		}
	}
}

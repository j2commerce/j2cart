<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewCustomers extends F0FViewCsv
{
	public function __construct($config = array())
	{
		$config['csv_filename'] ='customers_'.date('d-m-Y').'_'.time().'.csv';
		parent::__construct($config);
	}
}

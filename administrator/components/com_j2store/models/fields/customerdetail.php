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

class JFormFieldCustomerdetail extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Customerdetail';

	public function getRepeatable()
	{
		$orderinfo = J2Store::fof()->loadTable('Orderinfo','J2StoreTable');
		$orderinfo->load(array('order_id' => $this->item->order_id));
		$customer_name = $orderinfo->billing_first_name .' '. $orderinfo->billing_last_name;
		$html ='';
		$html .= $customer_name;
		$html .='<br>';
		$html .='<small>';
		$html .=$this->item->user_email;
		$html .='</small>';
		return $html;
	}
}

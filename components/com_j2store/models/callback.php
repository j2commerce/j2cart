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

class J2StoreModelCallback extends F0FModel
{
	function runCallback($method)
    {
		$app = Factory::getApplication();
		$rawDataPost = $app->input->getArray($_POST);
		$rawDataGet = $app->input->getArray($_GET);
		$data = array_merge ( $rawDataGet, $rawDataPost );

		// Some plugins result in an empty Itemid being added to the request
		// data, screwing up the payment callback validation in some cases (e.g.
		// PayPal).
		if (array_key_exists ( 'Itemid', $data )) {
			if (empty ( $data ['Itemid'] )) {
				unset ( $data ['Itemid'] );
			}
		}

		$plugin_helper = J2Store::plugin();
		$row = $plugin_helper->getPlugin($method);

		//sanity check
		if($row === false || $row->element !== $method) return false;

		//trigger a callback event
		J2Store::plugin()->event('Callback', array($row, $data));

		//run the post payment trigger. Callback normally used in post payment.
		$jResponse = J2Store::plugin()->event('PostPayment', array (
				$row,
				$data
		) );

		if (empty ( $jResponse ))
			return false;

		$status = false;

		foreach ( $jResponse as $response ) {
			$status = $status || $response;
		}

		return $status;
	}
}

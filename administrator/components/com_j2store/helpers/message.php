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

use Joomla\CMS\Language\Text;

/**
 * J2Store Message helper.
 */
class J2StoreMessage
{
	public static function getMessageTags()
  {
		return array(
					'billing'	 => self::billingTags(),
					'shipping'	 =>	self::shippingTags(),
					'additional' => self::additionalTags()
				);
	}

	public static function additionalTags()
  {
		$result = 	array(
				"[SITENAME]" 		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_SITENAME'),
				"[SITEURL]"	 		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_SITEURL'),
				"[INVOICE_URL]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_INVOICE_URL'),
				"[CUSTOMER_NOTE]"	=> Text::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOMER_NOTE'),
				"[PAYMENT_TYPE]"	=> Text::_('J2STORE_EMAILTEMPLATE_TAG_PAYMENT_TYPE'),
				"[SHIPPING_TYPE]"	=> Text::_('J2STORE_SHIPM_SHIPPING_TYPE'),
				"[ORDERID]"			=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ORDERID'),
				"[INVOICENO]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_INVOICEID'),
				"[ORDERDATE]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ORDERDATE'),
				"[ORDERSTATUS]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ORDERSTATUS'),
				"[ORDERAMOUNT]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ORDERAMOUNT'),
				"[ORDER_TOKEN]"		=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ORDER_TOKEN'),
				"[COUPON_CODE]"		=> Text::_('J2STORE_COUPON_CODE'),
				"[ITEMS]"			=> Text::_('J2STORE_EMAILTEMPLATE_TAG_ITEMS'),
		);
        J2Store::plugin()->event('AfterAdditionalTags', array(&$result));
        return $result;
	}

	public static function shippingTags()
  {
		return $result =array(
				"[SHIPPING_FIRSTNAME]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_FIRSTNAME'),
				"[SHIPPING_LASTNAME]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_LASTNAME'),
				"[SHIPPING_ADDRESS_1]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS_1'),
				"[SHIPPING_ADDRESS_2]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ADDRESS_2'),
				"[SHIPPING_CITY]" =>  Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_CITY'),
				"[SHIPPING_ZIP]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_ZIP'),
				"[SHIPPING_COUNTRY]" =>  Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_COUNTRY'),
				"[SHIPPING_STATE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_STATE'),
				"[SHIPPING_PHONE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_PHONE'),
				"[SHIPPING_MOBILE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_MOBILE'),
				"[SHIPPING_COMPANY]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_COMPANY'),
				"[SHIPPING_VATID]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_SHIPPING_VATID'),
				"[SHIPPING_TRACKING_ID]" => Text::_('J2STORE_SHIPPING_TRACKING_ID')
				);
	}

	public static function billingTags()
  {
			return $result = array(
				"[CUSTOMER_NAME]" 		=> Text::_('J2STORE_CUSTOMER_NAME'),
				"[BILLING_FIRSTNAME]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_FIRSTNAME'),
				"[BILLING_LASTNAME]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_LASTNAME'),
				"[BILLING_EMAIL]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_EMAIL'),
				"[BILLING_ADDRESS_1]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ADDRESS_1'),
				"[BILLING_ADDRESS_2]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ADDRESS_2'),
				"[BILLING_CITY]" =>  Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_CITY'),
				"[BILLING_ZIP]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_ZIP'),
				"[BILLING_COUNTRY]" =>  Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_COUNTRY'),
				"[BILLING_STATE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_STATE'),
				"[BILLING_PHONE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_PHONE'),
				"[BILLING_MOBILE]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_MOBILE'),
				"[BILLING_COMPANY]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_COMPANY'),
				"[BILLING_VATID]" => Text::_('J2STORE_EMAILTEMPLATE_TAG_BILLING_VATID')
				);
	}
}

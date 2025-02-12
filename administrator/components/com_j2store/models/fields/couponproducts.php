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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class JFormFieldCouponproducts extends FormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Couponproducts';

	protected function getInput()
    {
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
		$html .= J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, Text::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success'));
		return $html ;
	}
}

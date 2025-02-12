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

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;

class StandardCalculator extends JObject
{
	public function __construct($config=array())
    {
		parent::__construct($config);
	}

	public function calculate()
    {
		$variant = $this->get('variant');

		$pricing = new \stdclass();

		//set the base price
		$pricing->base_price = $variant->price;
		$pricing->price = $variant->price;
		$pricing->calculator = 'standard';

		//see if we have advanced pricing for this product / variant

		$model = J2Store::fof()->getModel('ProductPrices', 'J2StoreModel');
		$standard_calculator = $this;
		J2Store::plugin()->event('BeforeGetPrice', array(&$pricing, &$model,&$standard_calculator));

		$quantity = $this->get('quantity');
		$date = $this->get('date');
		$group_id = $this->get('group_id');

		$model->setState( 'variant_id', $variant->j2store_variant_id );

		//where quantity_from < $quantity
		$model->setState( 'filter_quantity', $quantity);

		$tz = Factory::getApplication()->getConfig()->get('offset');
		// does date even matter?
		$nullDate = Factory::getContainer()->get('DatabaseDriver')->getNullDate( );
		if ( empty( $date ) || $date == $nullDate )
		{
			$date = Factory::getDate('now')->toSql(true);//format('Y-m-d');
		}

		$model->setState( 'filter_date', $date );

		// does group_id?
		$user = Factory::getApplication()->getIdentity();
		if(empty($group_id)) $group_id = implode(',', Access::getGroupsByUser($user->id));
		$model->setState( 'group_id', $group_id );

		// set the ordering so the most discounted item is at the top of the list
		$model->setState( 'orderby', 'quantity_from' );
		$model->setState( 'direction', 'DESC' );

		try {
			$price = $model->getItem( );
			$pricing->data = $price;
		}catch (Exception $e) {
			$price = new stdClass();
		}
		if(isset($price->price)) {
			$pricing->special_price = $price->price;
			//this is going to be the sale price
			$pricing->price = $price->price;

			$pricing->is_discount_pricing_available = ($pricing->base_price > $pricing->price) ? true: false;

		}
		return $pricing;
	}
}

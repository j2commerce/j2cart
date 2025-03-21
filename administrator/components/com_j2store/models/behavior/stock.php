<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class J2StoreModelVariantsBehaviorStock extends F0FModelBehavior {


	public function onAfterBuildQuery(&$model, &$query)
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query->select($db->qn('#__j2store_productquantities').'.quantity')
				->select($db->qn('#__j2store_productquantities').'.j2store_productquantity_id')
				->innerJoin('#__j2store_productquantities AS #__j2store_productquantities ON #__j2store_productquantities.variant_id = #__j2store_variants.j2store_variant_id');
	//echo $query;
	}

	public function onAfterGetItem(&$model, &$record) {

		//var_dump($record);

	}


}

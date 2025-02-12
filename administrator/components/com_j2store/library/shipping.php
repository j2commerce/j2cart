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

class J2StoreShipping
{
    /**
     * Returns the list of shipping method types
     * @return array of objects
     */
    public static function getTypes()
    {
        static $instance;

        if (!is_array($instance)) {
            $instance = array();
        }
        if (empty($instance)) {

            $object = new \stdClass();
            $object->id = '0';
            $object->title = Text::_('J2STORE_SHIPM_FLAT_RATE_PER_ORDER');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '1';
            $object->title = Text::_('J2STORE_SHIPM_QUANTITY_BASED_PER_ORDER');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '2';
            $object->title = Text::_('J2STORE_SHIPM_PRICE_BASED_PER_ORDER');
            $instance[$object->id] = $object;


            $object = new \stdClass();
            $object->id = '3';
            $object->title = Text::_('J2STORE_SHIPM_FLAT_RATE_PER_ITEM');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '4';
            $object->title = Text::_('J2STORE_SHIPM_WEIGHT_BASED_PER_ITEM');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '5';
            $object->title = Text::_('J2STORE_SHIPM_WEIGHT_BASED_PER_ORDER');
            $instance[$object->id] = $object;


            $object = new \stdClass();
            $object->id = '6';
            $object->title = Text::_('J2STORE_SHIPM_PRICE_BASED_PER_ITEM');
            $instance[$object->id] = $object;
        }

        return $instance;
    }

    /**
     * Returns the requested shipping method object
     *
     * @param $id
     * @return object
     */
    public static function getType($id)
    {
        $items = self::getTypes();
        return $items[$id];
    }

    /**
     * Returns the list of shipping method types
     * @return array of objects
     */
    public static function getIncrementTypes()
    {
        static $instance;

        if (!is_array($instance)) {
            $instance = array();
        }
        if (empty($instance)) {

            $object = new \stdClass();
            $object->id = '0';
            $object->title = Text::_('J2STORE_SHIPPING_ADDITIONAL_INCREMENT');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '1';
            $object->title = Text::_('J2STORE_SHIPPING_ADDITIONAL_DECREMENT');
            $instance[$object->id] = $object;
        }

        return $instance;
    }

    /**
     * Returns the requested shipping method object
     *
     * @param $id
     * @return object
     */
    public static function getIncrementType($id)
    {
        $items = self::getIncrementTypes();
        return $items[$id];
    }
}

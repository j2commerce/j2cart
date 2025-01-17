<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_flexivariable
 *
 * @copyright Copyright (C) 2018 J2Store. All rights reserved.
 * @copyright Copyright (C) 2024 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/appmodel.php');

class J2StoreModelAppFlexiVariables extends J2StoreAppModel
{
    var $_element = 'app_flexivariable';
}

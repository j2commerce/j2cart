<?php
/*
 * mod_j2store_menu
 */

/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

// The J2Store menu will not show if the user has no access.
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_j2store')) {
    return;
}

if (!defined('F0F_INCLUDED')) {
    include_once JPATH_LIBRARIES . '/f0f/include.php';
}

require_once( dirname(__FILE__) . '/helper.php' );

Factory::getLanguage()->load('com_j2store', JPATH_ADMINISTRATOR);
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$link_type = $params->get('link_type', 'link');

require ModuleHelper::getLayoutPath('mod_j2store_menu', $params->get('layout', 'default'));

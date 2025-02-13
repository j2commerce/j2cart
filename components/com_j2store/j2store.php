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

// Load FOF
// Include F0F
if(!defined('F0F_INCLUDED')) {
	require_once JPATH_LIBRARIES . '/f0f/include.php';
}
if(!defined('F0F_INCLUDED')) {
?>
   <h2>J2STORE_INSTALLATION_MESSAGE_INCOMPLETE</h2>
<?php
}
if(!class_exists('J2StoreStrapper')){
	require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
}
J2StoreStrapper::addJS();
J2StoreStrapper::addCSS();
F0FDispatcher::getTmpInstance('com_j2store')->dispatch();
?>

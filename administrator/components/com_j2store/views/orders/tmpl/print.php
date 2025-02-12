<?php
/**
 * @package Joomla.Administrator
 * @subpackage com_j2store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

$task = Factory::getApplication()->input->getString('task');
?>
<?php echo $this->loadAnyTemplate('site:com_j2store/myprofile/view');?>

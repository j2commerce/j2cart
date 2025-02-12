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

$row_class = 'row';
$col_class = 'col-md-';
$primary_button = 'btn btn-primary ';
$secondary_button = 'btn btn-dark  ';
$success_button = 'btn btn-success ';
?>
<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterGeneralInformation', array($this)); ?>

<?php echo $this->loadTemplate('customer');?>

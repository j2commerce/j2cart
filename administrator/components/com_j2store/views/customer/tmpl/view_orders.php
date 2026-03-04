<?php
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
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
?>
<div class="well">
    <!-- Customer's  Billing Address Details  -->
    <address class="pull-left">
        <strong>
            <?php echo $this->item->first_name.' '.$this->item->last_name;?>
        </strong><br>
        <?php echo $this->item->address_1;?>
        <?php echo $this->item->city.' '.$this->item->zip;?>
        <?php echo $this->item->zone_name;?>
        <?php echo $this->item->country_name;?>
        <?php echo $this->item->phone_1;?>
    </address>

    <!--  Delete Options  for Billing Address -->
    <span class="pull-right">
        <a class="btn btn-danger" href="<?php echo Route::_('index.php?option=com_j2store&view=customer&task=delete&id='.$this->item->j2store_address_id);?>">
            <?php echo Text::_('J2STORE_DELETE');?>
        </a>
    </span>
</div>

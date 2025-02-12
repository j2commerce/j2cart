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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
?>
<?php if (!empty($sidebar)): ?>
    <div id="j2c-menu" class="mb-4">
        <?php echo $sidebar; ?>
    </div>
<?php endif;?>
<div class="j2store">
    <div class="px-4 py-5 my-5 text-center">
        <span class="fa-8x mb-4 fas fa-solid fa-truck-medical" aria-hidden="true"></span>
        <h1 class="display-5 fw-bold"><?php echo Text::_('J2STORE_SHIPPING_TROUBLESHOOTER_HEADING'); ?></h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4"><?php echo Text::_('J2STORE_SHIPPING_TROUBLESHOOT_INTRODUCTION'); ?></p>
            <div class="mb-4"><?php echo Text::_('J2STORE_SHIPPING_TROUBLESHOOT_INTRODUCTION_NOTE'); ?></div>
            <div class="text-center">
                <a href="<?php echo Route::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping'); ?>" class="btn btn-primary btn-lg px-4"><?php echo Text::_('J2STORE_SHIPPING_START_WIZARD'); ?><span class="fas fa-solid fa-arrow-right-long ms-2"></span></a>
            </div>
        </div>
    </div>
</div>

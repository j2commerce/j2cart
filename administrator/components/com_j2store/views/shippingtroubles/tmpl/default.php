<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$row_class = 'row';
$col_class = 'col-lg-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class; ?>">
    <?php if (!empty($sidebar)): ?>
    <div id="j-sidebar-container" class="<?php echo $col_class ?>2">
        <?php echo $sidebar; ?>
    </div>
    <div id="j-main-container" class="<?php echo $col_class ?>10">
        <?php else : ?>
        <div class="j2store">
            <?php endif; ?>
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
            <?php if (!empty($sidebar)): ?>
        </div>
        <?php else: ?>
    </div>
<?php endif; ?>
</div>

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

use Joomla\CMS\HTML\HTMLHelper;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
?>
    <?php if (!empty($sidebar)): ?>
    <div id="j2c-menu" class="mb-4">
        <?php echo $sidebar; ?>
    </div>
            <?php endif; ?>
<div class="j2store">
            <form action="index.php" method="post" name="adminForm" id="adminForm">
                <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                <?php echo J2Html::hidden('view', 'inventories'); ?>
                <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
                <?php echo J2Html::hidden('boxchecked', '0'); ?>
                <?php echo J2Html::hidden('filter_order', $this->state->filter_order); ?>
                <?php echo J2Html::hidden('filter_order_Dir', $this->state->filter_order_Dir); ?>

                <div class="j2store-inventory-list">
                    <!-- Products items -->
                    <?php if (J2Store::isPro()): ?>
                        <?php echo $this->loadTemplate('items'); ?>
                    <?php else: ?>
                        <?php echo J2Html::pro(); ?>
                    <?php endif; ?>
                </div>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
</div>

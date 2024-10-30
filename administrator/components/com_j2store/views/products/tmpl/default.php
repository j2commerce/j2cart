<?php
/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2024 J2Commerce, Inc. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */
// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$create_url = 'index.php?option=com_content&view=article&layout=edit';
$row_class = 'row';
$col_class = 'col-md-';
?>
<div class="<?php echo $row_class; ?>">

    <?php if (!empty($sidebar)): ?>
    <div id="j-sidebar-container" class="<?php echo $col_class; ?>2">
        <?php echo $sidebar; ?>
    </div>
    <div id="j-main-container" class="<?php echo $col_class; ?>10">
        <?php else : ?>
        <div class="j2store">
            <?php endif; ?>
            <script type="text/javascript">
                Joomla.submitbutton = function (pressbutton) {
                    if (pressbutton == 'create') {
                        window.open('<?php echo $create_url;?>')
                        return false;
                    }
                    Joomla.submitform(pressbutton);
                }

            </script>

            <div class="alert alert-block alert-info">
                <strong>
                    <?php echo Text::_('J2STORE_PRODUCTS_LIST_VIEW_HELP_TEXT'); ?>
                </strong>
            </div>
            <?php echo J2Store::help()->watch_video_tutorials(); ?>

            <form action="index.php" method="post" name="adminForm" id="adminForm">

                <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                <?php echo J2Html::hidden('view', 'products'); ?>
                <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
                <?php echo J2Html::hidden('boxchecked', '0'); ?>
                <?php echo J2Html::hidden('filter_order', $this->state->filter_order); ?>
                <?php echo J2Html::hidden('filter_order_Dir', $this->state->filter_order_Dir); ?>
                <?php echo JHTML::_('form.token'); ?>
                <div class="j2store-product-filters">
                    <div class="j2store-alert-box" style="display:none;"></div>
                    <!-- general Filters -->
                    <?php echo $this->loadTemplate('filters'); ?>
                    <!-- advanced filters -->
                    <?php echo $this->loadTemplate('advancedfilters'); ?>
                </div>
                <div class="j2store-product-list">
                    <!-- Products items -->
                    <?php echo $this->loadTemplate('items'); ?>
                </div>
            </form>
        </div>
    </div>

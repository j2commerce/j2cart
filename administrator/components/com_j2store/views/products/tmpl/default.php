<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;



$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$create_url = 'index.php?option=com_content&view=article&layout=edit';
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}


$shouldExpand = $this->state->since || $this->state->until || $this->state->visible || $this->state->taxprofile_id || $this->state->vendor_id || $this->state->manufacturer_id || $this->state->productid_from || $this->state->productid_to || $this->state->pricefrom || $this->state->priceto || $this->state->visible;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$script = "Joomla.submitbutton=function(pressbutton){if(pressbutton==='create'){window.open('".$create_url."');return false;}Joomla.submitform(pressbutton);}";
$wa->addInlineScript($script, [], []);
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
        <form action="index.php" method="post" name="adminForm" id="adminForm">
            <?php echo J2Html::hidden('option', 'com_j2store'); ?>
            <?php echo J2Html::hidden('view', 'products'); ?>
            <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
            <?php echo J2Html::hidden('boxchecked', '0'); ?>
            <?php echo J2Html::hidden('filter_order', $this->state->filter_order); ?>
            <?php echo J2Html::hidden('filter_order_Dir', $this->state->filter_order_Dir); ?>
            <?php echo HTMLHelper::_('form.token'); ?>
            <div class="j2store-product-filters">
                <div class="j2store-alert-box" style="display:none;"></div>
                <div class="js-stools">
                    <div class="js-stools-container-bar">
                        <?php echo $this->loadTemplate('filters'); ?>
                    </div>
                    <div class="js-stools-container-filters clearfix collapse<?php echo $shouldExpand ? ' show' : ''; ?>" id="collapseFilters">
                        <?php echo $this->loadTemplate('advancedfilters'); ?>
                    </div>
                </div>
            </div>
            <div class="j2store-product-list">
                <!-- Products items -->
                <?php echo $this->loadTemplate('items'); ?>
            </div>
        </form>
    </div>
</div>

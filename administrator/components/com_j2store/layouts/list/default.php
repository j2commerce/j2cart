<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license GNU GPL v3 or later
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');


$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class;?>">
<?php if(!empty( $sidebar )): ?>
<div id="j-sidebar-container" class="<?php echo $col_class;?>2">
    <?php echo $sidebar ; ?>
</div>
<div id="j-main-container" class="<?php echo $col_class;?>10">
<?php else : ?>
<div class="j2store">
<?php endif;?>

<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        if(pressbutton === 'edit' || pressbutton === 'add') {
            document.getElementById('j2_view').value = '<?php echo $vars->edit_view;?>';
        }
        Joomla.submitform(pressbutton);
        return true;
    }
</script>
     <?php if($vars->view == 'payments' || $vars->view == 'shippings') {
            echo ' <div class="alert alert-info">'.Text::_('COM_J2STORE_EXTENSIONS_ALERT').'</div>';
        }
     ?>
    <form action="<?php echo $vars->action_url;?>" method="post" name="adminForm" id="adminForm">
        <?php echo J2Html::hidden('option',$vars->option);?>
        <?php echo J2Html::hidden('view',$vars->view,array('id' => 'j2_view'));?>
        <?php echo J2Html::hidden('task','browse',array('id'=>'task'));?>
        <?php echo J2Html::hidden('boxchecked','0');?>
        <?php echo J2Html::hidden('filter_order', $vars->state->filter_order,array('id' => 'filter_order'));?>
        <?php echo J2Html::hidden('filter_order_Dir',$vars->state->filter_order_Dir, array('id' => 'filter_order_Dir'));?>
        <?php echo HTMLHelper::_( 'form.token' ); ?>
        <?php include 'default_filters.php';?>
        <?php include 'default_items.php';?>
    </form>
<?php if (!empty($sidebar)): ?>
    </div>
<?php else: ?>
</div>
<?php endif; ?>

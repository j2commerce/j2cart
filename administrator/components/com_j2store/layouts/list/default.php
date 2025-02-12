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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');


$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-lg-';


$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->addInlineScript("
    Joomla.submitbutton = function(pressbutton) {
        if(pressbutton === 'edit' || pressbutton === 'add') {
                document.getElementById('j2_view').value = '".$vars->edit_view."';
        }
        Joomla.submitform(pressbutton);
        return true;
    }
");

?>
<?php if(!empty( $sidebar )): ?>
    <div id="j2c-menu">
        <?php echo $sidebar ; ?>
    </div>
<?php endif;?>
<div class="j2store">
     <?php if($vars->view === 'payments' || $vars->view === 'shippings') {
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
</div>

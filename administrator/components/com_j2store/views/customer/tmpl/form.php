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
?>
<div class="j2store">
<?php if(!empty($this->items)):?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
	<?php echo J2Html::hidden('option','com_j2store');?>
	<?php echo J2Html::hidden('view','customer');?>
	<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
	<?php echo J2Html::hidden('email',$this->item->email,array('id'=>'email'));?>
	<!-- <input type="hidden" name="j2store_address_id" value="<?php // echo $this->item->email;?>" />-->
	<?php echo JHTML::_( 'form.token' ); ?>
	<div class="<?php echo $row_class ?>">
		<div class="<?php echo $col_class ?>6">
		<?php
		if($this->items && !empty($this->items)):
			foreach($this->items as $item):
			$this->item = $item;
		?>
		<?php echo $this->loadTemplate('addresses');?>

		<?php endforeach;?>
		<?php endif;?>
		</div>
		<div class="<?php echo $col_class ?>6">
			<?php echo $this->loadTemplate('orderhistory');?>
		</div>
	</div>
</form>
<?php endif;?>
</div>

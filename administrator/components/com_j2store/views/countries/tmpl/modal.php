<?php
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
HTMLHelper::_('script', 'system/core.js', false, true);
?>
<div class="j2store">
	<h1><?php echo Text::_('J2STORE_COUNTRIES')?></h1>
  <form action="index.php" method="post"	name="adminForm" id="adminForm">
				<?php echo J2Html::hidden('option','com_j2store');?>
				<?php echo J2Html::hidden('view','countries',['id'=>'view']);?>
				<?php echo J2Html::hidden('geozone_id',$this->geozone_id);?>
				<?php echo J2Html::hidden('task','elements',['id'=>'task']);?>
				<?php echo J2Html::hidden('boxchecked','0');?>
				<?php echo J2Html::hidden('filter_order','');?>
				<?php echo J2Html::hidden('filter_order_Dir','');?>
				<?php echo JHTML::_('form.token'); ?>

				<div class="j2store-country-filters">
					<div class="j2store-alert-box" style="display:none;">
					</div>
					<!-- general Filters -->
					<?php echo $this->loadTemplate('filters');?>

				</div>
				<div class="j2store-country-list">
					<?php echo $this->loadTemplate('items');?>
				</div>
	</form>
</div>



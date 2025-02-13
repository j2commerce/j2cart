<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.report_itemized
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

$db = Factory::getContainer()->get('DatabaseDriver');

$state = $vars->state;

$listOrder = $state->get('filter_order');
$listDirn = $state->get('filter_order_Dir');

$form = $vars->form;

$items = $vars->list;

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');
$style = '#filter_datetype{min-width: 200px;}';
$wa->addInlineStyle($style, [], []);
?>
<div class="j2store">
<form action="<?php echo $form['action'];?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','report');?>
		<?php echo J2Html::hidden('task','view',array('id'=>'task'));?>
		<?php echo J2Html::hidden('reportTask','',array('id'=>'reportTask'));?>
		<?php echo J2Html::hidden('format','html',array('id'=>'format'));?>
		<?php echo J2Html::hidden('id',$vars->id);?>
		<?php echo J2Html::hidden('boxchecked',0);?>
		<?php echo J2Html::hidden('filter_order',$listOrder);?>
		<?php echo J2Html::hidden('filter_order_Dir',$listDirn);?>
		<?php echo HTMLHelper::_('form.token'); ?>

        <div class="js-stools">
            <div class="js-stools-container-bar">
                <div class="btn-toolbar w-100 justify-content-end mb-3">
                    <div id="toolbar-icon icon-download" class="me-auto">
                        <a class="btn btn-success" href="<?php echo 'index.php?option=com_j2store&view=reports&format=csv&task=browse&reportTask=export&report_id='.$vars->id;?>">
                            <span class="icon-icon icon-download me-2"></span><?php echo Text::_('JTOOLBAR_EXPORT');?>
							</a>
					</div>
                    <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
                        <div class="input-group w-100">
                            <input type="text" name="filter_search" id="search" value="<?php echo htmlspecialchars($state->get('filter_search'));?>" class="text_area form-control j2store-product-filters" onchange="document.adminForm.submit();" placeholder="<?php echo Text::_( 'J2STORE_FILTER_SEARCH' ); ?>"/>
                            <button type="button" class="btn btn-primary" onclick="this.form.submit();"><span class="filter-search-bar__button-icon icon-search" aria-hidden="true" title="<?php echo Text::_( 'J2STORE_FILTER_GO' ); ?>"></span></button>
                            <button class="btn btn-primary" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_( 'J2STORE_FILTER_RESET' ); ?></button>
				</div>

                    <div class="ordering-select d-flex gap-2 ms-lg-2 flex-grow-1 flex-lg-grow-0">
							<?php
								$attribs = array (
                            'class' => 'form-select j2store-product-filters w-100',
									'onchange' => 'this.form.submit();'
								);
                        echo HTMLHelper::_ ( 'select.genericlist', $vars->orderDateType, 'filter_datetype', $attribs, 'value', 'text', $state->get ( 'filter_datetype' ) );
							?>
                        <?php  echo $vars->pagination->getLimitBox();?>
					</div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-3"><?php echo Text::_('PLG_J2STORE_REPORT_ITEMISED_EXPORT_HELP');?></div>

        <div class="table-responsive">
            <table id="optionsList" class="adminlist table itemList align-middle">
			<thead>
				<tr>
                        <td class="w-1"></td>

                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'J2STORE_PRODUCT_NAME', 'oi.orderitem_name', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>

                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'J2STORE_PRODUCT_ID', 'oi.product_id', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>

                        <th scope="col" class="title">
                            <?php echo Text::_('J2STORE_PRODUCT_OPTIONS');?>
				</th>
                        <th scope="col" class="title">
                            <?php echo Text::_('JCATEGORY');?>
				</th>
                        <th scope="col" class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'J2STORE_QUANTITY', 'sum', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
                        <th scope="col" class="id">
                            <?php echo HTMLHelper::_('grid.sort',  'J2STORE_REPORTS_ITEMISED_PURCHASES', 'count', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
			</tr>
			</thead>
			<tbody>
				<?php if($items) : ?>
					<?php foreach ($items as $i => $item):?>
				<tr class="row<?php echo $i%2; ?>">
                            <td class="small"><?php echo $i+1; ?></td>
                            <td class="small"><strong><?php echo $item->orderitem_name;?></strong></td>
                            <td class="small"><?php echo $item->product_id;?></td>
                            <td class="small">
					<?php

						if(isset($item->orderitem_attributes) && $item->orderitem_attributes):

						foreach($item->orderitem_attributes as $attr):?>
							<small><strong><?php echo $attr->orderitemattribute_name;?> :</strong> <?php echo $attr->orderitemattribute_value;?></small><br>
						<?php endforeach;?>
					<?php endif;?>
				</td>
                            <td class="small"><?php echo $item->category_name;?></td>
                            <td class="small"><?php echo $db->escape($item->sum);?></td>
                            <td class="small"><?php echo $db->escape($item->count);?></td>
                        </tr>
				<?php endforeach; ?>
			<?php else: ?>
                    <tr><td colspan="9"><?php echo Text::_('J2STORE_NO_ITEMS_FOUND'); ?></td></tr>
			<?php endif; ?>
			</tbody>
		  </table>
            <?php echo $vars->pagination->getListFooter(); ?>
        </div>
		</form>
</div>
<script type="text/javascript">
	function getExportedItems(){
        // Set values for #reportTask and #format
        document.getElementById('reportTask').value = 'exportItems';
        document.getElementById('format').value = 'csv';

        // Serialize the form data
        var form = document.getElementById('adminForm');
        var formData = new FormData(form);

        // Create an XMLHttpRequest for the AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);

        // Define what happens on successful response
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Success logic, if any
            }
        };

        // Define what happens on request completion
        xhr.onloadend = function () {
            setTimeout(function () {
							location.reload();
						}, 3000);
        };

        // Send the request with serialized form data
        xhr.send(formData);
	}
</script>

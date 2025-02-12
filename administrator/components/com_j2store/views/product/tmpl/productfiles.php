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
use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->addScript('j2store-jqueryFileTree-js','/media/j2store/js/jqueryFileTree.js');
$platform->addStyle('j2store-jqueryFileTree-css','/media/j2store/css/jqueryFileTree.css');

$row_class = 'row';
$col_class = 'col-md-';
$product_type_class = 'badge bg-success';
?>
<div class="product-downloadable">
		<div class="j2store-modal">
            <div id="myFileModal" style="display: none;">
            <h4 class="message-title"><?php echo Text::_('J2STORE_CHOOSE_FILE'); ?></h4>
            <hr>
            <div><div id="fileTreeDemo_1" class="demo1"></div></div>
            </div>
        </div>
<div class="j2store px-lg-4">
	<form class="form-horizontal form-validate" id="adminForm" 	name="adminForm" method="post" action="index.php">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','products');?>
		<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo J2Html::hidden('product_id', $this->product_id,array('id'=>'product_id'));?>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
	<div class="note">
        <fieldset class="options-form">
            <legend><?php echo Text::_('J2STORE_PFILE_CURRENT_ADD_FILES');?></legend>
            <table class="adminlist table itemList">
			<thead>
				<tr>
                        <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILE_DISPLAY_NAME');?></th>
                        <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILE_PATH');?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
				        <?php echo J2Html::text('product_file_display_name','',array("id"=>"download-total", 'class' =>'form-control')); ?>
					</td>
					<td>
				        <?php echo J2Html::text('product_file_save_name', '',array('class'=>'form-control' ,'id'=>'savename')); ?>
                        <a data-fancybox data-src="#myFileModal" type="button" class="btn btn-info choose-file" ><?php echo Text::_('J2STORE_CHOOSE_FILE');?></a>
					</td>
                    <td class="text-end">
                        <button class="btn btn-primary" onclick="document.getElementById('task').value='createproductfile'; document.adminForm.submit();">
					        <?php echo Text::_('J2STORE_PRODUCT_CREATE_PRICE'); ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
        </fieldset>
	</div>

    <fieldset class="options-form">
        <legend><?php echo Text::_('J2STORE_PFILE_CURRENT_FILES');?></legend>
        <div class="text-start">
            <button class="btn btn-success btn-sm" onclick="document.getElementById('task').value='saveproductfiles'; document.adminForm.submit();">
			    <?php echo Text::_('J2STORE_SAVE_ALL_CHANGES'); ?>
				</button>
			</div>
        <table class="table itemList">
				<thead>
					<tr>
                    <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILE_DISPLAY_NAME');?></th>
                    <th scope="col"><?php echo Text::_('J2STORE_PRODUCT_FILE_PATH');?></th>
					<th></th>
					</tr>
				</thead>
	<?php if(isset($this->productfiles) && !empty($this->productfiles)):?>
	<tbody  class="tr_file_attachement">
		<?php 	foreach($this->productfiles as $counter => $singleFile):?>
			<tr id="exist-file-tbody-<?php echo $singleFile->j2store_productfile_id;?>">

				<td>
                            <?php echo J2Html::text('product_files['.$counter.'][product_file_display_name]',$singleFile->product_file_display_name,array('class' =>'form-control')); ?>
					<?php echo J2Html::hidden('product_files['.$counter.'][product_file_save_name]',$singleFile->product_file_save_name); ?>
			</td>
                        <td><div class="form-text"><?php echo $singleFile->product_file_save_name;?></div></td>
                        <td class="text-end">
					<?php echo J2Html::hidden('product_files['.$counter.'][j2store_productfile_id]',$singleFile->j2store_productfile_id); ?>
					<?php echo J2Html::hidden('product_files['.$counter.'][product_id]',$singleFile->product_id); ?>
					<a class="btn btn-danger" href="index.php?option=com_j2store&view=products&task=deleteFiles&product_id=<?php echo $this->product_id;?>&productfile_id=<?php echo $singleFile->j2store_productfile_id; ?>" >
                                <?php echo Text::_('J2STORE_REMOVE');?>
							</a>

				</td>
			</tr>
		<?php endforeach;?>
		<?php else:?>
		<tr>
			<td colspan="4">
                            <?php echo Text::_('J2STORE_NO_RECORDS');?>
			</td>
		</tr>
		<?php endif;?>
		</tbody>
		</table>
    </fieldset>

	</form>
</div>

<script type="text/javascript">

function handler( event ) {
	(function($) {
		var target = $( event.target );
		if ( target.is( "li" ) ) {
		target.children().toggle();
		}
		$( ".choose-file" ).click( handler ).find( "ul" ).hide();
	})(j2store.jQuery);
}

(function($) {
$(document).ready( function() {
    $('#fileTreeDemo_1').fileTree({ script: 'index.php?option=com_j2store&view=products&task=getFiles' }, function(file) {
        $('#savename').val(file);
        $('#myFileModal').modal('hide');
    });
});
})(j2store.jQuery);

function deleteProductFiles(element){
	(function($){
		var file_id = $(element).attr('file_id');
		var delete_productfile = {
			option: 'com_j2store',
			view : 'products',
			task : 'deleteFiles',
			file_id : file_id,
			product_id : '<?php echo $this->product_id;?>'
		};
		if(file_id){
			$.ajax({
				url  : '<?php echo JRoute::_('index.php');?>',
			method:'post',
			data: delete_productfile ,
			beforeSend:function(){
				$("#file-delete-btn-"+file_id).attr('value','<?php echo Text::_('J2STORE_DELETING');?>');
			},
			success:function(json){
				if(json['success']){
					$("#exist-file-tbody-"+file_id).remove();
				}
			}
		})
		}

		})(j2store.jQuery);
}
</script>

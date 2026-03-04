<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php foreach($this->orders as $order):?>

<?php
$model = F0FModel::getTmpInstance('Orderdownloads', 'J2StoreModel');
$model->clearState()->setState('order_id', $order->order_id);
$downloads = $model->getList();
?>
<?php if(is_countable($downloads) ? count($downloads) : 0): ?>
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th><?php echo Text::_('J2STORE_INVOICE')?></th>
				<th><?php echo Text::_('J2STORE_FILES')?></th>
				<th><?php echo Text::_('J2STORE_ACCESS_EXPIRES')?></th>
				<th><?php echo Text::_('J2STORE_DOWNLOADS_REMAINING')?></th>
				
			</tr>
		</thead>		
		<?php foreach($downloads as $download) : ?>
			<?php
				$available = ($download->download_limit - $download->limit_count );
				$remaining = ($available < 0) ? 0 : $available;   
			?>
			<?php if(is_countable($download->files) ? count($download->files) : 0): ?>
			<tr>
				<td><?php echo $order->invoice; ?></td>
				<td>
					<table class="order-download-files">
					<?php foreach($download->files as $file): ?>
						<tr>
							<td><?php echo $file->product_file_display_name; ?></td>	
							<td>
							<?php $profile_html = J2store::plugin()->eventWithHtml('BeforeProfileDownload',[$file, $download]);?>
							<?php if(!empty($profile_html)):?>
								<?php echo $profile_html;?>								
								<?php elseif($model->validateDownload($download, $file)) : ?>
								<a href="<?php echo J2Store::platform()->getMyprofileUrl(['task' => 'download', 'token' => $download->token, 'pid' => $file->j2store_productfile_id]);
                                //JRoute::_('index.php?option=com_j2store&view=myprofile&task=download&token='.$download->token.'&pid='.$file->j2store_productfile_id); ?>">
									<?php echo Text::_('J2STORE_DOWNLOAD'); ?>
								</a>
                                <?php echo J2store::plugin()->eventWithHtml('AfterProfileDownload',[$file, $download]);?>
<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</table>
				</td>
				
				<td>
				<?php if($download->access_expires == Factory::getDbo()->getNullDate()): ?>
					<?php echo Text::_('J2STORE_NEVER_EXPIRES'); ?>
				<?php else: ?>
                    <?php echo HTMLHelper::date($download->access_expires, J2Store::config()->get('date_format', Text::_('DATE_FORMAT_LC1')), false);?>
				<?php endif;?>
				
				</td>
				<td><?php echo $remaining; ?></td>
			</tr>
			<?php endif; ?>
	
		<?php endforeach;?>
	</table>
	<?php endif; ?>
    <?php echo J2Store::plugin()->eventWithHtml('AfterOrderDownload',[$order]);?>
<?php endforeach;?>

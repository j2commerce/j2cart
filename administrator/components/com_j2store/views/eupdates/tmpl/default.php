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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.modal');
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');

$updates = J2Store::fof()->getModel('EUpdates', 'J2StoreModel')->getUpdates();

$update_link = J2Store::buildHelpLink('my-downloads.html', 'update');

$sidebar = JHtmlSidebar::render();
J2Store::fof()->getModel('Updates', 'J2StoreModel')->refreshUpdateSite();
//now get update
$updateInfo = J2Store::fof()->getModel('Updates', 'J2StoreModel')->getUpdates();

$row_class = 'row';
$col_class = 'col-md-';
?>
 <?php if(!empty( $sidebar )): ?>
    <div id="j2c-menu" class="mb-4">
      <?php echo $sidebar ; ?>
   </div>
    <?php endif;?>

<form action="<?php echo Route::_('index.php?option=com_j2store&view=eupdates'); ?>" method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html">
<div class="j2store updates">
		<?php if(isset($updateInfo['hasUpdate']) && $updateInfo['hasUpdate']) : ?>
            <fieldset class="options-form">
                <legend><?php echo Text::_('J2STORE_COMPONENT_UPDATE')?></legend>
                <div class="table-responsive">
                    <table class="table itemList align-middle">
				<thead>
				<tr>
                                <th scope="col">
						<?php echo '' ?>
					</th>
                                <th scope="col">
                                    <?php echo Text::_('J2STORE_EXISTING_VERSION');?>
					</th>
                                <th scope="col">
                                    <?php echo Text::_('J2STORE_NEW_VERSION');?>
					</th>
                                <th scope="col">
                                    <?php echo Text::_('J2STORE_DOWNLOAD');?>
					</th>
				</tr>
			</thead>
			<tbody>
                <tr>
                    <td><?php echo Text::_('COM_J2STORE'); ?></td>
					<td><?php echo J2STORE_VERSION; ?></td>
					<td><?php echo $updateInfo['version']; ?></td>
					<td>
                        <a class="btn btn-primary" href="<?php echo 'index.php?option=com_installer&view=update' ?>"><?php echo Text::_('J2STORE_UPDATE_TO_VERSION').' '.$updateInfo['version']; ?></a>
					</td>
                </tr>
			</tbody>
		</table>
                </div>
            </fieldset>
		<?php endif; ?>
        <fieldset class="options-form">
            <legend><?php echo Text::_('J2STORE_PLUGIN_APP_UPDATES')?></legend>
			<div class="alert alert-block alert-info">
                <?php echo Text::_('J2STORE_PLUGIN_APP_UPDATES_HELP')?>
			</div>
            <div class="table-responsive">
                <table class="table itemList align-middle">
			<thead>
				<tr>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_PLUGIN_APP_NAME');?>
					</th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_EXISTING_VERSION');?>
					</th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_NEW_VERSION');?>
					</th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_DOWNLOAD');?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if($updates):?>
			<?php foreach($updates as $ext): ?>
				<tr>
					<td>
                                    <?php echo Text::_($ext->name); ?>
					</td>
					<td><?php echo $ext->current_version;?></td>
					<td><?php echo $ext->new_version;?></td>
					<td>
					 	<a class="btn btn-success" target="_blank" href="<?php echo $update_link;?>">
                                        <span class="fa fa-refresh"></span> <?php echo Text::_('J2STORE_DOWNLOAD');?>
					 	</a>
					</td>
				</tr>
			        <?php endforeach; ?>
			    <?php endif;?>
			    </tbody>
		    </table>
	    </div>
        </fieldset>
    </div>
</form>

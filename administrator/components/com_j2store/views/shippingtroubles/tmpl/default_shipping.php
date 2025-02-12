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
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();

$this->params = J2Store::config();

$this->tab_name = 'com-j2store-wizard';
?>
<?php if (!empty($sidebar)): ?>
    <div id="j2c-menu" class="mb-4">
        <?php echo $sidebar; ?>
    </div>
<?php endif;?>
<div class="j2store">
    <?php include 'default_steps.php';?>
    <?php if ($this->shipping_available): ?>
        <?php echo HTMLHelper::_('uitab.startTabSet', $this->tab_name, ['active' => 'editor', 'recall' => true, 'breakpoint' => 768]); ?>
            <?php if (isset($this->shipping_messages) && !empty($this->shipping_messages)): ?>
	            <?php $count = 0; ?>
	            <?php foreach ($this->shipping_messages as $shipping_message): ?>
	                <?php $name = $shipping_message['name'] ?>
	                <?php $message = $shipping_message['message']; ?>
		            <?php foreach ($shipping_message as $shipping_messages): ?>
			            <?php if (empty($shipping_messages['shipping_name'])): ?>
				            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, str_replace(" ", "_", trim($name)), Text::_($name)); ?>
                                <table class="table itemList">
                                    <tbody>
                                    <?php foreach ($message as $key => $value):
	                                    if (strpos($value['value'], 'icon-unpublish') !== false) {
                                            $class = 'text-danger';
	                                    } else {
		                                    $class = 'text-success';
                                        }
                                        ?>
	                                    <?php if (!empty($value['name'])) : ?>
                                            <tr>
                                                <td><?php echo $value['name']; ?></td>
                                                <td class="<?php echo $class;?>"><?php echo $value['value']; ?></td>
                                            </tr>
	                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
				            <?php echo HTMLHelper::_('uitab.endTab'); ?>
		                <?php endif; ?>
		                <?php $count++; ?>
	                <?php endforeach; ?>
	            <?php endforeach; ?>
            <?php endif;?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <?php else: ?>
        <div class="alert alert-danger"><?php echo Text::sprintf('J2STORE_SHIPPING_TROUBLESHOOT_NOTE_MESSAGE', 'index.php?option=com_j2store&view=shippings', J2Store::buildHelpLink('support/user-guide/standard-shipping', 'shipping')); ?></div>
    <?php endif; ?>
    <div class="text-center mt-3">
        <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping_product'); ?>">
            <?php echo Text::_('JNEXT');?><span class="fas fa-solid fa-arrow-right ms-2"></span>
        </a>
    </div>
</div>

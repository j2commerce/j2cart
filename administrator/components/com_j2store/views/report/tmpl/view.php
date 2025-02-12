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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$app = $platform->application();

$row = $this->item;

$sidebar = JHtmlSidebar::render();

Factory::getApplication()->getLanguage()->load('plg_j2store_' . $row->element, JPATH_ADMINISTRATOR, null, true);

PluginHelper::importPlugin('j2store');
?>
<div class="j2store j2store-report">
    <div id="j-sidebar-container">
        <?php echo $sidebar ; ?>
    </div>
    <div class="js-stools mt-4 mb-3">
        <div class="js-stools-container-bar">
            <div class="btn-toolbar gap-2 align-items-center">
                <h2><?php echo Text::_('J2STORE_' . strtoupper($row->element)); ?></h2>
            </div>
        </div>
    </div>
    <?php
        $results = array();
        $results = $app->triggerEvent('onJ2StoreGetReportView', array($row));
        $html = '';
        foreach ($results as $result) {
            $html .= $result;
        }
        echo $html;
    ?>
</div>

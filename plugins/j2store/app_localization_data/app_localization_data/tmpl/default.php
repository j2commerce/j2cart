<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.app_localization_data
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form class="form-horizontal form-validate h-100 position-relative" id="adminForm" name="adminForm" method="post" action="index.php">
    <?php echo J2Html::hidden('option', 'com_j2store'); ?>
    <?php echo J2Html::hidden('view', 'apps'); ?>
    <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>
    <?php echo J2Html::hidden('appTask', '', array('id' => 'appTask')); ?>
    <?php echo J2Html::hidden('table', '', array('id' => 'table')); ?>
    <?php echo J2Html::hidden('id', $vars->id, array('id' => 'table')); ?>
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="j2store-tool-localization-data h-100 position-relative">
        <div class="card h-100 position-relative pb-5">
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <p><?php echo Text::_('J2STORE_APP_LOCALIZATION_DATA_HELP_TEXT'); ?></p>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div class="localization-box text-center">
                            <div class="tool-icon mb-2">
                                <span class="fas fa-solid fa-earth-americas display-4 fw-bolder"></span>
                            </div>
                            <h3 class="mb-3"><?php echo Text::_('J2STORE_COUNTRIES') ?></h3>
                <div class="tool-child tool-country">
                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                                    <button class="btn btn-primary btn-sm" onclick="myToolFunction('countries');">
                                        <span class="icon-icon icon-download me-2"></span><?php echo Text::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>
                </div>
            </div>
                    </div>
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div class="localization-box text-center">
                            <div class="tool-icon mb-2">
                                <span class="fas fa-solid fa-street-view display-4 fw-bolder"></span>
                            </div>
                            <h3 class="mb-3"><?php echo Text::_('J2STORE_ZONES') ?></h3>
                <div class="tool-child tool-zone">

                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                                    <button class="btn btn-primary btn-sm" onclick="myToolFunction('zones');">
                                        <span class="icon-icon icon-download me-2"></span><?php echo Text::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>
                            </div>
                        </div>
                </div>
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div class="localization-box text-center">
                            <div class="tool-icon mb-2">
                                <span class="fas fa-solid fa-ruler-combined display-4 fw-bolder"></span>
            </div>
                            <h3 class="mb-3"><?php echo Text::_('J2STORE_METRICS') ?></h3>
                <div class="tool-child tool-metrics">

                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                                    <button class="btn btn-primary btn-sm" onclick="myToolFunction('metrics');">
                                        <span class="icon-icon icon-download me-2"></span><?php echo Text::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>
                            </div>
                </div>
            </div>
                </div>
                </div>
            </div>
        </div>
</form>

<script type="text/javascript">
    function myToolFunction(table) {
        // Disable the button
        const toolButton = document.getElementById(`tool-btn-`+table);
        if (toolButton) {
            toolButton.disabled = true;
        }

        // Show the confirmation dialog
        const r = confirm("<?php echo Text::_('J2STORE_TABLE_WILL_BE_RESET'); ?>");
        if (r === true) {
            // Set the value of #appTask and #table inputs
            const appTask = document.getElementById('appTask');
            const tableInput = document.getElementById('table');

            if (appTask) {
                appTask.value = 'insertTableValues';
            }
            if (tableInput) {
                tableInput.value = table;
            }
        } else {
            return;
        }
    }
</script>

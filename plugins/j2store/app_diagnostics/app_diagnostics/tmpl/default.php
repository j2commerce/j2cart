<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_diagnostics
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
    <?php echo J2Html::hidden('option', 'com_j2store'); ?>
    <?php echo J2Html::hidden('view', 'apps'); ?>
    <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>
    <?php echo J2Html::hidden('appTask', '', array('id' => 'appTask')); ?>
    <?php echo J2Html::hidden('table', '', array('id' => 'table')); ?>
    <?php echo J2Html::hidden('id', $vars->id, array('id' => 'table')); ?>
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="alert alert-info alert-block">
        <?php echo Text::_('J2STORE_DIAGNOSTICS_HELP_TEXT'); ?>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <caption class="visually-hidden"><?php echo Text::_('PLG_J2STORE_TOOL_DIAGNOSTICS_INFORMATION') ?></caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-30">
                            <?php echo Text::_('PLG_J2STORE_SETTINGS'); ?>
                        </th>
                        <th scope="col">
                            <?php echo Text::_('PLG_J2STORE_SETTINGS_VALUE'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_SERVER'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['server']; ?><?php echo php_uname(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_PHP_VERSION'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['phpversion']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_JOOMLA_VERSION'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['version']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_J2STORE_VERSION'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['j2store_version']; ?><?php echo ($vars->info['is_pro'] == 1) ? 'Professional' : 'Core'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_MEMORY_LIMIT'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['memory_limit']; ?>
                            <?php if ((int) $vars->info['memory_limit'] < 64): ?>
                                <div class="alert alert-danger">
                                    <?php echo Text::_('PLG_J2STORE_MINIMUM_MEMORY_LIMIT_WARNING'); ?>
                                    <a target="_blank" href="https://magazine.joomla.org/issues/issue-dec-2010/item/295-Are-you-getting-your-fair-share-of-PHP-memory">
                                        <strong>Refer this article for more information</strong>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_CURL_ENABLED'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['curl']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_JSON_ENABLED'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['json']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_ERROR_REPORTING'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['error_reporting']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_CACHING_ENABLED'); ?></strong>
                        </td>
                        <td>
                            <?php echo $vars->info['caching']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_CACHE_PLUGIN_ENABLED'); ?></strong>
                        </td>
                        <td>
                            <?php if ($vars->info['plg_cache_enabled']): ?>
                                <?php echo Text::_('J2STORE_ENABLED') ?>
                                <div class="alert alert-danger">
                                    <?php echo Text::_('PLG_J2STORE_SYSTEM_CACHE_WARNING'); ?>
                                </div>
                            <?php else: ?>
                                <?php echo Text::_('J2STORE_DISABLED'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Text::_('PLG_J2STORE_DIAGNOSTICS_CLEAR_CART_CRON'); ?></strong>
                        </td>
                        <td>
                            <?php
                                $cron_key = J2Store::config()->get('queue_key', '');
                                echo trim(Uri::root(), '/') . '/' . 'index.php?option=com_j2store&view=crons&task=cron&cron_secret=' . $cron_key . '&command=clear_cart&clear_time=1440'
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>

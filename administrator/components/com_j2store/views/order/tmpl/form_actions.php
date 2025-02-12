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

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);

$row_class = 'row';
$col_class = 'col-md-';
?>

<nav class="app-icons quick-icons bg-transparent mb-4" aria-label="Order App Icons">
    <ul class="nav flex-wrap">
        <li class="quickicon quickicon-single">

            <a href="/administrator/index.php?option=com_checkin" class="success">
                <div class="quickicon-info">
                    <div class="quickicon-icon">
                        <div class="icon-unlock-alt" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="quickicon-name d-flex align-items-end">
                    Global Checkin                </div>
            </a>
        </li>
        <li class="quickicon quickicon-single">

            <a href="/administrator/index.php?option=com_cache">
                <div class="quickicon-info">
                    <div class="quickicon-icon">
                        <div class="icon-cloud" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="quickicon-name d-flex align-items-end">
                    Cache                </div>
            </a>
        </li>
        <li class="quickicon quickicon-single">

            <a href="/administrator/index.php?option=com_config">
                <div class="quickicon-info">
                    <div class="quickicon-icon">
                        <div class="icon-cog" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="quickicon-name d-flex align-items-end">
                    Global Configuration                </div>
            </a>
        </li>
    </ul>
</nav>



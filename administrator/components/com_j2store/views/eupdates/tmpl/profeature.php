<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_j2store
 *
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<div class="pro-feature">
    <div class="alert alert-primary text-center" role="alert">
        <h3 class="alert-heading fs-3"><?php echo Text::_('J2STORE_PART_OF_PRO_FEATURE'); ?></h3>
        <p class="mb-5"><?php echo Text::_('J2STORE_PART_OF_PRO_FEATURE2'); ?></p>
        <a class="btn btn-primary" target="_blank" href="<?php echo J2Store::buildSiteLink('download', 'prolink'); ?>"><span class="fas fa-solid fa-external-link-alt me-2"></span><?php echo Text::_('J2STORE_PART_OF_PRO_FEATURE_BTN_UPGRADE');?><span class="fas fa-solid fa-arrow-right ms-2"></span></a>
    </div>
</div>

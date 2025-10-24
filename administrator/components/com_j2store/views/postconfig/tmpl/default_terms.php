<?php
/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>


<div class="control-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" name="acceptlicense" id="acceptlicense" <?php if($this->params->get('acceptlicense')): ?> checked="checked" <?php endif; ?>>
        <label class="form-check-label" for="acceptlicense">
            <?php echo Text::_('J2STORE_POSTCONFIG_LBL_ACCEPTLICENSE')?>
        </label>
    </div>
    <div class="form-text"><?php echo Text::_('J2STORE_POSTCONFIG_DESC_ACCEPTLICENSE');?></div>
</div>
<div class="control-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" name="acceptsupport" id="acceptsupport" <?php if($this->params->get('acceptsupport')): ?> checked="checked" <?php endif; ?>>
        <label class="form-check-label" for="acceptsupport">
			<?php echo Text::_('J2STORE_POSTCONFIG_LBL_ACCEPTSUPPORT')?>
        </label>
    </div>
</div>




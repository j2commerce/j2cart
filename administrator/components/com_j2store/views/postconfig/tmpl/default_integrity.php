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
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_INTEGRITY_SYSTEM_PLUGIN_LABEL'));?>
    </div>
    <div class="controls">
	    <?php if($this->systemPlugin): ?>
            <span class="badge bg-success"><?php echo Text::_('JENABLED')?></span>
        <?php else:?>
            <span class="badge bg-danger"><?php echo Text::_('JDISABLED')?></span>
        <?php endif;?>
    </div>
</div>
<?php if($this->cachePlugin): ?>
    <div class="control-group">
        <div class="control-label">
            <?php echo J2Html::label(Text::_('J2STORE_INTEGRITY_CACHE_PLUGIN_LABEL'));?>
        </div>
        <div class="controls">
            <span class="badge bg-danger"><?php echo Text::_('JENABLED')?></span>
        </div>
    </div>
<?php endif;?>

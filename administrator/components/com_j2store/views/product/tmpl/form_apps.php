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
?>
<div class="alert alert-block alert-info">
	<?php echo JText::_('J2STORE_APP_TAB_HELP')?>
</div>
<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayProductForm', array($this, $this->item)); ?>

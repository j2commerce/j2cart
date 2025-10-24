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
		<?php echo J2Html::label(Text::_('J2STORE_CONF_INCLUDING_TAX_LABEL'), 'config_currency_auto');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::select()->clearState()
		    ->type('genericlist')
		    ->name('config_including_tax')
		    ->idTag('config_including_tax')
		    ->value($this->params->get('config_including_tax', 0))
		    ->attribs(array('class'=>'form-select'))
		    ->setPlaceHolders(array(
			    '0'=>Text::_('J2STORE_PRICES_EXCLUDING_TAXES'),
			    '1'=>Text::_('J2STORE_PRICES_INCLUDING_TAXES')
		    ))
		    ->getHtml();
	    ?>
        <small class="form-text"><?php echo Text::_('J2STORE_CONF_INCLUDING_TAX_DESC')?></small>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_DEFAULT_TAX_RATE'), 'tax_rate');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::text('tax_rate', '', array('id'=>'tax_rate','class'=>'form-control'));?>
        <small class="form-text"><?php echo Text::_('J2STORE_DEFAULT_TAX_RATE_DESC')?></small>
    </div>
</div>




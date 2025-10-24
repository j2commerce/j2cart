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
        <?php echo J2Html::label(Text::_('J2STORE_STORE_NAME'), 'store_name');?>
    </div>
    <div class="controls">
        <?php echo J2Html::text('store_name', $this->params->get('store_name'), array('id'=>'store_name','class'=>'form-control'));?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo J2Html::label(Text::_('J2STORE_ADDRESS_ZIP'), 'store_zip');?>
    </div>
    <div class="controls">
        <?php echo J2Html::text('store_zip', $this->params->get('store_zip'), array('id'=>'store_zip','class'=>'form-control'));?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo J2Html::label(Text::_('J2STORE_ADDRESS_COUNTRY'), 'country_id');?>
    </div>
    <div class="controls">
        <?php echo J2Html::select()->clearState()
            ->type('genericlist')
            ->name('country_id')
            ->idTag('country_id')
            ->value($this->params->get('country_id'))
            ->attribs(array('class'=>'form-select'))
            ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
            ->hasOne('Countries')
            ->setRelations(
                array (
                    'fields' => array (
                        'key'=>'j2store_country_id',
                        'name'=>'country_name'
                    )
                )
            )->getHtml();
        ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_STORE_DEFAULT_CURRENCY'), 'config_currency');?>
    </div>
    <div class="controls">
		<?php
		$currencies = J2Store::utilities()->world_currencies();
		echo J2Html::select()->clearState()
			->type('genericlist')
			->name('config_currency')
			->value($this->params->get('config_currency', 'USD'))
			->attribs(array('class'=>'form-select'))
			->setPlaceHolders($currencies)
			->getHtml();
		?>


    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_CURRENCY_SYMBOL_LABEL'), 'config_currency_symbol');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::text('config_currency_symbol', '', array('id'=>'config_currency_symbol', 'placeholder'=>'$','class'=>'form-control'));?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_STORE_CURRENCY_AUTO_UPDATE_CURRENCY'), 'config_currency_auto');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::select()->clearState()
		    ->type('genericlist')
		    ->name('config_currency_auto')
		    ->value($this->params->get('config_currency_auto', 1))
		    ->attribs(array('class'=>'form-select'))
		    ->setPlaceHolders(array(
			    '0'=>Text::_('JNO'),
			    '1'=>Text::_('JYES')
		    ))
		    ->getHtml();
	    ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'config_weight_class_id');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::select()->clearState()
		    ->type('genericlist')
		    ->name('config_weight_class_id')
		    ->value($this->params->get('config_weight_class_id', 1))
		    ->attribs(array('class'=>'form-select'))
		    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
		    ->hasOne('Weights')
		    ->setRelations(
			    array (
				    'fields' => array (
					    'key'=>'j2store_weight_id',
					    'name'=>'weight_title'
				    )
			    )
		    )->getHtml();
	    ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo J2Html::label(Text::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'config_length_class_id');?>
    </div>
    <div class="controls">
	    <?php echo J2Html::select()->clearState()
		    ->type('genericlist')
		    ->name('config_length_class_id')
		    ->value($this->params->get('config_length_class_id', 1))
		    ->attribs(array('class'=>'form-select'))
		    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
		    ->hasOne('Lengths')
		    ->setRelations(
			    array (
				    'fields' => array (
					    'key'=>'j2store_length_id',
					    'name'=>'length_title'
				    )
			    )
		    )->getHtml();
	    ?>
    </div>
</div>





<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_flexivariable
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');

$platform->loadExtra('script', 'media/j2store/js/j2store.js', false, false);

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$script = "Joomla.submitbutton = function(pressbutton) {
		if(pressbutton == 'save' || pressbutton == 'apply') {
			document.adminForm.task ='view';
			document.getElementById('appTask').value = pressbutton;
		}
	  if(pressbutton == 'cancel') {
		  Joomla.submitform('cancel');
	  }
		var atask = document.querySelector('#appTask').value;
		Joomla.submitform('view');
  }";
$wa->addInlineScript($script, [], []);
?>
<div class="j2store-configuration">
    <form action="<?php echo $vars->action; ?>" method="post" name="adminForm" id="adminForm"
          class="form-horizontal form-validate">
        <?php echo J2Html::hidden('option', 'com_j2store'); ?>
        <?php echo J2Html::hidden('view', 'apps'); ?>
        <?php echo J2Html::hidden('app_id', $vars->id); ?>
        <?php echo J2Html::hidden('appTask', '', array('id' => 'appTask')); ?>
        <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>

        <?php echo HTMLHelper::_( 'form.token' ); ?>
        <?php
        $field_sets = $vars->form->getFieldsets();
        $shortcode  = $vars->form->getValue( 'text' );
        $tab_count  = 0;
        echo HTMLHelper::_('uitab.startTabSet', 'configuration', array('active' => 'basic'));
        foreach ( $field_sets as $key => $attr ): ?>
            <?php echo HTMLHelper::_( 'uitab.addTab', 'configuration', $attr->name, Text::_( $attr->label, true ) );?>
            <?php if ( J2Store::isPro() != 1 && isset( $attr->ispro ) && $attr->ispro == 1 ) : ?>
                <?php echo J2Html::pro(); ?>
            <?php else: ?>
                <fieldset class="options-form">
                    <legend><?php echo Text::_($attr->label);?></legend>
                    <div class="form-grid">
                        <?php
                        $layout = '';
                        $style  = '';
                        $fields = $vars->form->getFieldset( $attr->name );
                        foreach ( $fields as $key => $field )
                        {
                            $pro = $field->getAttribute( 'pro' );
                            ?>
                            <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
                                <?php if($field->label):?>
                                    <div class="control-label">
                                        <label><?php echo $field->label; ?></label>
                                    </div>
                                <?php endif;?>
                                <?php if(J2Store::isPro() != 1 && $pro ==1 ): ?>
                                    <?php echo J2Html::pro(); ?>
                                <?php else: ?>
                                    <div class="controls">
                                        <?php echo $field->input; ?>
                                        <?php if($field->description):?>
                                            <div class="form-text"><?php echo Text::_($field->description); ?></div>
                                        <?php endif;?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </fieldset>
            <?php endif; ?>
            <?php echo HTMLHelper::_( 'uitab.endTab' );
            $tab_count ++;
        endforeach;?>
    </form>
</div>

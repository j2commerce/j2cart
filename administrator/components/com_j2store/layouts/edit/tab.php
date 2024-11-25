<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.formvalidator');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.Text::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.Text::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}
?>
<script  type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton == 'cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
        }else{
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }
    }
</script>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">
        <div class="j2store_<?php echo $vars->view;?>_edit">
            <form id="adminForm" class="form-validate" action="<?php echo $vars->action_url?>" method="post" name="adminForm">
                <?php echo J2Html::hidden('option','com_j2store');?>
                <?php echo J2Html::hidden('view',$vars->view);?>
                <?php if(isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                    <?php echo J2Html::hidden($vars->primary_key,$vars->id);?>
                <?php endif; ?>
                <?php echo J2Html::hidden('task', '', array('id'=>'task'));?>
                <?php echo HTMLHelper::_( 'form.token' ); ?>
                <div class="<?php echo $row_class;?>">
                    <?php if(isset($vars->field_sets) && !empty($vars->field_sets)):?>
	                    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic_options', 'recall' => true, 'breakpoint' => 768]); ?>
                        <?php foreach ($vars->field_sets as $field_set):?>
                            <?php if(isset($field_set['fields']) && !empty($field_set['fields'])):?>
			                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $field_set['id'], Text::_($field_set['label'])); ?>
                                <fieldset <?php echo isset($field_set['id']) && $field_set['id'] ? 'id="'.$field_set['id'].'"': '';?>
                                    <?php echo $field_set['class'] ? 'class="'.$field_set['class'].'"': '';?>

                                    <?php echo isset($field_set['class']) && is_array($field_set['class']) ? 'class="'.implode(' ',$field_set['class']).'"': '';?>>
                                    <legend><?php echo Text::_($field_set['label']);?></legend>
                                    <?php if(isset($field_set['is_pro']) && $field_set['is_pro'] && J2Store::isPro() != 1):?>
                                        <?php echo J2Html::pro();?>
                                    <?php else: ?>
                                        <div class="form-grid">
	                                        <?php foreach ($field_set['fields'] as $field_name => $field):?>
		                                        <?php $is_required = isset($field['options']['required']) && !empty($field['options']['required']) ? true:false;?>
                                                <div class="control-group">
                                                    <?php if($field['options']['hiddenLabel'] != true):?>
                                                        <div class="control-label">
                                                            <label>
                                                                <?php if(isset($field['label']) && !empty($field['label'])):?>
                                                                    <?php echo Text::_($field['label']);?><?php echo $is_required ? "<span>*</span>": '';?>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endif;?>
                                                    <div class="controls">
				                                        <?php if(isset($field['type']) && in_array($field['type'],array('number','text','email','password','textarea','file','radio','checkbox','button','submit','hidden'))):?>
					                                        <?php echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);?>
				                                        <?php else:?>
					                                        <?php echo J2Html::custom($field['type'],$field['name'],$field['value'],$field['options']);?>
				                                        <?php endif; ?>
				                                        <?php if(isset($field['desc']) && !empty($field['desc'])):?>
                                                            <div class="hide-aware-inline-help">
                                                                <small class="form-text"><?php echo Text::_($field['desc']);?></small>
                                                            </div>
				                                        <?php endif; ?>
                                                    </div>
                                                </div>
	                                        <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </fieldset>
			                    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                            <?php endif; ?>
                        <?php endforeach;?>
	                    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

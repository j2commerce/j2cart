<?php
/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @website https://www.j2commerce.com
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;



require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/message.php';
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.htmlspecialchars(Text::_('JLIB_FORM_CONTAINS_INVALID_FIELDS')).'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.htmlspecialchars(Text::_('JLIB_FORM_CONTAINS_INVALID_FIELDS')).'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}
?>
<script  type="text/javascript">
    function insertText(value) {
        (function ($) {
            Joomla.editors.instances['body'].replaceSelection(value);
        })(j2store.jQuery);
    }
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton == 'cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
        }else{
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;

                // Ensure the content from the Joomla CodeMirror editor instance is saved to the textarea
                if (Joomla.editors && Joomla.editors.instances && Joomla.editors.instances['source']) {
                    if (typeof Joomla.editors.instances['source'].getValue === 'function') {
                        var value = Joomla.editors.instances['source'].getValue();
                        var textarea = document.getElementById('source');
                        if (textarea) {
                            textarea.value = value;
                        }
                    }
                }

                form.submit();
            } else {
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
            <form id="adminForm" class="form-horizontal form-validate" action="<?php echo $vars->action_url?>" method="post" name="adminForm">
                <?php echo J2Html::hidden('option','com_j2store');?>
                <?php echo J2Html::hidden('view',$vars->view);?>
                <?php if(isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                    <?php echo J2Html::hidden($vars->primary_key,$vars->id);?>
                <?php endif; ?>
                <?php echo J2Html::hidden('task', '', array('id'=>'task'));?>
                <?php echo HTMLHelper::_( 'form.token' ); ?>
                <div class="<?php echo $row_class;?>">
                    <?php if(isset($vars->field_sets) && !empty($vars->field_sets)):?>
                        <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic_options')); ?>
                        <?php else: ?>
                            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic_options', 'recall' => true, 'breakpoint' => 768]); ?>
                        <?php endif; ?>
                        <?php foreach ($vars->field_sets as $field_set):?>
                            <?php if(isset($field_set['fields']) && !empty($field_set['fields'])):?>
                                <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', $field_set['id'], Text::_($field_set['label'])); ?>
                                <?php else: ?>
                                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $field_set['id'], Text::_($field_set['label'])); ?>
                                <?php endif; ?>
                                <fieldset <?php echo isset($field_set['id']) && $field_set['id'] ? 'id="'.$field_set['id'].'-fieldset"': '';?> class="options-form <?php echo isset($field_set['class']) && is_array($field_set['class']) ? ' '.implode(' ',$field_set['class']): '';?>">
                                    <legend><?php echo Text::_($field_set['label']);?></legend>
                                    <div class="form-grid">
	                                    <?php if(isset($field_set['is_pro']) && $field_set['is_pro'] && J2Store::isPro() != 1):?>
		                                    <?php echo J2Html::pro();?>
	                                    <?php else: ?>
		                                    <?php $layout = '';
		                                    $style = ''; ?>
		                                    <?php foreach ($field_set['fields'] as $field_name => $field):?>
			                                    <?php if($field_name == 'body'):?>
                                                    <div class="<?php echo $row_class ?>">
                                                        <div class="<?php echo $col_class ?>9">
                                                            <div class="control-group" style="<?php echo isset($field['style']) && !empty($field['style']) ? $field['style']: '';?>">
                                                                <div class="w-100">
								                                    <?php if(isset($field['type']) && in_array($field['type'],array('number','text','email','password','textarea','file','radio','checkbox','button','submit','hidden'))):?>
									                                    <?php echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);?>
								                                    <?php else:?>
									                                    <?php echo J2Html::custom($field['type'],$field['name'],$field['value'],$field['options']);?>
								                                    <?php endif; ?>
								                                    <?php if(isset($field['desc']) && !empty($field['desc'])):?>
                                                                        <div id="<?php echo $field['name'];?>-desc" class="hide-aware-inline-help">
                                                                            <small class="form-text"><?php echo Text::_($field['desc']);?></small>
                                                                        </div>
								                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div>
							                                    <?php include_once JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tmpl/form_tags.php' ;?>
                                                            </div>
                                                        </div>
                                                        <div class="<?php echo $col_class ?>3" style="<?php echo isset($field['style']) && !empty($field['style']) ? $field['style']: '';?>">
                                                            <label class="control-label"><?php echo Text::_('J2STORE_TEMPLATE_INSERT_MESSAGE_TAGS');?></label>
                                                            <div class="input-append">
                                                                <a class="btn btn-success" onclick="insertText(jQuery('#message_tag').prop('value'));">
                                                                    <i class="icon-arrow-left"></i>
                                                                </a>
                                                                <select id="message_tag" size="40">
								                                    <?php $message_tags = J2StoreMessage::getMessageTags();?>
								                                    <?php if(isset($message_tags) && !empty($message_tags)):?>
									                                    <?php foreach($message_tags as $key => $option_group):?>
                                                                            <optgroup label="<?php echo Text::_('J2STORE_'.strtoupper($key));?>">
											                                    <?php if(isset($option_group) && !empty($option_group)):?>
												                                    <?php foreach($option_group as $key => $text):?>
                                                                                        <option value="<?php echo $key;?>"><?php echo $text?></option>
												                                    <?php endforeach;?>
											                                    <?php endif;?>
                                                                            </optgroup>
									                                    <?php endforeach;?>
								                                    <?php endif;?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
			                                    <?php else:?>
				                                    <?php $is_required = isset($field['options']['required']) && !empty($field['options']['required']) ? true:false;?>
                                                    <div class="control-group" style="<?php echo isset($field['style']) && !empty($field['style']) ? $field['style']: '';?>">
                                                        <label class="control-label">
						                                    <?php if(isset($field['label']) && !empty($field['label'])):?>
							                                    <?php echo Text::_($field['label']);?><?php echo $is_required ? "<span>*</span>": '';?>
						                                    <?php endif; ?>
                                                        </label>
                                                        <div class="controls">
						                                    <?php if(isset($field['type']) && in_array($field['type'],array('number','text','email','password','textarea','file','radio','checkbox','button','submit','hidden'))):?>
							                                    <?php echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);?>
						                                    <?php else:?>
							                                    <?php echo J2Html::custom($field['type'],$field['name'],$field['value'],$field['options']);?>
						                                    <?php endif; ?>
	                                                        <?php if(isset($field['desc']) && !empty($field['desc'])):?>
                                                                <div id="<?php echo $field['name'];?>-desc" class="hide-aware-inline-help">
                                                                    <small class="form-text"><?php echo Text::_($field['desc']);?></small>
                                                                </div>
	                                                        <?php endif; ?>
                                                        </div>
                                                    </div>
			                                    <?php endif; ?>
		                                    <?php endforeach; ?>
	                                    <?php endif; ?>
                                    </div>

                                </fieldset>
                                <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                                <?php else: ?>
                                    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach;?>
                        <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
                        <?php else: ?>
                            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTabSet'); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>


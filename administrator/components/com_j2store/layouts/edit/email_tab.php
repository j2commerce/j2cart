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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/message.php';

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.Text::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
?>
<script  type="text/javascript">
    function insertText(value) {
        const editorInstance = Joomla.editors.instances['body'];
        if (editorInstance) {
            const currentContent = editorInstance.getValue();
            const selection = getSelectionText(editorInstance);
            const newContent = currentContent.replace(selection, value);
            editorInstance.setValue(newContent);
        }
    }
    function getSelectionText(editorInstance) {
        const selection = editorInstance.getSelection();
        return selection && selection.text ? selection.text : '';
    }

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
<div class="row">
    <div class="col-12">
        <div class="j2store_<?php echo $vars->view;?>_edit">
            <form id="adminForm" class="form-horizontal form-validate" action="<?php echo $vars->action_url?>" method="post" name="adminForm">
                <?php echo J2Html::hidden('option','com_j2store');?>
                <?php echo J2Html::hidden('view',$vars->view);?>
                <?php if(isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                    <?php echo J2Html::hidden($vars->primary_key,$vars->id);?>
                <?php endif; ?>
                <?php echo J2Html::hidden('task', '', array('id'=>'task'));?>
                <?php echo HTMLHelper::_( 'form.token' ); ?>
                <div class="row">
                    <?php if(isset($vars->field_sets) && !empty($vars->field_sets)):?>
                            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic_options', 'recall' => true, 'breakpoint' => 768]); ?>
                        <?php foreach ($vars->field_sets as $field_set):?>
                            <?php if(isset($field_set['fields']) && !empty($field_set['fields'])):?>
                                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $field_set['id'], Text::_($field_set['label'])); ?>
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
                                                    <div class="row">
                                                        <div class="col-lg-9">
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
							                                    <?php echo include_once JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tmpl/form_tags.php' ;?>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3" style="<?php echo isset($field['style']) && !empty($field['style']) ? $field['style']: '';?>">
                                                            <fieldset class="options-form px-3">
                                                                <legend><?php echo Text::_('J2STORE_TEMPLATE_INSERT_MESSAGE_TAGS');?></legend>

                                                                <div class="input-append mx-2">
                                                                    <a class="btn btn-primary mb-2" onclick="insertText(jQuery('#message_tag').prop('value'));">
                                                                    <i class="icon-arrow-left"></i>
                                                                </a>
                                                                    <select id="message_tag" size="40" class="form-control">
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
                                                            </fieldset>
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
                                    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
                            <?php endif; ?>
                        <?php endforeach;?>
                            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTabSet'); ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

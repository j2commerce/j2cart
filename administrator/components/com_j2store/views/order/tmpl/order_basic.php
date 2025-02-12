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
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('webcomponent.joomla-tab');

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
$version = 'old';
$document = Factory::getApplication()->getDocument();
    $version = 'current';
    $user_modal_url='index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0';
    $document->getWebAssetManager()
        ->useScript('webcomponent.field-user');


?>
<fieldset class="order-general-information options-form">
    <legend><?php echo Text::_('J2STORE_STORES_GROUP_BASIC');?></legend>
    <div class="form-grid">
		<div class="control-group">
            <div class="control-label">
			    <?php echo J2Html::label(Text::_('J2STORE_ORDER_ID') ,'order_id'); ?>
            </div>
			<div class="controls">
                <input type="text" readonly class="form-control-plaintext border-0 fw-bold" id="order_id" value="<?php echo $this->order->order_id;?>" />
			</div>
		</div>
		<?php if($this->order->invoice_prefix && $this->order->invoice_number):?>
		<div class="control-group">
                <div class="control-label">
				    <?php echo J2Html::label(Text::_('J2STORE_INVOICE') ,'invoice-prefix'); ?>
                </div>
			<div class="controls">
                    <input type="text" readonly class="form-control-plaintext" id="invoice-prefix" value="<?php echo $this->order->invoice_prefix;?><?php echo $this->order->invoice_number;?>" />
			</div>
		</div>
		<?php endif;?>
		<div class="control-group">
            <div class="control-label">
	            <?php echo J2Html::label(Text::_('J2STORE_ORDER_DATE') ,'created-on'); ?>
            </div>
			<div class="controls">
	            <?php echo JHtml::calendar($this->order->created_on, $this->form_prefix.'[created_on]','order-created-on','%d-%m-%Y', array('class'=>'form-control'));?>
			</div>
		</div>
		<div class="control-group">
            <div class="control-label">
			    <?php echo J2Html::label(Text::_('J2STORE_ORDER_EMAIL') ,$this->form_prefix.'[customer_email]'); ?>
            </div>
			<div class="controls">
					<?php
					$user_name = '';
					if($this->order->user_id){
						$user_name=J2Html::getUserNameById($this->order->user_id);
					}
					?>
                            <joomla-field-user class="field-user-wrapper" url="<?php echo $user_modal_url;?>" modal=".modal" modal-width="100%" modal-height="400px" input=".field-user-input" input-name=".field-user-input-name" button-select=".button-select">
                                <div class="input-group">
                        <input type="text" class="field-user-input-name form-control" name="<?php echo $this->form_prefix.'[user_name]';?>" readonly="" placeholder="Select a User." value="<?php echo $user_name;?>" id="jform_created_by">
                                    <button type="button" class="btn btn-primary button-select" title="Select User">
                                        <span class="icon-user icon-white" aria-hidden="true"></span>
                                        <span class="visually-hidden">Select User</span>
                                    </button>
                                </div>
                                <input type="hidden" data-onchange="" class="field-user-input " value="<?php echo $this->order->user_id;?>" name="<?php echo $this->form_prefix.'[user_id]';?>" id="jform_created_by_id">
                                <div id="userModal_jform_created_by" role="dialog" tabindex="-1" class="joomla-modal modal fade" data-url="<?php echo $user_modal_url;?>" data-iframe="<iframe class=&quot;iframe&quot; src=&quot;index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field=jform_created_by&quot; name=&quot;Select User&quot; title=&quot;Select User&quot; height=&quot;100%&quot; width=&quot;100%&quot;></iframe>">
                                    <div class="modal-dialog modal-lg jviewport-width80">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title">Select User</h3>
                                                <button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="Close">
                                                </button>
                                            </div>
                                            <div class="modal-body jviewport-height60">
                                            </div>
                                            <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                </joomla-field-user>
			</div>
		</div>
		<?php if(!empty($this->order->j2store_order_id) && $this->order->user_id <= 0):?>
			<div class="alert alert-info">
				<?php echo Text::_('J2STORE_EDIT_GUEST_ORDER_NOTE');?>
				<?php if(isset($this->order->user_email) && $this->order->user_email):?>
					<?php echo Text::sprintf('J2STORE_EDIT_GUEST_ORDER_USER_EMAIL_NOTE',$this->order->user_email);?>
				<?php endif;?>
			</div>
		<?php endif;?>
		<div class="control-group">
            <div class="control-label">
			    <?php echo J2Html::label(Text::_('J2STORE_CUSTOMER_CHECKOUT_LANGUAGE') ,'order-language'); ?>
            </div>
			<div class="controls">
				<?php   echo J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[customer_language]')
		            ->attribs(array('class'=>'form-select'))
						->value($this->order->customer_language)
						->setPlaceHolders($this->languages)
						->getHtml(); ?>
			</div>
		</div>
        <div class="alert alert-info"><?php echo Text::_('J2STORE_EDIT_ORDER_STATUS_NOTE');?></div>
        <div class="control-group">
            <div class="control-label">
			    <?php echo J2Html::label(Text::_('J2STORE_ORDER_STATUS') ,'order_status'); ?>
            </div>
			<div class="controls">
	            <?php echo str_replace(['<label', '</label', 'label '], ['<span', '</span', 'badge '], $this->order_status);?>
				<input type="hidden" name="<?php echo $this->form_prefix.'[order_state_id]';?>" value="<?php echo (isset($this->order->order_state_id) && !empty($this->order->order_state_id)) ? $this->order->order_state_id : 5;?>"/>
			</div>
		</div>
		<div class="control-group">
            <div class="control-label">
				<?php echo J2Html::label(Text::_('J2STORE_CUSTOMER_NOTE') ,'customer_note'); ?>
            </div>
			<div class="controls">
				<textarea name="<?php echo $this->form_prefix.'[customer_note]' ;?>" rows="3" class="form-control"><?php echo $this->order->customer_note;?></textarea>
			</div>
		</div>
		<div>
		<input type="hidden" name="<?php echo $this->form_prefix.'[update_history]';?>" value="<?php echo $this->update_history;?>"/>
		</div>
	</div>
</fieldset>

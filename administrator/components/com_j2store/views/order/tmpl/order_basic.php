<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');

// Derive the same id that J2Html::user() generates for this field name,
// then append '_id' to reach the hidden input the web-component updates.
$userFieldId  = trim(preg_replace('/_+/', '_', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->form_prefix.'[user_id]')), '_');
$hiddenUserId = $userFieldId . '_id';
$isGuest      = (($this->order->user_id ?? 0) <= 0);
?>
<div class="order-general-information">
	<div class="info-body">
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_DATE') ,'created-on',array('class'=>'control-label')); ?>
			<div class="controls">
				<?php echo JHtml::calendar($this->order->created_on, $this->form_prefix.'[created_on]','order-created-on','%d-%m-%Y', array('class'=>'input-small'));?>
			</div>
		</div>
		<?php if(!empty($this->order->invoice_prefix) && !empty($this->order->invoice_number)):?>
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_INVOICE') ,'invoice-prefix',array('class'=>'control-label')); ?>
                <div class="controls">
                    <?php echo $this->order->invoice_prefix;?><?php echo $this->order->invoice_number;?>
                </div>
            </div>
		<?php endif;?>
        <?php if(!empty($this->order->order_id)):?>
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_ORDER_ID') ,'order_id',array('class'=>'control-label')); ?>
                <div class="controls">
                    <?php echo $this->order->order_id;?>
                </div>
            </div>
        <?php endif;?>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_USER'), $userFieldId, array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo J2Html::user($this->form_prefix.'[user_id]', $this->order->user_id ?? 0); ?>
			</div>
		</div>

		<div class="control-group" id="j2store-guest-email-group"<?php echo $isGuest ? '' : ' style="display:none"'; ?>>
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_EMAIL'), $this->form_prefix.'[user_email]', array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo J2Html::input('email', $this->form_prefix.'[user_email]', $this->order->user_email ?? '', array('class' => 'form-control')); ?>
			</div>
		</div>

		<?php // Alert is always rendered; initial visibility is set via inline style. ?>
		<div class="alert alert-info" id="j2store-guest-order-alert"<?php echo $isGuest ? '' : ' style="display:none"'; ?>>
			<?php echo JText::_('J2STORE_EDIT_GUEST_ORDER_NOTE'); ?>
			<?php if (!empty($this->order->j2store_order_id) && !empty($this->order->user_email)): ?>
				<?php echo JText::sprintf('J2STORE_EDIT_GUEST_ORDER_USER_EMAIL_NOTE', $this->order->user_email); ?>
			<?php endif; ?>
		</div>

		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_CUSTOMER_CHECKOUT_LANGUAGE'), 'order-language',array('class'=>'control-label'));?>
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
        <div class="alert alert-info"><?php echo JText::_('J2STORE_EDIT_ORDER_STATUS_NOTE');?></div>
        <div class="control-group">
            <div><?php echo  J2Html::label(JText::_('J2STORE_ORDER_STATUS'),'order_status',array('class'=>'control-label'));?></div>
			<div class="controls">
				<?php echo $this->order_status;?>
				<input type="hidden" name="<?php echo $this->form_prefix.'[order_state_id]';?>" value="<?php echo (isset($this->order->order_state_id) && !empty($this->order->order_state_id)) ? $this->order->order_state_id : 5;?>"/>
			</div>
		</div>
		<div class="control-group">
			<?php echo  J2Html::label(JText::_('J2STORE_CUSTOMER_NOTE'),'customer_note',array('class'=>'control-label'));?>
			<div class="controls">
                <?php echo J2Html::input('textarea', $this->form_prefix.'[customer_note]', $this->order->customer_note); ?>
			</div>
		</div>
		<div>
		<input type="hidden" name="<?php echo $this->form_prefix.'[update_history]';?>" value="<?php echo $this->update_history;?>"/>
		</div>
	</div>
</div>

<script>
(function () {
    var input      = document.getElementById('<?php echo $hiddenUserId; ?>');
    var alert      = document.getElementById('j2store-guest-order-alert');
    var emailGroup = document.getElementById('j2store-guest-email-group');
    if (!input) { return; }

    function syncGuest() {
        var uid     = parseInt(input.value, 10) || 0;
        var isGuest = uid <= 0;
        if (alert)      { alert.style.display      = isGuest ? '' : 'none'; }
        if (emailGroup) { emailGroup.style.display  = isGuest ? '' : 'none'; }
    }

    input.addEventListener('change', syncGuest);
    syncGuest();
}());
</script>

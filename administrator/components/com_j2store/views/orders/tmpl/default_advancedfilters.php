<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$version = 'old';
$document = Factory::getApplication()->getDocument();

if(version_compare(JVERSION,'3.99.99','ge')) {
    $version = 'current';
    $user_modal_url = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0';
    $document->getWebAssetManager()->useScript('webcomponent.field-user');

}elseif (version_compare(JVERSION, '3.6.1', 'ge')) {
    $version = 'new';
    $user_modal_url = "index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field={field-user-id}&amp;ismoo=0&amp;excluded=WyIiXQ==";
} elseif (version_compare(JVERSION, '3.5.0', 'ge')) {
    $user_modal_url = "index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field={field-user-id}&amp;excluded=WyIiXQ==";
    $version = 'new';
}
if ($version == 'new') {
    $document->addScript(Uri::root(true) . '/media/jui/js/fielduser.min.js');
}
$row_class = 'row';
$col_class = 'col-md-';
$secondary_button = 'btn btn-dark';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $secondary_button = 'btn btn-inverse';
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="px-2 pt-2 pb-0 mb-5">
    <div class="<?php echo $row_class;?>"  id="advanced-search-controls">
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::select()->clearState()
		        ->type('genericlist')
		        ->name('paykey')
		        ->value($this->state->paykey)
		        ->attribs(array('onchange' => 'this.form.submit', 'class' => 'form-select j2store-product-filters'))
		        ->setPlaceHolders(array('' => Text::_('J2STORE_FILTER_PAYMENTS')))
		        ->hasOne('Payments')
		        ->setRelations(
			        array(
				        'fields' => array
				        (
					        'key' => 'element',
					        'name' => 'element'
				        )
			        )
		        )->getHtml();
	        ?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <div class="field-user">
		        <?php $user_name = '';
		        if ($this->state->user_id) {
			        $user_name = J2Html::getUserNameById($this->state->user_id);
		        }
		        ?>

                <?php if ($version == 'old'): ?>
                    <input type="text" class="input-small" name="user_name" value="<?php echo $user_name; ?>" id="jform_user_id_name" readonly aria-invalid="false"/>
                    <input type="hidden" onchange="j2storeGetAddress()" name="user_id" value="<?php echo $this->state->user_id; ?>" id="jform_user_id" class="j2store-order-filters" readonly />
                    <?php $url = 'index.php?option=com_users&view=users&layout=modal&tmpl=component&field=jform_user_id'; ?>
                    <?php echo J2StorePopup::popup($user_modal_url, '<span class="icon icon-user"></span>', array('class' => 'btn btn-primary modal_jform_created_by')); ?>
                <?php elseif ($version == 'new'): ?>
                    <div data-button-select=".button-select" data-input-name=".field-user-input-name"
                         data-input=".field-user-input" data-modal-height="400px"
                         data-modal-width="100%" data-modal=".modal"
                         data-url="<?php echo $user_modal_url; ?>" class="field-user-wrapper">
                        <div class="input-append">
                            <input type="text" class="field-user-input-name " name="user_name" readonly="" placeholder="Select a User." value="<?php echo $user_name; ?>" id="jform_created_by">
                            <a title="Select User" class="btn btn-primary button-select"><span class="icon-user"></span></a>
                            <div class="modal hide fade" tabindex="-1" id="userModal_jform_created_by">
                                <div class="modal-header">
                                    <button data-dismiss="modal" class="close" type="button">×</button>
                                    <h3>Select User</h3>
                                </div>
                                <div class="modal-body"></div>
                                <div class="modal-footer">
                                    <button data-dismiss="modal" class="btn">Cancel</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" data-onchange="" class="field-user-input" value="<?php echo $this->state->user_id; ?>" name="user_id" id="jform_created_by_id">
                    </div>
                <?php elseif($version == 'current'): ?>
                    <joomla-field-user class="field-user-wrapper" url="<?php echo $user_modal_url;?>" modal=".modal" modal-width="100%" modal-height="400px" input=".field-user-input" input-name=".field-user-input-name" button-select=".button-select">
                        <div class="input-group">
                            <input type="text" class="field-user-input-name form-control" name="<?php echo $this->form_prefix.'user_name';?>" readonly="" placeholder="Select a User" value="<?php echo $user_name;?>" id="jform_created_by">
                            <button type="button"  class="btn btn-primary button-select" title="Select User">
                                <span class="icon-user icon-white" aria-hidden="true"></span>
                                <span class="visually-hidden">Select User</span>
                            </button>
                        </div>
                        <input type="hidden" data-onchange="" class="field-user-input " value="<?php echo  $this->state->user_id;?>" name="<?php echo $this->form_prefix.'user_id';?>" id="jform_created_by_id">
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
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::text('moneysum', $this->state->moneysum, array('class' => 'form-control j2store-order-filters','placeholder'=>Text::_('J2STORE_ORDER_AMOUNT')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::text('coupon_code', $this->state->coupon_code, array('class' => 'form-control j2store-order-filters','placeholder'=>Text::_('J2STORE_FILTER_COUPON_CODE')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::calendar('since', $this->state->since, array('class' => 'form-control j2store-order-filters', 'placeholder'=>Text::_('J2STORE_ORDER_DATE_FROM')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::calendar('until', $this->state->until, array('class' => 'form-control j2store-order-filters', 'placeholder'=>Text::_('J2STORE_ORDER_DATE_TO')));?>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <div class="input-group">
                <span class="input-group-text"><span class="fas fa-solid fa-list-alt"></span></span>
	            <?php echo J2Html::text('frominvoice', $this->state->frominvoice, array('class' => 'form-control j2store-order-filters', 'placeholder'=>Text::_('J2STORE_ORDER_ID_FROM')));?>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <div class="input-group">
                <span class="input-group-text"><span class="fas fa-solid fa-list-alt"></span></span>
		        <?php echo J2Html::text('toinvoice', $this->state->toinvoice, array('class' => 'form-control j2store-order-filters', 'placeholder'=>Text::_('J2STORE_ORDER_ID_TO')));?>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
	        <?php echo J2Html::button('advanced_search', Text::_('J2STORE_APPLY_FILTER'), array('class' => 'btn btn-primary w-100', 'onclick' => 'this.form.submit();')); ?>

        </div>
    </div>
</div>


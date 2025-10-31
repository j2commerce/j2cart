<?php
/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;



$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();



$row_class = 'row';
$col_class = 'col-lg-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
$script = <<<JS
if (typeof j2store === 'undefined') {
    var j2store = {};
}
if (typeof j2store.jQuery === 'undefined') {
    j2store.jQuery = window.jQuery; // Assigns the global jQuery reference to j2store.jQuery
}
(function($) {
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('j2store-postconfig-apply').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Prepare form data
            const formData = new FormData();
            const inputs = document.querySelectorAll('#j2store-postconfig-form input[type="text"], #j2store-postconfig-form input[type="checkbox"]:checked, #j2store-postconfig-form input[type="radio"]:checked, #j2store-postconfig-form input[type="hidden"], #j2store-postconfig-form select, #j2store-postconfig-form textarea');

            inputs.forEach(input => {
                formData.append(input.name, input.value);
            });

            // Disable button and add loading indicator
            const applyButton = document.getElementById('j2store-postconfig-apply');
            applyButton.setAttribute('disabled', true);
            const loadingIndicator = document.createElement('span');
            loadingIndicator.className = 'wait';
            loadingIndicator.innerHTML = '&nbsp;<img src="media/j2store/images/loading.gif" alt="" />';
            applyButton.insertAdjacentElement('afterend', loadingIndicator);

            // Send AJAX request
            fetch('index.php?option=com_j2store&view=postconfig&task=saveConfig', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(json => {
                // Remove any existing warnings or errors
                document.querySelectorAll('.warning, .j2error').forEach(el => el.remove());

                // Handle JSON response
                if (json.redirect) {
                    window.location.href = json.redirect;
                } else if (json.error) {
                    for (const [key, value] of Object.entries(json.error)) {
                        if (value) {
                            const errorElement = document.createElement('span');
                            errorElement.className = 'j2error';
                            errorElement.innerHTML = value;
                            document.getElementById(key).insertAdjacentHTML('afterend', '<br class="j2error" />');
                            document.getElementById(key).insertAdjacentElement('afterend', errorElement);
                        }
                    }
                }
            })
            .finally(() => {
                // Re-enable button and remove loading indicator
                applyButton.removeAttribute('disabled');
                loadingIndicator.remove();
            });
        });
    });
})(window.jQuery);
JS;

$wa->addInlineScript($script, [], []);
?>
<div class="card mb-3">
    <div class="card-body">
        <div class="px-2 py-2 mb-0 text-center">
            <i class="fa-4x mb-2 fa-solid fas fa-rocket"></i>
            <h1 class="display-6 fw-bold"><?php echo Text::_('J2STORE_CONGRATULATIONS')?></h1>
            <div class="col-lg-6 mx-auto">
                <p class="lead mb-4"><?php echo Text::_('J2STORE_POSTCONFIG_WELCOME_MESSAGE'); ?></p>
                <p class="mb-4"><?php echo Text::_('J2STORE_POSTCONFIG_WHATTHIS'); ?></p>
            </div>
        </div>
        <form action="index.php" method="post" name="adminForm" id="j2store-postconfig-form" class="form-horizontal">
            <input type="hidden" name="option" value="com_j2store" />
            <input type="hidden" name="view" value="postconfig" /> <input type="hidden" name="task" id="task" value="saveConfig" />
            <input type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken()?>" value="1" />

            <div class="<?php echo $row_class;?>">
                <div class="<?php echo $col_class;?>12">
                    <fieldset id="fieldset-basic" class="options-form">
                        <legend><?php echo Text::_('J2STORE_BASIC_SETTINGS'); ?></legend>
                        <div class="form-grid">
						    <?php echo $this->loadTemplate('basic'); ?>
                        </div>
                    </fieldset>
                </div>

                <div class="<?php echo $col_class;?>12">
                    <fieldset id="fieldset-advanced" class="options-form">
                        <legend><?php echo Text::_('J2STORE_ADVANCED_SETTINGS'); ?></legend>
                        <div class="form-grid">
	                        <?php echo $this->loadTemplate('integrity'); ?>
	                        <?php echo $this->loadTemplate('advanced'); ?>
                        </div>
                    </fieldset>
                </div>
	            <?php if(J2Store::isPro() != 1): ?>
                    <div class="<?php echo $col_class;?>12">
                        <fieldset id="fieldset-terms" class="options-form">
                            <legend><?php echo Text::_('J2STORE_POSTCONFIG_LBL_MANDATORYINFO'); ?></legend>
                            <div class="form-grid">
                                <?php echo $this->loadTemplate('terms'); ?>
                            </div>
                        </fieldset>
                    </div>
	            <?php endif;?>
            </div>
            <div class="text-center text-lg-end">
                <button id="j2store-postconfig-apply" class="btn btn-primary btn-large" onclick="return false;"><?php echo Text::_('J2STORE_SAVE_AND_PROCEED');?></button>
            </div>
        </form>
    </div>
</div>

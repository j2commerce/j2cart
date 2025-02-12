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

use Joomla\CMS\Language\Text;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();

$shouldExpand = $this->state->since || $this->state->until || $this->state->paykey || $this->state->moneysum || $this->state->toinvoice || $this->state->coupon_code || $this->state->user_id;
?>
    <?php if (!empty($sidebar)): ?>
    <div id="j2c-menu" class="mb-4">
        <?php echo $sidebar; ?>
    </div>
<?php endif;?>
        <div class="j2store">
            <form action="index.php?option=com_j2store&view=orders" method="post" name="adminForm" id="adminForm">
                <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                <?php echo J2Html::hidden('view', 'orders'); ?>
                <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
                <?php echo J2Html::hidden('boxchecked', '0'); ?>
                <?php echo J2Html::hidden('filter_order', ''); ?>
                <?php echo J2Html::hidden('filter_order_Dir', ''); ?>
                <?php echo JHTML::_('form.token'); ?>
                <div class="j2store-order-filters">
                    <div class="j2store-alert-box" style="display:none;"></div>
                    <div class="js-stools">
                        <div class="js-stools-container-bar">
			                <?php echo $this->loadTemplate('filters'); ?>
                        </div>
                        <div class="js-stools-container-filters clearfix bg-white collapse<?php echo $shouldExpand ? ' show' : ''; ?>" id="collapseFilters">
			                <?php echo $this->loadTemplate('advancedfilters'); ?>
                        </div>
                    </div>
                </div>
                <div class="j2store-order-list">
                    <?php echo $this->loadTemplate('items'); ?>
                </div>
            </form>
</div>
<script type="text/javascript">
    /**
     * Method to reset only advanced filters values
     */
    function resetAdvancedFilters() {
        document.querySelectorAll("#advanced-search-controls .j2store-order-filters").forEach(function (element) {
            const name = element.getAttribute('name');
            const id = element.getAttribute('id');
            // Skip elements with specific names or IDs
            if (name !== 'reset' && name !== 'go' && id !== 'hideBtnAdvancedControl' && id !== 'showBtnAdvancedControl' && name !== 'advanced_search' && name !== 'reset_advanced_filters') {
                element.value = ''; // Reset value
            }
        });

        // Set the value of the element with ID 'j2store_paykey' to an empty string
        const payKeyElement = document.getElementById('j2store_paykey');
        if (payKeyElement) {
            payKeyElement.value = '';
        }
        // Submit the form with ID 'adminForm'
        const adminForm = document.getElementById('adminForm');
        if (adminForm) {
            adminForm.submit();
        }
    }


    document.getElementById("reset-filter").addEventListener('click', function () {
        // Reset specific fields
        const orderState = document.getElementById('j2store_orderstate');
        if (orderState) {
            orderState.value = '';
        }
        const payKey = document.getElementById('j2store_paykey');
        if (payKey) {
            payKey.value = '';
        }
        // Reset other filter inputs except specified ones
        document.querySelectorAll(".j2store-order-filters input").forEach(function (element) {
            const name = element.getAttribute('name');
            const id = element.getAttribute('id');

            if (name !== 'reset' && name !== 'go' && id !== 'showBtnAdvancedControl' && name !== 'advanced_search' && name !== 'reset_advanced_filters') {
                element.value = '';
            }
        });
        // Submit the form
        if (this.form) {
            this.form.submit();
        }
    });

    document.getElementById("reset-filter-search").addEventListener('click', function () {
        // Reset search input
        const searchInput = document.getElementById("search");
        if (searchInput) {
            searchInput.value = '';
        }

        // Submit the form
        if (this.form) {
            this.form.submit();
        }
    });


    function submitOrderState(id, order_id) {
        // Get the selected order state
        const orderStateElement = document.getElementById('order_state_id_' + id);
        const order_state = orderStateElement ? orderStateElement.value : '';

        // Check if notify customer checkbox is checked
        const notifyCustomerCheckbox = document.getElementById('notify_customer_' + id);
        const notify_customer = notifyCustomerCheckbox && notifyCustomerCheckbox.checked ? 1 : 0;

        // Disable the save button and show a loading message
        const saveButton = document.getElementById('order-list-save_' + id);
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.value = '<?php echo Text::_('J2STORE_SAVING_CHANGES'); ?>...';
        }

        // Prepare data for the AJAX request
        const data = new URLSearchParams();
        data.append('id', id);
        data.append('order_id', order_id);
        data.append('return', 'orders');
        data.append('notify_customer', notify_customer);
        data.append('order_state_id', order_state);

        // Perform the AJAX request using fetch
        fetch('index.php?option=com_j2store&view=orders&task=saveOrderstatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data.toString()
        })
            .then(response => response.json())
            .then(json => {
                // Handle success
                if (json.success && json.success.link) {
                    window.location = json.success.link;
                }

                // Handle error
                if (json.error) {
                    const alertBox = document.querySelector('.j2store-alert-box');
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.innerHTML = `<p class="alert alert-warning">' + json.error.msg + '</p>`;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }
    function jSelectUser_jform_user_id(id, title) {
        var old_id = document.getElementById('jform_user_id').value;
        document.getElementById('jform_user_id').value = id;
        document.getElementById('jform_user_id_name').value = title;
        document.getElementById('jform_user_id').className = document.getElementById('jform_user_id').className.replace();

        SqueezeBox.close();
    }
</script>

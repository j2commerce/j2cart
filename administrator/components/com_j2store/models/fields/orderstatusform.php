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

class JFormFieldOrderstatusform extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Orderstatusform';

	public function getRepeatable()
	{

		$cont_saving = Text::_('J2STORE_SAVING_CHANGES').'...';
		$html ='';
		$html .='<div class="j2store-order-status-form">';
		$html .= Text::_('J2STORE_CHANGE_ORDER_STATUS');
		$html .='<script type="text/javascript">
				function submitOrderState(id){
					var orderStateElement = document.getElementById("order_state_id_" + id);
					var notifyCustomerCheckbox = document.getElementById("notify_customer_" + id);
					var saveButton = document.getElementById("order-list-save_" + id);
					var order_state = orderStateElement ? orderStateElement.value : "";
					var notify_customer = notifyCustomerCheckbox && notifyCustomerCheckbox.checked ? 1 : 0;

					var xhr = new XMLHttpRequest();
					xhr.open("POST", "index.php?option=com_j2store&view=orders&task=orderstatesave", true);
					xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

					xhr.onload = function () {
						if (xhr.status === 200) {
							try {
								var json = JSON.parse(xhr.responseText);
								if (json.success && json.success.link) {
									window.location = json.success.link;
								}
							} catch (e) {
								console.error("Invalid JSON response", e);
					}
								}
					};

					xhr.onerror = function () {
						console.error("An error occurred while processing the AJAX request.");
					};

					if (saveButton) {
						saveButton.disabled = true;
						saveButton.value = "' . $cont_saving . '";
							}

					var postData = "id=" + encodeURIComponent(id) +
						"&return=orders" +
						"&notify_customer=" + encodeURIComponent(notify_customer) +
						"&order_state_id=" + encodeURIComponent(order_state);

					xhr.send(postData);
						}
		</script>';
		$html .=J2Html::select()->clearState()
            ->type('genericlist')
            ->name('order_state_id')
            ->value($this->item->order_state_id)
            ->idTag("order_state_id_".$this->item->j2store_order_id)
            ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
            ->hasOne('Orderstatuses')
            ->setRelations(array ('fields' => array('key'=>'j2store_orderstatus_id','name'=>'orderstatus_name')))
            ->getHtml();

        $html .= '<div class="form-check form-switch me-2 me-lg-3 pt-0">';
        $html .= '<input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="notify_customer_'.$this->item->j2store_order_id.'" id="notify_customer_'.$this->item->j2store_order_id.'" value="1">';
        $html .= '<label class="form-check-label cursor-pointer small" for="notify_customer_'.$this->item->j2store_order_id.'">'.Text::_('J2STORE_NOTIFY_CUSTOMER').'</label>';
        $html .= '</div>';
		$html .='<input type="hidden" name="return" value="orders" />';
		$html .='<button class="btn btn-primary btn-sm" id="order-list-save_'.$this->item->j2store_order_id.'" type="button" onclick="submitOrderState('.$this->item->j2store_order_id.')" >';
        $html .= Text::_('J2STORE_ORDER_STATUS_SAVE');
        $html .= '</button>';
		$html .='</div>';
		return $html;
	}
}

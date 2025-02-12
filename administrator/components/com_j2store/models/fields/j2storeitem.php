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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');

class JFormFieldJ2storeitem extends FormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'J2storeitem';

	protected function getInput()
    {
        $platform = J2Store::platform();
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
		$products = J2Store::fof()->getModel('Products' ,'J2StoreModel')->enabled(1)->getList();
		$productarray = [];
		$value = [];
		$link = 'index.php?option=com_j2store&amp;view=products&amp;task=setProducts&amp;tmpl=component&amp;object='.$this->name;
		$selected_value = [];
		if(isset($this->value) && !empty($this->value)){
			$selected_value = (isset($this->value['ids']) && !empty($this->value['ids'])) ? $this->value['ids'] : array();
		}

		if(is_array($selected_value) && !empty($selected_value)){
			foreach($products as $product){
				$product = J2Product::getInstance()
					->setId($product->j2store_product_id)
					->getProduct();
				 if(in_array($product->j2store_product_id ,$selected_value)){
					$productarray[$product->j2store_product_id] =$product->product_name;
				 }
			}
		}

		$js = "
		function jSelectItem(id, title, object) {
                // Check if the item already exists
                var exists = document.getElementById('j2store-product-li-' + id);
			if(!exists){
                    var container = document.createElement('li');
                    container.id = 'j2store-product-li-' + id;
                    container.className = 'j2store-product-list-menu';
                    document.getElementById('j2store-product-item-list').appendChild(container);

                    var span = document.createElement('label');
                    span.className = 'label label-info';
                    span.textContent = title;
                    container.appendChild(span);

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'jform[request][j2store_item][ids][]';
                    input.value = id;
                    container.appendChild(input);

                    var remove = document.createElement('a');
                    remove.className = 'btn btn-link btn-sm';
                    remove.innerHTML = '<span class=\"icon icon-remove text-danger\"></span>';
                    remove.onclick = function () {
                        container.remove();
                    };
                    container.appendChild(remove);
				}else{
                    alert('".Text::_("J2STORE_PRODUCT_ALREADY_EXISTS")."');
				}

                // Close the SqueezeBox if it exists
                if (typeof window.parent.SqueezeBox !== 'undefined' && typeof window.parent.SqueezeBox.close === 'function') {
			window.parent.SqueezeBox.close();
                } else {
                    var sboxWindow = document.getElementById('sbox-window');
                    if (sboxWindow && typeof sboxWindow.close === 'function') {
                        sboxWindow.close();
		}
			}
		}
		function removeProductList(id){
                var element = document.getElementById('j2store-product-li-' + id);
                if (element) {
                    element.remove();
                }
		}
		";
		$css ='#j2store-product-item-list{
					list-style:none;
					margin:5px;
				}'
				;

        $platform->addInlineScript($js);
        $platform->addStyle($js);
		$html .= J2Store::platform()->loadExtra('behavior.modal','a.modal');
		$html .='<div id="'.$fieldId.'">';
		$html .='<label class="control-label"></label>';
		$html .= '<span class="input-append">';
		$html .='<input type="text" id="'.$this->name.'" name=""  disabled="disabled"/>';
		$html .= '<a class="modal btn btn-primary" title="'.Text::_('J2STORE_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-list"></i>  '.Text::_('J2STORE_SELECT_PRODUCT').'</a>';
		$html .= J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, Text::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400));
		$html .= '</span>';
		$html .='<ul id="j2store-product-item-list" >';
		foreach($productarray as $key => $value){
			$html .='<li class="j2store-product-list-menu" id="j2store-product-li-'.$key.'">';
			$html .='<label class="label label-info">';
			$html .=$value;
			$html .='<input type="hidden" value="'.$key.'" name="jform[request][j2store_item][ids][]">';
			$html .='</label>';
			$html .= '<a class="btn btn-danger btn-link" onclick="removeProductList('.$key.');">';
			$html .= '<span class="icon icon-remove text-danger"></span>';
			$html .='</a>';
			$html .='</li>';
		}
		$html .='</ul>';

		$html .='</div>';
		return $html ;
	}
}

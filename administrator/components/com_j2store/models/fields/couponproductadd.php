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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$document = Factory::getApplication()->getDocument();

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('j2store-fancybox-script', Uri::root() .'media/j2store/js/jquery.fancybox.min.js', [], [], ['jquery']);
$wa->registerAndUseStyle('j2store-fancybox-css', Uri::root() .'media/j2store/css/jquery.fancybox.min.css', [], [], []);

require_once JPATH_ADMINISTRATOR."/components/com_j2store/library/popup.php";

class JFormFieldCouponproductadd extends FormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Couponproductadd';

	protected function getInput(){
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$script = "function jSelectProduct(product_id ,product_name ,field_id){
				var form = jQuery(\"#module-form\");
				var html ='';
				if(form.find('#'+field_id+ '  #product-row-'+product_id).length == 0){
					html +='<tr id=\"product-row-'+product_id +'\"><td><input type=\"hidden\" name=\"".$this->name."[]\" value='+product_id+' />'+product_name +'</td><td><button class=\"btn btn-danger\" onclick=\"jQuery(this).closest(\'tr\').remove();\"><i class=\"icon icon-trash\"></button></td></tr>';
					form.find(\"#\"+field_id).append(html);
					alert('Product added');
				}else{
					alert('Product already exists');
				}
			}";
        $wa->addInlineScript($script, [], [], []);
		$popupurl = "index.php?option=com_j2store&view=products&task=setCouponProducts&layout=couponproducts&tmpl=component&function=jSelectProduct&field=".$fieldId;
		$html = J2StorePopup::popup($popupurl, Text::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success'));
		$html .= "<div class=\"table-responsive\">";
		$html .= "<table class=\"table itemList align-middle\" id=\"jform_product_list\">";
		$html .= "	<tbody>";
		if(!empty($this->value)){
			$html .= "<tr>
                            <th></th>
			            	<th scope='col' class='text-end'>
			            		<a class=\"btn btn-danger btn-sm text-capitalize\" href=\"javascript:void(0);\"
			            		     onclick=\"jQuery('.j2store-product-list-tr').remove();\">
			            		       ".Text::_('J2STORE_DELETE_ALL_PRODUCTS')."
			            		       <i class=\"icon icon-trash ms-2\"></i></a>
	                		</th>
				  	</tr>";
			$i =1;
			if(is_string ( $this->value )){
				$this->value = explode ( ',', $this->value );
			}

			foreach($this->value as  $pid){
				$product = F0FModel::getTmpInstance('Products','J2StoreModel')->getItem($pid);
				if($product->j2store_product_id){
					$html .= "<tr class=\"j2store-product-list-tr\" id=\"product-row-$pid\">
						<td><input type=\"hidden\" name=\"$this->name[]\" value='$pid' />$product->product_name</td>
						<td class='text-end'><a class=\"btn btn-danger btn-sm\" href=\"javascript:void(0);\" onclick=\"jQuery(this).closest('tr').remove();\"><i class=\"icon icon-trash\"></i></a></td>
						</tr>";
				}
				$i++;
			}

		}
		$html .= "	</tbody>";
		$html .= "</table>";
		$html .= "</div>";
		$html .= "<script>
					(function($) {
						$(\"#jform_product_list\").bind(\"DOMSubtreeModified\", function() {
    						$(\"#jform_product_list input\").each(function(i) {
  								$(this).attr('name', \"$this->name[]\");
							});
						});

					})(jQuery);

					</script>";
		return $html ;
	}
}

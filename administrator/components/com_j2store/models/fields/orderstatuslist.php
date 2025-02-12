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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';

class JFormFieldOrderstatusList extends ListField
{
	protected $type = 'OrderstatusList';

	public function getRepeatable()
	{
		$html ='';
		if($this->item->orderstatus_id != '*'){
			$orderstatus = J2Store::fof()->loadTable('Orderstatus','J2StoreTable');
			$orderstatus->load($this->item->orderstatus_id);
			$html ='<label class="label">'.Text::_($orderstatus->orderstatus_name);
			if(isset($orderstatus->orderstatus_cssclass) && $orderstatus->orderstatus_cssclass){
				$html ='<label class="label  '.$orderstatus->orderstatus_cssclass.'">'.Text::_($orderstatus->orderstatus_name);
			}

		}else{
			$html ='<label class="label label-success">'.Text::_('J2STORE_ALL');
		}
		$html .='</label>';
		return $html;
	}

	public function getInput()
    {
		$model = J2Store::fof()->getModel('Orderstatuses','J2StoreModel');
		$orderlist = $model->getItemList();
		$attr = [];
		// Get the field options.
				// Initialize some field attributes.
        if($this->class){
            $attr['class']= !empty($this->class) ? $this->class: '';
        }

        if($this->size){
            $attr ['size']= !empty($this->size) ?$this->size : '';
        }

		if($this->multiple){
            $attr ['multiple']= $this->multiple ? 'multiple': '';
        }
        if($this->required){
            $attr ['required']= $this->required ? true:false;
        }

        if($this->autofocus){
            $attr ['autofocus']= $this->autofocus ? 'autofocus' : '';
        }

		// Initialize JavaScript field attributes.
        if($this->onchange){
            $attr ['onchange']= $this->onchange ?  $this->onchange : '';
        }

		//generate order status list
		$orderstatus_options = [];
		$orderstatus_options['*'] =  Text::_('JALL');
		foreach($orderlist as $row) {
			$orderstatus_options[$row->j2store_orderstatus_id] =  Text::_($row->orderstatus_name);
		}

        $displayData = array(
            'class' => 'form-select',
                'name' => $this->name,
                'value' => $this->value  ,
                'options' =>$orderstatus_options ,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => $this->required,
                'id' => '',
                'multiple'=> $this->multiple
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
	}
}

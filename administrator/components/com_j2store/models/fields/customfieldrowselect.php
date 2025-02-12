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

/**
 * Class used for showing rowselect only if item is not core
 * @author weblogicx
 *
 */
class JFormFieldCustomFieldRowSelect extends F0FFormFieldSelectrow
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Customfieldrowselect';

	public function getRepeatable()
	{
		$html ='';
		if(isset($this->item->field_core)  && $this->item->field_core){
			$html ='<div style="display:none;">';
		}elseif(isset($this->item->orderstatus_core) && $this->item->orderstatus_core){
			$html ='<div style="display:none;">';
		}
		$html .=parent::getRepeatable();
		if(isset($this->item->field_core)  && $this->item->field_core){
			$html .='</div>';
		}elseif(isset($this->item->orderstatus_core) && $this->item->orderstatus_core){
			$html .='</div>';
		}
		return $html;
	}
}

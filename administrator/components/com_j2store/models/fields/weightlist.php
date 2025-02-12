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

class JFormFieldWeightList extends ListField
{
	protected $type = 'WeightList';

	public function getInput()
    {
		$model = J2Store::fof()->getModel('Weights','J2StoreModel');
		$list = $model->enabled(1)->getItemList();
		$attr = [];
		// Get the field options.
		// Initialize some field attributes.
        $attr['class'] = !empty($this->class) ? (strpos($this->class, 'form-select') === false ? $this->class . ' form-select' : $this->class) : 'form-select';
		// Initialize JavaScript field attributes.
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';
		//generate country filter list
		$options = [];
		foreach($list as $row) {
			$options[$row->j2store_weight_id] =  Text::_($row->weight_title);
		}
		return J2Html::select()->clearState()
						->type('genericlist')
						->name($this->name)
						->attribs($attr)
						->value($this->value)
						->setPlaceHolders($options)
						->getHtml();
	}
}

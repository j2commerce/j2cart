<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class JFormFieldGeozoneList extends ListField {

	protected $type = 'GeozoneList';

	public function getInput() {

		$model = F0FModel::getTmpInstance('Geozones','J2StoreModel');
		$geozonelist = $model->enabled(1)->getItemList();
		$attr = array();
		$attr['class']= !empty($this->class) ? $this->class.' form-select': 'form-select';
		// Initialize JavaScript field attributes.
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';
		//generate country filter list
		$geozone_options = array();
		$geozone_options[''] =  Text::_('JALL');
		foreach($geozonelist as $row) {
			$geozone_options[$row->j2store_geozone_id] =  Text::_($row->geozone_name);
		}
		return J2Html::select()->clearState()
						->type('genericlist')
						->name($this->name)
						->attribs($attr)
						->value($this->value)
						->setPlaceHolders($geozone_options)
						->getHtml();
	}
}

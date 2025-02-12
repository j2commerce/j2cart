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

class JFormFieldGeozoneList extends ListField
{
	protected $type = 'GeozoneList';

	public function getInput()
    {
		$model = J2Store::fof()->getModel('Geozones','J2StoreModel');
		$geozonelist = $model->enabled(1)->getItemList();
		$attr = array();
		$attr['class']= !empty($this->class) ? $this->class.' form-select': 'form-select';
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';
		//generate geozone list
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

<?php
/**
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author  Ramesh Elamathi (weblogicxindia.com)
 * @author  Adam Melcher adam@j2commerce.com
 * @author  Olivier Buisard olivier@j2commerce.com
 * @website https://www.j2commerce.com
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');


class JFormFieldTagTemplateList extends ListField {

	protected $type = 'TagTemplateList';

	public function getInput() {

		$fieldName = $this->name;
		if (version_compare(JVERSION, '3.99.99', 'lt')) {
			$db = JFactory::getDbo();
		} else {
			$db = Factory::getContainer()->get('DatabaseDriver');
		}

		// Query to get the default template
		$query = $db->getQuery(true)
			->select($db->quoteName('template'))
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('client_id') . ' = 0')
			->where($db->quoteName('home') . ' = 1');
		$db->setQuery($query);
		$defaultTemplate = $db->loadResult();

		if (is_dir(JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/templates')) {
			$templatePath = JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/templates';
		} else {
			$templatePath = JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/products';
		}

		$componentFolders = [];
        J2Store::plugin()->event('TemplateFolderList',array(&$componentFolders));
		if (is_dir($templatePath)) {
			$templateFolders = array_diff(scandir($templatePath), ['.', '..']);
			$folders = array_merge($templateFolders, $componentFolders);
			$folders = array_unique($folders);
		} else {
			$folders = $componentFolders;
		}

		$include_array = ['tag'];
		$options = [];

		foreach ($folders as $folder) {
			foreach ($include_array as $include){
				$substring = substr ( $folder,0,strlen ( $include )  );
				if(($substring != $include) || ($folder == 'tag_default')){
					continue 2;
				}
			}
			if($folder != 'tmpl') {
				$options[] = HTMLHelper::_('select.option', $folder, $folder);
			}
		}
		array_unshift($options, HTMLHelper::_('select.option', 'tag_default', Text::_('J2STORE_USE_DEFAULT')));
		return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="form-select"', 'value', 'text', $this->value, $this->control_name . $this->name);

	}

}

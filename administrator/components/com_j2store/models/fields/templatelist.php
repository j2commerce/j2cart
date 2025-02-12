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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldTemplateList extends ListField
{
	protected $type = 'TemplateList';

	public function getInput()
	{
		$fieldName = $this->name;
        $db = Factory::getContainer()->get('DatabaseDriver');

		// Query to get the default template
		$query = $db->getQuery(true)
			->select($db->quoteName('template'))
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('client_id') . ' = 0')
			->where($db->quoteName('home') . ' = 1');
		$db->setQuery($query);
		$defaultTemplate = $db->loadResult();

		// Define the template paths
		if (is_dir(JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/templates')) {
			$templatePath = JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/templates';
		} else {
			$templatePath = JPATH_SITE . '/templates/' . $defaultTemplate . '/html/com_j2store/products';
		}

		$componentFolders = [];
		J2Store::plugin()->event('TemplateFolderList', [&$componentFolders]);

		// Fetch folders if the template path exists
		if (is_dir($templatePath)) {
			$templateFolders = array_diff(scandir($templatePath), ['.', '..']);
			$folders = array_merge($templateFolders, $componentFolders);
			$folders = array_unique($folders);
		} else {
			$folders = $componentFolders;
		}

		// Exclude unwanted folders
		$excludeArray = ['tag', 'default'];
		$options = [];

		foreach ($folders as $folder) {
			foreach ($excludeArray as $exclude) {
				if (strpos($folder, $exclude) === 0) {
					continue 2;
				}
			}
			if ($folder !== 'tmpl') {
				$options[] = HTMLHelper::_('select.option', $folder, $folder);
			}
		}

		// Add default option to the list
		array_unshift($options, HTMLHelper::_('select.option', 'default', Text::_('J2STORE_USE_DEFAULT')));

		// Return the generated select list
		return HTMLHelper::_('select.genericlist', $options, $fieldName, 'class="form-select"', 'value', 'text', $this->value, $this->control_name . $this->name);
	}
}

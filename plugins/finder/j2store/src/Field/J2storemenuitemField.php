<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.j2store
 *
 * @copyright   Copyright (c) 2014-17 Ramesh Elamathi / J2Store.org
 * @copyright   (C) 2026 J2Commerce, LLC. <https://www.j2commerce.com>
 * @license     GNU General Public License version 3 or later
 */

namespace Joomla\Plugin\Finder\J2Store\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuFactoryInterface;

class J2StoremenuitemField extends ListField
{
	public $type = 'j2storemenuitem';

	protected function getOptions()
	{
	    $options = [];
	    $menus = Factory::getContainer()->get(MenuFactoryInterface::class)->createMenu('site');

	    foreach ($menus->getMenu() as $item) {
	        if ($item->type== 'component') {
	            if (isset($item->query['option']) && $item->query['option'] == 'com_j2store' ) {
	                if (isset($item->query['catid'])) {
	                    $options[] = HTMLHelper::_('select.option', $item->id, $item->title, 'value', 'text');
	                }
	            }
	        }
	    }

	    // Merge any additional options in the XML definition.
	    $options = array_merge(parent::getOptions(), $options);

	    return $options;
	}
}

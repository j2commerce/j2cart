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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuFactoryInterface;

class JFormFieldJ2StoreProductListMenu extends FormField
{
    protected $type = 'j2storeproductlistmenu';

    /**
     * Method to get the field input markup.
     *
     * @return  string	The field input markup.
     * @since   1.6
     */
    protected function getInput()
    {
        $plugin = $this->getPlugin();
        if(isset($plugin->enabled) && $plugin->enabled){
            $options = array();
            $menus = Factory::getContainer()->get(MenuFactoryInterface::class)->createMenu('site');
            $menu_id = null;
            $options[''] = Text::_('J2STORE_SELECT_OPTION');
            foreach($menus->getMenu() as $item)
            {
                if($item->type === 'component'){
                    if(isset($item->query['option']) && $item->query['option'] === 'com_j2store' && isset($item->query['view']) && $item->query['view'] === 'products' ){
                        $options[$item->id] = $item->title;
                    }
                }
            }
            return HTMLHelper::_('select.genericlist', $options, $this->name, array('class'=>'form-select'), 'value', 'text', $this->value);
        }
        return '';
    }

    public function getLabel()
    {
        $plugin = $this->getPlugin();
        if(isset($plugin->enabled) && $plugin->enabled){
           return Text::_('J2STORE_CANONICAL_MENU');
        }
    }

    public function getPlugin()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__extensions')
            ->where('element='.$db->q('j2canonical'))
            ->where('type='.$db->q('plugin'))
            ->where('folder='.$db->q('system'));
        $db->setQuery($query);
        return $db->loadObject();
    }
}

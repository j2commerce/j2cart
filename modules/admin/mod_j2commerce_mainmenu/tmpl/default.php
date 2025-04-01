<?php
/**
 * @package     Joomla.Module
 * @subpackage  J2Commerce.mod_j2commerce_adminmenu
 *
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Module\Menu\Administrator\Menu\CssMenu;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

$enabled = !$app->getInput()->getBool('hidemainmenu');

if (in_array($module->position, ['icon', 'cpanel', 'cpanel-j2commerce'])) {

    $buttons = [];

    foreach($params->get('buttons') as $button) {
        if ($button->button === 'dashboard') {
            $buttons[] = [
                'name' => 'COM_J2STORE_MAINMENU_DASHBOARD',
                'image' => 'fas fa-solid fa-tachometer-alt',
                'link' => Route::_('index.php?option=com_j2store&amp;view=cpanel'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
            ];
        }

        if ($button->button === 'products') {
            $buttons[] = [
                'name' => 'COM_J2STORE_TITLE_PRODUCTS',
                'image' => 'fas fa-solid fa-tags',
                'link' => Route::_('index.php?option=com_j2store&amp;view=products'),
                'linkadd' => Route::_('index.php?option=com_j2store&amp;task=product.create'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
                'ajaxurl' => Route::_('index.php?option=com_j2store&amp;task=products.getQuickiconContent&amp;format=json'),
            ];
        }

        if ($button->button === 'options') {
            $buttons[] = [
                'name' => 'COM_J2STORE_TITLE_OPTIONS',
                'image' => 'fas fa-solid fa-list-ol',
                'link' => Route::_('index.php?option=com_j2store&amp;view=options'),
                'linkadd' => Route::_('index.php?option=com_j2store&amp;task=options.add'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
            ];
        }

        if ($button->button === 'inventories') {
            $buttons[] = [
                'name' => 'COM_J2STORE_TITLE_INVENTORIES',
                'image' => 'fas fa-solid fa-database',
                'link' => Route::_('index.php?option=com_j2store&amp;view=inventories'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
            ];
        }

        if ($button->button === 'orders') {
            $buttons[] = [
                'name' => 'COM_J2STORE_TITLE_ORDERS',
                'image' => 'fas fa-solid fa-list-alt',
                'link' => Route::_('index.php?option=com_j2store&amp;view=orders'),
                'linkadd' => Route::_('index.php?option=com_j2store&amp;task=orders.createOrder'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
                /*'ajaxurl' => Route::_('index.php?option=com_j2store&amp;task=orders.getQuickiconContent&amp;format=json'),*/
            ];
        }

        if ($button->button === 'reports') {
            $buttons[] = [
                'name' => 'COM_J2STORE_TITLE_REPORTS',
                'image' => 'fas fa-solid fa-chart-bar',
                'link' => Route::_('index.php?option=com_j2store&amp;view=reports'),
                'access' => ['core.admin', 'com_j2store'],
                'group' => 'MOD_J2COMMERCE_ADMINMENU_GROUP',
            ];
        }

        // TODO add pending orders
    }

    require ModuleHelper::getLayoutPath('mod_quickicon');
}

if ($module->position === 'menu') {

    // Disable the menu item in Components so no two trees open

    $db = Factory::getDbo();

    // Query to unpublish the menu item
    $query = $db->getQuery(true)
        ->update($db->quoteName('#__menu'))
        ->set($db->quoteName('published') . ' = 0') // not enough to remove from 'main' menu
        ->set($db->quoteName('menutype') . ' = 0')
        ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_j2store'))
        ->where($db->quoteName('client_id') . ' = 1')
        ->where($db->quoteName('menutype') . ' = ' . $db->quote('main'));

    $db->setQuery($query);
    $db->execute();

    // TODO How to revert it?

    MenusHelper::addPreset('j2commerce', 'J2Commerce', __DIR__ . '/../presets/j2commerce.xml');

    // Basic menu structure needed to call plugins and get the new menu items
    $menus = [
        'catalog' => ['submenu' => []],
        'sales' => ['submenu' => []],
        'localisation' => ['submenu' => []],
        'design' => ['submenu' => []],
        'setup' => ['submenu' => []],
        'apps' => ['submenu' => []],
        'reporting' => ['submenu' => []]
    ];

    $before_menus = $menus;

    PluginHelper::importPlugin('j2store');
    $app->triggerEvent('onJ2StoreAddDashboardMenuInJ2Store', array(&$menus));

    // In case we have plugins who use the old way to add menu items, we force them to the 'apps' submenu
    foreach ($menus as $key => $menu) {
        if (is_integer($key)) {
            $menus['apps']['submenu'][] = $menu;
        }
    }

    // Find the new menu items
    $additions_array = [];
    foreach ($menus as $key => $menu) {
        if (isset($menu['submenu'])) {
            $additions_array[$key] = array_diff_key($menu['submenu'], $before_menus[$key]['submenu']);
        }
    }

    $params = new Registry(['preset' => 'j2commerce']);
    $menu = new CssMenu($app);
    $root = $menu->load($params, $enabled);
    $root->level = 0;

    $children = $root->getChildren(true);
    foreach ($children as $child) {
        foreach ($additions_array as $key => $addition_array) {
            if ($child->alias === $key && !empty($addition_array)) {
                foreach ($addition_array as $key => $new_addition) {
                    $new_child = new AdministratorMenuItem();

                    $new_child->title = $new_addition['name'];
                    $new_child->alias = '';
                    $new_child->type = 'component';
                    $new_child->link = $new_addition['link'];
                    $new_child->parent_id = $child->id;
                    $new_child->component_id = $child->component_id;
                    $new_child->component = $child->component;
                    $new_child->language = $child->language;
                    $new_child->element = 'com_j2store';
                    $new_child->access = $child->access;
                    $new_child->scope = 'default';
                    $new_child->ajaxbadge = '';
                    $new_child->dashboard = '';
                    $new_child->target = '';
                    $new_child->icon = '';
                    $new_child->setParams($child->getParams());

                    $child->addChild($new_child);
                }
            }
        }
    }

    // Render the module layout
    require ModuleHelper::getLayoutPath('mod_menu', 'default');
}

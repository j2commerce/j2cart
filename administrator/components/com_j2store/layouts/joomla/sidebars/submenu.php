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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!defined('F0F_INCLUDED')) {
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

$platform = J2Store::platform();
$app      = $platform->application();
$wa       = Factory::getApplication()->getDocument()->getWebAssetManager();
$waState  = $wa->getManagerState();

if ($wa->assetExists('style', 'fontawesome')) {
	if (isset($waState['activeAssets']['style']['j2store-font-awesome-css'])) {
		$wa->disableStyle('j2store-font-awesome-css');
	}
} else {
	$platform->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
}

$menus = [
    'dashboard' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_DASHBOARD' ),
        'icon' => 'fas fa-solid fa-tachometer-alt',
        'active' => 1
	],
    'catalog' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_CATALOG' ),
        'icon' => 'fas fa-solid fa-tags',
        'submenu' => [
            'products' => 'fas fa-solid fa-tags',
            'inventories' => 'fas fa-solid fa-database',
            'options' => 'fas fa-solid fa-list-ol',
            'vendors' => 'fas fa-solid fa-male',
            'manufacturers' => 'fas fa-solid fa-user',
            'filtergroups' => 'fas fa-solid fa-filter'
        ]
	],
    'sales' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_SALES' ),
        'icon' => 'fas fa-solid fa-money fa-money-bill',
        'submenu' => [
            'orders' => 'fas fa-solid fa-list-alt',
            'customers' => 'fas fa-solid fa-users',
            'coupons' => 'fas fa-solid fa-scissors fa-cut',
            'vouchers' => 'fas fa-solid fa-gift'
        ]
	],
    'localisation' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_LOCALISATION' ),
        'icon' => 'fas fa-globe',
        'submenu' => [
            'countries' => 'fas fa-solid fa-globe',
            'zones' => 'fas fa-solid fa-flag',
            'geozones' => 'fas fa-solid fa-pie-chart fa-chart-pie',
            'taxrates' => 'fas fa-solid fa-calculator',
            'taxprofiles' => 'fas fa-solid fa-sitemap',
            'lengths' => 'fas fa-solid fa-arrows-alt-v fa-up-down',
            'weights' => 'fas fa-solid fa-arrows-alt-h fa-left-right',
            'orderstatuses' => 'fas fa-solid fa-check-square'
        ]
	],
    'design' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_DESIGN' ),
        'icon' => 'fas fa-solid fa-paint-brush',
        'submenu' => [
            'emailtemplates' => 'fas fa-solid fa-envelope',
            'invoicetemplates' => 'fas fa-solid fa-print'
        ]
	],
    'setup' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_SETUP' ),
        'icon' => 'fas fa-solid fa-cogs',
        'submenu' => [
            'configuration' => 'fas fa-solid fa-cogs',
            'currencies' => 'fas fa-solid fa-dollar fa-dollar-sign',
            'payments' => 'fas fa-solid fa-credit-card',
            'shippings' => 'fas fa-solid fa-truck',
            'shippingtroubles' => 'fas fa-solid fa-bug',
            'customfields' => 'fas fa-solid fa-th-list',
        ]
	],
    'apps' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_APPS' ),
        'icon' => 'fas fa-solid fa-th',
        'submenu' => [
            'apps' => 'fas fa-th',
            /*'appstores' => 'fas fa-solid fa-shop',*/
        ],
        'active' => 0
	],
    'reporting' => [
        'name' => Text::_ ( 'COM_J2STORE_MAINMENU_REPORTING' ),
        'icon' => 'fas fa-solid fa-pie-chart fa-chart-pie',
        'submenu' => [
            'Reports' => 'fas fa-solid fa-chart-bar'
        ]
    ]
];

// Dynamically add menu items
$j2StorePlugin = J2Store::plugin();
$j2StorePlugin->event('AddDashboardMenuInJ2Store',array(&$menus));

// Get installed version
$db = Factory::getContainer()->get('DatabaseDriver');
$query = $db->getQuery(true);
$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element').' = '.$db->quote('com_j2store'));
$query->where('type ='.$db->q('component'));
$db->setQuery($query);
$row = json_decode($db->loadResult());

//check updates if logged in as administrator
$user = Factory::getApplication()->getIdentity();
$fof_helper = J2Store::fof();
$isroot = $user->authorise('core.admin');
$updateInfo = array();
if($isroot) {
//refresh the update sites first
    $fof_helper->getModel('Updates', 'J2StoreModel')->refreshUpdateSite();
    //now get update
    $updateInfo =  $fof_helper->getModel('Updates', 'J2StoreModel')->getUpdates();
}
$view = $app->input->getString('view','cpanels');

// Add the inline script and defer it
$wa->addInlineScript("window.addEventListener('resize', function () {
        if (window.innerWidth < 1400) {
            const menuIcon = document.querySelector('#menu-collapse-icon.toggle-off');
            if (menuIcon && menuIcon.style.display !== 'none') {
                menuIcon.click();
            }
        }
    });
    window.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 1400) {
            const menuIcon = document.querySelector('#menu-collapse-icon.toggle-off');
            if (menuIcon && menuIcon.style.display !== 'none') {
                menuIcon.click();
            }
        }
    });", [], []);
?>
<div id="j2store-navbar">
    <div class="d-none d-lg-flex align-items-center mb-3">
        <div class="j2store-social-share">
            <a class="btn btn-primary px-2" href="https://www.facebook.com/j2commerce" onclick="return ! window.open(this.href);">
                <i class="fa-brands fab fa-facebook-f fa-fw" aria-hidden="true"></i>
            </a>
            <a class="btn btn-primary px-2" href="https://github.com/j2commerce" onclick="return ! window.open(this.href);">
                <i class="fa-brands fab fa-github fa-fw" aria-hidden="true"></i>
            </a>
        </div>
        <h3 class="text-black mb-0 ms-2 fw-bolder">v <?php echo isset($row->version) ? $row->version : J2STORE_VERSION; ?>
            <?php if(J2Store::isPro() == 1): ?>
                <?php echo 'PRO'; ?>
            <?php else: ?>
                <?php echo 'CORE'; ?>
            <?php endif; ?>
        </h3>
    </div>
    <nav class="navbar navbar-expand-lg bg-primary" role="navigation" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo 'index.php?option=com_j2store&view=cpanels';?>">
                <?php echo HTMLHelper::_('image', 'com_j2commerce/dashboard-logo.png', 'j2Commerce logo', ['class' => 'img-fluid'], true); ?>
                <h5 class="mb-0 ms-2 fw-normal d-none d-sm-block d-lg-none text-white-50 small">v <?php echo isset($row->version) ? $row->version : J2STORE_VERSION; ?>
                    <?php if(J2Store::isPro() == 1): ?>
                        <?php echo 'PRO'; ?>
                    <?php else: ?>
                        <?php echo 'CORE'; ?>
                    <?php endif; ?>
                </h5>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarJ2StoreSubmenu" aria-controls="navbarJ2StoreSubmenu" aria-expanded="false" aria-label="Toggle Navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarJ2StoreSubmenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100 justify-content-xl-end">
                    <?php $view = $app->input->getString('view');
                    foreach($menus as $key => $value): ?>
                        <?php if(isset($value['submenu']) && count($value['submenu'])):?>
                            <li data-menu-key="<?php echo $key;?>" class="nav-item dropdown flex-lg-grow-1 flex-xl-grow-0 me-xl-2" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $value['name'];?>">
                                <a href="javascript:void(0);" class="nav-link dropdown-toggle submenu-dropdown-toggle text-lg-center" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-fw me-1 me-xxl-2 <?php echo isset($value['icon']) ? $value['icon'] : '';?>" aria-hidden="true"></i>
                                    <span class="submenu-title d-inline d-lg-none d-xl-inline fs-6"><?php echo $value['name'];?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark bg-primary">
                                    <?php foreach($value['submenu'] as $sub_key => $sub_value):?>
                                        <?php if(is_array ( $sub_value )): ?>
                                            <?php
                                                $class =  '';
                                                $appTask = $app->input->get('appTask','');
                                                if($view == 'apps' && $appTask == $sub_key){
                                                    $class =  'active';
                                                    $collapse = 'in';
                                                }
                                                $link_url = isset( $sub_value['link'] ) ? $sub_value['link']: 'index.php?option=com_j2store&view='.strtolower($sub_key);
                                                $sub_menu_span_class = isset( $sub_value['icon'] ) ? $sub_value['icon']:'';
                                            ?>
                                        <?php else: ?>
                                            <?php
                                                $class =  '';
                                                if($view == $sub_key){
                                                    $class =  'active';
                                                    $collapse = 'in';
                                                }
                                                $link_url = 'index.php?option=com_j2store&view='.strtolower($sub_key);
                                                $sub_menu_span_class = isset( $sub_value ) ? $sub_value:'';
                                            ?>
                                        <?php endif;?>
                                        <li data-menu-key="<?php echo $sub_key;?>">
                                            <a class="dropdown-item <?php echo $class ?>" href="<?php echo $link_url;?>">
                                                <i class="fa-fw me-1 me-xxl-2 <?php echo $sub_menu_span_class;?>" aria-hidden="true"></i>
                                                <span class="fs-6"><?php echo Text::_('COM_J2STORE_TITLE_'.strtoupper($sub_key));?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else : ?>
                            <?php
                            $active_class ='';
                            if(isset($value['active']) && $value['active'] && $view =='cpanels'){
                                $active_class ='active';
                            }
                            ?>
                            <li data-menu-key="<?php echo $key;?>" class="nav-item <?php echo $active_class;?> flex-lg-grow-1 flex-xl-grow-0 me-xl-2 position-relative">
                                <?php if (isset($value['link']) && $value['link'] != ''): ?>
                                    <a class="nav-link text-lg-center text-nowrap" aria-current="page" href="<?php echo $value['link'];?>">
                                <?php else : ?>
                                    <?php if ($key === 'dashboard') : ?>
                                        <a class="nav-link text-nowrap <?php echo $active_class;?> text-lg-center" aria-current="page" href="<?php echo 'index.php?option=com_j2store&view=cpanels';?>">
                                    <?php else : ?>
                                        <a class="nav-link text-nowrap <?php echo $active_class;?> text-lg-center" aria-current="page" href="javascript:void(0);">
                                    <?php endif; ?>
                                <?php endif; ?>
                                    <i class="fa-fw me-1 me-xxl-2 <?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i><span class="submenu-title d-inline d-lg-none d-xl-inline fs-6"><?php echo Text::_('COM_J2STORE_MAINMENU_'.strtoupper($value['name']));?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php echo J2Store::modules()->loadposition('j2store-navbar-position');?>
</div>

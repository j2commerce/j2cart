<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_j2store_menu
 *
 * @copyright Copyright (C) 2014-24 J2Store. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$waState = $wa->getManagerState();

if ($wa->assetExists('style', 'fontawesome')) {
    if (isset($waState['activeAssets']['style']['j2store-font-awesome-css'])) {
        $wa->disableStyle('j2store-font-awesome-css');
    }
} else {
    $platform->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
}

$platform->addInlineStyle('ul.nav.j2store-admin-menu > li ul { overflow: visible; }');
$platform->addStyle('j2store-menu-module','/administrator/modules/mod_j2store_menu/css/j2store_module_menu.css');

HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

$menus = [
    'dashboard' => [
        'name' => Text::_('COM_J2STORE_MAINMENU_DASHBOARD'),
        'icon' => 'fas fa-solid fa-tachometer-alt',
        'active' => 1
    ],
    'catalog' => [
        'name' => Text::_('COM_J2STORE_MAINMENU_CATALOG'),
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
        'name' => Text::_('COM_J2STORE_MAINMENU_SALES'),
        'icon' => 'fas fa-solid fa-money fa-money-bill',
        'submenu' => [
            'orders' => 'fas fa-solid fa-list-alt',
            'customers' => 'fas fa-solid fa-users',
            'coupons' => 'fas fa-solid fa-scissors fa-cut',
            'vouchers' => 'fas fa-solid fa-gift'
        ]
    ],
    'localisation' => [
        'name' => Text::_('COM_J2STORE_MAINMENU_LOCALISATION'),
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
        'name' => Text::_('COM_J2STORE_MAINMENU_DESIGN'),
        'icon' => 'fas fa-solid fa-paint-brush',
        'submenu' => [
            'emailtemplates' => 'fas fa-solid fa-envelope',
            'invoicetemplates' => 'fas fa-solid fa-print'
        ]
    ],
    'setup' => [
        'name' => Text::_('COM_J2STORE_MAINMENU_SETUP'),
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
        'name' => Text::_('COM_J2STORE_MAINMENU_APPS'),
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
$j2StorePlugin->event('AddDashboardMenuInJ2Store', array(&$menus));
?>
<div class="header-item-content dropdown header-profile">
    <button class="dropdown-toggle d-flex align-items-center ps-0 py-0" data-bs-toggle="dropdown" type="button"
            title="<?php echo Text::_('COM_J2STORE'); ?>">
        <div class="header-item-icon">
            <span class="fa fa-cart-shopping fa-shopping-cart" aria-hidden="true"></span>
        </div>
        <div class="header-item-text">
            <?php echo Text::_('COM_J2STORE'); ?>
        </div>
        <span class="icon-angle-down" aria-hidden="true"></span>
    </button>
    <div id="j2menu" class="dropdown-menu dropdown-menu-end">
        <?php foreach($menus as $key => $value) : ?>
            <?php if(isset($value['submenu']) && count($value['submenu'])):?>
                <a class="dropdown-item j2submenu align-items-center" aria-haspopup="true" href="#">
                    <i class="fa-fw me-1 me-xxl-2 <?php echo isset($value['icon']) ? $value['icon'] : '';?>" aria-hidden="true"></i>
                    <span><?php echo $value['name'];?></span>
                    <i class="fas fa-chevron-right ms-auto"></i>
                </a>
                <div class="j2submenu-list dropdown-menu dropdown-menu-end">
                    <?php foreach($value['submenu'] as $sub_key => $sub_value) : ?>
                        <?php if (is_array ( $sub_value )) : ?>
                            <?php
                            $link_url = isset($sub_value['link']) ? $sub_value['link'] : 'index.php?option=com_j2store&view=' . strtolower($sub_key);
                            $sub_menu_icon_class = isset( $sub_value['icon'] ) ? $sub_value['icon'] : '';
                            ?>
                        <?php else: ?>
                            <?php
                            $link_url = 'index.php?option=com_j2store&view=' . strtolower($sub_key);
                            $sub_menu_icon_class = $sub_value ?? '';
                            ?>
                        <?php endif;?>
                        <?php
                            $title = 'COM_J2STORE_TITLE_' . strtoupper($sub_key);
                            $translatedTitle = Text::_($title);
                            if ($translatedTitle === $title) {
                                $translatedTitle = Text::_('COM_J2STORE_MAINMENU_' . strtoupper($sub_key));
                            }
                        ?>
                        <a class="dropdown-item align-items-center" href="<?php echo Route::_($link_url);?>">
                            <i class="fa-fw me-1 me-xxl-2 <?php echo $sub_menu_icon_class;?>" aria-hidden="true"></i>
                            <span><?php echo $translatedTitle; ?></span>
                        </a>
                    <?php endforeach;?>
                </div>
            <?php else : ?>
                <?php
                $url = 'javascript:void(0);';
                if ($key === 'dashboard') {
                    $url = Route::_('index.php?option=com_j2store&view=cpanels');
                }
                ?>
                <a class="dropdown-item" href="<?php echo $url; ?>">
                    <span class="fa-fw me-1 me-xxl-2 <?php echo isset($value['icon']) ? $value['icon'] : '';?>" aria-hidden="true"></span>
                    <?php echo $value['name']; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<script>
    var dropdowns = document.querySelectorAll('.j2submenu')
    var width = screen.width;
    dropdowns.forEach((dd)=>{
        dd.addEventListener('mouseover', function (e) {
            var rect = document.getElementById("j2menu").getBoundingClientRect();
            var el = this.nextElementSibling
            if(rect.x > 1000){
                el.style.class = el.classList.add("j2right");
            }else{
                el.style.class = el.classList.add("j2left");
            }
            el.style.class = el.classList.add("show");
        });
        dd.addEventListener('touchstart', function (e) {
            var rect = document.getElementById("j2menu").getBoundingClientRect();
            var el = this.nextElementSibling
            if(rect.x > 1000){
                el.style.class = el.classList.add("j2right");
            }else{
                el.style.class = el.classList.add("j2left");
            }
            el.style.class = el.classList.add("show");
        });
        dd.addEventListener('mouseout', function (e) {
            var el = this.nextElementSibling
            el.style.class = el.classList.remove("show");
        });
        dd.addEventListener('touchend', function (e) {
            var el = this.nextElementSibling
            el.style.class = el.classList.remove("show");
        });
    });
</script>

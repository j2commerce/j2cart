<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (c) 2024 J2Commerce . All rights reserved.
 * @license GNU GPL v3 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


// load tooltip behavior
$platform = J2Store::platform();
$sidebar = JHtmlSidebar::render();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');

$sortFields = array(
    'id' => Text::_('JGRID_HEADING_ID'),
    'name' => Text::_('COM_ATS_TICKETS_HEADING_TITLE'),
    'state' => Text::_('JSTATUS'),
);

$total = count($this->items);
$counter = 0;
$col = 3;
$row_class = 'row';
$col_class = 'col-lg-';

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);
$session = Factory::getApplication()->getSession();
?>
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '$order') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn);
    }
</script>

<?php if (!empty($sidebar)): ?>
	<?php echo $sidebar; ?>
<?php endif;?>

<form action="<?php echo Route::_('index.php?option=com_j2store&view=apps'); ?>" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="task" value="browse"/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists->order; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists->order_Dir; ?>"/>
    <input type="hidden" id="token" name="<?php echo $session->getFormToken(); ?>" value="1"/>
    <div id="j-main-container">
        <div class="j2store apps">
            <div class="js-stools" role="search">
                <div class="js-stools-container-bar">
                    <div class="btn-toolbar gap-2 align-items-center">
                        <h2><?php echo Text::_('COM_J2STORE_TITLE_APPS') ?></h2>
                        <div class="filter-search-bar btn-group app-search ms-auto">
                            <div class="input-group">
                                <input type="text" name="search" id="search" value="<?php echo $this->escape($this->getModel()->getState('search', '')); ?>" class="form-control" onchange="document.adminForm.submit();" placeholder="<?php echo Text::_('J2STORE_APP_NAME'); ?>"/>

                                <button type="submit" class="filter-search-bar__button btn btn-primary" aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
                                    <span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="filter-search-actions btn-group">
                            <button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-clear" onclick="document.id('search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info"> <?php echo Text::_('COM_J2STORE_EXTENSIONS_ALERT') ?></div>
		    <?php $i = -1 ?>

                <table class="table itemList">
                    <thead>
                    <tr>
                        <th scope="col" class="w-10 text-center d-none d-xxl-table-cell"><?php echo Text::_('J2STORE_APP_ID');?></th>
                        <th scope="col" class="w-10 text-center"><?php echo Text::_('JSTATUS');?></th>
                        <th scope="col" class="w-50"><?php echo Text::_('J2STORE_APP');?></th>
                        <th scope="col" class="w-10 d-none d-lg-table-cell"><?php echo Text::_('J2STORE_APP_VERSION');?></th>

                        <th scope="col" class="w-30 d-none d-xxl-table-cell"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $app):
                        $i++;
                        $app->published = $app->enabled;
                        Factory::getApplication()->getLanguage()->load('plg_j2store_' . $app->element, JPATH_ADMINISTRATOR);
                        $params = $platform->getRegistry($app->manifest_cache);
	                    $desc = Text::_($params->get('description'));

	                    $imageExtensions = ['jpg', 'png', 'webp'];
	                    $imagePath = '';

	                    foreach ($imageExtensions as $extension) {
		                    $path = JPATH_SITE . '/media/plg_j2store_'.$app->element.'/images/' . $app->element . '.' . $extension;
		                    if (file_exists($path)) {
			                    $imagePath = Uri::root(true) . '/media/plg_j2store_' . $app->element . '/images/' . $app->element . '.' . $extension;
			                    break;
		                    }
	                    }
                    ?>
                        <tr class="row<?php echo $i;?>">
                            <td class="text-center align-middle d-none d-xxl-table-cell">
                                <?php echo $app->extension_id;?>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($app->enabled): ?>
                                    <a class="js-grid-item-action tbody-icon app-button-unpublish" href="<?php echo 'index.php?option=com_j2store&view=apps&task=unpublish&id=' . $app->extension_id . '&' . $session->getFormToken() . '=1'; ?>" title="<?php echo Text::_('J2STORE_DISABLE'); ?>">
                                        <span class="icon-publish" aria-hidden="true"></span>
                                    </a>
                                <?php else: ?>
                                    <a class="js-grid-item-action tbody-icon app-button-publish" href="<?php echo 'index.php?option=com_j2store&view=apps&task=publish&id=' . $app->extension_id . '&' . $session->getFormToken() . '=1'; ?>" title="<?php echo Text::_('J2STORE_ENABLE'); ?>">
                                        <span class="icon-unpublish" aria-hidden="true"></span>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-block d-lg-flex">
                                    <div class="flex-shrink-0">
                                        <?php if ($app->enabled): ?>
                                            <a href="<?php echo 'index.php?option=com_j2store&view=apps&task=view&layout=view&id=' . $app->extension_id ?>" class="d-none d-lg-inline-block d-md-block">
                                        <?php else: ?>
                                            <span class="d-none d-lg-inline-block d-md-block">
                                        <?php endif;?>
                                        <?php if($imagePath):?>
                                            <img src="<?php echo $imagePath; ?>" class="img-fluid j2commerce-app-image" alt="<?php echo Text::_($app->name); ?>"/>
                                        <?php elseif (file_exists(JPATH_SITE . '/plugins/j2store/' . $app->element . '/images/' . $app->element . '.png')): ?>
                                            <img src="<?php echo Uri::root(true) . '/plugins/j2store/' . $app->element . '/images/' . $app->element . '.png'; ?>" class="img-fluid j2commerce-app-image" alt="<?php echo Text::_($app->name); ?>"/>
                                        <?php elseif (file_exists(JPATH_SITE . '/media/j2store/images/' . $app->element . '.png')): ?>
                                            <img src="<?php echo Uri::root(true) . '/media/j2store/images/' . $app->element . '.png'; ?>" class="img-fluid j2commerce-app-image" alt="<?php echo Text::_($app->name); ?>"/>

                                        <?php else: ?>
                                            <img src="<?php echo Uri::root(true) . '/media/j2store/images/app_placeholder.png'; ?>" class="img-fluid j2commerce-app-image" alt="<?php echo Text::_($app->name); ?>"/>
                                        <?php endif; ?>
                                        <?php if ($app->enabled): ?>
                                            </a>
	                                    <?php else: ?>
                                            </span>
                                        <?php endif;?>
                                    </div>
                                    <div class="flex-grow-1 ms-lg-3 mt-2 mt-lg-0">
                                        <div>
                                            <?php if ($app->enabled): ?>
                                            <a href="<?php echo 'index.php?option=com_j2store&view=apps&task=view&layout=view&id=' . $app->extension_id ?>"><?php echo Text::_($app->name); ?></a>
                                            <?php else: ?>
                                                <span class="text-dark"><?php echo Text::_($app->name); ?></span>
                                            <?php endif;?>

                                        </div>
                                        <div class="small d-none d-md-block"><?php echo Text::_(HTMLHelper::_('string.truncate', $desc, 120, true, true));?></div>
                                        <div class="small d-block d-lg-none"><b><?php echo Text::_('J2STORE_APP_VERSION');?>:</b> <?php echo $params->get('version'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle d-none d-lg-table-cell">
                                <small><b><?php echo $params->get('version'); ?></b></small>
                            </td>
                            <td class="align-middle text-center d-none d-xxl-table-cell">
                                <?php if ($app->enabled): ?>
                                    <a class="btn btn-sm btn-primary app-button-open text-decoration-none" href="<?php echo 'index.php?option=com_j2store&view=apps&task=view&layout=view&id=' . $app->extension_id ?>" title="<?php echo Text::_('J2STORE_APP_OPEN'); ?>">
                                        <?php echo Text::_('J2STORE_APP_OPEN'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
            <nav class="pagination__wrapper" aria-label="Pagination">
                <?php echo $this->pagination->getListFooter();?>
            </nav>
            <div class="text-center mt-2 mb-4 px-4">
                <span class="fa-4x mb-2 fa-solid fas fa-cart-plus"></span>
                <h2 class="fs-1 fw-bold"><?php echo Text::_('J2STORE_APP_STORE_TITLE');?></h2>
                <p class="fs-3 text-muted"><?php echo Text::_('J2STORE_APP_STORE_DESC');?></p>
                <a target="_blank" class="btn btn-primary app-button-open" href="https://www.j2commerce.com/extensions"><?php echo Text::_('J2STORE_GET_MORE_APPS'); ?></a>
            </div>
        </div>
    </div>
</form>

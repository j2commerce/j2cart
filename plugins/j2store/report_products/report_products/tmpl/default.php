<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.report_products
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

HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'top']);
HTMLHelper::_('bootstrap.collapse', '[data-bs-toggle="collapse"]', '');

$check_order_id = array();

$platform = J2Store::platform();
$platform->addIncludePath(JPATH_COMPONENT . '/helpers/html');
$platform->loadExtra('behavior.multiselect');

$row_class = 'row';
$col_class = 'col-md-';

unset ($listOrder);
$listOrder = $vars->state->get('filter_order', 'orderitem.j2store_orderitem_id');
$listDirn = $vars->state->get('filter_order_Dir');
$order_status = $vars->state->get('filter_orderstatus');
$currency = J2Store::currency();
$currency_symbol = J2Store::currency()->getSymbol();

$wa  = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');
$wa->usePreset('choicesjs');
$wa->useScript('webcomponent.field-fancy-select');

$script = "Joomla.submitbutton = function(pressbutton) {
            if(pressbutton === 'cancel') {
                document.querySelectorAll('.csvdiv').forEach(function(element) {
                    element.innerHTML = '';
                });
            }
            Joomla.submitform(pressbutton);
            return true;
        }";
$wa->addInlineScript($script, [], []);
$form = $vars->form;

$style = 'joomla-field-fancy-select .choices{min-width: 220px;}.chart-body{max-height:500px!important;}.chart-body #barChart{height:400px!important;width:100%!important;};';
$wa->addInlineStyle($style, [], []);

$wa->registerAndUseScript('com_j2commerce.chart', 'com_j2commerce/chart.js', [], ['defer' => true]);
?>
    <div class="j2store">
        <form class="form-horizontal" method="post"
              action="<?php echo $form['action']; ?>" name="adminForm" id="adminForm">
            <div class="js-stools">
                <div class="js-stools-container-bar">
                    <div class="btn-toolbar w-100 justify-content-end mb-3">
                        <div id="toolbar-icon icon-download" class="me-auto">
                            <button class="btn btn-success" onclick="jQuery('.csvdiv').html('<input type=\'hidden\' name=\'format\' value=\'csv\'>');this.form.submit();"><span class="icon-icon icon-download me-2"></span><?php echo Text::_('JTOOLBAR_EXPORT');?>
				</button>
                </div>
                        <div class="filter-search-bar btn-group flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
                            <div class="input-group w-100">
                                <input type="text" name="filter_search" id="search" value="<?php echo htmlspecialchars($vars->state->get('filter_search')); ?>" class="text_area form-control j2store-product-filters" onchange="document.adminForm.submit();" placeholder="<?php echo Text::_( 'J2STORE_FILTER_SEARCH' ); ?>"/>
                                <button type="button" class="btn btn-primary" onclick="jQuery('.csvdiv').html('');this.form.submit();"><span class="filter-search-bar__button-icon icon-search" aria-hidden="true" title="<?php echo Text::_( 'J2STORE_FILTER_GO' ); ?>"></span></button>
                                <button class="btn btn-primary" onclick="document.getElementById('search').value='';jQuery('.csvdiv').html('');this.form.submit();"><?php echo Text::_( 'J2STORE_FILTER_RESET' ); ?></button>
                            </div>
                </div>
				<?php if ($vars->state->get('filter_datetype') == 'custom'): ?>
                            <div class="filter-search-actions btn-group ms-lg-2 flex-grow-1 flex-lg-grow-0 mb-2 mb-lg-0">
                                <button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-filter w-100" data-bs-toggle="collapse" data-bs-target="#advanced-search-controls" aria-expanded="false" aria-controls="advanced-search-controls">
                                    <?php echo Text::_('JFILTER_OPTIONS');?><span class="icon-angle-down ms-1" aria-hidden="true"></span>
				</button>
                            </div>
                <?php endif; ?>

                            <div class="ordering-select d-flex gap-2 ms-lg-2 flex-grow-1 flex-lg-grow-0">
                                <?php
                                $attribs = [
                                    'list.select' => $vars->state->get('filter_orderstatus'),
                                    'name' => 'filter_orderstatus[]',
                                    'multiple' => 'multiple',
                                    'id' => 'filter_orderstatus'
                                ];
                                ?>
                                <joomla-field-fancy-select search-placeholder="<?= Text::_('J2STORE_FILTER_ORDERSTATUS_SEARCH_PLACEHOLDER') ?>">
                                    <?=
                                    HTMLHelper::_('select.genericlist', $vars->orderStatus, 'filter_orderstatus[]', $attribs, 'value', 'text', $vars->state->get('filter_orderstatus'))
                                    ?>
                                </joomla-field-fancy-select>
                                <?php
                                $attribs2 = [
                                    'name' => 'filter_datetype',
                                    'id' => 'filter_datetype',
                                    'onchange' => "jQuery('.csvdiv').html('');this.form.submit();"
                                ];
                                ?>
                                <joomla-field-fancy-select search-placeholder="<?= Text::_('J2STORE_FILTER_DURATION_SEARCH_PLACEHOLDER') ?>">
                                    <?=
                                    HTMLHelper::_('select.genericlist', $vars->orderDateType, 'filter_datetype', $attribs2 , 'value', 'text', $vars->state->get('filter_datetype'))
                                    ?>
                                </joomla-field-fancy-select>
                                <?php echo $vars->pagination->getLimitBox(); ?>
                            </div>

                    </div>
                </div>
            </div>
            <div class="px-2 pt-2 pb-0 mb-5">
                <div class="<?php echo $row_class;?> collapse"  id="advanced-search-controls">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <?php echo HTMLHelper::calendar($vars->state->get('filter_order_from_date'), 'filter_order_from_date', 'filter_order_from_date', '%Y-%m-%d', array('class' => 'form-control','placeholder' => Text::_('J2STORE_ORDERS_EXPORT_FROM_DATE'))); ?>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <?php echo HTMLHelper::calendar($vars->state->get('filter_order_to_date'), 'filter_order_to_date', 'filter_order_to_date', '%Y-%m-%d', array('class' => 'form-control','placeholder' => Text::_('J2STORE_ORDERS_EXPORT_TO_DATE'))); ?>
                </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <button class="btn btn-primary" onclick="document.getElementById('filter_order_from_date').value='',document.getElementById('filter_order_to_date').value='';this.form.submit();">
                            <i class="icon icon-remove me-2"></i>
                            <?php echo Text::_( 'J2STORE_FILTER_RESET' ); ?>
                    </button>
                </div>
            </div>
            </div>
            <?php if (count($vars->products)): ?>
                <div class="card mb-4">
                    <div class="card-body chart-body">
                        <canvas id="barChart"></canvas>
                </div>

            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table itemList align-middle">
                    <thead>
                    <tr>
                            <th scope="col">
                                <?php echo HTMLHelper::_('grid.sort', 'PLG_J2STORE_PRODUCT_NAME', 'orderitem.orderitem_name', $vars->state->get('filter_order_Dir'), $vars->state->get('filter_order')); ?>
                        </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('grid.sort', 'J2STORE_REPORT_TOTAL_QUANTITY', 'orderitem.orderitem_quantity', $vars->state->get('filter_order_Dir'), $vars->state->get('filter_order'));?>
                        </th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_REPORT_PRODUCT_DISCOUNT'); ?>
                        </th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_REPORT_PRODUCT_TAX'); ?>
                        </th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_REPORT_PRODUCT_WITHOUT_TAX'); ?>
                        </th>
                            <th scope="col">
                                <?php echo Text::_('J2STORE_REPORT_PRODUCT_WITH_TAX'); ?>
                        </th>
                    </tr>
                    </thead>
                    <?php if (count($vars->products)): ?>
                        <?php
                        $qty_total = 0;
                        $discount_total = 0;
                        $total_without_tax = 0;
                        $total_with_tax = 0;
                        $total_tax = 0;
                        ?>
                        <?php foreach ($vars->products as $product): ?>
                            <?php
                            $qty_total += $product->total_qty;
                            $discount_total += $product->total_item_discount + $product->total_item_discount_tax;
                            $total_without_tax += $product->total_final_price_without_tax;
                            $total_with_tax += $product->total_final_price_with_tax;
                            $total_tax += $product->total_item_tax;
                            ?>
                            <tbody>
                            <tr>
                                <td class="small">
                                    <strong class="d-block"><?php echo $product->orderitem_name; ?></strong>
                                    <strong><?php echo Text::_('J2STORE_SKU'); ?>:</strong> <?php echo $product->orderitem_sku; ?></td>
                                <td class="small"><?php echo $product->total_qty; ?></td>
                                <td class="small"><?php echo $currency->format($product->total_item_discount + $product->total_item_discount_tax); ?></td>
                                <td class="small"><?php echo $currency->format($product->total_item_tax); ?></td>
                                <td class="small"><?php echo $currency->format($product->total_final_price_without_tax); ?></td>
                                <td class="small"><?php echo $currency->format($product->total_final_price_with_tax); ?></td>
                            </tr>
                            </tbody>
                        <?php endforeach; ?>
                        <tr>
                            <td class="py-4 border-top"><strong><?php echo Text::_('J2STORE_TOTAL'); ?></strong></td>
                            <td class="py-4 border-top"><strong><?php echo $qty_total; ?></strong></td>
                            <td class="py-4 border-top"><strong><?php echo $currency->format($discount_total); ?></strong></td>
                            <td class="py-4 border-top"><strong><?php echo $currency->format($total_tax); ?></strong></td>
                            <td class="py-4 border-top"><strong><?php echo $currency->format($total_without_tax); ?></strong></td>
                            <td class="py-4 border-top"><strong><?php echo $currency->format($total_with_tax); ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tbody>
                        <tr>
                            <td colspan="6"><?php echo Text::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
                        </tr>
                        </tbody>
                    <?php endif; ?>
                </table>
                <?php echo $vars->pagination->getListFooter(); ?>
            </div>
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
            <input type="hidden" name="reportTask" value=""/>
            <input type="hidden" name="task" value="view"/>
            <input type="hidden" name="report_id" value=" <?php echo $vars->id; ?>"/>
            <input type="hidden" name="boxchecked" value=""/>
            <input type="hidden" name="order_change" value="0"/>
            <div class="csvdiv">

            </div>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
<?php if (!empty($vars->product_amount)): ?>
    <script>
        const ctx = document.getElementById('barChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($vars->product_name);?>,
                datasets: [{
                    label: '<?php echo Text::_("J2STORE_REPORT_PRODUCT_CHART_PRODUCTS_REVENUE");?>',
                    data: <?php echo json_encode($vars->product_amount);?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1,
                    yAxisID: 'y-left'
                },
                {
                    label: '<?php echo Text::_("J2STORE_REPORT_PRODUCT_CHART_PRODUCTS_SOLD");?>',
                    data: <?php echo json_encode($vars->product_qty); ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y-right'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    'y-left': {
                        beginAtZero: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return '<?php echo $currency_symbol; ?>' + value.toLocaleString();
                            }
                        }
                    },
                    'y-right': {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const value = context.raw || 0;
                                if (context.datasetIndex === 0) {
                                    return `<?php echo $currency_symbol; ?>${value.toLocaleString()}`; // Dollar amounts
                                } else {
                                    return value.toLocaleString() + ' <?php echo Text::_("J2STORE_REPORT_PRODUCT_CHART_PRODUCTS_SOLD");?>'; // Quantity amounts
                                }
                            }
                        }
                    },
                    subtitle: {
                        display: true,
                        text: '<?php echo Text::_("J2STORE_J2C_REPORT_PRODUCTS");?>',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    </script>
<?php endif; ?>

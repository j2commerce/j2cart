<?php
/**
 * @package     Joomla.Module
 * @subpackage  J2Commerce.mod_j2store_chart
 *
 * @copyright Copyright (C) 2017 J2Store. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// No direct access to this file
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_j2commerce.chart', 'com_j2commerce/chart.js', [], ['defer' => true]);

$currency = J2Store::currency();
$currency_symbol = J2Store::currency()->getSymbol();

$show_daily = false;
$show_monthly = false;
$show_yearly = false;

if (in_array('daily', $chart_type, true))
    $show_daily = true;
if (in_array('monthly', $chart_type, true))
    $show_monthly = true;
if (in_array('yearly', $chart_type, true))
    $show_yearly = true;


$style = '#dayChart{min-height: 220px;};';
$wa->addInlineStyle($style, [], []);
?>

<div class="j2commerce_chart">
    <div class="row">
        <?php if($show_daily && (isset($days))):
            $ddayValues = array_column($days, 'dday');
            $chartDays = '[' . implode(',', $ddayValues) . ']';

            $totalValues = array_column($days, 'total');
            $chartTotal = '[' . implode(',', $totalValues) . ']';

            $totalQty = array_column($days, 'total_num_orders');
            $chartQty = '[' . implode(',', $totalQty) . ']';
            ?>
            <div class="col-12 mb-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h3 mb-0"><i class="fas fa-solid fa-calendar-day me-2"></i><?php echo Text::_('MOD_J2STORE_CHART_DAILY_SALES_REPORT');?></h2>
                    </div>
                    <div class="card-body chart-body pt-0">
                        <canvas id="dayChart"></canvas>
                    </div>
                </div>
                <script>
                    const ctx = document.getElementById('dayChart').getContext('2d');
                    const myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo $chartDays;?>,
                            datasets: [{
                                label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_REVENUE");?>',
                                data: <?php echo $chartTotal;?>,
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(75, 192, 192, 1)'

                                ],
                                borderWidth: 1,
                                yAxisID: 'y-left'
                            },
                                {
                                    label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_COUNT");?>',
                                    data: <?php echo $chartQty; ?>,
                                    backgroundColor: [
                                        'rgba(54, 162, 235, 0.2)'
                                    ],
                                    borderColor: [
                                        'rgba(54, 162, 235, 1)'
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
                                }
                            }
                        }
                    });
                </script>
            </div>
        <?php endif;?>
        <?php if($show_monthly && (isset($months))):

            $monthsValues = array_column($months, 'dmonth');
            $chartMonthsJson = json_encode($monthsValues);


            $totalMonthsValues = array_column($months, 'total');
            $chartMonthsTotal = json_encode($totalMonthsValues);

            $totalMonthsQty = array_column($months, 'total_num_orders');
            $chartMonthsQty = json_encode($totalMonthsQty);
            ?>
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h3 mb-0"><i class="fas fa-solid fa-calendar-week me-2"></i><?php echo Text::_('MOD_J2STORE_CHART_MONTHLY_SALES_REPORT');?></h2>
                    </div>
                    <div class="card-body chart-body pt-0">
                        <canvas id="monthChart"></canvas>
                    </div>
                </div>
                <script>
                    const mtx = document.getElementById('monthChart').getContext('2d');
                    const monthChart = new Chart(mtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo $chartMonthsJson;?>,
                            datasets: [{
                                label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_REVENUE");?>',
                                data: <?php echo $chartMonthsTotal;?>,
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
                                yAxisID: 'y-left'
                            },
                                {
                                    label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_COUNT");?>',
                                    data: <?php echo $chartMonthsQty; ?>,
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
                                }
                            }
                        }
                    });
                </script>
            </div>
        <?php endif;?>
        <?php if($show_yearly && (isset($years))):
            $yearsValues = array_column($years, 'dyear');
            $chartYearsJson = json_encode($yearsValues);


            $totalYearsValues = array_column($years, 'total');
            $chartYearsTotal = json_encode($totalYearsValues);

            $totalYearsQty = array_column($years, 'total_num_orders');
            $chartYearsQty = json_encode($totalYearsQty);
            ?>
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h3 mb-0"><i class="fas fa-solid fa-calendar me-2"></i><?php echo Text::_('MOD_J2STORE_CHART_YEARLY_SALES_REPORT');?></h2>
                    </div>
                    <div class="card-body chart-body pt-0">
                        <canvas id="yearChart"></canvas>
                    </div>
                </div>
                <script>
                    const ytx = document.getElementById('yearChart').getContext('2d');
                    const yearChart = new Chart(ytx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo $chartYearsJson;?>,
                            datasets: [{
                                label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_REVENUE");?>',
                                data: <?php echo $chartYearsTotal;?>,
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
                                    label: '<?php echo Text::_("MOD_J2COMMERCE_CHART_TOTAL_COUNT");?>',
                                    data: <?php echo $chartYearsQty; ?>,
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
                                }
                            }
                        }
                    });
                </script>
            </div>
        <?php endif;?>
    </div>
</div>





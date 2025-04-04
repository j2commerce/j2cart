<?php
/**
 * --------------------------------------------------------------------------------
 * Module - Chart
 * --------------------------------------------------------------------------------
 * @package     Joomla 5.x
 * @subpackage  J2 Store
 * @copyright   Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @copyright   Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license     GNU GPL v3 or later
 * @link        https://www.j2commerce.com
 * --------------------------------------------------------------------------------
 *
 * */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;


require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$document = Factory::getApplication()->getDocument();
$module_id = $module->id;
$document->addScript('https://www.gstatic.com/charts/loader.js');
$currency = J2Store::currency();

$script = '
google.charts.load("current", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart_'.$module_id.');

         function drawChart_'.$module_id.'() {

        //year chart axis.

        var yearchart = google.visualization.arrayToDataTable([

         ["'.Text::_("J2STORE_YEAR").'","'.Text::_("J2STORE_AMOUNT").'"],

         ';


foreach($years as $order) {
	//year charts properties.
	$script .="

			['".$order['dyear']."',".$order['total']."],

			";

}

$script .=']);


    //month chart axis.

    var monthchart = google.visualization.arrayToDataTable([

    ["'.Text::_("J2STORE_CHART_MONTH").'","'.Text::_("J2STORE_CHART_TOTAL_AMOUNT").'"],
    ';

/**
 * array of itemsmonth.
 */
foreach($months as $item) {

	//$months = array('','January','Feb','March','April','May','June','July','August','Sep','Oct','Nov','Dec');

	//month charts properties.
	$script .='

    	["'.$item['dmonth'].'",'.$item['total'].'],

    	';

}
$script .=']);

    //day chart axis.

    var daychart = google.visualization.arrayToDataTable([
    ["'.Text::_("J2STORE_CHART_MONTH").'","'.Text::_("J2STORE_CHART_TOTAL_AMOUNT").'"],
    ';

/**
 * array of items.
 */
if(!empty($days)){

	foreach($days as $itemday){
		//day charts properties.
		$script .='
    	["'.$itemday['dday'].'",'.$itemday['total'].'],

    	';

	}
}

//  echo Text::_("J2STORE_CHART_TOTAL_AMOUNT");

$year_title = Text::_('MOD_J2STORE_CHART_YEARLY_SALES_REPORT');
$monthly_title = Text::_('MOD_J2STORE_CHART_MONTHLY_SALES_REPORT');
$daily_title = Text::_('MOD_J2STORE_CHART_DAILY_SALES_REPORT');
if($chart_type=='daily'){

	$script .=']);
		//day chart options.
		var dayoptions = {
		title:"'.$daily_title.'",
		pointSize: 6,
		height:300,
		backgroundColor: "#ffffffb3",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],
		vAxis:{
				title:"'.Text::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"#444444"},
				baselineColor: "#ffffff",
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},

		};

		//day line chart.

		var daycharts = new google.visualization.LineChart(document.getElementById("daily_report_'.$module_id.'"));
		daycharts.draw(daychart, dayoptions);

		}
		';
}elseif($chart_type=='monthly'){

	$script .=']);
		//month chart options.
		var monthoptions = {

		title: "'.$monthly_title.'",
		pointSize: 6,
		height:300,
		backgroundColor: "#ffffffb3",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],
		vAxis:{
				title:"'.Text::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"blue"},
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},
		};

		//month line chart.
		var monthcharts = new google.visualization.LineChart(document.getElementById("monthly_report_'.$module_id.'"));
		monthcharts.draw(monthchart,monthoptions);
		}
		';
}elseif($chart_type=='yearly'){

	$script .=']);
		//year chart options.

		var yearoptions = {

		title:"'.$year_title .'",
		pointSize: 6,
		height:300,
		backgroundColor: "#ffffffb3",
		curveType: "function",
        pointSize: 10,
		colors: ["#9ACAE6", "#E674B9", "#D0278E","#D0278E","#e49307", "#D0278E"],

		hAxis: {
				title: "'.Text::_('J2STORE_CHART_YEAR').'",
				titleTextStyle: {color: "blue"},
				format:"#"
				},
		vAxis:{
				title:"'.Text::_('J2STORE_CHART_TOTAL_AMOUNT').'",
				titleTextStyle:{color:"green"},
				format:"'.$currency->getSymbol().' #",
				viewWindowMode: "explicit",
				viewWindow:{ min: 0 }
			},
		};


		//year line chart.

		var yearcharts = new google.visualization.LineChart(document.getElementById("yearly_report_'.$module_id.'"));
		yearcharts.draw(yearchart, yearoptions);

		}
		';


}
//script declaration.
$document->addScriptDeclaration($script);

?>
<?php if(!empty($days) || !empty($months) || !empty($years)):?>
    <div class="card mb-4">
        <div class="card-body">
			<?php if(!empty($days)):?>
                <div id="daily_report_<?php echo $module_id;?>" class="mod-j2store-daily-sale-chart"></div>
			<?php endif;?>

			<?php if(!empty($months)):?>
                <div id="monthly_report_<?php echo $module_id;?>" class="mod-j2store-monthly-sale-chart"></div>
			<?php endif;?>

			<?php if(!empty($years)):?>
                <div id="yearly_report_<?php echo $module_id;?>" class="mod-j2store-yearly-sale-chart"></div>
			<?php endif;?>
        </div>
    </div>
<?php endif;?>



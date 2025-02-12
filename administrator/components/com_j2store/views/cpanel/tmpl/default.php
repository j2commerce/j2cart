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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.multiselect');

$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-md-';
?>
<style type="text/css">
	input[disabled] {
		background-color: #46a546 !important;
	}
</style>
<form action="<?php echo Route::_('index.php?option=com_j2store&view=cpanel'); ?>" method="post" name="adminForm" id="adminForm">
        <?php if(!empty( $sidebar )): ?>
        <div id="j2c-menu">
            <?php echo $sidebar ; ?>
        </div>
        <?php endif;?>
        <div id="j2c-main-container">
                <div  class ="box-widget-body ">
            <div id="container" class ="box-widget-body">
                            <?php echo J2Store::plugin()->eventWithHtml('BeforeCpanelView'); ?>
                            <?php echo J2Store::help()->free_topbar(); ?>
                                <?php echo J2Store::help()->info_j2commerce(); ?>
                            <?php if(PluginHelper::isEnabled('system', 'cache')): ?>
                                    <?php echo J2Store::help()->alert_with_static_message(
                                        'danger',
                                        Text::_('J2STORE_ATTENTION'),
                                        Text::_('J2STORE_SYSTEM_CACHE_ENABLED_NOTIFICATION')
                                    ); ?>
                                <?php endif; ?>
                                <?php $content_plugin = PluginHelper::isEnabled('content', 'socialshare'); ?>
                                <?php if($content_plugin):?>
                                    <?php echo J2Store::help()->alert_with_static_message(
                                        'danger',
                                        Text::_('J2STORE_ATTENTION'),
                                        Text::_('J2STORE_CONTENT_SOCIAL_SHARE_ENABLED_WARNING')
                                    );
                                    ?>
                                <?php endif; ?>

                                <div class="subscription_message" style="display:none;">
                                    <div class="alert alert-block alert-warning">
                                        <h4>
                                            <span class="subscription"></span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="stats-mini">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-1');?>
                                </div>
                                <div class="chart">
                                    <?php echo J2Store::modules()->loadposition('j2store-module-position-3');?>
                                </div>

                                <div class="<?php echo $row_class;?>">
                                    <div class="<?php echo $col_class;?>6 statistics">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-5');?>
                                    </div>
                                    <div class="<?php echo $col_class;?>6 latest_orders">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-4');?>
                                </div>
                            </div>
                <?php /*echo J2Store::help()->watch_video_tutorials();*/ ?>
            </div>
        </div>
    </div>
</form>
<?php
$platform->addInlineScript('
    setTimeout(function () {
        fetch("index.php?option=com_j2store&view=cpanels&task=getEupdates")
            .then(response => response.json())
            .then(json => {
                if (json["total"]) {
                    document.querySelector(".eupdate-notification .total").innerHTML = json["total"];
                    document.querySelector(".eupdate-notification").style.display = "block";
                }
            })
            .catch(error => console.error("Error fetching update data:", error));
    }, 2000);
');
/*$platform->addInlineScript('setTimeout(function () {
	(function($){
	$.ajax({
		  url: "index.php?option=com_j2store&view=cpanels&task=getEupdates",
		  dataType:\'json\'
		}).done(function(json) {
			if(json[\'total\']){
				$(\'.eupdate-notification .total\').html(json[\'total\']);
				$(\'.eupdate-notification\').show();
			}
		});

	})(j2store.jQuery);

}, 2000);');*/

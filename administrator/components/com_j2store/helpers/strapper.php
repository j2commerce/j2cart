<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store.com_j2store
 *
 * @copyright Copyright (C) 2014 Weblogicxindia.com. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;


require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class J2StoreStrapper {
    public static $instance = null;
    public function __construct($properties=null) {

    }
    public static function getInstance(array $config = array())
    {
        if (!self::$instance)
        {
            self::$instance = new self($config);
        }

        return self::$instance;
    }
    public static function addJS() {
        $params = J2Store::config();
        $platform = J2Store::platform();
	    $app = Factory::getApplication();
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $platform->loadExtra('jquery.framework');
        $platform->loadExtra('bootstrap.framework');


	    $wa->registerAndUseScript('j2store-namespace',Uri::root().'media/j2store/js/j2store.namespace.js');
        $ui_location = $params->get ( 'load_jquery_ui', 3 );
        $load_fancybox = $params->get ( 'load_fancybox', 1 );
        $load_timepicker = $params->get ( 'load_timepicker', 1 );

        switch ($ui_location) {

            case '0' :
                // load nothing
                break;
            case '1':
                if ($app->isClient('site')) {
	                $wa->registerAndUseScript('j2store-jquery-ui',Uri::root().'media/j2store/js/jquery-ui.min.js');
                }
                break;

            case '2' :
                if ($app->isClient('administrator')) {
	                $wa->registerAndUseScript('j2store-jquery-ui',Uri::root().'media/j2store/js/jquery-ui.min.js');
                }
                break;

            case '3' :
            default :
	        $wa->registerAndUseScript('j2store-jquery-ui',Uri::root().'media/j2store/js/jquery-ui.min.js');
                break;
        }
        switch ($load_timepicker) {
            case '0' :
                // load nothing
                break;
            case '1':
                if ($app->isClient('site')) {
	                $wa->registerAndUseScript('j2store-jquery-ui',Uri::root().'media/j2store/js/jquery-ui.min.js');
	                $wa->registerAndUseScript('j2store-timepicker-script',Uri::root().'media/j2store/js/jquery-ui-timepicker-addon.js');
                }
                break;
            case '2' :
                if ($app->isClient('administrator')) {
	                $wa->registerAndUseScript('j2store-timepicker-script',Uri::root().'media/j2store/js/jquery-ui-timepicker-addon.js');
                    self::loadTimepickerScript();
                }
                break;
            case '3' :
            default :
	            $wa->registerAndUseScript('j2store-timepicker-script',Uri::root().'media/j2store/js/jquery-ui-timepicker-addon.js');
                self::loadTimepickerScript();
                break;
        }

        if($app->isClient('administrator')) {
	        $wa->registerAndUseScript('j2store-jquery-validate-script',Uri::root().'media/j2store/js/jquery.validate.min.js');
	        $wa->registerAndUseScript('j2store-admin-script',Uri::root().'media/j2store/js/j2store_admin.js');
	        $wa->registerAndUseScript('j2store-fancybox-script',Uri::root().'media/j2store/js/jquery.fancybox.min.js');
        }
        else {
	        $wa->registerAndUseScript('j2store-jquery-zoom-script',Uri::root().'media/j2store/js/jquery.zoom.js');
	        $wa->registerAndUseScript('j2store-script',Uri::root().'media/j2store/js/j2store.js');
	        $wa->registerAndUseScript('j2store-media-script',Uri::root().'media/j2store/js/bootstrap-modal-conflit.js');
            if($load_fancybox) {
	            $wa->registerAndUseScript('j2store-fancybox-script',Uri::root().'media/j2store/js/jquery.fancybox.min.js');
                $platform->addInlineScript('jQuery(document).off("click.fb-start", "[data-trigger]");');
            }
        }
        J2Store::plugin ()->event ( 'AfterAddJS' );
    }

    public static function addCSS() {
        $j2storeparams = J2Store::config();
	    $app = Factory::getApplication();
        $platform = J2Store::platform();
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();


        // load full bootstrap css bundled with J2Commerce.
        if ($app->isClient('site') && $j2storeparams->get('load_bootstrap', 0)) {
	        $wa->registerAndUseStyle('j2store-bootstrap', Uri::root().'media/j2store/css/bootstrap.min.css');
        }

        // for site side, check if the param is enabled.
        if ($app->isClient('site') && $j2storeparams->get('load_minimal_bootstrap', 0)) {
	        $wa->registerAndUseStyle('j2store-minimal',Uri::root().'media/j2store/css/minimal-bs.css');
        }

        // jquery UI css
        $ui_location = $j2storeparams->get('load_jquery_ui', 3);
        switch ($ui_location) {
            case '0' :
                // load nothing
                break;
            case '1' :
                if ($app->isClient('site')) {
	                $wa->registerAndUseStyle('j2store-custom-css',Uri::root().'media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '2' :
                if ($app->isClient('administrator')) {
	                $wa->registerAndUseStyle('j2store-custom-css',Uri::root().'media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '3' :
            default :
	        $wa->registerAndUseStyle('j2store-custom-css',Uri::root().'media/j2store/css/jquery-ui-custom.css');
                break;
        }


        if ($app->isClient('administrator')) {
	        $wa->registerAndUseStyle('j2store-admin-css', Uri::root().'media/j2store/css/J4/j2store_admin.css');
	        $wa->registerAndUseStyle('listview-css', Uri::root().'media/j2store/css/backend/listview.css');
	        $wa->registerAndUseStyle('editview-css', Uri::root().'media/j2store/css/backend/editview.css');
	        $wa->registerAndUseStyle('j2store-fancybox-css',Uri::root().'media/j2store/css/jquery.fancybox.min.css');
        } else {
            J2Store::strapper()->addFontAwesome();
            // Add related CSS to the <head>
            if ($app->getDocument()->getType() === 'html' && $j2storeparams->get('j2store_enable_css', 1)) {
                $template = self::getDefaultTemplate();
                // j2store.css
                if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/j2store.css')){
                    $wa->registerAndUseStyle('j2store-css', Uri::root() . 'templates/' . $template . '/css/j2store.css');
                } elseif (file_exists(JPATH_SITE . '/media/templates/site/' . $template . '/css/j2store.css')) {
                    $wa->registerAndUseStyle('j2store-css', Uri::root() .'media/templates/site/' . $template . '/css/j2store.css');
                } else {
                    $wa->registerAndUseStyle('j2store-css', 'j2store/j2store.css');
                }
            }
            $load_fancybox = $j2storeparams->get('load_fancybox', 1 );
            if($load_fancybox){
	            $wa->registerAndUseStyle('j2store-fancybox-css', Uri::root() .'media/j2store/css/jquery.fancybox.min.css');
            }
        }
        J2Store::plugin()->event('AfterAddCSS');
    }

    public static function getDefaultTemplate() {

        static $tsets;

        if (!is_array($tsets))
        {
            $tsets = array();
        }
        $id = 1;
        if(!isset($tsets[$id])) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
            $db->setQuery( $query );
            $tsets[$id] = $db->loadResult();
        }
        return $tsets[$id];
    }

    public static function loadTimepickerScript() {
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        static $sets;
        $platform = J2Store::platform();
        if (!is_array($sets))
        {
            $sets = array();
        }
        $id = 1;
        if(!isset($sets[$id])) {
	        $wa->addInlineScript(self::getTimePickerScript());
            $sets[$id] = true;
        }
    }

    public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false) {
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if($isAdmin) {
	        $wa->registerAndUseScript('j2store-ui-timepicker',Uri::root() .'media/j2store/js/jquery-ui-timepicker-addon.js');
	        $wa->registerAndUseStyle('j2store-ui-custom',Uri::root() .'media/j2store/css/jquery-ui-custom.css');
        }

        if(empty($date_format)) {
            $date_format = 'yy-mm-dd';
        }

        if(empty($time_format)) {
            $time_format = 'HH:mm';
        }
        $localisation = self::getDateLocalisation();

        $element_date = $prefix.'_date';
        $element_time = $prefix.'_time';
        $element_datetime = $prefix.'_datetime';

        $timepicker_script ="
			if(typeof(j2store) == 'undefined') {
				var j2store = {};
			}

			if(typeof(jQuery) != 'undefined') {
				jQuery.noConflict();
			}

			if(typeof(j2store.jQuery) == 'undefined') {
				j2store.jQuery = jQuery.noConflict();
			}

			if(typeof(j2store.jQuery) != 'undefined') {

				(function($) {
					$(document).ready(function(){
						/*date, time, datetime*/

						if( $('.$element_date').length ){
							$('.$element_date').datepicker({dateFormat: '$date_format'});
						}

						if($('.$element_datetime').length){
							$('.$element_datetime').datetimepicker({
									dateFormat: '$date_format',
									timeFormat: '$time_format',
									$localisation
							});
						}

						if($('.$element_time').length){
							$('.$element_time').timepicker({timeFormat: '$time_format', $localisation});
						}

					});
				})(j2store.jQuery);
			}
			";

        return $timepicker_script;

    }

    public static function getDateLocalisation($as_array=false) {
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        //add localisation

        $params = J2Store::config();
        $language = Factory::getApplication()->getLanguage()->getTag();
        if($params->get('jquery_ui_localisation', 0) && strpos($language, 'en') === false) {

	        $wa->registerAndUseScript('jquery-ui-i18n',Uri::root() .'ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');

            //set the language default
            $tag = explode('-', $language);
            if(isset($tag[0]) && strlen($tag[0]) == 2) {
                $script = "";
                $script .= "(function($) { $.datepicker.setDefaults($.datepicker.regional['{$tag[0]}']); })(j2store.jQuery);";
	            $wa->addInlineScript($script);
            }

        }

        //localisation
        $currentText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_CURRENT_TEXT'));
        $closeText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_CLOSE_TEXT'));
        $timeOnlyText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_CHOOSE_TIME'));
        $timeText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_TIME'));
        $hourText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_HOUR'));
        $minuteText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_MINUTE'));
        $secondText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_SECOND'));
        $millisecondText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_MILLISECOND'));
        $timezoneText = addslashes(Text::_('J2STORE_TIMEPICKER_JS_TIMEZONE'));

        if($as_array) {

            $localisation = array (
                'currentText' => $currentText,
                'closeText' => $closeText,
                'timeOnlyTitle' => $timeOnlyText,
                'timeText' => $timeText,
                'hourText' => $hourText,
                'minuteText' => $minuteText,
                'secondText' => $secondText,
                'millisecText' => $millisecondText,
                'timezoneText' => $timezoneText
            );

        } else {

            $localisation ="
			currentText: '$currentText',
			closeText: '$closeText',
			timeOnlyTitle: '$timeOnlyText',
			timeText: '$timeText',
			hourText: '$hourText',
			minuteText: '$minuteText',
			secondText: '$secondText',
			millisecText: '$millisecondText',
			timezoneText: '$timezoneText'
			";
        }

        return $localisation;

    }

    public static function addDateTimePicker($element, $json_options) {
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $timepicker_script = self::getDateTimePickerScript($element, $json_options) ;
        $wa->addInlineScript($timepicker_script );
    }

    public static function getDateTimePickerScript($element, $json_options) {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = self::getDateLocalisation(true);
        $variables['dateFormat'] = $option_params->get('date_format','yy-mm-dd');
        $variables['timeFormat'] = $option_params->get('time_format','HH:mm');
        if ($option_params->get('hide_pastdates', 1)) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode($variables);
        $timepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datetimepicker({$variables});
			});
		})(j2store.jQuery);";

        return $timepicker_script;
    }

    public static function addDatePicker($element, $json_options) {
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $datepicker_script = self::getDatePickerScript($element, $json_options) ;
        $wa->addInlineScript($datepicker_script);

    }

    public static function getDatePickerScript($element, $json_options) {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = array();
        $variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
        if ($option_params->get ( 'hide_pastdates', 1 )) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode($variables);
        $datepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datepicker({$variables});
			});
		})(j2store.jQuery);";

        return $datepicker_script;
    }

    public static function sizeFormat($filesize)
    {
        if($filesize > 1073741824) {
            return number_format($filesize / 1073741824, 2)." Gb";
        } elseif($filesize >= 1048576) {
            return number_format($filesize / 1048576, 2)." Mb";
        } elseif($filesize >= 1024) {
            return number_format($filesize / 1024, 2)." Kb";
        } else {
            return $filesize." bytes";
        }
    }

    public function addFontAwesome(){
	    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
	    $waState = $wa->getManagerState();
        $config = J2Store::config();
        $font_awesome_ui = $config->get('load_fontawesome_ui',1);
        if($font_awesome_ui){
	        if(isset($waState['activeAssets']['style']['fontawesome'])){
		        $wa->useStyle('fontawesome');
	        } else {
		        $wa->registerAndUseStyle('fontawesome',Uri::root() .'media/j2store/css/font-awesome.min.css');
	        }
        }
    }
}

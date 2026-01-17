<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store.com_j2store
 *
 * @copyright Copyright (C) 2014-24 Weblogicxindia.com. All rights reserved.
 * @copyright Copyright (C) 2024-26 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');

class J2StoreStrapper
{
    public static $instance = null;

    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public static function addJS()
    {
        $params = J2Store::config();
        $app = Factory::getApplication();
        $platform = J2Store::platform();

        $platform->loadExtra('jquery.framework');
        $platform->loadExtra('bootstrap.framework');

        $platform->addScript('j2store-namespace', 'j2store/j2store.namespace.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);

        switch ($params->get('load_jquery_ui', 3))
        {
            case '0' :
                // load nothing
                break;
            case '1':
                if ($app->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                }
                break;
            case '2' :
                if ($app->isClient('administrator')) {
                    $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                }
                break;
            case '3' :
            default :
                $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
        }

        switch ($params->get('load_timepicker', 1))
        {
            case '0' :
                // load nothing
                break;
            case '1':
                if ($app->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                    $platform->addScript('j2store-timepicker-script', 'j2store/jquery-ui-timepicker-addon.js', ['relative' => true, 'version' => 'auto'], [], ['j2store-jquery-ui']);
                }
                break;
            case '2' :
                if ($app->isClient('administrator')) {
                    $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                    $platform->addScript('j2store-timepicker-script', 'j2store/jquery-ui-timepicker-addon.js', ['relative' => true, 'version' => 'auto'], [], ['j2store-jquery-ui']);
                    self::loadTimepickerScript();
                }
                break;
            case '3' :
            default :
                $platform->addScript('j2store-jquery-ui', 'j2store/jquery-ui.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                $platform->addScript('j2store-timepicker-script', 'j2store/jquery-ui-timepicker-addon.js', ['relative' => true, 'version' => 'auto'], [], ['j2store-jquery-ui']);
                self::loadTimepickerScript();
        }

        if($app->isClient('administrator')) {
            $platform->addScript('j2store-jquery-validate-script', 'j2store/jquery.validate.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
            $platform->addScript('j2store-admin-script', 'j2store/j2store_admin.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
            $platform->addScript('j2store-fancybox-script', 'j2store/jquery.fancybox.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
        } else {
            $platform->addScript('j2store-jquery-zoom-script', 'j2store/jquery.zoom.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
            $platform->addScript('j2store-script', 'j2store/j2store.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
            $platform->addScript('j2store-media-script', 'j2store/bootstrap-modal-conflit.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']); // cannot rename as used in Newline code
            if($params->get('load_fancybox', 1)) {
                $platform->addScript('j2store-fancybox-script', 'j2store/jquery.fancybox.min.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
                $platform->addInlineScript('jQuery(document).off("click.fb-start", "[data-trigger]");');
            }
        }

        J2Store::plugin ()->event('AfterAddJS');
    }

    public static function addCSS()
    {
        $j2storeparams = J2Store::config();
        $app = Factory::getApplication();
        $platform = J2Store::platform();

        // load full bootstrap css bundled with J2Commerce.
        if ($app->isClient('site') && $j2storeparams->get('load_bootstrap', 0)) {
            $platform->addStyle('j2store-bootstrap', 'j2store/bootstrap.min.css', ['relative' => true, 'version' => 'auto']);
        }

        // for site side, check if the param is enabled.
        if ($app->isClient('site') && $j2storeparams->get('load_minimal_bootstrap', 0)) {
            $platform->addStyle('j2store-minimal', 'j2store/minimal-bs.css', ['relative' => true, 'version' => 'auto']);
        }

        // jquery UI css
        switch ($j2storeparams->get('load_jquery_ui', 3))
        {
            case '0' :
                // load nothing
                break;
            case '1' :
                if ($app->isClient('site')) {
                    $platform->addStyle('j2store-custom-css', 'j2store/jquery-ui-custom.css', ['relative' => true, 'version' => 'auto']);
                }
                break;
            case '2' :
                if ($app->isClient('administrator')) {
                    $platform->addStyle('j2store-custom-css', 'j2store/jquery-ui-custom.css', ['relative' => true, 'version' => 'auto']);
                }
                break;
            case '3' :
            default :
                $platform->addStyle('j2store-custom-css', 'j2store/jquery-ui-custom.css', ['relative' => true, 'version' => 'auto']);
        }

        if ($app->isClient('administrator')) {
            $platform->addStyle('j2store-admin-css', 'j2store/J4/j2store_admin.css', ['relative' => true, 'version' => 'auto']);
            $platform->addStyle('listview-css', 'j2store/backend/listview.css', ['relative' => true, 'version' => 'auto']);
            $platform->addStyle('editview-css', 'j2store/backend/editview.css', ['relative' => true, 'version' => 'auto']);
            $platform->addStyle('j2store-fancybox-css', 'j2store/jquery.fancybox.min.css', ['relative' => true, 'version' => 'auto']);
        } else {
            J2Store::strapper()->addFontAwesome();
            // Add related CSS to the <head>
            if ($app->getDocument()->getType() === 'html' && $j2storeparams->get('j2store_enable_css', 1)) {
                $template = self::getDefaultTemplate();
                // j2store.css
                if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/j2store.css')){
                    $platform->addStyle('j2store-css', 'templates/' . $template . '/css/j2store.css');
                } elseif (file_exists(JPATH_SITE . '/media/templates/site/' . $template . '/css/j2store.css')) {
                    $platform->addStyle('j2store-css', 'media/templates/site/' . $template . '/css/j2store.css');
                } else {
                    $platform->addStyle('j2store-css', 'j2store/j2store.css', ['relative' => true, 'version' => 'auto']);
                }
            }

            if($j2storeparams->get('load_fancybox', 1)){
                $platform->addStyle('j2store-fancybox-css', 'j2store/jquery.fancybox.min.css', ['relative' => true, 'version' => 'auto']);
            }
        }

        J2Store::plugin()->event('AfterAddCSS');
    }

    public static function getDefaultTemplate()
    {
        static $defaultemplate = null;

        if ($defaultemplate === null) {
            $db = Factory::getContainer()->get('DatabaseDriver');

            $query = $db->getQuery(true);

            $query->select('template');
            $query->from('#__template_styles');
            $query->where($db->quoteName('client_id') . '= 0');
            $query->where($db->quoteName('home') . '= 1');

            $db->setQuery($query);

            $defaultemplate = $db->loadResult();
        }

        return $defaultemplate;
    }

    public static function loadTimepickerScript()
    {
        static $loadedTimePickerScript = false;

        if (!$loadedTimePickerScript) {
            $platform = J2Store::platform();
            $platform->addInlineScript(self::getTimePickerScript());
            $loadedTimePickerScript = true;
        }
    }

    public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false)
    {
        if ($isAdmin) {
            $platform = J2Store::platform();
            $platform->addScript('j2store-timepicker-script', 'j2store/jquery-ui-timepicker-addon.js', ['relative' => true, 'version' => 'auto'], [], ['j2store-jquery-ui']);
            $platform->addStyle('j2store-custom-css', 'j2store/jquery-ui-custom.css', ['relative' => true, 'version' => 'auto']);
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

    public static function getDateLocalisation($as_array=false)
    {
        $params = J2Store::config();
        $language = Factory::getApplication()->getLanguage()->getTag();
        if ($params->get('jquery_ui_localisation', 0) && strpos($language, 'en') === false) {
            $platform = J2Store::platform();
            $platform->addScript('jquery-ui-i18n', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');

            //set the language default
            $tag = explode('-', $language);
            if(isset($tag[0]) && strlen($tag[0]) == 2) {
                $script = "(function($) { $.datepicker.setDefaults($.datepicker.regional['{$tag[0]}']); })(j2store.jQuery);";
                $platform->addInlineScript($script);
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

    public static function addDateTimePicker($element, $json_options)
    {
        $platform = J2Store::platform();
        $timepicker_script = self::getDateTimePickerScript($element, $json_options) ;
        $platform->addInlineScript($timepicker_script);
    }

    public static function getDateTimePickerScript($element, $json_options)
    {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = self::getDateLocalisation(true);
        $variables['dateFormat'] = $option_params->get('date_format', 'yy-mm-dd');
        $variables['timeFormat'] = $option_params->get('time_format', 'HH:mm');
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

    public static function addDatePicker($element, $json_options)
    {
        $platform = J2Store::platform();
        $datepicker_script = self::getDatePickerScript($element, $json_options) ;
        $platform->addInlineScript($datepicker_script);
    }

    public static function getDatePickerScript($element, $json_options)
    {
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

    public function addFontAwesome()
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $config = J2Store::config();

        if ($config->get('load_fontawesome_ui', 1)) {
            if ($wa->assetExists('style', 'fontawesome')) {
                $wa->useStyle('fontawesome');
            } else {
                $wa->registerAndUseStyle('fontawesome', 'j2store/font-awesome.min.css', ['relative' => true, 'version' => 'auto']);
            }
        }
    }
}

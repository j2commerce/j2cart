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

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');

class J2StoreStrapper
{
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

    public static function addJS()
    {
        $params = J2Store::config();
        $platform = J2Store::platform();
        $document = Factory::getApplication()->getDocument();

        $platform->loadExtra('jquery.framework');
        $platform->loadExtra('bootstrap.framework');

        $platform->addScript('j2store-namespace','/media/j2store/js/j2store.namespace.js');
        $ui_location = $params->get ( 'load_jquery_ui', 3 );
        $load_fancybox = $params->get ( 'load_fancybox', 1 );
        $load_timepicker = $params->get ( 'load_timepicker', 1 );

        switch ($ui_location) {

            case '0' :
                // load nothing
                break;
            case '1':
                if ($platform->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    $platform->addScript('j2store-jquery-ui', '/media/j2store/js/jquery-ui.min.js');
                }
                break;

            case '3' :
            default :
                 $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                break;
        }
        switch ($load_timepicker) {

            case '0' :
                // load nothing
                break;
            case '1':
                if ($platform->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                    $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                    self::loadTimepickerScript();
                }
                break;

            case '3' :
            default :
                // $manager = $platform->application()->getDocument()->getWebAssetManager();
                $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                self::loadTimepickerScript();
                break;
        }

        if($platform->isClient('administrator')) {

            $platform->addScript('j2store-jquery-validate-script','/media/j2store/js/jquery.validate.min.js');
            $platform->addScript('j2store-admin-script','/media/j2store/js/j2store_admin.js');
            $platform->addScript('j2store-fancybox-script','/media/j2store/js/jquery.fancybox.min.js');
        }
        else {
            $platform->addScript('j2store-jquery-zoom-script','/media/j2store/js/jquery.zoom.js');
            $platform->addScript('j2store-script','/media/j2store/js/j2store.js');
            $platform->addScript('j2store-media-script','/media/j2store/js/bootstrap-modal-conflit.js');
            if($load_fancybox) {
                $platform->addScript('j2store-fancybox-script',  '/media/j2store/js/jquery.fancybox.min.js');
                $platform->addInlineScript('jQuery(document).off("click.fb-start", "[data-trigger]");');
            }
        }
        J2Store::plugin ()->event ( 'AfterAddJS' );
    }

    public static function addCSS()
    {
        $j2storeparams = J2Store::config ();
        $platform = J2Store::platform();

        // load full bootstrap css bundled with J2Store.
        if ($platform->isClient('site') && $j2storeparams->get ( 'load_bootstrap', 0 )) {
            $platform->addStyle('j2store-bootstrap', '/media/j2store/css/bootstrap.min.css');
        }

        // for site side, check if the param is enabled.
        if ($platform->isClient('site') && $j2storeparams->get ( 'load_minimal_bootstrap', 0 )) {
            $platform->addStyle('j2store-minimal','/media/j2store/css/minimal-bs.css');
        }

        // jquery UI css
        $ui_location = $j2storeparams->get ( 'load_jquery_ui', 3 );
        switch ($ui_location) {

            case '0' :
                // load nothing
                break;
            case '1' :
                if ($platform->isClient('site')) {
                    $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '3' :
            default :
                $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                break;
        }


        if ($platform->isClient('administrator')) {
                $platform->addStyle('j2store-admin-css', '/media/j2store/css/J4/j2store_admin.css');
                $platform->addStyle('listview-css', '/media/j2store/css/backend/listview.css');
                $platform->addStyle('editview-css', '/media/j2store/css/backend/editview.css');
            $platform->addStyle('j2store-fancybox-css','/media/j2store/css/jquery.fancybox.min.css');
        } else {
            J2Store::strapper()->addFontAwesome();
            $document =Factory::getApplication()->getDocument();
            // Add related CSS to the <head>
            if (($document->getType() === 'html') && $j2storeparams->get('j2store_enable_css')) {
                $template = self::getDefaultTemplate ();
                // j2store.css
                if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/j2store.css'))
                    $platform->addStyle( 'j2store-css', '/templates/' . $template . '/css/j2store.css' );
                else
                    $platform->addStyle( 'j2store-css', '/media/j2store/css/j2store.css' );
            } else {
                $platform->addStyle ( 'j2store-css','/media/j2store/css/j2store.css' );
            }
            $load_fancybox = $j2storeparams->get ( 'load_fancybox', 1 );
            if($load_fancybox){
                $platform->addStyle ( 'j2store-fancybox-css', '/media/j2store/css/jquery.fancybox.min.css' );
            }
        }
        J2Store::plugin ()->event ( 'AfterAddCSS' );
    }

    public static function getDefaultTemplate()
    {
        static $tsets;

        if ( !is_array( $tsets ) )
        {
            $tsets = array( );
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

    public static function loadTimepickerScript()
    {
        static $sets;
        $platform = J2Store::platform();
        if ( !is_array( $sets ) )
        {
            $sets = [];
        }
        $id = 1;
        if(!isset($sets[$id])) {
            $platform->addInlineScript(self::getTimePickerScript());
            $sets[$id] = true;
        }
    }

    public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false)
    {

        //initialise the date/time picker
        if($isAdmin) {
            $platform = J2Store::platform();
            $platform->addScript('j2store-ui-timepicker','/media/j2store/js/jquery-ui-timepicker-addon.js');
            $platform->addStyle('j2store-ui-custom','/media/j2store/css/jquery-ui-custom.css');
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

        $timepicker_script = "document.addEventListener('DOMContentLoaded', function() {
            /* Check if date, datetime, and time elements exist and initialize them */

            // Initialize date picker
            var dateElements = document.querySelectorAll('".$element_date."');
            if (dateElements.length) {
                dateElements.forEach(function(el) {
                    new flatpickr(el, { dateFormat: '".$date_format."' });
					});
				}

            // Initialize datetime picker
            var datetimeElements = document.querySelectorAll('".$element_datetime."');
            if (datetimeElements.length) {
                datetimeElements.forEach(function(el) {
                    new flatpickr(el, {
                        enableTime: true,
                        dateFormat: '".$date_format."',
                        timeFormat: '".$time_format."',
                        locale: ".$localisation."
                    });
                });
				}

            // Initialize time picker
            var timeElements = document.querySelectorAll('".$element_time."');
            if (timeElements.length) {
                timeElements.forEach(function(el) {
                    new flatpickr(el, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: '".$time_format."',
                        locale: ".$localisation."
                    });
			          });
	          }
        });
	";
        return $timepicker_script;
    }

    public static function getDateLocalisation($as_array=false)
    {
        //add localisation

        $params = J2Store::config();
        $language = Factory::getApplication()->getLanguage()->getTag();
        if($params->get('jquery_ui_localisation', 0) && strpos($language, 'en') === false) {
            $platform = J2Store::platform();
            $platform->addScript('jquery-ui-i18n','/ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');
            //set the language default
            $tag = explode('-', $language);
            if(isset($tag[0]) && (strlen($tag[0]) === 2)) {
                $script = "";
                $script .= "document.addEventListener('DOMContentLoaded', function() {
                            if (typeof j2store !== 'undefined' && typeof j2store.datepicker !== 'undefined') {
                                if (j2store.datepicker.regional && j2store.datepicker.regional['".$tag[0]."']) {
                                    j2store.datepicker.setDefaults(j2store.datepicker.regional['".$tag[0]."']);
                                }
                            }
                        });
                    ";
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
        $timepicker_script = self::getDateTimePickerScript($element, $json_options) ;
        J2Store::platform()->addInlineScript($timepicker_script );
    }

    public static function getDateTimePickerScript($element, $json_options)
    {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = self::getDateLocalisation (true);
        $variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
        $variables['timeFormat'] = $option_params->get ( 'time_format', 'HH:mm' );
        if ($option_params->get ( 'hide_pastdates', 1 )) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode ( $variables );
        $timepicker_script = "
            document.addEventListener('DOMContentLoaded', function() {
                const elements = document.querySelectorAll('".$element."');
                elements.forEach(function(element) {
                    if (typeof j2store !== 'undefined' && typeof j2store.datetimepicker === 'function') {
                        j2store.datetimepicker(element, ".$variables.");
                    }
			          });
            });
        ";
        return $timepicker_script;
    }

    public static function addDatePicker($element, $json_options)
    {

        $datepicker_script = self::getDatePickerScript($element, $json_options) ;
        J2Store::platform()->addInlineScript($datepicker_script);
    }

    public static function getDatePickerScript($element, $json_options)
    {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = [];
        $variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
        if ($option_params->get ( 'hide_pastdates', 1 )) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode ( $variables );
        $datepicker_script = "
            document.addEventListener('DOMContentLoaded', function() {
                const elements = document.querySelectorAll('".$element."');
                elements.forEach(function(element) {
                    if (typeof j2store !== 'undefined' && typeof j2store.datepicker === 'function') {
                        j2store.datepicker(element, ".$variables.");
                    }
			          });
            });
        ";
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
        $config = J2Store::config();
        $font_awesome_ui = $config->get('load_fontawesome_ui',1);
        if($font_awesome_ui){
            J2Store::platform()->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
        }
    }
}

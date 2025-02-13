<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Store.app_currencyupdater
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');

class plgJ2StoreApp_currencyupdater extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element   = 'app_currencyupdater';

    function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
    }

    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }

    /**
     * Overriding
     *
     * @param $row
     * @return string
     * @throws Exception
     */
    function onJ2StoreGetAppView( $row )
    {
        if (!$this->_isMe($row))
        {
            return null;
        }
        return $this->viewList();
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        ToolbarHelper::title(Text::_('J2STORE_APP').'-'.Text::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
        ToolbarHelper::apply ( 'apply' );
        ToolbarHelper::save ();
        ToolbarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new \stdClass();
        $fof_helper = J2Store::fof();
        $model = $fof_helper->getModel('AppCurrencyUpdaters', 'J2StoreModel');
        $data = $this->params->toArray();
        $new_data = array();
        $new_data['params'] = $data;
        $form = $model->getForm($new_data);
        $vars->form = $form;
        $id = $app->input->getInt('id', 0);
        $vars->id = $id;
        $vars->action = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        return $this->_getLayout('default', $vars);
    }

    /**
     * Update currency based on store currency
     * @param $rows - available currency list
     *
    */
    public function onJ2StoreUpdateCurrencies($rows, $force)
    {
        if(count($rows)){
            $store = J2Store::config();
            $store_currency = $store->get('config_currency');
            $db = Factory::getContainer()->get('DatabaseDriver');
            foreach ($rows as $result) {
                $currency_value = $this->calculateCurrency($store_currency,$result['currency_code'],1);
                if((float)$currency_value){
                    $query = $db->getQuery(true);
                    $query->update('#__j2store_currencies')->set('currency_value ='.$db->q((float)$currency_value))
                        ->set('modified_on='.$db->q(date('Y-m-d H:i:s')))
                        ->where('currency_code='.$db->q($result['currency_code']));
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
    }

    /**
     * calculate currency value
     * @param $fromCurrency - store currency or base currency
     * @param $toCurrency - other currency code
     * @param $amount - amount to convert
     * @return float - currency value
    */
    function calculateCurrency($fromCurrency, $toCurrency, $amount)
    {
        $fromCurrency = urlencode($fromCurrency);
        $toCurrency = urlencode($toCurrency);
        $from_Currency = urlencode($fromCurrency);
        $to_Currency = urlencode($toCurrency);
        $api_type = $this->params->get('currency_converter_api_type', 'exchangerate_host');
        $converted_amount = 0;
        $url = '';
        if ($api_type === 'exchangerate_host') {
            $url = "https://api.exchangerate.host/convert?from=$from_Currency&to=".$to_Currency;
        }elseif ($api_type === 'exchangerate_api') {
            $exchangerate_api = $this->params->get('exchangerate_api_key', '');
            $url = "https://v6.exchangerate-api.com/v6/" . $exchangerate_api . "/pair/" . $from_Currency . "/" . $to_Currency;
        }elseif ($api_type === 'currencyapi') {
            $currencyapi_api = $this->params->get('currencyapi_key', '');
            $header_request = array('apikey:'.$currencyapi_api);
            $url = "https://api.currencyapi.com/v3/latest?base_currency=$from_Currency&currencies=$to_Currency";
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $get = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        if ($api_type === 'exchangerate_host' && !empty($get)){
            $currency_data = json_decode($get);
            $converted_amount = isset( $currency_data->info->rate) && !empty( $currency_data->info->rate) ?  $currency_data->info->rate:'';
        }elseif ($api_type === 'exchangerate_api' && !empty($get)){
            $currency_data = json_decode($get);
            $converted_amount = isset($currency_data->conversion_rate) && !empty($currency_data->conversion_rate) ? $currency_data->conversion_rate: '';
        }elseif ($api_type === 'currencyapi' && !empty($get)){
            $currency_data = json_decode($get);
            $converted_amount = isset( $currency_data->data->$to_Currency->value ) && !empty( $currency_data->data->$to_Currency->value) ?  $currency_data->data->$to_Currency->value:'';
        }
        return $converted_amount;
    }

    /**
     * calculate currency value
     * @param $fromCurrency - store currency or base currency
     * @param $toCurrency - other currency code
     * @param $amount - amount to convert
     * @return float - currency value
     */
    function calculateCurrency1($fromCurrency, $toCurrency, $amount)
    {
        $amount = urlencode($amount);
        $fromCurrency = urlencode($fromCurrency);
        $toCurrency = urlencode($toCurrency);
        $amount = urlencode($amount);
        $from_Currency = urlencode($fromCurrency);
        $to_Currency = urlencode($toCurrency);
        $url = "https://www.msn.com/en-us/money/currencydetails/fi-$from_Currency$to_Currency";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $get = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        $converted_amount = 0;
        if(!empty($get)){
            $dom = new DOMDocument();
            $dom->loadHTML($get);
            $arr = $dom->getElementsByTagName("li"); // DOMNodeList Object
            $count = 0;

            foreach($arr as $item) { // DOMElement Object
                $class =  $item->getAttribute("class");
                $p_attr = $item->getElementsByTagName("p");
                $title = $p_attr[0]->getAttribute('title');
                $first_count = $count;
                if(strtolower($title) === 'open'){
                    $count = 1;
                }
                if(!empty($first_count) && ($first_count === $count)){
                    $converted_amount = $title;
                    break;
                }
            }
        }
        return $converted_amount;
    }
}

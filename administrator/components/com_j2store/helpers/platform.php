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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class J2StorePlatform
{
    /**
     * instance variable
     * @var null
     */
    public static $instance = null;

    /**
     * J2StorePlatform constructor.
     * @param null $properties
     */
    public function __construct($properties=null)
    {

    }

    /**
     * class instance
     * @return J2StorePlatform|null
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return \Joomla\CMS\Application\CMSApplication|null
     */
    public function application()
    {
        $app = null;
        try{
            $app = Factory::getApplication();
        }catch (\Exception $e){
            $app = null;
        }
        return $app;
    }

    public function redirect($url,$message = '',$notice = 'info')
    {
        $app = $this->application();
        if(!empty($message)){
            $app->enqueueMessage(Text::_($message),$notice);
        }
        $app->redirect($url);
    }

    public function isClient($identifier = 'site')
    {
        try{
            $status = $this->application()->isClient($identifier);
        }catch (\Exception $e){
            $status = false;
        }
        return $status;
    }

    public function toInteger($input,$default = null)
    {
        $output = [];
        $output = ArrayHelper::toInteger($input,$default);
        return $output;
    }

    public function fromObject($source, $recurse = true, $regex = null)
    {
        $output = [];
        $output = ArrayHelper::fromObject($source, $recurse, $regex);
        return $output;
    }

    public function toObject(array $array, $class = 'stdClass', $recursive = true)
    {
        $output = new stdClass();
        $output = ArrayHelper::toObject($array, $class, $recursive);
        return $output;
    }

    public function toString(array $array, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false)
    {
        $output = '';
        $output = ArrayHelper::toString($array, $innerGlue, $outerGlue, $keepOuterKey);
        return $output;
    }

    public function getValue($array, $name, $default = null, $type = '')
    {
        $output = $default;
        $output = ArrayHelper::getValue($array, $name, $default, $type);
        return $output;
    }

    public function loadExtra($behaviour,...$methodArgs)
    {
        if(!in_array($behaviour,array('behavior.framework','behavior.modal','bootstrap.tooltip','behavior.tooltip'))){
            HTMLHelper::_($behaviour,implode(',',$methodArgs));
        }elseif($behaviour == 'behavior.modal'){
            HTMLHelper::_('script', 'system/fields/modal-fields.min.js', array('version' => 'auto', 'relative' => true));
        }
    }
    public function addIncludePath($path)
    {
        HTMLHelper::addIncludePath($path);
    }

    public function checkAdminMenuModule()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $sql = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__modules')
            ->where($db->qn('module') . ' = ' . $db->q('mod_j2store_menu'));
        $db->setQuery($sql);
        try
        {
            $count = $db->loadResult();

            if($count > 0){
                $sql = $db->getQuery(true)
                    ->update($db->qn('#__modules'))
                    ->set($db->qn('published') . ' = ' . $db->q(0))
                    ->where($db->qn('module') . ' = ' . $db->q('mod_j2store_menu'));
                $db->setQuery($sql);
                $db->execute();
            }
        }
        catch (Exception $exc)
        {

        }
    }

    public function addScript($asset, $uri ,$options = [], $attributes = [], $dependencies = [])
    {
        $url = trim(Uri::root(),'/').$uri;
        $wa = $this->application()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript($asset,$url,$options,$attributes,$dependencies);
    }

    public function addStyle($asset, $uri ,$options = [], $attributes = [], $dependencies = [])
    {
        $url = trim(Uri::root(),'/').$uri;
        $wa = $this->application()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle($asset,$url,$options,$attributes,$dependencies);
    }

    public function addInlineScript( $content, $options = [], $attributes = [], $dependencies = [])
    {
        $wa = $this->application()->getDocument()->getWebAssetManager();
        $wa->addInlineScript($content,$options,$attributes,$dependencies);
    }

    public function addInlineStyle( $content, $options = [], $attributes = [], $dependencies = [])
    {
        $wa = $this->application()->getDocument()->getWebAssetManager();
        $wa->addInlineStyle($content,$options,$attributes,$dependencies);
    }

    public function raiseError($code, $message)
    {
        throw new Exception($message, $code);
    }

    public function getMyprofileUrl($params = array(),$is_xml = false,$no_sef = false)
    {
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'myprofile'
        );
        $active = J2StoreRouterHelper::findMenuMyprofile ( $qoptions );

        $url = 'index.php?option=com_j2store&view=myprofile';
        if(!empty($params)){
            $url .= "&".http_build_query($params);
        }
        if(isset($active) && is_object($active) && $active->id){
            $url .= '&Itemid='.$active->id;
        }
        if(!$no_sef){
            $url = Route::link('site',$url,$is_xml);
        }
        return $url;
    }

    public function getCheckoutUrl($params = array())
    {
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'checkout'
        );
        $active = J2StoreRouterHelper::findCheckoutMenu( $qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return Route::link('site','index.php?option=com_j2store&view=checkout&'.http_build_query($params).$item_id,false);
    }

    function getThankyouPageUrl($params = array())
    {
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'checkout',
            'layout' => 'postpayment',
            'task' => 'confirmPayment'
        );
        $active = J2StoreRouterHelper::findThankyouPageMenu( $qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }else{
            unset($qoptions['layout']);
            unset($qoptions['task']);
            $active = J2StoreRouterHelper::findCheckoutMenu( $qoptions );
            if(isset($active) && is_object($active) && $active->id){
                $item_id = '&Itemid='.$active->id;
            }
        }
        return Route::link('site','index.php?option=com_j2store&view=checkout&layout=postpayment&task=confirmPayment&'.http_build_query($params).$item_id,false);
    }

    public function getCartUrl($params = array())
    {
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'carts'
        );
        $active = J2StoreRouterHelper::findMenuCarts($qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return Route::link('site','index.php?option=com_j2store&view=carts&'.http_build_query($params).$item_id,false);
    }

    function getProductUrl($params = array(),$is_tag_view = false)
    {
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
        );
        $view = $this->application()->input->get('view','');
        if($view === 'producttags'){
            $qoptions['view'] = 'producttags';
        }elseif($is_tag_view){
            $qoptions['view'] = 'producttags';
        }else{
            $qoptions['view'] = 'products';
        }
        $qoptions = array_merge($qoptions,$params);

        if($qoptions['view'] === 'producttags'){
            $active = J2StoreRouterHelper::findProductTagsMenu( $qoptions );
        }else{
            $active = J2StoreRouterHelper::findProductMenu( $qoptions );
        }
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return Route::link('site','index.php?option=com_j2store&view='.$qoptions['view'].'&'.http_build_query($params).$item_id,false);
    }

    function getRootUrl()
    {
        $rootURL = rtrim(Uri::base(),'/');
        $subpathURL = Uri::base(true);
        if(!empty($subpathURL) && ($subpathURL != '/')) {
            $rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
        }
        return $rootURL;
    }

    public function getRegistry($json,$is_array = false)
    {
        if (!$json instanceof Registry || !$json instanceof Registry) {
            $params = new Registry();
            try {
                if($is_array){
                    $params->loadArray($json);
                }else{
                    $params->loadString($json);
                }
            } catch (\Exception $e) {
                $params = new Registry('{}');
            }
        } else {
            $params = $json;
        }
        return $params;
    }

    public function getImagePath($path)
    {
        $status = false;
        if(empty($path)){
            return $status;
        }
        $file_path = parse_url($path);
        if(isset($file_path['path']) && !empty($file_path['path']) && file_exists(JPATH_SITE.'/'.urldecode($file_path['path']))){
            $status = Uri::root().$file_path['path'];
        }
        return $status;
    }

    public function getLabel($label_info = '')
    {
        $label_class = 'badge bg-';
        return $label_class.$label_info;
    }

    public function getMenuLinks()
    {
        $items = MenusHelper::getMenuLinks();
        return $items;
    }

    public function eventTrigger($event_name,$args)
    {
        $results = $this->application()->triggerEvent($event_name, $args);

        return $results;
    }

    public function eventJ2Store4($eventName)
    {
        $plugin_helper = J2Store::plugin();
        $return = array();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $order_query = " ORDER BY ordering ASC ";
        $query = "SELECT * FROM #__extensions WHERE  enabled = '1' AND folder=".$db->q('j2store')." AND type='plugin' {$order_query}";
        $db->setQuery( $query );
        $plugins = $db->loadObjectList();
        if (!empty($plugins))
        {
            foreach ($plugins as $plugin)
            {
                if ($plugin_helper->hasEvent( $plugin, $eventName ))
                {
                    $return[] = $plugin->element;
                }
            }
        }
        PluginHelper::importPlugin('j2store');
        $app = Factory::getApplication();
        $app->triggerEvent('onJ2StoreAfterGetPluginsWithEvent', array(&$return));
        return $return;
    }
}

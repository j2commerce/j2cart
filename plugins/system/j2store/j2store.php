<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.j2store
 *
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Access;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;

// Make sure FOF is loaded, otherwise do not run
if (!defined('F0F_INCLUDED')) {
  include_once JPATH_LIBRARIES . '/f0f/include.php';
}

if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true)) {
  return;
}

// Do not run if j2store component is not enabled
if (!ComponentHelper::isEnabled('com_j2store', true)) {
  return;
}

if (is_file(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
  require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
} else {
  return;
}

class plgSystemJ2Store extends CMSPlugin
{
  function __construct( &$subject, $config )
  {
    parent::__construct( $subject, $config );

    // Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
    if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
      if (function_exists('error_reporting')) {
        $oldLevel = error_reporting(0);
      }
      $serverTimezone = @date_default_timezone_get();
      if (empty($serverTimezone) || !is_string($serverTimezone)) {
        $serverTimezone = 'UTC';
      }
      if (function_exists('error_reporting')) {
        error_reporting($oldLevel);
      }
      @date_default_timezone_set($serverTimezone);
    }
    //load language
    $this->loadLanguage('com_j2store', JPATH_SITE);
    //if($this->_mainframe->isAdmin())return;
  }

  /**
   * J2store event for content plugin event
   */
  public function onContentPrepare($extension, &$article, &$params)
  {
    if (!class_exists('J2Store')) {
      if (is_file(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
          require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
      } else {
          return;
      }
    }
    J2Store::plugin()->event('ContentPrepare',array($extension,&$article,&$params));
  }

  public function onAfterRender()
  {
    // Get the application object
    $app = Factory::getApplication();

    // Check if the site is the frontend, the option is com_j2store and J2STORE_VERSION is defined
    if ($app->isClient('site') && $app->input->get('option', '') == 'com_j2store' && defined('J2STORE_VERSION')) {
      // Get the body of the response
      $body = $app->getBody();

      // Define the additional class name
      $classAddition = 'j2c-' . str_replace('.', '-', J2STORE_VERSION);

      // Insert the class into the body tag, handling existing classes
      if (preg_match('/<body[^>]*class="([^"]*)"/', $body, $matches)) {
        // There are existing classes, append the class
        $body = preg_replace('/(<body[^>]*class="[^"]*)"/', '$1 ' . $classAddition . '"', $body);
      } else {
        // No existing classes, add the class attribute
        $body = preg_replace('/<body([^>]*)>/', '<body$1 class="' . $classAddition . '">', $body);
      }

      // Set the modified body back to the response
      $app->setBody($body);
    }
  }

  public function onAfterRoute()
  {
    $app = Factory::getApplication();
    $document = Factory::getDocument();
    $wam = $document->getWebAssetManager();
    $baseURL = Uri::root();

    $script = "var j2storeURL = '{$baseURL}';";
    $wam->addInlineScript($script);

    if (J2Store::platform()->isClient('site')) {
      //$this->_addCartJS();
      $coupon = $app->input->getString('coupon','');
      if (!empty($coupon)) {
        F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' )->set_coupon($coupon);
      }
    }
    $option = $app->input->getString('option','');
    $is_change_filter_input = $this->params->get('is_change_filter_input',1);
    if ($is_change_filter_input && $app->isClient('administrator') && !empty($option) && $option == 'com_j2store') {
      $script = "
        document.addEventListener('DOMContentLoaded', function() {
          var filterOrder = document.querySelector('#adminForm input[name=\"filter_order\"]');
          if (filterOrder) {
            filterOrder.setAttribute('name', 'sort_order');
          }
          var filterOrderDir = document.querySelector('#adminForm input[name=\"filter_order_Dir\"]');
          if (filterOrderDir) {
            filterOrderDir.setAttribute('name', 'sort_order_Dir');
          }
        });
     ";
      $wam->addInlineScript($script);
    }
  }

  public function onUserLogin($user, $options = array())
  {
    return $this->doLoginUser($user, $options);
  }

  private function doLoginUser($user, $options=array())
  {
    $app = Factory::getApplication();
    if (J2Store::platform()->isClient('administrator')) {
      return true;
    }

    $session = Factory::getSession();
    $old_sessionid = $session->get('old_sessionid', '', 'j2store');
    $user['id'] = intval(UserHelper::getUserId($user['username']));
    if (!class_exists('J2Store')) {
      if (is_file(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
        require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
      } else {
        return;
      }
    }
    //cart
    $helper = J2Store::cart();
    if (!empty($old_sessionid)) {
      $helper->resetCart( $old_sessionid, $user['id'] );
      //TODO do the same for wish lists
    } else {
      $helper->updateSession( $user['id'], $session->getId() );
    }

    return true;
  }

  /**
   * Called when Joomla! is booting up and checks for inventory. Thanks Nicholas (Based on Akeeba Subscriptions)
   */
  public function onAfterInitialise()
  {
    $app = Factory::getApplication();
    $option = $app->input->getString('option','');
    $is_change_filter_input = $this->params->get('is_change_filter_input',1);
    if ($is_change_filter_input && $app->isClient('administrator') && !empty($option) && $option == 'com_j2store') {
        $sort_key = $app->input->get('sort_order','');
        $sort_order_dir = $app->input->get('sort_order_Dir','');
        if (!empty($sort_key)) {
            $new_key = str_replace('sort','filter','sort_order');
            $app->input->set($new_key,$sort_key);
        }
        if (!empty($sort_order_dir)) {
            $new_key = str_replace('sort','filter','sort_order_Dir');
            $app->input->set($new_key,$sort_order_dir);
        }
    }

    // Check if we need to run
    if (!$this->doIHaveToRun()) {
      return;
    }
    $this->onJ2StoreCronTask('inventorycontrol');
  }

  public function onJ2StoreCronTask($task, $options = array())
  {
    if ($task != 'inventorycontrol') {
      return;
    }

    //check if inventory is enabled
    if (!class_exists('J2Store')) {
      if (is_file(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
        require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
      } else {
        return;
      }
    }
    $config = J2Store::config();
    if ($config->get('enable_inventory', 0) != 1 || $config->get('cancel_order', 0) != 1) {
      //inventory not enabled. return.
      return;
    }

    // Get today's date
    $jNow = new Date();
    $now = $jNow->toUnix();

    F0FModel::getTmpInstance('Orders', 'J2StoreModel')->cancel_unpaid_orders();

    // Update the last run info and quit
    $this->setLastRunTimestamp();
  }

  /**
   * Fetches the com_j2store component's parameters as a Registry instance
   *
   * @return Registry The component parameters
   */
  private function getComponentParameters()
  {
    $component = ComponentHelper::getComponent('com_j2store');
    return $component->getParams();
  }

  /**
   * "Do I have to run?" - the age old question. Let it be answered by checking the
   * last execution timestamp, stored in the component's configuration.
   */
  private function doIHaveToRun()
  {
    $params = $this->getComponentParameters();
    $lastRunUnix = $params->get('plg_j2store_inventory_control_timestamp', 0);
    $dateInfo = getdate($lastRunUnix);
    $nextRunUnix = mktime(0, 0, 0, $dateInfo['mon'], $dateInfo['mday'], $dateInfo['year']);
    $nextRunUnix += 24 * 3600;
    $now = time();

    return ($now >= $nextRunUnix);
  }

  /**
   * Saves the timestamp of this plugin's last run
   */
  private function setLastRunTimestamp()
  {
    $lastRun = time();
    $params = $this->getComponentParameters();
    $params->set('plg_j2store_inventory_control_timestamp', $lastRun);

    $db = Factory::getDBO();
    $data = $params->toString();

    $query = $db->getQuery(true)
      ->update($db->quoteName('#__extensions'))
      ->set($db->quoteName('params') . ' = ' . $db->quote($data))
      ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2store'))
      ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
    $db->setQuery($query);
    $db->execute();
  }

  public function onJ2StoreAfterUpdateCart($cart_id, $data)
  {
    $plugin = PluginHelper::getPlugin('system', 'cache');
    $params = J2Store::platform()->getRegistry($plugin->params);
    $options = array(
      'defaultgroup' => 'page',
      'browsercache' => $params->get('browsercache', false),
      'caching'      => false,
    );
    $cache = Cache::getInstance('page', $options);
    $cache->clean();
  }

  public function onJ2StoreBeforeGetPrice($pricing,$model,$calculator)
  {
    $app = Factory::getApplication();
    $user_id = $app->input->getInt('user_id',0);
    $view = $app->input->get('view','');
    $task = $app->input->get('task','');

    if (!empty($user_id) && in_array($task, array('displayAdminProduct')) && in_array($view, array('products'))) {
      $user = Factory::getUser ($user_id);
      $group_id = implode(',', Access::getGroupsByUser($user->id));
      $calculator->set('group_id',$group_id);
    }
  }

  /**
   * add setup fee
   * */
  function onJ2StoreCalculateFees($order)
  {
    $app = Factory::getApplication();
    $option = $app->input->get('option','');
    $view = $app->input->get('view','');

    if (J2Store::platform()->isClient('administrator') && in_array ($order->order_type, array('normal')) && $option == 'com_j2store' && in_array ($view, array('orders','order'))) {
      $db = Factory::getDbo();
      $query = $db->getQuery(true);
      $query->select('*')->from('#__j2store_orderfees')->where('order_id=' . $db->quote($order->order_id));
      $db->setQuery($query);
      $lists = $db->loadObjectList();
      foreach ($lists as $list) {
        $order->add_fee($list->name, $list->amount, $list->taxable, $list->tax_class_id);
      }
    }
  }
}

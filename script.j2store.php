<?php
/**
 * @package J2Store
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
// use Joomla\Database\DatabaseDriver; // not in Joomla 3

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder'); // keep to ensure install under Joomla 3 prior to upgrade to Joomla 4

// Load FOF if not already loaded
if (!defined('F0F_INCLUDED')) {
  $paths = array(
    (defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php', __DIR__ . '/fof/include.php',
  );

  foreach ($paths as $filePath) {
    if (!defined('F0F_INCLUDED') && file_exists($filePath)) {
      @include_once $filePath;
    }
  }
}

// Pre-load the installer script class from our own copy of FOF
if (!class_exists('F0FUtilsInstallscript', false)) {
  @include_once __DIR__ . '/fof/utils/installscript/installscript.php';
}

// Pre-load the database schema installer class from our own copy of FOF
if (!class_exists('F0FDatabaseInstaller', false)) {
  @include_once __DIR__ . '/fof/database/installer.php';
}

// Pre-load the update utility class from our own copy of FOF
if (!class_exists('F0FUtilsUpdate', false)) {
  @include_once __DIR__ . '/fof/utils/update/update.php';
}

// Pre-load the cache cleaner utility class from our own copy of FOF
if (!class_exists('F0FUtilsCacheCleaner', false)) {
  @include_once __DIR__ . '/fof/utils/cache/cleaner.php';
}

class Com_J2storeInstallerScript extends F0FUtilsInstallscript
{
  /**
   * The component's name
   *
   * @var   string
   */
  protected $componentName = 'com_j2store';

  /**
   * The title of the component (printed on installation and uninstallation messages)
   *
   * @var string
   */
  protected $componentTitle = 'J2Store Joomla Shopping cart';

  protected $minimumJoomlaVersion = '4.0.0';
  protected $maximumJoomlaVersion = '5.99.99';

    /**
     * Store original error reporting level
     *
     * @var int
     */
    protected $originalErrorReporting;

  protected $removeFilesAllVersions = array(
    'files' => array(
      // Use pathnames relative to your site's root, e.g.
      // 'administrator/components/com_foobar/helpers/whatever.php'
      'components/com_j2store/views/products/tmpl/default.html',
      'components/com_j2store/views/products/tmpl/default.php',
      'components/com_j2store/views/products/tmpl/default_cart.php',
      'components/com_j2store/views/products/tmpl/default_filters.php',
      'components/com_j2store/views/products/tmpl/default_general.php',
      'components/com_j2store/views/products/tmpl/default_images.php',
      'components/com_j2store/views/products/tmpl/default_inventory.php',
      'components/com_j2store/views/products/tmpl/default_item.php',
      'components/com_j2store/views/products/tmpl/default_modules.php',
      'components/com_j2store/views/products/tmpl/default_price.php'
    ),
    'folders' => array(
      // Use pathnames relative to your site's root, e.g.
      // 'administrator/components/com_foobar/baz'
      'plugins/j2store/tool_localization_data',
      'plugins/j2store/tool_diagnostics'
    )
  );

  /**
   * The list of extra modules and plugins to install on component installation / update and remove on component
   * uninstallation.
   *
   * @var   array
   */
  protected $installation_queue = array(        // modules => { (folder) => { (module) => { (position), (published) } }* }*
    'modules' => array(
      'admin' => array(
        'j2store_chart' => array('j2store-module-position-3', 1),
        'j2store_stats_mini' => array('j2store-module-position-1', 1),
        'j2store_orders' => array('j2store-module-position-4', 1),
        'j2store_stats' => array('j2store-module-position-5', 1),
        'j2store_menu' => array('menu', 1)
      ),
      'site' => array(
        'mod_j2store_currency' => array('left', 0),
        'mod_j2store_cart' => array('left', 0)
      )
    ),
    'plugins' => array(
      'content' => array('j2store' => 1),
      'system' => array(
        'j2store' => 1,
        'j2pagecache' => 0,
        'j2canonical' => 0
      ),
      'search' => array('j2store' => 0),
      'finder' => array('j2store' => 0),
      'user' => array('j2userregister' => 0),
      'installer' => array('j2store' => 1),
      'j2store' => array(
        'shipping_free' => 0,
        'shipping_standard' => 1,
        'payment_cash' => 1,
        'payment_moneyorder' => 1,
        'payment_banktransfer' => 1,
        'payment_paypal' => 1,
        'report_products' => 1,
        'payment_sagepayform' => 1,
        'report_itemised' => 1,
        'app_localization_data' => 1,
        'app_diagnostics' => 1,
        'app_currencyupdater' => 1,
        'app_flexivariable' => 1,
        'app_schemaproducts' => 1,
        'app_bootstrap3' => 0,
        'app_bootstrap4' => 0,
        'app_bootstrap5' => 1
      )
    )
  );

    public function postflight($type, $parent)
    {
        parent::postflight($type, $parent);

        // Restore original error reporting level
        if (isset($this->originalErrorReporting)) {
            error_reporting($this->originalErrorReporting);
            ini_set('display_errors', $this->originalErrorReporting > 0 ? 1 : 0);
        }

        // Remove extra column from the variants table

        $db = Factory::getDbo();

        try {
            $alterQuery = "ALTER TABLE `#__j2store_variants` DROP `campaign_variant_id`";
            $db->setQuery($alterQuery);
            $db->execute();
        } catch (\Exception $e) {
            // Can fail if the column does not exist
        }

        // Security exploitation check (CVE fixed in 4.0.21)
        $checkResult   = $this->_checkForExploitation();
        $verdict       = $this->_getExploitationVerdict($checkResult);
        $cleanupResult = null;
        if ($verdict === 'hacked') {
            $cleanupResult = $this->_removeExploitationFiles($checkResult);
        }
        $this->_renderSecurityCheck($checkResult, $verdict, $cleanupResult);

        // Template override CSRF check
        $overrideWarnings = $this->_checkTemplateOverrides();
        $this->_renderTemplateOverrideWarnings($overrideWarnings);
    }

  public function preflight($type, $parent)
  {
      // Store current error reporting level and set to exclude warnings/deprecations
      $this->originalErrorReporting = error_reporting();
      error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_NOTICE);
      ini_set('display_errors', 1);

    if (parent::preflight($type, $parent)) {
      $app = Factory::getApplication();
      $db = Factory::getDbo();

      // Remove the old j2store component update site that is now obsolete
      $this->_removeUpdateSite('component', 'com_j2store', '', 'https://cdn.j2store.net/j2store4.xml');

      //check of curl is present
      if (!function_exists('curl_init') || !is_callable('curl_init')) {

        $msg = "<p>cURL extension is not enabled in your PHP installation. Please contact your hosting service provider</p>";

        if (version_compare(JVERSION, '3.0', 'gt')) {
          Log::add($msg, Log::WARNING, 'jerror');
        } else {
          $app->enqueueMessage($msg, 'error');
        }
        return false;
      }

      if (!function_exists('json_encode')) {

        $msg = "<p>JSON extension is not enabled in your PHP installation. Please contact your hosting service provider</p>";

        if (version_compare(JVERSION, '3.0', 'gt')) {
          Log::add($msg, Log::WARNING, 'jerror');
        } else {
          $app->enqueueMessage($msg, 'error');
        }
        return false;
      }

      //get the table list
      $alltables = $db->getTableList();
      //get prefix
      $prefix = $db->getPrefix();
      //conservative method
      $xmlfile = JPATH_ADMINISTRATOR . '/components/com_j2store/manifest.xml';
      if (\JFile::exists($xmlfile)) {
        $xml = simplexml_load_file($xmlfile);
        $version = (string)$xml->version;
        if (version_compare($version, '3.9.99', 'lt')) {
          $parent->getParent()->abort('You cannot install J2Store Version 4 over older versions directly. A migration tool should be used first to migrate your previous store data.');
          return false;
        }
      }

      //let us check the manifest cache as well. Cannot trust joomla installer
      $query = $db->getQuery(true);
      $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2store'))
        ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
      $db->setQuery($query);
      $result = $db->loadResult();

      if ($result) {
        $manifest = json_decode($result);
        $version = $manifest->version;
        if (!empty($version)) {
          // abort if the current J2Store release is older
          /*if( version_compare( $version, '3.9.99', 'lt' ) ) {
              $parent->getParent()->abort('You cannot install J2Store Version 4 over the old versions directly. A migration tool should be used first to migrate your previous store data.');
              return false;
          }*/
          if (version_compare($version, '3.9.99', 'lt')) {
            if (!ComponentHelper::isEnabled('com_j2migrationchecker')) {
              $parent->getParent()->abort('The J2Store v4 Migration component com_j2migrationchecker was not found. Please install it before updating to J2Store 4.');
              return false;
            }
            if (!in_array($prefix . 'extension_check', $alltables)) {
              $parent->getParent()->abort('The J2Store v4 Migration component com_j2migrationchecker was not found. Please install it before updating to J2Store 4.');
              return false;
            }
            $query = "SELECT * FROM #__extension_check";
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if (empty($result)) {
              $parent->getParent()->abort('Complete the migration before installing J2Store 4.');
              return false;
            }
            foreach ($result as $key => $value) {
              if (empty($value->installation_status)) {
                $parent->getParent()->abort('You did not complete the J2Store 4 migration steps, please complete the migration before installing J2Store 4.');
                return false;
              }
            }
          }
        }
      }

      //some times the user might have uninstalled v2 and try installing v3. Let us stop them doing so.
      //check for the prices table. It he has the prices table, then he is certainly having the old version.

      if (in_array($prefix . 'j2store_prices', $alltables)) {
        //user has the prices table. So the old version data might be there.
        $parent->getParent()->abort('Tables of J2Store Version 2.x found. If you have already installed J2Store Version 2, its tables might be there. If you do not have any data in those tables, then you can delete those tables via phpmyadmin and then install J2store version 3. Otherwise, you will have to use our migration tool');
        return false;
      }

      //if we are here, then all checks are passed. Let us allow the user to install J2Store Version 3. Just make sure to remove the template overrides and incompatible modules

      //----file removal//
      //check in the template overrides.

      //first get the default template
      $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
      $db->setQuery($query);
      $template = $db->loadResult();

      $template_path = JPATH_SITE . '/templates/' . $template . '/html';
      $com_override_path = $template_path . '/com_j2store';

      //j2store overrides - mycart
      if (\JFolder::exists($com_override_path . '/carts')) {
        if (\JFile::exists($com_override_path . '/carts/default_items.php')) {
          if (!\JFolder::move($com_override_path . '/carts/default_items.php', $com_override_path . '/carts/old_default_items.php')) {
            $parent->getParent()->abort('Could not move file ' . $com_override_path . '/carts/default_items.php. It might be having old code. So please Check permissions and rename this file. ');
            return false;
          }
        }
      }

      if (\JFolder::exists($com_override_path . '/cart')) {
        if (\JFile::exists($com_override_path . '/cart/default_items.php')) {
          if (!\JFolder::move($com_override_path . '/cart/default_items.php', $com_override_path . '/cart/old_default_items.php')) {
            $parent->getParent()->abort('Could not move file ' . $com_override_path . '/cart/default_items.php. It might be having old code. So please Check permissions and rename this file. ');
            return false;
          }
        }
      }

      //the following renaming should happen only during new installs. If its an update, then these issues probably taken care of.

      if ($type != 'update') {
        if (\JFolder::exists($com_override_path . '/checkout')) {
          if (\JFile::exists($com_override_path . '/checkout/shipping_yes.php')) {
            if (!\JFolder::move($com_override_path . '/checkout/shipping_yes.php', $com_override_path . '/checkout/old_shipping_yes.php')) {
              $parent->getParent()->abort('Could not move file ' . $com_override_path . '/checkout/shipping_yes.php. It might be having old code. So please Check permissions and rename this file. ');
              return false;
            }
          }
        }

        //j2store overrides - products
        if (\JFolder::exists($com_override_path . '/products')) {
          if (\JFolder::exists($com_override_path . '/old_products')) {
            if (!\JFolder::delete($com_override_path . '/old_products')) {
              $parent->getParent()->abort('Could not delete folder ' . $com_override_path . '/products  Check permissions.');
              return false;
            }
          }
          if (!\JFolder::move($com_override_path . '/products', $com_override_path . '/old_products')) {
            $parent->getParent()->abort('Could not move folder ' . $com_override_path . '/products. Check permissions.');
            return false;
          }
        }
      }

      if (version_compare(JVERSION, '3.99.99', 'ge') && isset($this->installation_queue) && isset($this->installation_queue['modules']['admin']['j2store_menu'])) {
        $this->installation_queue['modules']['admin']['j2store_menu'] = array('status', 1);
      }
      //----end of file removal//
      //all set. Lets rock..

      return true;
    }

    return false;
  }

  public function uninstall($parent)
  {
    // Safety net: disable all J2Store modules and plugins at the DB level
    // BEFORE files are removed. This catches bundled extensions, third-party
    // J2Store plugins, and survives any failure in uninstallSubextensions().
    // Without this, remaining enabled extensions call J2Store:: after the
    // component files are gone, causing a fatal "Class J2Store not found".
    $this->_disableAllJ2StoreExtensions();

    // Uninstall database
    $dbInstaller = new F0FDatabaseInstaller(array(
      'dbinstaller_directory' =>
        ($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' . $this->schemaXmlPath
    ));

    // Uninstall modules and plugins
    $status = $this->uninstallSubextensions($parent);

    // Uninstall post-installation messages on Joomla! 3.2 and later
    $this->uninstallPostInstallationMessages();

    // Show the post-uninstallation page
    $this->renderPostUninstallation($status, $parent);

  }

  /**
   * Unpublishes all J2Store modules and disables all J2Store plugins directly
   * in the database. Called at the very start of uninstall() so that no
   * J2Store-dependent extension can fire after the component files are removed,
   * regardless of whether uninstallSubextensions() succeeds or fails, and
   * regardless of whether the extensions were bundled or third-party.
   */
  private function _disableAllJ2StoreExtensions(): void
  {
      try {
          $db = Factory::getDbo();

          // Unpublish all J2Store modules (admin and site)
          $db->setQuery(
              $db->getQuery(true)
                  ->update($db->quoteName('#__modules'))
                  ->set($db->quoteName('published') . ' = 0')
                  ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_j2store%'))
          );
          $db->execute();

          // Disable all J2Store plugins:
          // - plugins in the dedicated j2store folder (payment, shipping, app, report)
          // - plugins in standard Joomla folders whose element contains 'j2store'
          //   (e.g. system/j2store, content/j2store, finder/j2store, installer/j2store)
          $db->setQuery(
              $db->getQuery(true)
                  ->update($db->quoteName('#__extensions'))
                  ->set($db->quoteName('enabled') . ' = 0')
                  ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                  ->where(
                      '(' . $db->quoteName('folder')  . ' = '    . $db->quote('j2store') .
                      ' OR ' . $db->quoteName('element') . ' LIKE ' . $db->quote('%j2store%') . ')'
                  )
          );
          $db->execute();

          // Clear the modules and plugins cache so the disabled state takes
          // effect immediately without a manual cache flush.
          $cache = Factory::getCache('com_modules', '');
          $cache->clean();
          $cache = Factory::getCache('com_plugins', '');
          $cache->clean();

      } catch (\Exception $e) {
          // Silent fail — this is a best-effort safety net; do not interrupt
          // the rest of the uninstall process if the DB sweep fails.
      }
  }

  protected function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
  {
    $fofInstallationStatus = $this->_installFOF($parent);
    $this->_installLocalisation($parent);
  }

  private function _installFOF($parent)
  {
    $src = $parent->getParent()->getPath('source');

    // Load dependencies
    JLoader::import('joomla.filesystem.file');
    JLoader::import('joomla.utilities.date');
    $source = $src . '/fof';

    if (!defined('JPATH_LIBRARIES')) {
      $target = JPATH_ROOT . '/libraries/f0f';
    } else {
      $target = JPATH_LIBRARIES . '/f0f';
    }
    $haveToInstallFOF = false;

    if (!is_dir($target)) {
      $haveToInstallFOF = true;
    } else {
      $fofVersion = array();

      if (file_exists($target . '/version.txt')) {
        $rawData = file_get_contents($target . '/version.txt');
        $info = explode("\n", $rawData);
        $fofVersion['installed'] = array(
          'version' => trim($info[0]),
          'date' => new Date(trim($info[1]))
        );
      } else {
        $fofVersion['installed'] = array(
          'version' => '0.0',
          'date' => new Date('2011-01-01')
        );
      }

      $rawData = file_get_contents($source . '/version.txt');
      $info = explode("\n", $rawData);
      $fofVersion['package'] = array(
        'version' => trim($info[0]),
        'date' => new Date(trim($info[1]))
      );

      $haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
    }

    $installedFOF = false;

    if ($haveToInstallFOF) {
      $versionSource = 'package';
      $installer = new Installer();
      $installedFOF = $installer->install($source);
    } else {
      $versionSource = 'installed';
    }

    if (!isset($fofVersion)) {
      $fofVersion = array();

      if (file_exists($target . '/version.txt')) {
        $rawData = file_get_contents($target . '/version.txt');
        $info = explode("\n", $rawData);
        $fofVersion['installed'] = array(
          'version' => trim($info[0]),
          'date' => new Date(trim($info[1]))
        );
      } else {
        $fofVersion['installed'] = array(
          'version' => '0.0',
          'date' => new Date('2011-01-01')
        );
      }

      $rawData = file_get_contents($source . '/version.txt');
      $info = explode("\n", $rawData);
      $fofVersion['package'] = array(
        'version' => trim($info[0]),
        'date' => new Date(trim($info[1]))
      );
      $versionSource = 'installed';
    }

    if (!($fofVersion[$versionSource]['date'] instanceof Date)) {
      $fofVersion[$versionSource]['date'] = new Date;
    }

    return array(
      'required' => $haveToInstallFOF,
      'installed' => $installedFOF,
      'version' => $fofVersion[$versionSource]['version'],
      'date' => $fofVersion[$versionSource]['date']->format('Y-m-d'),
    );
  }

  function _installLocalisation($parent)
  {
    $installer = $parent->getParent();

    $db = Factory::getDbo();
    //get the table list
    $alltables = $db->getTableList();
    //get prefix
    $prefix = $db->getPrefix();
    // we have to separate try catch , because may country install fail, zone table also get affect install
    try {
      $country_status = false;
      if (!in_array($prefix . 'j2store_countries', $alltables)) {
        $country_status = true;
      } else {
        $query = $db->getQuery(true);
        $query->select('*')->from('#__j2store_countries');
        $db->setQuery($query);
        $country_list = $db->loadAssocList();
        if (count($country_list) < 1) {
          $country_status = true;
        }
      }

      if ($country_status) {
        //countries
        $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/countries.sql';
        $this->_executeSQLFiles($sql);
      }
    } catch (Exception $e) {
      // do nothing
    }

    try {
      $zone_status = false;
      if (!in_array($prefix . 'j2store_zones', $alltables)) {
        $zone_status = true;
      } else {
        $query = $db->getQuery(true);
        $query->select('*')->from('#__j2store_zones');
        $db->setQuery($query);
        $zone_list = $db->loadAssocList();
        if (count($zone_list) < 1) {
          $zone_status = true;
        }
      }

      if ($zone_status) {
        //zones
        $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/zones.sql';
        $this->_executeSQLFiles($sql);
      }
    } catch (Exception $e) {
      // do nothing
    }

    try {
      //metrics
      $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/lengths.sql';
      $this->_executeSQLFiles($sql);

      $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/weights.sql';
      $this->_executeSQLFiles($sql);
    } catch (Exception $e) {
      // do nothing
    }

    // ALTER IGNORE removed in latest mysql version
    $query = 'SHOW INDEX FROM `#__j2store_productquantities`';
    $db->setQuery($query);
    $product_qty_index = $db->loadObjectList();
    $add_index = true;
    foreach ($product_qty_index as $pro_qty_index) {
      if (in_array($pro_qty_index->Key_name, array('variantidx'))) {
        $add_index = false;
        break;
      }
    }
    if ($add_index) {
      try {
        $query = 'ALTER TABLE #__j2store_productquantities ADD UNIQUE INDEX variantidx (variant_id)';
        $this->_sqlexecute($query);
      } catch (Exception $e) {
        // do nothing
      }

    }
    //remove duplicates from the product quantities table
    /*$query = 'ALTER TABLE #__j2store_productquantities ENGINE MyISAM;
    ALTER IGNORE TABLE #__j2store_productquantities ADD UNIQUE INDEX variantidx (variant_id);
    ALTER TABLE #__j2store_productquantities ENGINE InnoDB;';
    $this->_sqlexecute($query);*/
  }

  private function _executeSQLFiles($sql)
  {
    if (\JFile::exists($sql)) {
      $db = Factory::getDbo();
      $queries = JDatabaseDriver::splitSql(file_get_contents($sql));
      foreach ($queries as $query) {
        $query = trim($query);
        if ($query != '' && $query[0] != '#') {
          $db->setQuery($query);
          try {
            $db->execute();
          } catch (Exception $e) {
            //do nothing as customer can do this very well by going to the tools menu
          }
        }
      }
    }
  }

  private function _sqlexecute($query)
  {
    $db = Factory::getDbo();
    $db->setQuery($query);
    try {
      $db->execute();
    } catch (Exception $e) {
      //do nothing as customer can do this very well by going to the tools menu
    }
  }

  private function _removeUpdateSite($type, $element, $folder = '', $location = '')
  {
    $db = Factory::getDBO();

    $query = $db->getQuery(true);

    $query->select('extension_id');
    $query->from('#__extensions');
    $query->where($db->quoteName('type').'='.$db->quote($type));
    $query->where($db->quoteName('element').'='.$db->quote($element));
    if ($folder) {
      $query->where($db->quoteName('folder').'='.$db->quote($folder));
    }

    $db->setQuery($query);

    $extension_id = '';
    try {
      $extension_id = $db->loadResult();
    } catch (\RuntimeException $e) {
      Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
      return false;
    }

    if ($extension_id) {

      $query->clear();

      $query->select('update_site_id');
      $query->from('#__update_sites_extensions');
      $query->where($db->quoteName('extension_id').'='.$db->quote($extension_id));

      $db->setQuery($query);

      $updatesite_id = array(); // can have several results
      try {
        $updatesite_id = $db->loadColumn();
      } catch (\RuntimeException $e) {
        Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        return false;
      }

      if (empty($updatesite_id)) {
        return false;
      }

      foreach ($updatesite_id as $id) {
        $query->clear();

        $query->delete($db->quoteName('#__update_sites'));
        $query->where($db->quoteName('update_site_id').' = '.$db->quote($id));
        if ($location) {
          $query->where($db->quoteName('location').' = '.$db->quote($location));
        }

        $db->setQuery($query);

        try {
          $db->execute();
        } catch (\RuntimeException $e) {
          Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
          return false;
        }
      }
    } else {
      return false;
    }

    return true;
  }

    // -------------------------------------------------------------------------
    // Security exploitation check (CVE fixed in 4.0.21)
    // -------------------------------------------------------------------------

    /**
     * Scans the site for evidence that the unauthenticated upload vulnerability
     * (fixed in 4.0.21) was exploited before this update was applied.
     *
     * @return array
     */
    private function _checkForExploitation(): array
    {
        $check = [
            'file_option_count'    => 0,
            'options_table_exists' => false,
            'upload_files'         => [],
            'legacy_files'         => [],
            'db_upload_count'      => 0,
            'db_table_exists'      => false,
            'suspicious_names'     => [],
            'invoices_unexpected'  => [],
            'protection_missing'   => [],
            'errors'               => [],
        ];

        $db     = Factory::getDbo();
        $prefix = $db->getPrefix();

        try {
            $tables = $db->getTableList();
        } catch (\Exception $e) {
            $check['errors'][] = 'Could not list database tables: ' . $e->getMessage();
            return $check;
        }

        // 1. Check whether any 'file' type option is defined
        if (in_array($prefix . 'j2store_options', $tables)) {
            $check['options_table_exists'] = true;
            try {
                $q = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__j2store_options'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('file'));
                $db->setQuery($q);
                $check['file_option_count'] = (int) $db->loadResult();
            } catch (\Exception $e) {
                $check['errors'][] = 'Options table query failed: ' . $e->getMessage();
            }
        }

        // 2. Count rows in the uploads table
        if (in_array($prefix . 'j2store_uploads', $tables)) {
            $check['db_table_exists'] = true;
            try {
                $q = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__j2store_uploads'));
                $db->setQuery($q);
                $check['db_upload_count'] = (int) $db->loadResult();
            } catch (\Exception $e) {
                $check['errors'][] = 'Uploads table query failed: ' . $e->getMessage();
            }
        }

        // 3. Scan media/j2store/uploads/ for user files
        $protectionFiles = ['.', '..', '.htaccess', 'web.config'];
        $uploadsDir      = JPATH_ROOT . '/media/j2store/uploads';

        if (!file_exists($uploadsDir . '/.htaccess')) {
            $check['protection_missing'][] = 'media/j2store/uploads/.htaccess';
        }
        if (!file_exists($uploadsDir . '/web.config')) {
            $check['protection_missing'][] = 'media/j2store/uploads/web.config';
        }

        if (is_dir($uploadsDir)) {
            $files = @scandir($uploadsDir);
            if ($files !== false) {
                foreach ($files as $f) {
                    if (in_array($f, $protectionFiles)) {
                        continue;
                    }
                    $check['upload_files'][] = $f;
                    if (preg_match('/\.(php\d*|phtml|phar)\./i', $f)) {
                        $check['suspicious_names'][] = $f;
                    }
                }
            }
        }

        // 4. Scan the legacy media/com_j2store/uploads/ path (J2Store v3 / early v4)
        $legacyDir = JPATH_ROOT . '/media/com_j2store/uploads';
        if (is_dir($legacyDir)) {
            if (!file_exists($legacyDir . '/.htaccess')) {
                $check['protection_missing'][] = 'media/com_j2store/uploads/.htaccess';
            }
            $files = @scandir($legacyDir);
            if ($files !== false) {
                foreach ($files as $f) {
                    if (in_array($f, $protectionFiles)) {
                        continue;
                    }
                    $check['legacy_files'][] = $f;
                    if (preg_match('/\.(php\d*|phtml|phar)\./i', $f)) {
                        $check['suspicious_names'][] = $f;
                    }
                }
            }
        }

        // 5. Scan media/j2store/invoices/ — should only ever contain PDFs
        $invoicesDir = JPATH_ROOT . '/media/j2store/invoices';
        if (is_dir($invoicesDir)) {
            $files = @scandir($invoicesDir);
            if ($files !== false) {
                foreach ($files as $f) {
                    if (in_array($f, $protectionFiles)) {
                        continue;
                    }
                    if (!preg_match('/\.pdf$/i', $f)) {
                        $check['invoices_unexpected'][] = $f;
                    }
                }
            }
        }

        return $check;
    }

    /**
     * Derives a verdict string from a _checkForExploitation() result array.
     *
     * @param  array  $check
     * @return string  'hacked' | 'suspicious' | 'clean' | 'unknown'
     */
    private function _getExploitationVerdict(array $check): string
    {
        if (!$check['options_table_exists']) {
            return 'unknown';
        }

        $hasUploadFiles = !empty($check['upload_files']) || $check['db_upload_count'] > 0;

        if ($check['file_option_count'] === 0 && $hasUploadFiles) {
            return 'hacked';
        }

        if (!empty($check['suspicious_names']) || !empty($check['invoices_unexpected'])) {
            return 'suspicious';
        }

        if (!empty($check['legacy_files'])) {
            return 'suspicious';
        }

        return 'clean';
    }

    /**
     * Removes foreign files from the uploads directories and truncates
     * #__j2store_uploads when exploitation is definitively confirmed.
     *
     * @param  array  $check  Result array from _checkForExploitation()
     * @return array
     */
    private function _removeExploitationFiles(array $check): array
    {
        $result = [
            'removed_files' => [],
            'failed_files'  => [],
            'db_truncated'  => false,
            'db_error'      => '',
        ];

        $protectionFiles = ['.', '..', '.htaccess', 'web.config'];

        $uploadsDir = JPATH_ROOT . '/media/j2store/uploads';
        if (is_dir($uploadsDir)) {
            foreach ((array) $check['upload_files'] as $filename) {
                if (in_array($filename, $protectionFiles)) {
                    continue;
                }
                $path = $uploadsDir . '/' . $filename;
                if (@unlink($path)) {
                    $result['removed_files'][] = 'media/j2store/uploads/' . $filename;
                } else {
                    $result['failed_files'][] = 'media/j2store/uploads/' . $filename;
                }
            }
        }

        $legacyDir = JPATH_ROOT . '/media/com_j2store/uploads';
        if (is_dir($legacyDir)) {
            foreach ((array) $check['legacy_files'] as $filename) {
                if (in_array($filename, $protectionFiles)) {
                    continue;
                }
                $path = $legacyDir . '/' . $filename;
                if (@unlink($path)) {
                    $result['removed_files'][] = 'media/com_j2store/uploads/' . $filename;
                } else {
                    $result['failed_files'][] = 'media/com_j2store/uploads/' . $filename;
                }
            }
        }

        if ($check['db_table_exists'] && $check['db_upload_count'] > 0) {
            try {
                $db = Factory::getDbo();
                $db->truncateTable('#__j2store_uploads');
                $result['db_truncated'] = true;
            } catch (\Exception $e) {
                $result['db_error'] = $e->getMessage();
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Template override CSRF check
    // -------------------------------------------------------------------------

    /**
     * Scans the default site template (and all site templates for plugin
     * overrides) for com_j2store template override files that appear to be
     * missing CSRF token protection.
     *
     * @return array  Array of ['file' => string, 'type' => 'php_form'|'js_form']
     */
    private function _checkTemplateOverrides(): array
    {
        $warnings = [];

        try {
            $db    = Factory::getDbo();
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
            $db->setQuery($query);
            $template = $db->loadResult();
        } catch (\Exception $e) {
            return $warnings;
        }

        if (!$template) {
            return $warnings;
        }

        $comOverridePath = JPATH_SITE . '/templates/' . $template . '/html/com_j2store';

        // Returns true when the file already contains any CSRF token call.
        $hasToken = static function (string $path): bool {
            $content = @file_get_contents($path);
            if ($content === false) {
                return true; // unreadable → skip
            }
            return strpos($content, 'form.token') !== false
                || strpos($content, 'getFormToken') !== false;
        };

        /* These files contain a PHP <form> block and need <?php echo JHtml::_('form.token'); ?>  before </form>. */
        $phpFormFiles = [
            'carts/default.php',
            'carts/default_calculator.php',
            'carts/default_coupon.php',
            'carts/default_shipping.php',
            'carts/default_voucher.php',
        ];

        /* These files build a hidden upload <form> inside a JavaScript string and need  <?php echo JSession::getFormToken(); ?>  as a hidden input. */
        $jsFormFiles = [
            'product/adminitem_configurableoptions.php',
            'product/adminitem_options.php',
            'product/item_configurableoptions.php',
            'product/item_options.php',
        ];

        /* These files pass cart item data as a PHP array and need  JSession::getFormToken() => '1'  added to that array. */
        $arrayFormFiles = [
            'carts/default_items.php',
        ];

        foreach ($phpFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'php_form',
                ];
            }
        }

        foreach ($jsFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'js_form',
                ];
            }
        }

        foreach ($arrayFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'array_form',
                ];
            }
        }

        // Template overrides — search all site templates.
        // Override path: templates/<site-template>/html/com_j2store/templates/<subtemplate>/
        $pluginFiles = [
            'cart.php'                        => 'php_form',
            'default_configurableoptions.php' => 'js_form',
            'default_options.php'             => 'js_form',
            'view_configurableoptions.php'    => 'js_form',
            'view_options.php'                => 'js_form',
        ];

        $pluginDirs = glob(JPATH_SITE . '/templates/*/html/com_j2store/templates', GLOB_ONLYDIR);
        if ($pluginDirs) {
            foreach ($pluginDirs as $pluginDir) {
                $subtemplates = glob($pluginDir . '/*', GLOB_ONLYDIR);
                if (!$subtemplates) {
                    continue;
                }
                foreach ($subtemplates as $subtemplateDir) {
                    foreach ($pluginFiles as $filename => $type) {
                        $full = $subtemplateDir . '/' . $filename;
                        if (file_exists($full) && !$hasToken($full)) {
                            $warnings[] = [
                                'file' => ltrim(str_replace(JPATH_SITE, '', $full), '/\\'),
                                'type' => $type,
                            ];
                        }
                    }
                }
            }
        }

        return $warnings;
    }

    /**
     * Outputs an HTML warning block listing template override files that are
     * missing CSRF token protection, with instructions for each type.
     *
     * @param  array  $warnings  Result of _checkTemplateOverrides()
     */
    private function _renderTemplateOverrideWarnings(array $warnings): void
    {
        if (empty($warnings)) {
            return;
        }

        $phpFormFiles   = [];
        $jsFormFiles    = [];
        $arrayFormFiles = [];

        foreach ($warnings as $w) {
            if ($w['type'] === 'php_form') {
                $phpFormFiles[] = $w['file'];
            } elseif ($w['type'] === 'js_form') {
                $jsFormFiles[] = $w['file'];
            } elseif ($w['type'] === 'array_form') {
                $arrayFormFiles[] = $w['file'];
            }
        }
        ?>
        <div style="margin-top:20px;padding:15px;border-radius:4px;background:#fff3cd;border:2px solid #ffc107;color:#856404;">
            <h3 style="margin-top:0;">&#x26A0; Template Override CSRF Check</h3>
            <p>The following template override files are present on this site but appear to be
               missing CSRF (cross-site request forgery) token protection. <strong>These files must be updated manually.</strong></p>

            <?php if (!empty($phpFormFiles)): ?>
                <p><strong>In the following file(s), add <code>&lt;?php echo JHtml::_('form.token'); ?&gt;</code>
                   immediately before each <code>&lt;/form&gt;</code> closing tag in these files:</strong></p>
                <ul>
                    <?php foreach ($phpFormFiles as $f): ?>
                        <li><code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($jsFormFiles)): ?>
                <p><strong>In the following file(s), locate the JavaScript hidden-upload form string and add
                   a hidden input whose <code>name</code> attribute is the output of
                   <code>&lt;?php echo JSession::getFormToken(); ?&gt;</code>:</strong></p>
                <ul>
                    <?php foreach ($jsFormFiles as $f): ?>
                        <li><code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <p>Example of the corrected JavaScript form string:</p>
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;">$('body').prepend(
  '&lt;form enctype="multipart/form-data" id="form-upload" style="display:none;"&gt;'
  + '&lt;input type="file" name="file" /&gt;'
  + '&lt;input type="hidden" name="&lt;?php echo JSession::getFormToken(); ?&gt;" value="1" /&gt;'
  + '&lt;/form&gt;');</pre>
            <?php endif; ?>

            <?php if (!empty($arrayFormFiles)): ?>
                <p><strong>In the following file(s), add <code>JSession::getFormToken() =&gt; '1'</code>
                   to the cart item data array. Change:</strong></p>
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;">'cartitem_id' =&gt; $item-&gt;cartitem_id</pre>
                <p><strong>to:</strong></p>
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;">'cartitem_id' =&gt; $item-&gt;cartitem_id, JSession::getFormToken() =&gt; '1'</pre>
                <ul>
                    <?php foreach ($arrayFormFiles as $f): ?>
                        <li><code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p>After updating the override files, clear the Joomla cache.</p>
            <p>Find those reminders in the J2Commerce dashboard.</p>
        </div>
        <?php
    }

    /**
     * Outputs an HTML security check block that Joomla captures as the
     * extension_message shown at the end of installation.
     *
     * @param  array       $check
     * @param  string      $verdict
     * @param  array|null  $cleanup
     */
    private function _renderSecurityCheck(array $check, string $verdict, ?array $cleanup): void
    {
        $styles = [
            'hacked'     => 'background:#f8d7da;border:2px solid #f5c6cb;color:#721c24;',
            'suspicious' => 'background:#fff3cd;border:2px solid #ffc107;color:#856404;',
            'clean'      => 'background:#d4edda;border:2px solid #c3e6cb;color:#155724;',
            'unknown'    => 'background:#e2e3e5;border:2px solid #d6d8db;color:#383d41;',
        ];
        $style = $styles[$verdict] ?? $styles['unknown'];
        ?>
        <div style="margin-top:20px;padding:15px;border-radius:4px;<?php echo $style; ?>">
            <h3 style="margin-top:0;">
                <?php if ($verdict === 'hacked'): ?>&#x26A0; Security Alert: Exploitation Detected
                <?php elseif ($verdict === 'suspicious'): ?>&#x26A0; Security Warning: Suspicious Files Found
                <?php elseif ($verdict === 'clean'): ?>&#x2713; Security Check: No Exploitation Detected
                <?php else: ?>Security Check: Could Not Determine Status
                <?php endif; ?>
            </h3>

            <?php if ($verdict === 'hacked'): ?>
                <p><strong>This site has been exploited.</strong> Files were uploaded through
                the unauthenticated upload endpoint fixed in this release, and no
                &ldquo;File&rdquo; type product option has ever been configured &mdash;
                meaning all uploads in the database and on disk are foreign.</p>

                <?php if ($cleanup !== null): ?>
                    <?php if (!empty($cleanup['removed_files'])): ?>
                        <p><strong style="color:#155724;">&#x2713; <?php echo count($cleanup['removed_files']); ?> foreign file(s) were automatically removed:</strong><br>
                            <code><?php echo htmlspecialchars(implode(', ', array_slice($cleanup['removed_files'], 0, 20)), ENT_QUOTES, 'UTF-8'); ?>
                                <?php echo count($cleanup['removed_files']) > 20 ? ' &hellip; and ' . (count($cleanup['removed_files']) - 20) . ' more' : ''; ?>
                            </code>
                        </p>
                    <?php endif; ?>
                    <?php if ($cleanup['db_truncated']): ?>
                        <p><strong style="color:#155724;">&#x2713; The <code>#__j2store_uploads</code> database table was cleared.</strong></p>
                    <?php endif; ?>
                    <?php if (!empty($cleanup['failed_files'])): ?>
                        <p><strong>&#x26A0; <?php echo count($cleanup['failed_files']); ?> file(s) could not be deleted (check directory permissions):</strong><br>
                            <code><?php echo htmlspecialchars(implode(', ', $cleanup['failed_files']), ENT_QUOTES, 'UTF-8'); ?></code>
                        </p>
                    <?php endif; ?>
                    <?php if ($cleanup['db_error'] !== ''): ?>
                        <p><strong>&#x26A0; Database table could not be cleared:</strong>
                            <code><?php echo htmlspecialchars($cleanup['db_error'], ENT_QUOTES, 'UTF-8'); ?></code>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($check['legacy_files'])): ?>
                    <p><strong>Additional action required:</strong> The legacy
                    <code>media/com_j2store/uploads/</code> directory (J2Store v3 / early v4)
                    also contained foreign files. Please remove them manually.</p>
                <?php endif; ?>

            <?php elseif ($verdict === 'suspicious'): ?>
                <p><strong>Suspicious files were found.</strong> A &ldquo;File&rdquo; type
                product option is configured so some uploads may be legitimate, but the
                following items require manual review:</p>

            <?php elseif ($verdict === 'clean'): ?>
                <p>No evidence of exploitation was found. The upload folder contains no
                user files and the database uploads table is empty. This vulnerability
                was not exploited on this site prior to this update.</p>

            <?php else: ?>
                <p>The database could not be queried to determine whether this site
                was affected. Please review <code>media/j2store/uploads/</code> manually.</p>
            <?php endif; ?>

            <?php if (!empty($check['upload_files'])): ?>
                <p><strong><?php echo count($check['upload_files']); ?> file(s) in <code>media/j2store/uploads/</code>:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', array_slice($check['upload_files'], 0, 20)), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo count($check['upload_files']) > 20 ? ' &hellip; and ' . (count($check['upload_files']) - 20) . ' more' : ''; ?>
                    </code>
                </p>
            <?php endif; ?>

            <?php if ($check['db_upload_count'] > 0): ?>
                <p><strong><?php echo $check['db_upload_count']; ?> record(s) in <code>#__j2store_uploads</code></strong> table.</p>
            <?php endif; ?>

            <?php if (!empty($check['suspicious_names'])): ?>
                <p><strong style="color:red;">&#x26A0; Suspicious filenames (double-extension pattern):</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['suspicious_names']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['legacy_files'])): ?>
                <p><strong><?php echo count($check['legacy_files']); ?> file(s) in legacy <code>media/com_j2store/uploads/</code>:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', array_slice($check['legacy_files'], 0, 20)), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo count($check['legacy_files']) > 20 ? ' &hellip; and ' . (count($check['legacy_files']) - 20) . ' more' : ''; ?>
                    </code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['invoices_unexpected'])): ?>
                <p><strong>Unexpected non-PDF file(s) in <code>media/j2store/invoices/</code>:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['invoices_unexpected']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['protection_missing'])): ?>
                <p><strong>&#x26A0; Missing directory protection files:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['protection_missing']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['errors'])): ?>
                <p><em>Check errors: <?php echo htmlspecialchars(implode('; ', $check['errors']), ENT_QUOTES, 'UTF-8'); ?></em></p>
            <?php endif; ?>
        </div>
        <?php
    }
}

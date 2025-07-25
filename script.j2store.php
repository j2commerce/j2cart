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
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

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
  protected $componentTitle = 'J2Commerce Joomla Shopping Cart';

  protected $minimumPHPVersion = '7.4.0';
  protected $minimumJoomlaVersion = '4.0.0';
  protected $maximumJoomlaVersion = '5.99.99';

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
  protected $installation_queue = array(
    'modules' => array(
      'admin' => array(
        'mod_j2commerce_chart' => array('', 0), // we just want to install the module
        'mod_j2commerce_checklist' => array('', 0), // we just want to install the module
        'j2store_stats_mini' => array('j2store-module-position-1', 1),
        'j2store_orders' => array('j2store-module-position-4', 1),
        'j2store_stats' => array('j2store-module-position-5', 1),
        'j2store_menu' => array('status', 1)
      ),
      'site' => array(
        'mod_j2store_currency' => array('left', 0),
        'mod_j2store_cart' => array('left', 0),
        'mod_j2store_products_advanced' => array('j2store-product-module', 0)
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
        'app_bootstrap5' => 1
      )
    )
  );

    function _deleteFolder($folderPath) {
        if (!is_dir($folderPath)) {
            return false;
        }

        $files = array_diff(scandir($folderPath), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? $this->_deleteFolder($filePath) : unlink($filePath);
        }
        return rmdir($folderPath);
    }

  public function postflight($type, $parent)
  {
      parent::postflight($type, $parent);

      // Remove extra column from the variants table

      $db = Factory::getDbo();

      try {
          $alterQuery = "ALTER TABLE `#__j2store_variants` DROP `campaign_variant_id`";
          $db->setQuery($alterQuery);
          $db->execute();
      } catch (\Exception $e) {
          // Can fail if the column does not exist
      }

      // TODO remove once we are done moving files from j2store to com_j2commerce
      $source = JPATH_SITE . '/media/j2store/j2commerce';
      $destination = JPATH_SITE . '/media/com_j2commerce';

      $move_files = true;
      if (is_dir($destination)) {
          // make sure we remove the old folder to make sure we add all new files
          if (!$this->_deleteFolder($destination)) {
              $move_files = false;
              Factory::getApplication()->enqueueMessage('Could not delete J2Commerce media folder');
          }
      }

      if ($move_files) {
          if (!rename($source, $destination)) {
              Factory::getApplication()->enqueueMessage('Could not move J2Commerce media files');
          }
      }

      $dashboard_positions = [];

      $dashboard_positions[] = 'j2store-module-position-1';
      $dashboard_positions[] = 'j2store-module-position-2';
      $dashboard_positions[] = 'j2store-module-position-3';
      $dashboard_positions[] = 'j2store-module-position-4';
      $dashboard_positions[] = 'j2store-module-position-5';

      if ($type === 'update') {
          // Remove the old chart module
          if ($this->isModuleInAnyPositions('mod_j2store_chart', $dashboard_positions)) {
              $this->removeModuleFromAnyPositions('mod_j2store_chart', $dashboard_positions);
          }
      }

      // New charts
      if (!$this->isModuleInAnyPositions('mod_j2commerce_chart', $dashboard_positions)) {
          $this->addModuleToPosition('mod_j2commerce_chart', 'j2store-module-position-3', ['chart_type' => ['daily', 'monthly', 'yearly']]);
      }

      // Quick Start Checklist
      if (!$this->isModuleInAnyPositions('mod_j2commerce_checklist', $dashboard_positions)) {
          $this->addModuleToPosition('mod_j2commerce_checklist', 'j2store-module-position-1');
      }
  }

    /**
     * Add modules to the dashboard.
     *
     * @param   string  $position   The name of the position to add the module to
     * @param   string  $module     The name of the admin module
     * @param   array   $params     The list of parameters to set to the module
     *
     * @return  void
     *
     * @throws  Exception
     */
    private function addModuleToPosition(string $module_name, string $position, array $module_params = [])
    {
        $model  = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $module = [
            'id'         => 0,
            'asset_id'   => 0,
            'language'   => '*',
            'note'       => '',
            'published'  => 1,
            'assignment' => 0,
            'client_id'  => 1,
            'showtitle'  => 0,
            'content'    => '',
            'module'     => $module_name,
            'position'   => $position,
        ];

        // Load the module's language file
        Factory::getLanguage()->load($module_name, JPATH_ADMINISTRATOR);

        $module['title']  = Text::_(strtoupper($module_name));
        $module['access'] = (int) Factory::getApplication()->get('access', 1);
        $module['params'] = array_merge([
            'menutype' => '*',
            'style'    => 'System-none',
        ], $module_params);

        if (!$model->save($module)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_INSTALLER_ERROR_COMP_INSTALL_FAILED_TO_CREATE_DASHBOARD', $model->getError()));
        }
    }

    /**
     * Does at least one instance of a given module exist in the dashboard
     *
     * @param   string  $position   The position to check. If no position is mentionned, all positions are checked
     * @param   string  $module     The module to check
     *
     * @return  bool
     */
    private function isModuleInAnyPositions(string $module, array $positions = []): bool
    {
        if (empty($positions)) {
            return 0;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules'))
            ->where([
                $db->quoteName('module') . ' = :module',
                $db->quoteName('client_id') . ' = ' . $db->quote(1),
                $db->quoteName('published') . ' = ' . $db->quote(1)
            ])
            ->whereIn($db->quoteName('position'), $positions, ParameterType::STRING)
            ->bind(':module', $module, ParameterType::STRING);

        $modules = $db->setQuery($query)->loadResult() ?: 0;

        return $modules > 0;
    }

    /**
     * Remove a module from the dashboard
     *
     * @param   string  $module_name   The name of the module to remove
     *
     * @return  void
     */
    private function removeModuleFromAnyPositions(string $module_name, array $positions = [])
    {
        if (empty($positions)) {
            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__modules'))
            ->where([
                $db->quoteName('module') . ' = :module',
                $db->quoteName('client_id') . ' = ' . $db->quote(1),
                $db->quoteName('published') . ' = ' . $db->quote(1)
            ])
            ->whereIn($db->quoteName('position'), $positions, ParameterType::STRING)
            ->bind(':module', $module_name, ParameterType::STRING);

        try {
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Could not uninstall ' . $module_name);
        }
    }

  public function preflight($type, $parent)
  {
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
        $xml = Factory::getXML($xmlfile);
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

      //----end of file removal//
      //all set. Lets rock..

      return true;
    }

    return false;
  }

  public function uninstall($parent)
  {
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
}

<?php
/**
 * @package J2Store
 * @copyright Copyright (C) 2014-2019 Weblogicx India. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;

/**
 * J2Store Installer Script
 *
 * This is a standard Joomla installer script that does NOT depend on F0F at class-definition time.
 * F0F is installed early in postflight() and then loaded before any F0F-dependent functionality is
 * used. This ensures that:
 *  - Error reporting can be configured in preflight() before any F0F code runs.
 *  - Installation succeeds even when the FOF compatibility plugin is disabled.
 */
class Com_J2storeInstallerScript extends InstallerScript
{
    /**
     * The component's name
     *
     * @var string
     */
    protected $componentName = 'com_j2store';

    /**
     * The title of the component (printed on installation and uninstallation messages)
     *
     * @var string
     */
    protected $componentTitle = 'J2Store/J2Commerce Joomla Shopping Cart';

    /**
     * The maximum Joomla version allowed
     *
     * @var string
     */
    protected $maximumJoomlaVersion = '6.99.99'; // not part of InstallerScript convention but checked in preflight()

    /**
     * Error reporting level saved by _suppressErrors(), restored by _restoreErrors().
     * Kept for backward compatibility; no longer used.
     *
     * @var int|null
     */
    //protected $originalErrorReporting;

    /**
     * Whether _suppressErrors() installed a handler that _restoreErrors() must pop.
     * Kept for backward compatibility; no longer used.
     *
     * @var bool
     */
    protected $errorHandlerInstalled = false;

    /**
     * Obsolete files to delete on install/update (Joomla InstallerScript convention – paths relative to JPATH_ROOT with leading /)
     *
     * @var array
     */
    protected $deleteFiles = [];

    /**
     * Obsolete folders to delete on install/update (Joomla InstallerScript convention – paths relative to JPATH_ROOT with leading /)
     *
     * @var array
     */
    protected $deleteFolders = [];

    /**
     * Result of the security exploitation check, populated by _checkForExploitation()
     * and consumed by _renderPostInstallation().
     *
     * @var array|null
     */
    private $securityCheckResult = null;

    /**
     * Result of the automatic cleanup performed when exploitation is confirmed,
     * populated by _removeExploitationFiles() and consumed by _renderSecurityCheck().
     *
     * @var array|null
     */
    private $securityCleanupResult = null;

    /**
     * Template override files missing CSRF tokens, populated by _checkTemplateOverrides()
     * and consumed by _renderTemplateOverrideWarnings().
     *
     * @var array|null
     */
    private $templateOverrideWarnings = null;

    /**
     * Extra modules and plugins to install on component installation / update,
     * and to remove on component uninstallation.
     *
     * @var array
     */
    protected $installation_queue = array(
        'modules' => array(
            'admin' => array(
                'j2store_chart'      => array('j2store-module-position-3', 1),
                'j2store_stats_mini' => array('j2store-module-position-1', 1),
                'j2store_orders'     => array('j2store-module-position-4', 1),
                'j2store_stats'      => array('j2store-module-position-5', 1),
                'j2store_menu'       => array('status', 1),
            ),
            'site' => array(
                'mod_j2store_currency' => array('left', 0),
                'mod_j2store_cart'     => array('left', 0),
            ),
        ),
        'plugins' => array(
            'content' => array('j2store' => 1),
            'system'  => array(
                'j2store'     => 1,
                'j2pagecache' => 0,
                'j2canonical' => 0,
            ),
            'search'    => array('j2store' => 0),
            'finder'    => array('j2store' => 0),
            'user'      => array('j2userregister' => 0),
            'installer' => array('j2store' => 1),
            'j2store'   => array(
                'shipping_free'         => 0,
                'shipping_standard'     => 1,
                'payment_cash'          => 1,
                'payment_moneyorder'    => 1,
                'payment_banktransfer'  => 1,
                'payment_paypal'        => 1,
                'report_products'       => 1,
                'payment_sagepayform'   => 1,
                'report_itemised'       => 1,
                'app_localization_data' => 1,
                'app_diagnostics'       => 1,
                'app_currencyupdater'   => 1,
                'app_flexivariable'     => 1,
                'app_schemaproducts'    => 1,
                'app_bootstrap3'        => 0,
                'app_bootstrap4'        => 0,
                'app_bootstrap5'        => 1,
            ),
        ),
    );

    public function __construct($installer)
    {
        $this->minimumJoomla = '5.0.0';
    }

    // -------------------------------------------------------------------------
    // Joomla installer hook methods
    // -------------------------------------------------------------------------

    /**
     * Joomla pre-flight event. Runs before Joomla installs or updates the component.
     * No F0F dependency – error reporting is suppressed here before anything F0F-related is touched.
     *
     * @param  string $type   install | update | discover_install
     * @param  InstallerAdapter $parent Installer adapter
     *
     * @return bool
     */
    public function preflight($type, $parent)
    {
        $this->_log("preflight() started – type={$type}, PHP=" . PHP_VERSION . ", Joomla=" . JVERSION);

        if ($type === 'uninstall') {
            $this->_log('preflight() – uninstall, skipping checks');
            return true;
        }

        // Suppress all PHP deprecated/warning output immediately so that nothing can corrupt
        // the AJAX JSON response that Joomla sends back to the browser.
        $this->_suppressErrors();

        // checks minimum PHP and Joomla versions and that an upgrade is performed
        if (!parent::preflight($type, $parent)) {
            $this->_log('preflight() – parent::preflight() returned false (PHP/Joomla version check or downgrade block)', 'WARNING');
            return false;
        }

        // Prevent updating from a J2Store release older than 4.0.5.
        // parent::preflight() sets $this->extension via $parent->getName(), so getItemArray() is safe to use here.
        if ($type === 'update') {
            $installed = $this->getItemArray('manifest_cache', '#__extensions', 'name', $this->extension);

            if (isset($installed['version']) && version_compare($installed['version'], '4.0.5', 'lt')) {
                $this->_log('preflight() – blocked: installed version ' . $installed['version'] . ' is too old for direct update', 'ERROR');
                Factory::getApplication()->enqueueMessage(
                    'You cannot update directly from J2Store ' . $installed['version'] . '. '
                    . 'Please update to J2Store 4.0.5 or later first.',
                    'error'
                );
                return false;
            }

            $this->_log('preflight() – update from version ' . ($installed['version'] ?? 'unknown'));
        }

        if (!empty($this->maximumJoomlaVersion) && !version_compare(JVERSION, $this->maximumJoomlaVersion, 'le')) {
            $this->_log('preflight() – blocked: Joomla ' . JVERSION . ' exceeds maximum ' . $this->maximumJoomlaVersion, 'ERROR');
            Log::add(
                "You need Joomla! {$this->maximumJoomlaVersion} or earlier to install this component",
                Log::WARNING,
                'jerror'
            );
            return false;
        }

        // Remove the old j2store component update site that is now obsolete
        $this->_removeUpdateSite('component', 'com_j2store', '', 'https://cdn.j2store.net/j2store4.xml');
        $this->_log('preflight() – removed obsolete update site (cdn.j2store.net)');

        // Check that cURL is present
        if (!function_exists('curl_init') || !is_callable('curl_init')) {
            $this->_log('preflight() – blocked: cURL not available', 'ERROR');
            Log::add(
                'cURL extension is not enabled in your PHP installation. Please contact your hosting service provider',
                Log::WARNING,
                'jerror'
            );
            return false;
        }

        // Check that JSON is present
        if (!function_exists('json_encode')) {
            $this->_log('preflight() – blocked: JSON extension not available', 'ERROR');
            Log::add(
                'JSON extension is not enabled in your PHP installation. Please contact your hosting service provider',
                Log::WARNING,
                'jerror'
            );
            return false;
        }

        // Block installation if other FOF library versions are present – they cause class-loading conflicts.
        if (!$this->_checkForFofConflicts()) {
            $this->_log('preflight() – blocked: FOF conflict detected', 'ERROR');
            return false;
        }

        // Automatically enable the appropriate Joomla backward-compatibility plugin and turn on
        // all of its options.  Under Joomla 5 this is plg_behaviour_compat; under Joomla 6 it is
        // plg_behaviour_compat6.  Returns false when the plugin was just enabled or its options
        // were just fixed (restart required) OR when the plugin is not installed / DB error.
        // enableCompatWithOptions() returns false only when the plugin is not installed
        // or a DB error occurred.  The compatJustEnabled restart path returns true above.
        if (!$this->enableCompatWithOptions()) {
            $this->_log('preflight() – blocked: enableCompatWithOptions() returned false (plugin not installed or DB error)', 'ERROR');
            return false;
        }

        // Remove obsolete files and folders BEFORE Joomla copies the new package files,
        // otherwise stale files can break the installation.
        $this->removeFiles();
        $this->_log('preflight() – removeFiles() completed');

        // Reset OPcache so the server picks up replaced .php files
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->_log('preflight() – OPcache reset');
        }

        $this->_log('preflight() – completed successfully');
        return true;
    }

    /**
     * Runs after install, update or discover_update.
     *
     * Order of operations:
     *  1. Install F0F library (no F0F dependency)
     *  2. Load F0F from the freshly installed location
     *  3. Load F0F helper classes (F0FDatabaseInstaller etc.)
     *  4. Update database schema
     *  5. Install bundled sub-extensions (modules, plugins)
     *  6. Install localisation data
     *  7. Apply J2Store-specific schema patches
     *  8. Render installation status output
     *  9. Restore error reporting
     *
     * @param  string $type   install | update | discover_update
     * @param  object $parent Installer adapter
     */
    public function postflight($type, $parent)
    {
        $this->_log("postflight() started – type={$type}");

        // Nothing to do on uninstall – _renderPostUninstallation() is called directly from uninstall().
        if ($type === 'uninstall') {
            $this->_log('postflight() – uninstall, skipping');
            return;
        }

        // Re-install error suppression specifically for postflight.
        $this->_suppressErrors(true);

        try {
            $this->_runPostflight($type, $parent);
        } catch (\Throwable $e) {
            $this->_log('postflight() – UNCAUGHT EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
            $this->_log('postflight() – Stack trace: ' . $e->getTraceAsString(), 'ERROR');
            // Log the error for the administrator but do not re-throw.
            try {
                Log::add('J2Store postflight error: ' . $e->getMessage(), Log::WARNING, 'jerror');
            } catch (\Throwable $logEx) {
                // Even logging failed; silently continue.
            }
        }

        $this->_log('postflight() – completed');
    }

    /**
     * Internal postflight worker – extracted so the public postflight() can wrap it in
     * a Throwable catch without duplicating the entire method body.
     *
     * @param  string $type   install | update | discover_update
     * @param  object $parent Installer adapter
     */
    private function _runPostflight(string $type, $parent): void
    {
        $this->_log('_runPostflight() – started');
        $mainInstaller = $parent->getParent();

        $obLevelBefore = ob_get_level();
        ob_start();

        try {
            // ---- Restart gate ----
            // The compat plugin was enabled in the DB during preflight() but was NOT active
            // at request boot, so JInput and other legacy aliases are unavailable.  Skip all
            // F0F-dependent steps and output a loud restart notice instead.  The notice is
            // captured by the ob buffer below and set as extension_message — the only channel
            // that is guaranteed to be visible to the user in Joomla's installer UI.
            /*if (self::$compatJustEnabled) {
                $this->_log('_runPostflight() – compatJustEnabled: outputting restart notice, skipping F0F steps');
                echo $this->_buildRestartNotice();
                return;
            }*/

            // ---- Step 1: Install F0F FIRST (pure Joomla Installer – no F0F dependency) ----
            $this->_log('_runPostflight() – Step 1: installing F0F library');
            $fofInstallationStatus = $this->_installFOF($parent);
            $this->_log('_runPostflight() – Step 1: F0F install result=' . ($fofInstallationStatus ? 'true' : 'false'));

            // ---- Step 2: Load F0F now that it is installed ----
            $this->_log('_runPostflight() – Step 2: loading F0F');
            $this->_loadF0F();
            $this->_log('_runPostflight() – Step 2: F0F_INCLUDED=' . (defined('F0F_INCLUDED') ? constant('F0F_INCLUDED') : 'NOT DEFINED'));

            // ---- Step 3: Load F0F helper classes ----
            $this->_log('_runPostflight() – Step 3: loading F0F helpers');
            $this->_loadF0FHelpers();
            $this->_log('_runPostflight() – Step 3: F0FDatabaseInstaller=' . (class_exists('F0FDatabaseInstaller', false) ? 'loaded' : 'NOT loaded'));

            // ---- Step 4: Update database schema ----
            if (class_exists('F0FDatabaseInstaller', false)) {
                $this->_log('_runPostflight() – Step 4: running F0FDatabaseInstaller::updateSchema()');
                $dbInstaller = new F0FDatabaseInstaller(array(
                    'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/' . $this->componentName . '/sql/xml',
                ));
                $dbInstaller->updateSchema();
                $this->_log('_runPostflight() – Step 4: schema update completed');
            } else {
                $this->_log('_runPostflight() – Step 4: SKIPPED – F0FDatabaseInstaller not available', 'WARNING');
            }

            // ---- Step 5: Install bundled sub-extensions ----
            $this->_log('_runPostflight() – Step 5: installing sub-extensions');
            $status = $this->_doInstallSubextensions($parent);
            $this->_log('_runPostflight() – Step 5: sub-extensions done – modules=' . count($status->modules) . ', plugins=' . count($status->plugins));

            // ---- Step 6: Install localisation data ----
            $this->_log('_runPostflight() – Step 6: installing localisation data');
            $this->_installLocalisation($parent);
            $this->_log('_runPostflight() – Step 6: localisation done');

            // ---- Step 7: J2Store-specific schema patches ----
            $this->_log('_runPostflight() – Step 7: applying schema patches');
            $db = Factory::getDbo();

            try {
                $db->setQuery("ALTER TABLE `#__j2store_variants` DROP `campaign_variant_id`"); // TODO keep?
                $db->execute();
                $this->_log('_runPostflight() – Step 7: dropped campaign_variant_id column');
            } catch (\Exception $e) {
                // Can fail if the column does not exist – that is fine
                $this->_log('_runPostflight() – Step 7: campaign_variant_id patch skipped (' . $e->getMessage() . ')');
            }

            // ---- Step 7b: Decode double-escaped checkout layout fields ----
            // Versions prior to 4.1.5 applied htmlspecialchars() twice when rendering the
            // checkout layout textareas, so each save added another layer of HTML encoding.
            // Decode each field repeatedly until the value stabilises.
            $this->_log('_runPostflight() – Step 7b: decoding checkout layout fields');
            $layoutKeys = ['store_billing_layout', 'store_shipping_layout', 'store_payment_layout'];
            foreach ($layoutKeys as $key) {
                try {
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('config_meta_value'))
                        ->from($db->quoteName('#__j2store_configurations'))
                        ->where($db->quoteName('config_meta_key') . ' = ' . $db->quote($key));
                    $db->setQuery($query);
                    $raw = $db->loadResult();
                    if ($raw !== null) {
                        $decoded = $raw;
                        // Repeatedly decode until the value no longer changes.
                        do {
                            $prev    = $decoded;
                            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        } while ($decoded !== $prev);
                        if ($decoded !== $raw) {
                            $updateQuery = $db->getQuery(true)
                                ->update($db->quoteName('#__j2store_configurations'))
                                ->set($db->quoteName('config_meta_value') . ' = ' . $db->quote($decoded))
                                ->where($db->quoteName('config_meta_key') . ' = ' . $db->quote($key));
                            $db->setQuery($updateQuery);
                            $db->execute();
                            $this->_log("_runPostflight() – Step 7b: decoded {$key}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->_log("_runPostflight() – Step 7b: failed for {$key}: " . $e->getMessage(), 'WARNING');
                }
            }
            $this->_log('_runPostflight() – Step 7b: done');

            // ---- Step 7c: Security exploitation check ----
            $this->_log('_runPostflight() – Step 7c: running exploitation check');
            $this->securityCheckResult = $this->_checkForExploitation();
            $verdict = $this->_getExploitationVerdict($this->securityCheckResult);
            $this->_log('_runPostflight() – Step 7c: verdict=' . $verdict);
            if ($verdict === 'hacked') {
                $this->_log('_runPostflight() – Step 7c: verdict is hacked, removing exploitation files');
                $this->securityCleanupResult = $this->_removeExploitationFiles($this->securityCheckResult);
                $this->_log('_runPostflight() – Step 7c: cleanup done, removed=' . count($this->securityCleanupResult['removed_files']) . ' failed=' . count($this->securityCleanupResult['failed_files']));
            }

            // ---- Step 7d: Template override CSRF check ----
            $this->_log('_runPostflight() – Step 7d: running template override check');
            $this->templateOverrideWarnings = $this->_checkTemplateOverrides();
            $this->_log('_runPostflight() – Step 7d: found ' . count($this->templateOverrideWarnings) . ' affected file(s)');

            // ---- Step 8: Render post-installation status ----
            $this->_log('_runPostflight() – Step 8: rendering status HTML');
            $this->_renderPostInstallation($status, $fofInstallationStatus, $parent);
            $this->_log('_runPostflight() – Step 8: done');
        } finally {
            if (ob_get_level() > $obLevelBefore) {
                $capturedHtml = ob_get_clean();
                $mainInstaller->set('extension_message', $capturedHtml ?: '');
            }
            $this->_log('_runPostflight() – finished (ob captured and set as extension_message)');
        }
    }

    /**
     * Runs on uninstallation.
     * Database tables are intentionally left in place so that store data is preserved
     * if the extension is ever reinstalled.
     *
     * @param object $parent Installer adapter
     */
    public function uninstall($parent)
    {
        $this->_log('uninstall() – started');

        // Safety net: disable all J2Store modules and plugins at the DB level
        // BEFORE files are removed. This catches bundled extensions, third-party
        // J2Store plugins, and survives any failure in _doUninstallSubextensions().
        // Without this, remaining enabled extensions call J2Store:: after the
        // component files are gone, causing a fatal "Class J2Store not found".
        $this->_disableAllJ2StoreExtensions();

        // Uninstall bundled modules and plugins
        $status = $this->_doUninstallSubextensions($parent);

        // Show the post-uninstallation page
        $this->_renderPostUninstallation($status, $parent);
        $this->_log('uninstall() – completed');
    }

    /**
     * Unpublishes all J2Store modules and disables all J2Store plugins directly
     * in the database. Called at the very start of uninstall() so that no
     * J2Store-dependent extension can fire after the component files are removed,
     * regardless of whether _doUninstallSubextensions() succeeds or fails, and
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

    // -------------------------------------------------------------------------
    // F0F bootstrapping helpers
    // -------------------------------------------------------------------------

    /**
     * Conditionally suppresses PHP errors for the remainder of the current HTTP request,
     * based on the Joomla global error-reporting setting:
     *
     *  - 'none'    : Joomla already told PHP to silence everything; nothing to do, no message.
     *  - 'default' : Joomla defers to php.ini; we cannot override it safely, so we just notify
     *                the admin that php.ini must have a safe error_reporting value.
     *  - anything else (Simple / Maximum / Development …): override PHP error reporting to 0
     *                (none) and push a custom error handler so Joomla's own handler cannot
     *                convert warnings/notices into exceptions.
     *
     * Suppression is intentionally NEVER restored within this request.  Joomla's installer
     * controller calls exit() after postflight() returns, which triggers any registered
     * shutdown functions before output buffers are fully flushed.  Restoring error reporting
     * at that point re-enables error output during the buffer-flush phase.  Since each PHP
     * request starts with its own error-reporting state, leaving it suppressed for the rest
     * of this one request is completely safe.
     */
    private function _suppressErrors($report = false): void
    {
        // Read the Joomla global error-reporting preference.
        $config               = Factory::getApplication()->getConfig();
        $joomlaErrorReporting = $config->get('error_reporting', 'default');

        // PHP error reporting is already silent; nothing to do.
        if ($joomlaErrorReporting === 'none') {
            return;
        }

        // We cannot safely change this; just inform the admin after complete installation.
        if ($report) {
            if ($joomlaErrorReporting === 'default') {
                Factory::getApplication()->enqueueMessage(
                    'Joomla error reporting is set to <strong>System Default</strong>. '
                    . 'If your <strong>php.ini</strong> does not already contain '
                    . '<code>error_reporting = E_ALL &amp; ~E_DEPRECATED &amp; ~E_STRICT</code> '
                    . '(or a lower level), PHP deprecation notices will appear after installation. You can set error reporting to <strong>None</strong> otherwise.',
                    'notice'
                );
            } else {
                Factory::getApplication()->enqueueMessage(
                    'Joomla error reporting is set to <strong>Simple</strong> or <strong>Maximum</strong>. '
                    . 'PHP deprecation notices will appear after installation. '
                    . 'Set error reporting to <strong>None</strong> or to <strong>System Default</strong>, in which case the <strong>php.ini</strong> must contain '
                    . '<code>error_reporting = E_ALL &amp; ~E_DEPRECATED &amp; ~E_STRICT</code>.',
                    'notice'
                );
            }
        }

        if ($this->errorHandlerInstalled) {
            return; // Already installed – idempotent
        }

        // Unconditionally silence ALL PHP output of notices/warnings/deprecations for the
        // remainder of this request.  This is necessary because any stray PHP deprecated or
        // warning output (including those from Joomla's own Installer.php) would be injected
        // into the AJAX JSON response and cause a parse error in the browser.
        //
        // We do NOT try to honour the Joomla error_reporting config here:
        //  - 'none' / '0'   → php.ini may still have display_errors On; we must override it.
        //  - 'default'      → php.ini controls it; we cannot trust it to be safe.
        //  - anything else  → we suppress anyway.
        //
        // All suppression is for this single HTTP request only; the next request starts fresh.
        error_reporting(0);
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');
        @ini_set('log_errors', '0'); // prevent deprecations leaking into error_log and breaking output

        // E_STRICT was a no-op from PHP 8.0 and its constant was deprecated in PHP 8.4,
        // so it is intentionally omitted from the mask.
        $suppressedMask = E_WARNING | E_NOTICE | E_DEPRECATED
            | E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED
            | E_ALL; // belt-and-suspenders: swallow everything

        set_error_handler(static function (int $errno) use ($suppressedMask): bool {
            // Return true  → "I handled it" – PHP will NOT invoke any further handler.
            // Return false → fall through to PHP's built-in handler.
            return (bool) ($errno & $suppressedMask);
        });

        $this->errorHandlerInstalled = true;
    }

    /**
     * Writes a timestamped entry to administrator/logs/j2store_install.php.
     *
     * The file starts with a PHP die() guard (standard Joomla convention) so it cannot be
     * served directly from the web.  Each call appends one line:
     *   [YYYY-MM-DD HH:MM:SS] [LEVEL] Message
     *
     * Failures are silently swallowed – logging must never break the installation.
     *
     * @param  string $message  Text to log
     * @param  string $level    One of DEBUG / INFO / WARNING / ERROR  (default: INFO)
     */
    private function _log(string $message, string $level = 'INFO'): void
    {
        try {
            $logFile = (defined('JPATH_ADMINISTRATOR') ? JPATH_ADMINISTRATOR : JPATH_ROOT . '/administrator')
                . '/logs/j2store_install.php';

            // Create file with PHP die() guard if it does not yet exist
            if (!file_exists($logFile)) {
                $header = "<?php die('Forbidden.'); ?>\n"
                    . "#\n"
                    . "# J2Store / J2Commerce Installation Log\n"
                    . "# This file is regenerated on every install / update / uninstall.\n"
                    . "#\n";
                @file_put_contents($logFile, $header, LOCK_EX);
            }

            $line = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . "\n";
            @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Silently ignore – logging must never break installation
        }
    }

    /**
     * No-op kept for safety; suppression is intentionally never restored within a request.
     * See _suppressErrors() for the rationale.
     */
    private function _restoreErrors(): void
    {
        /*if ($this->errorHandlerInstalled) {
            restore_error_handler();
            $this->errorHandlerInstalled = false;
        }

        if (isset($this->originalErrorReporting)) {
            error_reporting($this->originalErrorReporting);
            ini_set('display_errors', $this->originalErrorReporting > 0 ? 1 : 0);
            $this->originalErrorReporting = null;
        }*/
    }

    /**
     * Checks for other FOF library versions installed alongside J2Store's bundled F0F (f0f).
     * Scans JPATH_LIBRARIES for known alternative FOF directory names.
     * Returns false (and enqueues an error) if any conflicts are found, blocking the installation.
     *
     * @return bool  True if no conflicts found, false otherwise
     */
    private function _checkForFofConflicts(): bool
    {
        $librariesBase = defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries';

        // Known alternative FOF directory names (J2Store uses 'f0f', not any of these)
        $knownVariants = array('fof', 'fof3', 'fof30', 'fof4', 'fof40');

        $conflicts = array_values(array_filter($knownVariants, static function ($dir) use ($librariesBase) {
            return is_dir($librariesBase . '/' . $dir);
        }));

        if (!empty($conflicts)) {
            Factory::getApplication()->enqueueMessage(
                'Installation postponed:'
                . '<br>You need to perform a simple cleanup before you can resume the installation of J2Commerce.'
                . '<br><br>The following FOF library folder(s) were found on the server: <code>/libraries/' . implode('</code>, <code>/libraries/', $conflicts) . '</code>.'
                . '<br>Multiple FOF installations can cause conflicts and unused or unsupported code can jeopardize the site.'
                . '<br>Please uninstall the libraries from <a href="index.php?option=com_installer&view=manage&filter[type]=library">System -&gt; Manage -&gt; Extensions -&gt; filter by the library type</a>. '
                . '<br>Make sure you keep the FOF library packaged with J2Commerce. It has a version number similar to <code>revAC1796x</code> and is located in <code>/libraries/f0f</code>.'
                . '<br>If some or all listed FOF libraries are missing from the Joomla console, they are still present on the server, just not visible.'
                . '<br>In that case, you have to delete the folder(s) manually from the server.'
                . '<br><br>Once the libraries are deleted, you can restart the installation.',
                'warning'
            );
            return false;
        }

        return true;
    }

    /**
     * Loads F0F from the installed libraries location or, as a fallback, from the bundled copy.
     * Safe to call multiple times (no-op if F0F is already loaded).
     */
    private function _loadF0F()
    {
        if (defined('F0F_INCLUDED')) {
            $this->_log('_loadF0F() – already loaded (' . F0F_INCLUDED . ')');
            return;
        }

        $paths = array(
            (defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php',
            __DIR__ . '/lib_fof/include.php',
        );

        foreach ($paths as $filePath) {
            $this->_log('_loadF0F() – trying ' . $filePath . ' exists=' . (file_exists($filePath) ? 'yes' : 'no'));
            if (!defined('F0F_INCLUDED') && file_exists($filePath)) {
                @include_once $filePath;
                $this->_log('_loadF0F() – included ' . $filePath . ' F0F_INCLUDED=' . (defined('F0F_INCLUDED') ? constant('F0F_INCLUDED') : 'still not defined'));
            }
        }

        if (!defined('F0F_INCLUDED')) {
            $this->_log('_loadF0F() – WARNING: F0F could not be loaded from any path', 'WARNING');
        }
    }

    /**
     * Loads the F0F helper classes we need (F0FDatabaseInstaller, F0FUtilsCacheCleaner).
     * Must be called after _loadF0F().
     */
    private function _loadF0FHelpers()
    {
        if (!defined('F0F_INCLUDED')) {
            $this->_log('_loadF0FHelpers() – skipped, F0F not loaded', 'WARNING');
            return;
        }

        $librariesBase = defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries';

        if (!class_exists('F0FDatabaseInstaller', false)) {
            foreach (array($librariesBase . '/f0f/database/installer.php', __DIR__ . '/lib_fof/database/installer.php') as $path) {
                $this->_log('_loadF0FHelpers() – F0FDatabaseInstaller trying ' . $path . ' exists=' . (file_exists($path) ? 'yes' : 'no'));
                if (file_exists($path)) {
                    @include_once $path;
                    $this->_log('_loadF0FHelpers() – F0FDatabaseInstaller included from ' . $path);
                    break;
                }
            }
        } else {
            $this->_log('_loadF0FHelpers() – F0FDatabaseInstaller already loaded');
        }

        if (!class_exists('F0FUtilsCacheCleaner', false)) {
            foreach (array($librariesBase . '/f0f/utils/cache/cleaner.php', __DIR__ . '/lib_fof/utils/cache/cleaner.php') as $path) {
                if (file_exists($path)) {
                    @include_once $path;
                    $this->_log('_loadF0FHelpers() – F0FUtilsCacheCleaner included from ' . $path);
                    break;
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Sub-extension management (standalone, no F0F platform dependency)
    // -------------------------------------------------------------------------

    /**
     * Installs sub-extensions (modules, plugins) bundled with the main extension.
     * Uses Joomla APIs directly (Factory::getDbo(), Joomla\CMS\Installer\Installer).
     *
     * @param  object $parent Installer adapter
     *
     * @return stdClass Installation status with ->modules and ->plugins arrays
     */
    protected function _doInstallSubextensions($parent)
    {
        $src = $parent->getParent()->getPath('source');
        $db  = Factory::getDbo();

        $status          = new stdClass();
        $status->modules = array();
        $status->plugins = array();

        // ---- Modules ----
        if (isset($this->installation_queue['modules']) && count($this->installation_queue['modules'])) {
            foreach ($this->installation_queue['modules'] as $folder => $modules) {
                if (!count($modules)) {
                    continue;
                }

                foreach ($modules as $module => $modulePreferences) {
                    if (empty($folder)) {
                        $folder = 'site';
                    }

                    // Normalise the element name: keys may or may not carry the 'mod_' prefix
                    // (admin keys don't; site keys do), so avoid double-prefixing.
                    $elementName = strncmp($module, 'mod_', 4) === 0 ? $module : 'mod_' . $module;

                    // Resolve the module source path
                    $path = "{$src}/modules/{$folder}/{$module}";
                    if (!is_dir($path)) {
                        $path = "{$src}/modules/{$folder}/mod_{$module}";
                    }
                    if (!is_dir($path)) {
                        $path = "{$src}/modules/{$module}";
                    }
                    if (!is_dir($path)) {
                        $path = "{$src}/modules/mod_{$module}";
                    }
                    if (!is_dir($path)) {
                        continue;
                    }

                    // Was the module already installed?
                    $sql = $db->getQuery(true)
                        ->select('COUNT(*)')
                        ->from('#__modules')
                        ->where($db->quoteName('module') . ' = ' . $db->quote($elementName));
                    $db->setQuery($sql);

                    try {
                        $count = $db->loadResult();
                    } catch (\Exception $e) {
                        $count = 0;
                    }

                    $installer = new Installer();
                    if (method_exists($installer, 'setDatabase')) {
                        $installer->setDatabase($db);
                    }
                    $levelBefore = ob_get_level();
                    ob_start();
                    try {
                        $result = $installer->install($path);
                    } catch (\Throwable $e) {
                        $result = false;
                        $this->_log("_doInstallSubextensions() – module {$elementName} THREW: " . $e->getMessage(), 'ERROR');
                    } finally {
                        while (ob_get_level() > $levelBefore) {
                            ob_end_clean();
                        }
                    }

                    $this->_log("_doInstallSubextensions() – module {$elementName} ({$folder}) result=" . ($result ? 'ok' : 'FAILED'));

                    $status->modules[] = array(
                        'name'   => $elementName,
                        'client' => $folder,
                        'result' => $result,
                    );

                    if (!$count) {
                        list($modulePosition, $modulePublished) = $modulePreferences;

                        // A. Set position and published state
                        $sql = $db->getQuery(true)
                            ->update($db->quoteName('#__modules'))
                            ->set($db->quoteName('position') . ' = ' . $db->quote($modulePosition))
                            ->where($db->quoteName('module') . ' = ' . $db->quote($elementName));
                        if ($modulePublished) {
                            $sql->set($db->quoteName('published') . ' = 1');
                        }
                        $db->setQuery($sql);
                        try {
                            $db->execute();
                        } catch (\Exception $e) {
                        }

                        // B. Set ordering for back-end modules
                        if ($folder === 'admin') {
                            try {
                                $q = $db->getQuery(true)
                                    ->select('MAX(' . $db->quoteName('ordering') . ')')
                                    ->from($db->quoteName('#__modules'))
                                    ->where($db->quoteName('position') . ' = ' . $db->quote($modulePosition));
                                $db->setQuery($q);
                                $position = (int) $db->loadResult() + 1;

                                $q = $db->getQuery(true)
                                    ->update($db->quoteName('#__modules'))
                                    ->set($db->quoteName('ordering') . ' = ' . $position)
                                    ->where($db->quoteName('module') . ' = ' . $db->quote($elementName));
                                $db->setQuery($q)->execute();
                            } catch (\Exception $e) {
                            }
                        }

                        // C. Assign to all pages
                        try {
                            $q = $db->getQuery(true)
                                ->select('id')
                                ->from($db->quoteName('#__modules'))
                                ->where($db->quoteName('module') . ' = ' . $db->quote($elementName));
                            $db->setQuery($q);
                            $moduleid = $db->loadResult();

                            $q = $db->getQuery(true)
                                ->select('*')
                                ->from($db->quoteName('#__modules_menu'))
                                ->where($db->quoteName('moduleid') . ' = ' . (int) $moduleid);
                            $db->setQuery($q);
                            $assignments = $db->loadObjectList();

                            if (empty($assignments)) {
                                $o = (object) array('moduleid' => $moduleid, 'menuid' => 0);
                                $db->insertObject('#__modules_menu', $o);
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }

        // ---- Plugins ----
        if (isset($this->installation_queue['plugins']) && count($this->installation_queue['plugins'])) {
            foreach ($this->installation_queue['plugins'] as $folder => $plugins) {
                if (!count($plugins)) {
                    continue;
                }

                foreach ($plugins as $plugin => $published) {
                    // Resolve the plugin source path
                    $path = "{$src}/plugins/{$folder}/{$plugin}";
                    if (!is_dir($path)) {
                        $path = "{$src}/plugins/{$folder}/plg_{$plugin}";
                    }
                    if (!is_dir($path)) {
                        $path = "{$src}/plugins/{$plugin}";
                    }
                    if (!is_dir($path)) {
                        $path = "{$src}/plugins/plg_{$plugin}";
                    }
                    if (!is_dir($path)) {
                        continue;
                    }

                    // Was the plugin already installed?
                    $query = $db->getQuery(true)
                        ->select('COUNT(*)')
                        ->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('element') . ' = ' . $db->quote($plugin))
                        ->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
                    $db->setQuery($query);

                    try {
                        $count = $db->loadResult();
                    } catch (\Exception $e) {
                        $count = 0;
                    }

                    $installer = new Installer();
                    if (method_exists($installer, 'setDatabase')) {
                        $installer->setDatabase($db);
                    }
                    $levelBefore = ob_get_level();
                    ob_start();
                    try {
                        $result = $installer->install($path);
                    } catch (\Throwable $e) {
                        $result = false;
                        $this->_log("_doInstallSubextensions() – plugin plg_{$plugin} ({$folder}) THREW: " . $e->getMessage(), 'ERROR');
                    } finally {
                        while (ob_get_level() > $levelBefore) {
                            ob_end_clean();
                        }
                    }

                    $this->_log("_doInstallSubextensions() – plugin plg_{$plugin} ({$folder}) result=" . ($result ? 'ok' : 'FAILED'));

                    $status->plugins[] = array(
                        'name'   => 'plg_' . $plugin,
                        'group'  => $folder,
                        'result' => $result,
                    );

                    if ($published && !$count) {
                        $query = $db->getQuery(true)
                            ->update($db->quoteName('#__extensions'))
                            ->set($db->quoteName('enabled') . ' = 1')
                            ->where($db->quoteName('element') . ' = ' . $db->quote($plugin))
                            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
                        $db->setQuery($query);
                        try {
                            $db->execute();
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }

        // Clear com_modules and com_plugins cache
        $this->_clearExtensionsCache();

        return $status;
    }

    /**
     * Uninstalls sub-extensions (modules, plugins) bundled with the main extension.
     *
     * @param  object $parent Installer adapter
     *
     * @return stdClass Uninstallation status with ->modules and ->plugins arrays
     */
    protected function _doUninstallSubextensions($parent)
    {
        $db = Factory::getDbo();

        $status          = new stdClass();
        $status->modules = array();
        $status->plugins = array();

        // ---- Modules ----
        if (isset($this->installation_queue['modules']) && count($this->installation_queue['modules'])) {
            foreach ($this->installation_queue['modules'] as $folder => $modules) {
                if (!count($modules)) {
                    continue;
                }

                foreach ($modules as $module => $modulePreferences) {
                    // Normalise: site module keys already carry 'mod_', admin keys do not.
                    $elementName = strncmp($module, 'mod_', 4) === 0 ? $module : 'mod_' . $module;

                    $sql = $db->getQuery(true)
                        ->select($db->quoteName('extension_id'))
                        ->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('element') . ' = ' . $db->quote($elementName))
                        ->where($db->quoteName('type') . ' = ' . $db->quote('module'));
                    $db->setQuery($sql);

                    try {
                        $id = $db->loadResult();
                    } catch (\Exception $e) {
                        $id = 0;
                    }

                    if ($id) {
                        $installer = new Installer();
                        if (method_exists($installer, 'setDatabase')) {
                            $installer->setDatabase($db);
                        }
                        $result = $installer->uninstall('module', $id, 1);
                        $status->modules[] = array(
                            'name'   => $elementName,
                            'client' => $folder,
                            'result' => $result,
                        );
                    }
                }
            }
        }

        // ---- Plugins ----
        if (isset($this->installation_queue['plugins']) && count($this->installation_queue['plugins'])) {
            foreach ($this->installation_queue['plugins'] as $folder => $plugins) {
                if (!count($plugins)) {
                    continue;
                }

                foreach ($plugins as $plugin => $published) {
                    $sql = $db->getQuery(true)
                        ->select($db->quoteName('extension_id'))
                        ->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                        ->where($db->quoteName('element') . ' = ' . $db->quote($plugin))
                        ->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
                    $db->setQuery($sql);

                    try {
                        $id = $db->loadResult();
                    } catch (\Exception $e) {
                        $id = 0;
                    }

                    if ($id) {
                        $installer = new Installer();
                        if (method_exists($installer, 'setDatabase')) {
                            $installer->setDatabase($db);
                        }
                        $result = $installer->uninstall('plugin', $id, 1);
                        $status->plugins[] = array(
                            'name'   => 'plg_' . $plugin,
                            'group'  => $folder,
                            'result' => $result,
                        );
                    }
                }
            }
        }

        $this->_clearExtensionsCache();

        return $status;
    }

    /**
     * Clears the com_modules and com_plugins Joomla cache using the Joomla Cache API.
     */
    private function _clearExtensionsCache()
    {
        try {
            $conf = Factory::getConfig();

            foreach (array('com_modules', 'com_plugins') as $group) {
                foreach (array(0, 1) as $clientId) {
                    $options = array(
                        'defaultgroup' => $group,
                        'cachebase'    => $clientId
                            ? JPATH_ADMINISTRATOR . '/cache'
                            : $conf->get('cache_path', JPATH_SITE . '/cache'),
                    );
                    $cache = \Joomla\CMS\Cache\Cache::getInstance('callback', $options);
                    $cache->clean();
                }
            }
        } catch (\Exception $e) {
            // Non-fatal
        }
    }

    /**
     * Renders the post-installation / post-update status table.
     *
     * @param stdClass $status           Sub-extension installation status
     * @param array    $fofInstallStatus FOF installation status array
     * @param object   $parent           Installer adapter
     */

    protected function _renderPostInstallation($status, $fofInstallStatus, $parent)
    {
        $rows = 0;
        $this->_renderSecurityCheck();
        $this->_renderTemplateOverrideWarnings();
        ?>
        <table class="table table-striped" width="100%">
            <thead>
            <tr>
                <th class="title" colspan="2">Extension</th>
                <th width="30%">Status</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr class="row<?php echo($rows++ % 2); ?>">
                <td class="key" colspan="2"><?php echo $this->componentTitle; ?></td>
                <td><strong style="color: green">Installed</strong></td>
            </tr>
            <?php if (!empty($fofInstallStatus['required'])): ?>
                <tr class="row<?php echo($rows++ % 2); ?>">
                    <td class="key" colspan="2">
                        <strong>Framework on Framework (FOF) <?php echo $fofInstallStatus['version']; ?></strong>
                        [<?php echo $fofInstallStatus['date']; ?>]
                    </td>
                    <td><strong>
                            <span style="color: <?php echo $fofInstallStatus['installed'] ? 'green' : 'red'; ?>; font-weight: bold;">
                                <?php echo $fofInstallStatus['installed'] ? 'Installed' : 'Not Installed'; ?>
                            </span>
                        </strong></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($status->modules)): ?>
                <tr>
                    <th>Module</th>
                    <th>Client</th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                    <tr class="row<?php echo($rows++ % 2); ?>">
                        <td class="key"><?php echo $module['name']; ?></td>
                        <td class="key"><?php echo ucfirst($module['client']); ?></td>
                        <td><strong style="color: <?php echo $module['result'] ? 'green' : 'red'; ?>">
                                <?php echo $module['result'] ? 'Installed' : 'Not installed'; ?>
                            </strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($status->plugins)): ?>
                <tr>
                    <th>Plugin</th>
                    <th>Group</th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                    <tr class="row<?php echo($rows++ % 2); ?>">
                        <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                        <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                        <td><strong style="color: <?php echo $plugin['result'] ? 'green' : 'red'; ?>">
                                <?php echo $plugin['result'] ? 'Installed' : 'Not installed'; ?>
                            </strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Renders the post-uninstallation status table.
     *
     * @param stdClass $status Sub-extension uninstallation status
     * @param object   $parent Installer adapter
     */
    protected function _renderPostUninstallation($status, $parent)
    {
        $rows = 1;
        ?>
        <table class="table table-striped" width="100%">
            <thead>
            <tr>
                <th class="title" colspan="2">Extension</th>
                <th width="30%">Status</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr class="row<?php echo($rows++ % 2); ?>">
                <td class="key" colspan="2"><?php echo $this->componentTitle; ?></td>
                <td><strong style="color: green">Removed</strong></td>
            </tr>
            <?php if (!empty($status->modules)): ?>
                <tr>
                    <th>Module</th>
                    <th>Client</th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                    <tr class="row<?php echo($rows++ % 2); ?>">
                        <td class="key"><?php echo $module['name']; ?></td>
                        <td class="key"><?php echo ucfirst($module['client']); ?></td>
                        <td><strong style="color: <?php echo $module['result'] ? 'green' : 'red'; ?>">
                                <?php echo $module['result'] ? 'Removed' : 'Not removed'; ?>
                            </strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($status->plugins)): ?>
                <tr>
                    <th>Plugin</th>
                    <th>Group</th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                    <tr class="row<?php echo($rows++ % 2); ?>">
                        <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                        <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                        <td><strong style="color: <?php echo $plugin['result'] ? 'green' : 'red'; ?>">
                                <?php echo $plugin['result'] ? 'Removed' : 'Not removed'; ?>
                            </strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    // -------------------------------------------------------------------------
    // Security exploitation check
    // -------------------------------------------------------------------------

    /**
     * Scans for evidence that the unauthenticated file upload vulnerability
     * (fixed in 4.1.6) was exploited on this site before the update was applied.
     *
     * Returns a structured array consumed by _renderSecurityCheck() and logged
     * via _getExploitationVerdict().
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
                    // Double-extension pattern used to disguise PHP as a safe type
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
     * Possible values:
     *   'hacked'     – definitive: uploads exist with no file-option configured
     *   'suspicious' – ambiguous: unexpected files or unprotected legacy path
     *   'clean'      – no evidence of exploitation
     *   'unknown'    – could not query the database to determine
     *
     * @param  array  $check
     * @return string
     */
    private function _getExploitationVerdict(array $check): string
    {
        if (!$check['options_table_exists']) {
            return 'unknown';
        }

        $hasUploadFiles = !empty($check['upload_files']) || $check['db_upload_count'] > 0;

        // Definitive: uploads exist and no file-type option has ever been configured
        if ($check['file_option_count'] === 0 && $hasUploadFiles) {
            return 'hacked';
        }

        // Suspicious indicators even when a file option exists
        if (!empty($check['suspicious_names']) || !empty($check['invoices_unexpected'])) {
            return 'suspicious';
        }

        // Legacy folder has files — could be old legitimate use or old attack
        if (!empty($check['legacy_files'])) {
            return 'suspicious';
        }

        return 'clean';
    }

    /**
     * Removes foreign files from the uploads directories and truncates the
     * #__j2store_uploads table when exploitation is definitively confirmed.
     *
     * Only protection files (.htaccess, web.config) are preserved. All other
     * files in media/j2store/uploads/ and (if present) the legacy
     * media/com_j2store/uploads/ path are deleted.
     *
     * @param  array  $check  Result array from _checkForExploitation()
     * @return array  {
     *     removed_files:  string[]  — paths of successfully deleted files,
     *     failed_files:   string[]  — paths that could not be deleted,
     *     db_truncated:   bool      — whether #__j2store_uploads was truncated,
     *     db_error:       string    — error message if truncation failed, '' otherwise
     * }
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

        // Delete user files from media/j2store/uploads/
        $uploadsDir = JPATH_ROOT . '/media/j2store/uploads';
        if (is_dir($uploadsDir)) {
            foreach ((array) $check['upload_files'] as $filename) {
                if (in_array($filename, $protectionFiles)) {
                    continue;
                }
                $path = $uploadsDir . '/' . $filename;
                if (@unlink($path)) {
                    $result['removed_files'][] = 'media/j2store/uploads/' . $filename;
                    $this->_log("_removeExploitationFiles() – deleted {$path}");
                } else {
                    $result['failed_files'][] = 'media/j2store/uploads/' . $filename;
                    $this->_log("_removeExploitationFiles() – FAILED to delete {$path}", 'WARNING');
                }
            }
        }

        // Delete user files from the legacy media/com_j2store/uploads/ path
        $legacyDir = JPATH_ROOT . '/media/com_j2store/uploads';
        if (is_dir($legacyDir)) {
            foreach ((array) $check['legacy_files'] as $filename) {
                if (in_array($filename, $protectionFiles)) {
                    continue;
                }
                $path = $legacyDir . '/' . $filename;
                if (@unlink($path)) {
                    $result['removed_files'][] = 'media/com_j2store/uploads/' . $filename;
                    $this->_log("_removeExploitationFiles() – deleted {$path}");
                } else {
                    $result['failed_files'][] = 'media/com_j2store/uploads/' . $filename;
                    $this->_log("_removeExploitationFiles() – FAILED to delete {$path}", 'WARNING');
                }
            }
        }

        // Truncate the uploads database table
        if ($check['db_table_exists'] && $check['db_upload_count'] > 0) {
            try {
                $db = Factory::getDbo();
                $db->truncateTable('#__j2store_uploads');
                $result['db_truncated'] = true;
                $this->_log('_removeExploitationFiles() – truncated #__j2store_uploads');
            } catch (\Exception $e) {
                $result['db_error'] = $e->getMessage();
                $this->_log('_removeExploitationFiles() – failed to truncate table: ' . $e->getMessage(), 'WARNING');
            }
        }

        return $result;
    }

    /**
     * Renders the security exploitation check result as an inline HTML block
     * appended to the post-installation status output.
     */
    private function _renderSecurityCheck(): void
    {
        if ($this->securityCheckResult === null) {
            return;
        }

        $check   = $this->securityCheckResult;
        $verdict = $this->_getExploitationVerdict($check);

        $styles = [
            'hacked'     => 'background:#f8d7da;border:2px solid #f5c6cb;color:#721c24;',
            'suspicious' => 'background:#fff3cd;border:2px solid #ffc107;color:#856404;',
            'clean'      => 'background:#d4edda;border:2px solid #c3e6cb;color:#155724;',
            'unknown'    => 'background:#e2e3e5;border:2px solid #d6d8db;color:#383d41;',
        ];

        $style = $styles[$verdict] ?? $styles['unknown'];
        ?>
        <div style="padding:15px;border-radius:4px;<?php echo $style; ?>">
            <h3 style="margin-top:0;">
                <?php if ($verdict === 'hacked'): ?>
                    &#x26A0; Security Alert: Exploitation Detected
                <?php elseif ($verdict === 'suspicious'): ?>
                    &#x26A0; Security Warning: Suspicious Files Found
                <?php elseif ($verdict === 'clean'): ?>
                    &#x2713; Security Check: No Exploitation Detected
                <?php else: ?>
                    Security Check: Could Not Determine Status
                <?php endif; ?>
            </h3>

            <?php if ($verdict === 'hacked'): ?>
                <p><strong>This site has been exploited.</strong> Files were uploaded through
                the unauthenticated upload endpoint fixed in this release, and no
                &ldquo;File&rdquo; type product option has ever been configured &mdash;
                meaning all uploads in the database and on disk are foreign.</p>

                <?php if ($this->securityCleanupResult !== null): ?>
                    <?php $cleanup = $this->securityCleanupResult; ?>
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
                    also contained foreign files. Those files are listed below &mdash;
                    please remove them manually as this path is outside the scope of the
                    automatic cleanup.</p>
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
                <p><strong><?php echo count($check['upload_files']); ?> file(s) found in
                    <code>media/j2store/uploads/</code>:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', array_slice($check['upload_files'], 0, 20)), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo count($check['upload_files']) > 20 ? ' &hellip; and ' . (count($check['upload_files']) - 20) . ' more' : ''; ?>
                    </code>
                </p>
            <?php endif; ?>

            <?php if ($check['db_upload_count'] > 0): ?>
                <p><strong><?php echo $check['db_upload_count']; ?> record(s) in
                    <code>#__j2store_uploads</code></strong> database table.</p>
            <?php endif; ?>

            <?php if (!empty($check['suspicious_names'])): ?>
                <p><strong style="color:red;">&#x26A0; Suspicious filenames detected
                    (double-extension attack pattern):</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['suspicious_names']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['legacy_files'])): ?>
                <p><strong><?php echo count($check['legacy_files']); ?> file(s) found in the
                    legacy <code>media/com_j2store/uploads/</code> path</strong> (J2Store v3 /
                    early v4 upload directory &mdash; this path is not covered by the 4.1.6
                    update and must be secured manually):<br>
                    <code><?php echo htmlspecialchars(implode(', ', array_slice($check['legacy_files'], 0, 20)), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo count($check['legacy_files']) > 20 ? ' &hellip; and ' . (count($check['legacy_files']) - 20) . ' more' : ''; ?>
                    </code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['invoices_unexpected'])): ?>
                <p><strong>Unexpected non-PDF file(s) in <code>media/j2store/invoices/</code>
                    &mdash; invoices should only contain PDFs:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['invoices_unexpected']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['protection_missing'])): ?>
                <p><strong>&#x26A0; Missing web server protection files &mdash;
                    files in these directories may be publicly accessible:</strong><br>
                    <code><?php echo htmlspecialchars(implode(', ', $check['protection_missing']), ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php endif; ?>

            <?php if (!empty($check['errors'])): ?>
                <p><em>Check errors: <?php echo htmlspecialchars(implode('; ', $check['errors']), ENT_QUOTES, 'UTF-8'); ?></em></p>
            <?php endif; ?>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Template override CSRF check
    // -------------------------------------------------------------------------

    /**
     * Scans the default site template (and all site templates for plugin
     * overrides) for com_j2store template override files that appear to be
     * missing CSRF token protection.
     *
     * @return array  Array of ['file' => string, 'type' => 'php_form'|'js_form'|'array_form'|'data_form']
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

        $hasFormToken = static function (string $path): bool {
            $content = @file_get_contents($path);
            if ($content === false) {
                return true; // unreadable → skip
            }
            return strpos($content, 'form.token') !== false;
        };

        $hasGetFormToken = static function (string $path): bool {
            $content = @file_get_contents($path);
            if ($content === false) {
                return true; // unreadable → skip
            }
            return strpos($content, 'getFormToken') !== false;
        };

        $phpFormFiles = [
            'carts/default.php',
            'carts/default_calculator.php',
            'carts/default_coupon.php',
            'carts/default_shipping.php',
            'carts/default_voucher.php',
        ];

        $jsFormFiles = [
            'product/adminitem_configurableoptions.php',
            'product/adminitem_options.php',
            'product/item_configurableoptions.php',
            'product/item_options.php',
        ];

        /* These files pass cart item data as a PHP array and need  JSession::getFormToken() => '1'  added to that array. */
        $arrayFormFiles = [
            'carts/default.php',
            'carts/default_items.php',
        ];

        foreach ($phpFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasFormToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'php_form',
                ];
            }
        }

        foreach ($jsFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasGetFormToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'js_form',
                ];
            }
        }

        foreach ($arrayFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasGetFormToken($full)) {
                $warnings[] = [
                    'file' => 'templates/' . $template . '/html/com_j2store/' . $file,
                    'type' => 'array_form',
                ];
            }
        }

        $pluginFiles = [
            'cart.php'                        => 'data_form',
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
                        $full    = $subtemplateDir . '/' . $filename;
                        $checker = $type === 'php_form' ? $hasFormToken : $hasGetFormToken;
                        if (file_exists($full) && !$checker($full)) {
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
     * Renders an HTML warning block for template override files missing CSRF
     * token protection, appended after the security check block.
     */
    private function _renderTemplateOverrideWarnings(): void
    {
        if (empty($this->templateOverrideWarnings)) {
            return;
        }

        $phpFormFiles   = [];
        $jsFormFiles    = [];
        $arrayFormFiles = [];
        $dataFormFiles  = [];

        foreach ($this->templateOverrideWarnings as $w) {
            if ($w['type'] === 'php_form') {
                $phpFormFiles[] = $w['file'];
            } elseif ($w['type'] === 'js_form') {
                $jsFormFiles[] = $w['file'];
            } elseif ($w['type'] === 'array_form') {
                $arrayFormFiles[] = $w['file'];
            } elseif ($w['type'] === 'data_form') {
                $dataFormFiles[] = $w['file'];
            }
        }
        ?>
        <div style="margin-top:20px;margin-bottom:20px;padding:15px;border-radius:4px;background:#fff3cd;border:2px solid #ffc107;color:#856404;">
            <h3 style="margin-top:0;">&#x26A0; Template Override Token protection</h3>
            <p>The following template override files appear to be missing token protection. <strong>These files must be updated manually.</strong><br>
                Note: You may not find a place to insert the missing code if your overrides differ significantly from the original files, and you may not need to add it at all.
            </p>

            <?php if (!empty($phpFormFiles)): ?>
                <p><strong>In the following file(s), add <code>&lt;?php echo JHtml::_('form.token'); ?&gt;</code>
                   immediately before each <code>&lt;/form&gt;</code> closing tag:</strong></p>
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
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;white-space: pre-wrap; word-wrap: break-word;">$('body').prepend('&lt;form enctype="multipart/form-data" id="form-upload" style="display:none;"&gt;&lt;input type="hidden" name="&lt;?php echo JSession::getFormToken(); ?&gt;" value="1" /&gt;&lt;input type="file" name="file" /&gt;</form&gt;');</pre>
            <?php endif; ?>

            <?php if (!empty($arrayFormFiles)): ?>
                <p><strong>In the following file(s), add <code>JSession::getFormToken() =&gt; '1'</code> to the getCartUrl function.</strong></p>
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;">$platform->getCartUrl(array(JSession::getFormToken() =&gt; '1', ...</pre>
                <ul>
                    <?php foreach ($arrayFormFiles as $f): ?>
                        <li><code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($dataFormFiles)): ?>
                <p><strong>In the following file(s), add <code>data-&lt;?php echo JSession::getFormToken(); ?&gt;="1"</code> as an attribute on the link with class <code>j2store_add_to_cart_button</code>:</strong></p>
                <pre style="background:#f8f9fa;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;">&lt;a class="... j2store_add_to_cart_button" data-&lt;?php echo JSession::getFormToken(); ?&gt;="1"&gt;...&lt;/a&gt;</pre>
                <ul>
                    <?php foreach ($dataFormFiles as $f): ?>
                        <li><code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p>After updating the override files, clear the Joomla cache, if enabled.</p>
            <p>Find those reminders in the J2Commerce dashboard.</p>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // FOF library installer (standalone – no F0F dependency)
    // -------------------------------------------------------------------------

    /**
     * Installs or updates the bundled F0F library into JPATH_LIBRARIES.
     * Uses only the standard Joomla Installer – no F0F dependency at all.
     *
     * @param  object $parent Installer adapter
     *
     * @return array  Status array: required, installed, version, date
     */
    private function _installFOF($parent)
    {
        $src    = $parent->getParent()->getPath('source');
        $source = $src . '/lib_fof';
        $target = (defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f';

        $this->_log("_installFOF() – source={$source}");
        $this->_log("_installFOF() – target={$target}");
        $this->_log("_installFOF() – target dir exists=" . (is_dir($target) ? 'yes' : 'no'));

        $haveToInstallFOF = false;

        if (!is_dir($target)) {
            $haveToInstallFOF = true;
            $this->_log('_installFOF() – F0F not installed, will install');
        } else {
            $fofVersion = array();

            if (file_exists($target . '/version.txt')) {
                $rawData               = file_get_contents($target . '/version.txt');
                $info                  = explode("\n", $rawData);
                $fofVersion['installed'] = array(
                    'version' => trim($info[0]),
                    'date'    => new Date(trim($info[1])),
                );
            } else {
                $fofVersion['installed'] = array(
                    'version' => '0.0',
                    'date'    => new Date('2011-01-01'),
                );
            }

            $rawData             = file_get_contents($source . '/version.txt');
            $info                = explode("\n", $rawData);
            $fofVersion['package'] = array(
                'version' => trim($info[0]),
                'date'    => new Date(trim($info[1])),
            );

            $haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
            $this->_log('_installFOF() – installed=' . $fofVersion['installed']['version']
                . ' package=' . $fofVersion['package']['version']
                . ' needsUpdate=' . ($haveToInstallFOF ? 'yes' : 'no'));
        }

        $installedFOF = false;

        if ($haveToInstallFOF) {
            $versionSource = 'package';
            $installer     = new Installer();
            // Joomla 6+ requires the database to be set explicitly
            if (method_exists($installer, 'setDatabase')) {
                $installer->setDatabase(Factory::getDbo());
            }

            $levelBefore  = ob_get_level();
            ob_start();
            try {
                $installedFOF = $installer->install($source);
                $this->_log('_installFOF() – Installer::install() returned ' . ($installedFOF ? 'true' : 'false'));
            } catch (\Throwable $e) {
                $installedFOF = false;
                $this->_log('_installFOF() – Installer::install() THREW: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
            } finally {
                while (ob_get_level() > $levelBefore) {
                    ob_end_clean();
                }
            }
        } else {
            $versionSource = 'installed';
        }

        if (!isset($fofVersion)) {
            $fofVersion = array();

            if (file_exists($target . '/version.txt')) {
                $rawData               = file_get_contents($target . '/version.txt');
                $info                  = explode("\n", $rawData);
                $fofVersion['installed'] = array(
                    'version' => trim($info[0]),
                    'date'    => new Date(trim($info[1])),
                );
            } else {
                $fofVersion['installed'] = array(
                    'version' => '0.0',
                    'date'    => new Date('2011-01-01'),
                );
            }

            $rawData             = file_get_contents($source . '/version.txt');
            $info                = explode("\n", $rawData);
            $fofVersion['package'] = array(
                'version' => trim($info[0]),
                'date'    => new Date(trim($info[1])),
            );

            $versionSource = 'installed';
        }

        if (!($fofVersion[$versionSource]['date'] instanceof Date)) {
            $fofVersion[$versionSource]['date'] = new Date();
        }

        return array(
            'required'  => $haveToInstallFOF,
            'installed' => $installedFOF,
            'version'   => $fofVersion[$versionSource]['version'],
            'date'      => $fofVersion[$versionSource]['date']->format('Y-m-d'),
        );
    }

    // -------------------------------------------------------------------------
    // Localisation installer
    // -------------------------------------------------------------------------

    /**
     * Installs country, zone, and metric data if the tables are empty.
     *
     * @param object $parent Installer adapter (used to get the source path)
     */
    private function _installLocalisation($parent)
    {
        $installer = $parent->getParent();
        $db        = Factory::getDbo();

        $alltables = $db->getTableList();
        $prefix    = $db->getPrefix();

        try {
            $country_status = false;
            if (!in_array($prefix . 'j2store_countries', $alltables)) {
                $country_status = true;
            } else {
                $db->setQuery($db->getQuery(true)->select('*')->from('#__j2store_countries'));
                $country_list = $db->loadAssocList();
                if (count($country_list) < 1) {
                    $country_status = true;
                }
            }

            if ($country_status) {
                $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/countries.sql';
                $this->_executeSQLFiles($sql);
            }
        } catch (\Exception $e) {
        }

        try {
            $zone_status = false;
            if (!in_array($prefix . 'j2store_zones', $alltables)) {
                $zone_status = true;
            } else {
                $db->setQuery($db->getQuery(true)->select('*')->from('#__j2store_zones'));
                $zone_list = $db->loadAssocList();
                if (count($zone_list) < 1) {
                    $zone_status = true;
                }
            }

            if ($zone_status) {
                $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/zones.sql';
                $this->_executeSQLFiles($sql);
            }
        } catch (\Exception $e) {
        }

        try {
            $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/lengths.sql';
            $this->_executeSQLFiles($sql);

            $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/weights.sql';
            $this->_executeSQLFiles($sql);
        } catch (\Exception $e) {
        }

        // Add a unique index on variant_id in productquantities (ALTER IGNORE removed in recent MySQL)
        // TODO keep?
        $db->setQuery('SHOW INDEX FROM `#__j2store_productquantities`');
        $product_qty_index = $db->loadObjectList();
        $add_index         = true;

        foreach ($product_qty_index as $pro_qty_index) {
            if (in_array($pro_qty_index->Key_name, array('variantidx'))) {
                $add_index = false;
                break;
            }
        }

        if ($add_index) {
            try {
                $this->_sqlexecute('ALTER TABLE #__j2store_productquantities ADD UNIQUE INDEX variantidx (variant_id)');
            } catch (\Exception $e) {
            }
        }
    }

    // -------------------------------------------------------------------------
    // Database utilities
    // -------------------------------------------------------------------------

    /**
     * Executes all SQL statements in a .sql file.
     *
     * @param string $sql Absolute path to the .sql file
     */
    private function _executeSQLFiles($sql)
    {
        if (is_file($sql)) {
            $db      = Factory::getDbo();
            $queries = Installer::splitSql(file_get_contents($sql));

            foreach ($queries as $query) {
                $query = trim($query);
                if ($query !== '' && $query[0] !== '#') {
                    $db->setQuery($query);
                    try {
                        $db->execute();
                    } catch (\Exception $e) {
                        // Ignore – operator can run this manually via the Tools menu
                    }
                }
            }
        }
    }

    /**
     * Executes a single SQL statement, silently ignoring failures.
     *
     * @param string $query
     */
    private function _sqlexecute($query)
    {
        $db = Factory::getDbo();
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (\Exception $e) {
        }
    }

    // -------------------------------------------------------------------------
    // Update-site management
    // -------------------------------------------------------------------------

    /**
     * Removes a Joomla update site entry for a given extension.
     *
     * @param  string $type     Extension type (component, plugin, module, …)
     * @param  string $element  Extension element name
     * @param  string $folder   Plugin folder (empty for non-plugins)
     * @param  string $location Update-site URL to match
     *
     * @return bool
     */
    private function _removeUpdateSite($type, $element, $folder = '', $location = '')
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where($db->quoteName('type') . ' = ' . $db->quote($type))
            ->where($db->quoteName('element') . ' = ' . $db->quote($element));

        if ($folder) {
            $query->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
        }

        $db->setQuery($query);

        try {
            $extension_id = $db->loadResult();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        if (!$extension_id) {
            return false;
        }

        $query = $db->getQuery(true)
            ->select('update_site_id')
            ->from('#__update_sites_extensions')
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));

        $db->setQuery($query);

        try {
            $updatesite_ids = $db->loadColumn();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }

        if (empty($updatesite_ids)) {
            return false;
        }

        foreach ($updatesite_ids as $id) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__update_sites'))
                ->where($db->quoteName('update_site_id') . ' = ' . $db->quote($id));

            if ($location) {
                $query->where($db->quoteName('location') . ' = ' . $db->quote($location));
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                return false;
            }
        }

        return true;
    }

    /**
     * Automatically enables the Joomla backward-compatibility behaviour plugin and turns on
     * all of its parameter options so that J2Store works out of the box.
     *
     * - Joomla 5.x: plg_behaviour_compat  (folder=behaviour, element=compat)
     * - Joomla 6.x: plg_behaviour_compat6 (folder=behaviour, element=compat6)
     *
     * If the plugin is not installed the installation is aborted (returns false).
     * If enabling or param-saving fails for any other reason the installation is also aborted.
     *
     * @return bool  True on success, false on failure (install is aborted by the caller)
     */
    private function enableCompatWithOptions(): bool
    {
        $jVersion     = defined('JVERSION') ? JVERSION : '5.0.0';
        $majorVersion = (int) explode('.', $jVersion)[0];

        $element = $majorVersion >= 6 ? 'compat6' : 'compat';
        $label   = $element === 'compat6'
            ? 'plg_behaviour_compat6 (Behaviour – Backward Compatibility 6)'
            : 'plg_behaviour_compat (Behaviour – Backward Compatibility)';

        $this->_log("enableCompatWithOptions() – Joomla major={$majorVersion}, looking for element={$element}");

        $plugin = $this->getBehaviourPluginByElement($element);

        if (!$plugin) { // Should not happen
            $this->_log("enableCompatWithOptions() – plugin {$element} NOT FOUND", 'ERROR');
            Factory::getApplication()->enqueueMessage(
                'Installation blocked: the <strong>' . $label . '</strong> plugin is not installed. '
                . 'Please install it and make sure <strong>all of its options are enabled</strong> '
                . 'before installing J2Commerce.',
                'error'
            );
            return false;
        }

        $wasDisabled = !(int) $plugin->enabled;
        $this->_log("enableCompatWithOptions() – plugin found id={$plugin->extension_id} enabled={$plugin->enabled}");

        $params = array();
        if (!empty($plugin->params)) {
            $decoded = json_decode($plugin->params, true);
            if (is_array($decoded)) {
                $params = $decoded;
            }
        }

        $updatedParams  = $this->enableBooleanLikeParams($params);
        $paramsChanged  = ($updatedParams !== $params);
        $paramsJson     = json_encode($updatedParams);
        if ($paramsJson === false) {
            $paramsJson = '{}';
        }

        $this->_log("enableCompatWithOptions() – wasDisabled={$wasDisabled} paramsChanged=" . ($paramsChanged ? 'yes' : 'no'));

        try {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->set($db->quoteName('params')  . ' = ' . $db->quote($paramsJson))
                ->where($db->quoteName('extension_id') . ' = ' . (int) $plugin->extension_id);
            $db->setQuery($query)->execute();
            $this->_log("enableCompatWithOptions() – DB update successful");
        } catch (\Exception $e) {
            $this->_log("enableCompatWithOptions() – DB update FAILED: " . $e->getMessage(), 'ERROR');
            Log::add(
                'J2Commerce: Installation blocked — could not automatically enable ' . strip_tags($label) . '. '
                . 'Please enable the backward-compatibility plugin manually and retry. '
                . 'Error: ' . $e->getMessage(),
                Log::WARNING,
                'jerror'
            );
            return false;
        }

        $jMajor = (int) explode('.', JVERSION)[0];
        $label  = $jMajor >= 6
            ? 'Behaviour &ndash; Backward Compatibility 6'
            : 'Behaviour &ndash; Backward Compatibility';

        // If the plugin was disabled at request boot its class aliases are not yet registered.
        // Set the flag so postflight() outputs a prominent restart notice via extension_message
        // (the only channel guaranteed to be visible in Joomla's installer UI).
        // We return TRUE so the files are installed normally; postflight handles the rest.
        if ($wasDisabled) {
            $this->_log("enableCompatWithOptions() – plugin was disabled at boot; flagging for restart notice in postflight");
            Factory::getApplication()->enqueueMessage(
                'The <strong>' . $label . '</strong> plugin was <strong>disabled</strong>. '
                . 'J2Commerce has automatically <strong>enabled it</strong> (as well as its parameters), but its compatibility '
                . 'layer was not yet active when this page loaded. '
                . 'Installation could <strong>not</strong> be completed.<br>'
                . 'Please install J2Commerce again, it will complete fully this time.',
                'error'
            );
            return false;
        }

        // Plugin was already enabled at boot but some options were off — fixed.
        if ($paramsChanged) {
            $this->_log("enableCompatWithOptions() – options were off; fixed, flagging for restart notice in postflight");
            Factory::getApplication()->enqueueMessage(
                'Some or all of the <strong>' . $label . '</strong> plugin parameters were <strong>disabled</strong>. '
                . 'J2Commerce has automatically <strong>enabled them</strong>, but it requires a page reload. '
                . 'Installation could <strong>not</strong> be completed.<br>'
                . 'Please install J2Commerce again, it will complete fully this time.',
                'error'
            );
            return false;
        }

        $this->_log("enableCompatWithOptions() – done, returning true");
        return true;
    }

    /**
     * Returns the #__extensions row for a behaviour plugin by its element name, or null if not found.
     *
     * @param  string $element  Plugin element name (e.g. 'compat' or 'compat6')
     *
     * @return object|null
     */
    private function getBehaviourPluginByElement(string $element): ?object
    {
        try {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(array(
                    $db->quoteName('extension_id'),
                    $db->quoteName('enabled'),
                    $db->quoteName('params'),
                ))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type')    . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder')  . ' = ' . $db->quote('behaviour'))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));
            $db->setQuery($query);

            return $db->loadObject() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Recursively switch boolean-like option values to enabled.
     *
     * @param mixed $value
     * @return mixed
     */
    private function enableBooleanLikeParams($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->enableBooleanLikeParams($child);
            }

            return $value;
        }

        if ($value === false || $value === 0 || $value === '0' || $value === 'false') {
            return '1';
        }

        return $value;
    }
}

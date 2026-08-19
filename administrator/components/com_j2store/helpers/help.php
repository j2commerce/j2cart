<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2024-26 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * J2Store help texts and videos.
 */

class J2Help {

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

    public function watch_video_tutorials()
    {
        $html = '';

        $video_url = 'https://www.youtube.com/@j2commerce';

        $html .= '<div class="video-tutorial panel panel-solid-info alert alert-info" role="alert">';
        $html .= '<span class="me-2">';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
        $html .= Text::_('J2STORE_VIDEO_TUTORIALS_HELP_TEXT');
        $html .= '</span>';
        $html .= '<a class="btn btn-sm btn-primary text-light text-nowrap" target="_blank" href="' . $video_url . '">';
        $html .= Text::_('J2STORE_WATCH');
        $html .= '</a>';
        $html .= '</div>';

    return $html;
  }

	public function free_topbar() {
		$html = '';
		if ( J2Store::isPro() ) {
			return $html;
		}

        $free_topbar_url = J2Store::buildSiteLink('download', 'prolink');

        $html .= '<div class="free-topbar panel panel-solid-info alert alert-info" role="alert">';
        $html .= '<span class="me-2">';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
        $html .= Text::_('J2STORE_FREE_TOPBAR_HELP_TEXT');
        $html .= '</span>';
        $html .= '<a class="btn btn-sm btn-primary text-light text-nowrap" target="_blank" href="' . $free_topbar_url . '">';
        $html .= Text::_('J2STORE_UPGRADE_PRO');
        $html .= '</a>';
        $html .= '</div>';

		return $html;
	}

	public function info_j2commerce()
	{
	    $html = '';

	    $type = 'j2commerce_isnew';

        // Check if this alert to be shown.
	    $params = J2Store::config();
	    if ($params->get($type, 0)) {
	        return $html;
	    }

	    $url = Route::_ ('index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1');

        $html .= '<div class="user-notifications alert alert-info ' . $type . '" role="alert">';
	    $html .= '<p>';
        $html .= '<span class="fas fa-solid fa-info-circle flex-shrink-0 me-2" area-hidden="true"></span>';
	    $html .= Text::_('J2STORE_TAKEOVER_INFO');
	    $html .= '</p>';
	    $html .= '<a class="btn btn-sm btn-dark text-light text-nowrap me-3" href="' . $url . '">' . Text::_('J2STORE_GOT_IT_AND_HIDE') . '</a>';
	    $html .= '<a href="https://www.j2commerce.com/j2store" class="btn btn-sm btn-primary text-light text-nowrap me-3" title="'.Text::_('J2STORE_VISIT_J2COMMERCE').'" target="_blank"><span class="fas fa-solid fa-external-link-alt fa-arrow-up-right-from-square me-2"></span>'.Text::_('J2STORE_FIND_OUT_MORE').'</a>';
	    $html .= '</div>';

	    return $html;
	}

	public function alert($type, $title, $message) {

		$html = '';

        // Check if this alert to be shown.
		$params = J2Store::config();
        if ($params->get($type, 0)) {
            return $html;
        }

		$url = Route::_ ( 'index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . Session::getFormToken() . '=1' );

        $html .= '<div class="user-notifications alert alert-info ' . $type . '" role="alert">';
        $html .= '<h4 class="alert-heading">' . $title . '</h4>';
		$html .= '<p>' . $message . '</p>';
        $html .= '<a class="btn btn-sm btn-dark text-light text-nowrap" href="' . $url . '">' . Text::_('J2STORE_GOT_IT_AND_HIDE') . '</a>';
		$html .= '</div>';

		return $html;
	}

	public function alert_with_static_message($type, $title, $message) {

		$html = '';

        // Message never hidden
        $html .= '<div class="user-notifications alert alert-' . $type . '" role="alert">';
        $html .= '<h4 class="alert-heading">' . $title . '</h4>';
        $html .= '<p>' . $message . '</p>';
		$html .= '</div>';

		return $html;
	}

    /**
     * Checks for evidence that the unauthenticated upload vulnerability
     * (fixed in 4.0.21) was exploited on this site.
     * Returns an HTML alert when exploitation is detected or files are suspicious;
     * returns an empty string when everything looks clean.
     *
     * @return string
     */
    public function security_upload_check(): string
    {
        try {
            $db     = Factory::getDbo();
            $prefix = $db->getPrefix();
            $tables = $db->getTableList();
        } catch (\Exception $e) {
            return '';
        }

        // Cannot determine status without the options table
        if (!in_array($prefix . 'j2store_options', $tables)) {
            return '';
        }

        // Count file-type product options
        try {
            $q = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__j2store_options'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('file'));
            $db->setQuery($q);
            $fileOptionCount = (int) $db->loadResult();
        } catch (\Exception $e) {
            return '';
        }

        // Count upload records
        $dbUploadCount = 0;
        if (in_array($prefix . 'j2store_uploads', $tables)) {
            try {
                $q = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__j2store_uploads'));
                $db->setQuery($q);
                $dbUploadCount = (int) $db->loadResult();
            } catch (\Exception $e) {}
        }

        $protectionFiles    = ['.', '..', '.htaccess', 'web.config'];
        $uploadFiles        = [];
        $suspiciousNames    = [];
        $legacyFiles        = [];
        $invoicesUnexpected = [];
        $protectionMissing  = [];

        $uploadsDir = JPATH_ROOT . '/media/j2store/uploads';
        if (!file_exists($uploadsDir . '/.htaccess')) {
            $protectionMissing[] = 'media/j2store/uploads/.htaccess';
        }
        if (!file_exists($uploadsDir . '/web.config')) {
            $protectionMissing[] = 'media/j2store/uploads/web.config';
        }
        if (is_dir($uploadsDir)) {
            foreach ((array) @scandir($uploadsDir) as $f) {
                if (in_array($f, $protectionFiles)) { continue; }
                $uploadFiles[] = $f;
                if (preg_match('/\.(php\d*|phtml|phar)\./i', $f)) {
                    $suspiciousNames[] = $f;
                }
            }
        }

        $legacyDir = JPATH_ROOT . '/media/com_j2store/uploads';
        if (is_dir($legacyDir)) {
            if (!file_exists($legacyDir . '/.htaccess')) {
                $protectionMissing[] = 'media/com_j2store/uploads/.htaccess';
            }
            foreach ((array) @scandir($legacyDir) as $f) {
                if (in_array($f, $protectionFiles)) { continue; }
                $legacyFiles[] = $f;
                if (preg_match('/\.(php\d*|phtml|phar)\./i', $f)) {
                    $suspiciousNames[] = $f;
                }
            }
        }

        $invoicesDir = JPATH_ROOT . '/media/j2store/invoices';
        if (is_dir($invoicesDir)) {
            foreach ((array) @scandir($invoicesDir) as $f) {
                if (in_array($f, $protectionFiles)) { continue; }
                if (!preg_match('/\.pdf$/i', $f)) {
                    $invoicesUnexpected[] = $f;
                }
            }
        }

        $hasUploadFiles = !empty($uploadFiles) || $dbUploadCount > 0;

        if ($fileOptionCount === 0 && $hasUploadFiles) {
            $verdict = 'hacked';
        } elseif (!empty($suspiciousNames) || !empty($invoicesUnexpected) || !empty($legacyFiles)) {
            $verdict = 'suspicious';
        } else {
            return ''; // clean — nothing to show
        }

        $e = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };

        $html  = '<div class="user-notifications alert alert-' . ($verdict === 'hacked' ? 'danger' : 'warning') . '" role="alert">';
        $html .= '<h4 class="alert-heading">&#x26A0; ';
        $html .= $verdict === 'hacked'
            ? 'Security Alert: Upload Exploitation Detected'
            : 'Security Warning: Suspicious Upload Files Found';
        $html .= '</h4>';

        if ($verdict === 'hacked') {
            $html .= '<p><strong>This site has been exploited.</strong> Files were uploaded through '
                . 'the unauthenticated upload endpoint fixed in J2Store 4.0.21, and no '
                . '&ldquo;File&rdquo; type product option has ever been configured &mdash; '
                . 'meaning all uploads on disk and in the database are foreign. '
                . 'Please re-install J2Store 4.0.21 or later to trigger automatic cleanup, '
                . 'or remove the files manually and truncate the <code>#__j2store_uploads</code> table.</p>';
        } else {
            $html .= '<p><strong>Suspicious files were found.</strong> A &ldquo;File&rdquo; type '
                . 'product option is configured so some uploads may be legitimate, but the '
                . 'items below require manual review.</p>';
        }

        if (!empty($uploadFiles)) {
            $shown = array_slice($uploadFiles, 0, 20);
            $html .= '<p><strong>' . count($uploadFiles) . ' file(s) in <code>media/j2store/uploads/</code>:</strong><br>'
                . '<code>' . $e(implode(', ', $shown))
                . (count($uploadFiles) > 20 ? ' &hellip; and ' . (count($uploadFiles) - 20) . ' more' : '')
                . '</code></p>';
        }

        if ($dbUploadCount > 0) {
            $html .= '<p><strong>' . $dbUploadCount . ' record(s) in <code>#__j2store_uploads</code>.</strong></p>';
        }

        if (!empty($suspiciousNames)) {
            $html .= '<p><strong style="color:inherit;">&#x26A0; Suspicious filenames (double-extension pattern):</strong><br>'
                . '<code>' . $e(implode(', ', $suspiciousNames)) . '</code></p>';
        }

        if (!empty($legacyFiles)) {
            $shown = array_slice($legacyFiles, 0, 20);
            $html .= '<p><strong>' . count($legacyFiles) . ' file(s) in legacy <code>media/com_j2store/uploads/</code>:</strong><br>'
                . '<code>' . $e(implode(', ', $shown))
                . (count($legacyFiles) > 20 ? ' &hellip; and ' . (count($legacyFiles) - 20) . ' more' : '')
                . '</code></p>';
        }

        if (!empty($invoicesUnexpected)) {
            $html .= '<p><strong>Unexpected non-PDF file(s) in <code>media/j2store/invoices/</code>:</strong><br>'
                . '<code>' . $e(implode(', ', $invoicesUnexpected)) . '</code></p>';
        }

        if (!empty($protectionMissing)) {
            $html .= '<p><strong>&#x26A0; Missing directory protection files:</strong><br>'
                . '<code>' . $e(implode(', ', $protectionMissing)) . '</code></p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Checks whether the dompdf library is installed and, if so, whether it is
     * below the minimum required version (3.1.6). Returns an HTML warning when
     * an outdated version is detected; returns an empty string otherwise.
     *
     * @return string
     */
    public function dompdf_check(): string
    {
        try {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type')    . ' = ' . $db->quote('library'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('dompdf'));
            $db->setQuery($query);
            $manifestCache = $db->loadResult();
        } catch (\Exception $e) {
            return '';
        }

        // Library not installed — nothing to warn about.
        if ($manifestCache === null) {
            return '';
        }

        $manifest = json_decode($manifestCache, true);
        $version  = $manifest['version'] ?? '';

        if ($version === '' || version_compare($version, '3.1.6', '>=')) {
            return '';
        }

        $downloadUrl = 'https://github.com/j2commerce/plg_dompdf_library/releases/download/3.1.6/lib_dompdf-v3.1.6.zip';

        $html  = '<div class="user-notifications alert alert-warning alert-dismissible fade show" role="alert">';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . Text::_('JCLOSE') . '"></button>';
        $html .= '<h4 class="alert-heading">&#x26A0; ' . Text::_('J2STORE_ATTENTION') . '</h4>';
        $html .= '<p><strong>The dompdf library is outdated.</strong> '
            . 'Version <strong>' . htmlspecialchars($version, ENT_QUOTES, 'UTF-8') . '</strong> is installed; '
            . 'version <strong>3.1.6</strong> or later is required. '
            . 'Please update via the Joomla Extensions Update page. '
            . 'If the update is not listed there, download it manually using the button below.</p>';
        $html .= '<a class="btn btn-sm btn-warning" href="'
            . htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8')
            . '" target="_blank">Download dompdf 3.1.6</a>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Checks whether any com_j2store or app_bootstrap5 template override files
     * are present on the site but appear to be missing CSRF token protection.
     * Returns an HTML warning when affected files are found; empty string otherwise.
     *
     * @return string
     */
    public function template_override_check(): string
    {
        try {
            $db    = Factory::getDbo();
            $query = 'SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1';
            $db->setQuery($query);
            $template = $db->loadResult();
        } catch (\Exception $e) {
            return '';
        }

        if (!$template) {
            return '';
        }

        $comOverridePath = JPATH_SITE . '/templates/' . $template . '/html/com_j2store';

        $hasFormToken = static function (string $path): bool {
            $content = @file_get_contents($path);
            if ($content === false) {
                return true; // unreadable — skip
            }
            return strpos($content, 'form.token') !== false;
        };

        $hasGetFormToken = static function (string $path): bool {
            $content = @file_get_contents($path);
            if ($content === false) {
                return true; // unreadable — skip
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

        // Files needing a CSRF token added to a PHP array (not a form tag)
        $arrayFormFiles = [
            'carts/default.php',
            'carts/default_items.php',
        ];

        $phpWarnings   = [];
        $jsWarnings    = [];
        $arrayWarnings = [];
        $dataWarnings  = [];

        foreach ($phpFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasFormToken($full)) {
                $phpWarnings[] = 'templates/' . $template . '/html/com_j2store/' . $file;
            }
        }

        foreach ($jsFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasGetFormToken($full)) {
                $jsWarnings[] = 'templates/' . $template . '/html/com_j2store/' . $file;
            }
        }

        foreach ($arrayFormFiles as $file) {
            $full = $comOverridePath . '/' . $file;
            if (file_exists($full) && !$hasGetFormToken($full)) {
                $arrayWarnings[] = 'templates/' . $template . '/html/com_j2store/' . $file;
            }
        }

        // Templates overrides across all site templates
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
                            $rel = ltrim(str_replace(JPATH_SITE, '', $full), '/\\');
                            if ($type === 'php_form') {
                                $phpWarnings[] = $rel;
                            } elseif ($type === 'js_form') {
                                $jsWarnings[] = $rel;
                            } elseif ($type === 'data_form') {
                                $dataWarnings[] = $rel;
                            }
                        }
                    }
                }
            }
        }

        if (empty($phpWarnings) && empty($jsWarnings) && empty($arrayWarnings) && empty($dataWarnings)) {
            return '';
        }

        $e = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };

        $html  = '<div class="user-notifications alert alert-warning alert-dismissible fade show" role="alert">';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . Text::_('JCLOSE') . '"></button>';
        $html .= '<h4 class="alert-heading">&#x26A0; Template Override Token protection</h4>';
        $html .= '<p>The following template override files appear to be missing token protection. '
            . '<strong>Update these files manually.</strong><br>Note: You may not find a place to insert the missing code if your overrides differ significantly from the original files, and you may not need to add it at all.</p>';

        if (!empty($phpWarnings)) {
            $html .= '<p><strong>In the following file(s), add <code>&lt;?php echo JHtml::_(\'form.token\'); ?&gt;</code> '
                . 'immediately before each <code>&lt;/form&gt;</code> closing tag:</strong></p><ul>';
            foreach ($phpWarnings as $f) {
                $html .= '<li><code>' . $e($f) . '</code></li>';
            }
            $html .= '</ul>';
        }

        if (!empty($jsWarnings)) {
            $html .= '<p><strong>In the following file(s), add <code>&lt;?php echo JSession::getFormToken(); ?&gt;</code> '
                . 'as a hidden input name in the JavaScript upload form string:</strong></p><ul>';
            foreach ($jsWarnings as $f) {
                $html .= '<li><code>' . $e($f) . '</code></li>';
            }
            $html .= '</ul>';
            $html .= '<p>Example of the corrected JavaScript form string:</p>';
            $html .= "<pre style=\"background:#fff;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;white-space: pre-wrap; word-wrap: break-word;\">$('body').prepend('&lt;form enctype=\"multipart/form-data\" id=\"form-upload\" style=\"display:none;\" /&gt;";
            $html .= "&lt;input type=\"hidden\" name=\"&lt;?php echo JSession::getFormToken(); ?&gt;\" value=\"1\" /&gt;";
            $html .= "&lt;input type=\"file\" name=\"file\" /&gt;";
            $html .= "&lt;/form&gt;');</pre>";
        }

        if (!empty($arrayWarnings)) {
            $html .= '<p><strong>In the following file(s), add <code>JSession::getFormToken() =&gt; \'1\'</code> to the getCartUrl function.</strong></p>'
                . '<pre style="background:#fff;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;white-space: pre-wrap; word-wrap: break-word;">$platform->getCartUrl(array(JSession::getFormToken() =&gt; \'1\', ...</pre>'
                . '<ul>';
            foreach ($arrayWarnings as $f) {
                $html .= '<li><code>' . $e($f) . '</code></li>';
            }
            $html .= '</ul>';
        }

        if (!empty($dataWarnings)) {
            $html .= '<p><strong>In the following file(s), add <code>data-&lt;?php echo JSession::getFormToken(); ?&gt;=&quot;1&quot;</code> as an attribute on the link with class <code>j2store_add_to_cart_button</code>:</strong></p>'
                . '<pre style="background:#fff;padding:8px;border-radius:3px;font-size:12px;overflow-x:auto;white-space: pre-wrap; word-wrap: break-word;">&lt;a class=&quot;... j2store_add_to_cart_button&quot; data-&lt;?php echo JSession::getFormToken(); ?&gt;=&quot;1&quot;&gt;...&lt;/a&gt;</pre>'
                . '<ul>';
            foreach ($dataWarnings as $f) {
                $html .= '<li><code>' . $e($f) . '</code></li>';
            }
            $html .= '</ul>';
        }

        $html .= '<p>After updating, clear the Joomla cache, if enabled.</p>';
        $html .= '</div>';

        return $html;
    }

}

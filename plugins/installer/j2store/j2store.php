<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.j2store
 *
 * @copyright Copyright (C) 2016 J2Store. All rights reserved.
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Uri\Uri;

class PlgInstallerJ2Store extends CMSPlugin
{
    public function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        if (preg_match('/j2commerce.com\//', $url) == false) {
            return false;
        }

        // dlid used by third-party services like YourSites - the url already contains the dlid - do not go through the pre-checks
        if (strpos($url, 'dlid=') !== false) {
            return true;
        }

        // we will append the dlid to the url, if there is one, for every extension being updated by J2Commerce

        $store = J2Store::storeProfile();
        $downloadId = $store->get('downloadid');
        if (!isset($downloadId) || empty($downloadId)) {
            $downloadId = '';
        }

        $downloadId = trim($downloadId);

        if ($downloadId) {
            $uri = new Uri($url);
            $uri->setVar('dlid', $downloadId);
            $url = $uri->render();

            Factory::getApplication()->enqueueMessage('If you encounter a download error, make sure the Download Id/Key in the <a href="' . Uri::base() . 'index.php?option=com_j2store&view=configuration">configuration settings of J2Commerce</a> is correct and that you have an active subscription.');
        } else {
            Factory::getApplication()->enqueueMessage('If you encounter a download error, you are missing the Download Id/Key in the <a href="' . Uri::base() . 'index.php?option=com_j2store&view=configuration">configuration settings of J2Commerce (Update tab)</a>. Check your account at <a href="https://www.j2commerce.com/my-account" target="_blank">J2Commerce.com</a>.');
        }

        return true;
    }

}

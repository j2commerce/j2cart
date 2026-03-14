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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class J2Image
{
    public static $instance = null;

    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Determine if the given image URL is local (host matches current site) or from a CDN (different host).
     *
     * @param string $imageUrl The image URL to check.
     * @return bool True if local, false if from a CDN.
     */
    public function isLocalImage($imageUrl)
    {
        // If it doesn't start with http:// or https://, it's a relative path (local).
        // Note: filter_var(FILTER_VALIDATE_URL) rejects URLs with spaces, so we use preg_match instead.
        if (!preg_match('#^https?://#i', $imageUrl)) {
            return true;
        }

        // Parse the image URL to get its host (parse_url handles spaces in path correctly)
        $imageHost = parse_url($imageUrl, PHP_URL_HOST);

        // Get the current site's domain
        $siteHost = parse_url(Uri::root(), PHP_URL_HOST);

        // Compare hosts (case-insensitive)
        return strcasecmp($siteHost, $imageHost) === 0;
    }

    /**
     * Get the full URL for an image, ensuring local images have the site root.
     *
     * Handles:
     *  - Plain local URLs:        https://www.j2commerce.com/images/tada.jpg
     *  - Joomla fragment URLs:    https://www.j2commerce.com/images/tada.jpg#joomlaImage://local-images/...
     *  - URLs with spaces:        https://www.j2commerce.com/images/tada tidi.jpg
     *  - External/CDN URLs:       https://joomla.org/images/todo.jpg
     *
     * @param string $imagePath The image path or URL.
     * @return string The full image URL.
     */
    public function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }

        // Strip any Joomla image fragment (#joomlaImage://...) before all other processing.
        // parse_url also naturally strips fragments, but cleanImageURL handles the Joomla-specific format.
        $imageObject = HTMLHelper::cleanImageURL($imagePath);
        $cleanPath   = $imageObject->url;

        if ($this->isLocalImage($cleanPath)) {
            // Extract the path component (strips scheme, host, query, and any remaining fragment)
            $parsedPath = parse_url($cleanPath, PHP_URL_PATH);

            if (empty($parsedPath)) {
                return '';
            }

            // Check if the file physically exists on disk (urldecode handles encoded characters and spaces)
            if (!is_file(JPATH_SITE . '/' . urldecode(ltrim($parsedPath, '/')))) {
                return '';
            }

            // Already a full absolute local URL
            if (preg_match('#^https?://#i', $cleanPath)) {
                return $cleanPath;
            }

            // Relative path - prepend the site root
            return Uri::root() . ltrim($parsedPath, '/');
        }

        // External/CDN image - return the clean path as-is (no file existence check possible)
        return $cleanPath;
    }
}

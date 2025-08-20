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

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

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
        // If it's a relative path, it's definitely local
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Parse the image URL to get its host
        $imageHost = parse_url($imageUrl, PHP_URL_HOST);

        // Get the current site's domain
        $siteHost = parse_url(Uri::root(), PHP_URL_HOST);

        // Compare hosts (case-insensitive)
        return strcasecmp($siteHost, $imageHost) === 0;
    }

    /**
     * Get the full URL for an image, ensuring local images have the site root.
     *
     * @param string $imagePath The image path or URL.
     * @return string The full image URL.
     */
    public function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }
        if ($this->isLocalImage($imagePath)) {
            // Local image - ensure it has the site root
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $imagePath; // Already a full local URL
            }
            return Uri::root() . ltrim($imagePath, '/');
        } else {
            // CDN image - return as-is
            return $imagePath;
        }
    }
}

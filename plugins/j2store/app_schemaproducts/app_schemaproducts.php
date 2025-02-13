<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  J2Commerce.app_schemaproducts
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
use Joomla\CMS\Uri\Uri;

require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/app.php');

class plgJ2StoreApp_schemaproducts extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_schemaproducts';

    /**
     * @param $row
     * @return string|null
     * @throws Exception
     */
    function onJ2StoreGetAppView($row)
    {
        if (!$this->_isMe($row)) {
            return null;
        }
        return $this->viewList();
    }

    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        ToolbarHelper::title(Text::_('J2STORE_APP') . '-' . Text::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        $vars = new \stdClass();
        $vars->id = $app->input->getInt('id', 0);
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$vars->id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

    function onJ2StoreViewProductList(&$items, &$view, &$params, $model)
    {
        $schema = array(
            '@context' => 'https://schema.org/',
            '@type' => "ItemList"
        );
        $i = 1;
        $currency = J2store::currency();
        foreach ($items as $item) {
            $product_url = rtrim(Uri::base(), '/') . '/' . ltrim($item->product_link, '/');

            $item_list = [
                "@type" => "ListItem",
                "item" => [
                    '@type' => "Product",
                    'name' => $item->product_name ?? '',
                    'sku' => $item->sku ?? '',
                    'url' => $product_url,
                ]
            ];

            if (!empty($item->variant->j2store_variant_id ?? '')) {
                $item_list['item']['offers'] = [
                    '@type' => 'Offer',
                    'price' => isset($item->pricing->price) ? round($item->pricing->price, 2) : 0,
                    'priceCurrency' => $currency->getCode() ?? 'USD',
                    'url' => $product_url,
                    'availability' => 'https://schema.org/' . ($item->variant->availability ? 'InStock' : 'OutOfStock'),
                ];
            }

            if (isset($item->main_image) && !empty($item->main_image)) {
                $item_list['item']['image'] = rtrim(Uri::base(), '/') . '/' . ltrim($item->main_image, '/');
            } elseif (isset($item->thumb_image) && !empty($item->thumb_image)) {
                $item_list['item']['image'] = rtrim(Uri::base(), '/') . '/' . ltrim($item->thumb_image, '/');

            }
            if (isset($item->brand_name) && !empty($item->brand_name)) {
                $item_list['item']['brand'] = $item->brand_name;
            }
            if (isset($item->introtext) && !empty($item->introtext)) {
                $item_list['item']['description'] = substr($item->introtext, 0, 200);
            }
            //aggregateRating
            //review
            $schema['itemListElement'][] = $item_list;
            $i++;
        }
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $prettyPrint = JDEBUG ? JSON_PRETTY_PRINT : 0;
        $wa->addInline(
            'script',
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint),
            ['name' => 'inline.category-products-schemaorg'],
            ['type' => 'application/ld+json']
        );
    }

    function onJ2StoreViewProduct(&$item, &$view)
    {
        $product_url = rtrim(Uri::base(), '/') . '/' . ltrim($item->product_link, '/');
        $currency = J2store::currency();
        $item_list = array(
            '@context' => 'https://schema.org/',
            '@type' => "Product",
            'name' => $item->product_name,
            'sku' => (isset($item->variant->sku) && !empty($item->variant->sku)) ? $item->variant->sku : '',
            'url' => $product_url,
            //'position' => 1
        );
        if (isset($item->variant->j2store_variant_id) && !empty($item->variant->j2store_variant_id)) {
            $item_list['offers'] = array(
                '@type' => 'Offer',
                'price' => isset($item->pricing->price) ? round($item->pricing->price, 2) : 0,
                'priceCurrency' => $currency->getCode(),
                'url' => $product_url
            );
            $item_list['offers']['availability'] = 'https://schema.org/' . ($item->variant->availability ? 'InStock' : 'OutOfStock');

        }

        if (isset($item->main_image) && !empty($item->main_image)) {
            $main_image = rtrim(Uri::base(), '/') . '/' . ltrim($item->main_image, '/');
            $item_list['image'] = $main_image;
        } elseif (isset($item->thumb_image) && !empty($item->thumb_image)) {
            $thumb_image = rtrim(Uri::base(), '/') . '/' . ltrim($item->thumb_image, '/');
            $item_list['image'] = $thumb_image;
        }
        if (isset($item->brand_name) && !empty($item->brand_name)) {
            $item_list['brand'] = $item->brand_name;
        }
        if (isset($item->introtext) && !empty($item->introtext)) {
            $item_list['description'] = substr($item->introtext, 0, 200);
        }
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $prettyPrint = JDEBUG ? JSON_PRETTY_PRINT : 0;
        $wa->addInline(
            'script',
            json_encode($item_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | $prettyPrint),
            ['name' => 'inline.product-detail-schemaorg'],
            ['type' => 'application/ld+json']
        );
    }
}

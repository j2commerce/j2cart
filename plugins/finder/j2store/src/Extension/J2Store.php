<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.j2store
 *
 * @copyright   Copyright (c) 2014-17 Ramesh Elamathi / J2Store.org
 * @copyright   Copyright (c) 2024-2026 J2Commerce. All rights reserved.
 * @license     GNU GPL v3 or later
 */

namespace Joomla\Plugin\Finder\J2Store\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search adapter for com_j2store.
 *
 * This is a hybrid implementation that uses Joomla 5/6 service provider patterns
 * while maintaining compatibility with J2Store's existing coding style.
 *
 * @since  4.1.0
 */
final class J2Store extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'J2Store';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_j2store';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'products';

    /**
     * The task for product view.
     *
     * @var    string
     * @since  2.5
     */
    protected $task = 'view';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'J2Store Products';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__content';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderCategoryChangeState' => 'onFinderCategoryChangeState',
            'onFinderChangeState'         => 'onFinderChangeState',
            'onFinderAfterDelete'         => 'onFinderAfterDelete',
            'onFinderBeforeSave'          => 'onFinderBeforeSave',
            'onFinderAfterSave'           => 'onFinderAfterSave',
            'onFinderIndexAfterIndex'     => 'onFinderIndexAfterIndex',
        ]);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        // Load the main J2Store helper first (bootstraps FOF and other dependencies)
        $j2storeHelper = JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/j2store.php';
        if (file_exists($j2storeHelper)) {
            require_once $j2storeHelper;
        }

        // Load the site router
        $siteRouter = JPATH_SITE . '/components/com_j2store/router.php';
        if (file_exists($siteRouter)) {
            include_once $siteRouter;
        }

        return true;
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   FinderEvent\AfterCategoryChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState(FinderEvent\AfterCategoryChangeStateEvent $event): void
    {
        // Make sure we're handling com_j2store categories.
        if ($event->getExtension() === 'com_j2store') {
            $this->categoryStateChange($event->getPks(), $event->getValue());
        }
    }

    /**
     * Method to update index data on category access level changes
     *
     * @param   array    $pks    A list of primary key ids of the content that has changed state.
     * @param   integer  $value  The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function categoryStateChange($pks, $value)
    {
        $db = $this->getDatabase();

        foreach ($pks as $pk) {
            $query = clone($this->getStateQuery());
            $query->where('c.id = ' . $db->quote((int) $pk));
            $query->select('p.*');
            $query->join(
                'INNER',
                '#__j2store_products AS p ON p.product_source = ' . $db->quote('com_content')
                . ' AND p.product_source_id = c.id AND p.enabled = 1'
            );

            // Get the published states.
            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Adjust the state for each item within the category.
            foreach ($items as $item) {
                // Translate the state.
                $temp = $this->translateState($item->state, $value);

                // Update the item.
                $this->change($item->j2store_product_id, 'state', $temp);

                // Reindex the item
                $this->reindex($item->j2store_product_id);
            }
        }
    }

    /**
     * Method to change the value of a content item's property in the links
     * table. This is used to synchronize published and access states that
     * are changed when not editing an item directly.
     *
     * @param   string   $id        The ID of the item to change.
     * @param   string   $property  The property that is being changed.
     * @param   integer  $value     The new value of that property.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function change($id, $property, $value)
    {
        // Check for a property we know how to handle.
        if ($property !== 'state' && $property !== 'access') {
            return true;
        }

        $db = $this->getDatabase();

        // Get the url for the content id.
        $item = $db->quote($this->getUrl($id, $this->extension, $this->layout));

        // Update the content items.
        $query = $db->createQuery()
            ->update($db->quoteName('#__finder_links'))
            ->set($db->quoteName($property) . ' = ' . $db->quote((int) $value))
            ->where($db->quoteName('url') . ' = ' . $item);
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_j2store.product') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove item from the index.
        $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for an article that has been saved.
     * It also makes adjustments if the access level of an item or the
     * category to which it belongs has changed.
     *
     * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle articles here.
        if ($context === 'com_j2store.article' || $context === 'com_j2store.product') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item.
            $this->reindex($row->id);
        }

        // Check for access changes in the category.
        if ($context === 'com_categories.category') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }

        // Handle excluding com_content articles when they have a J2Store product
        // This prevents duplicate search results (article AND product)
        if ($this->params->get('exclude_linked_articles', 0) && ($context === 'com_content.article' || $context === 'com_content.form')) {
            $articleId = (int) ($row->id ?? 0);

            if ($articleId > 0) {
                // Check if this article has an associated J2Store product
                if ($this->hasJ2StoreProduct($articleId)) {
                    // Remove the article from the finder index
                    $this->removeArticleFromIndex($articleId);
                }
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   FinderEvent\BeforeSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle articles here.
        if ($context === 'com_j2store.article' || $context === 'com_j2store.product') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category.
        if ($context === 'com_categories.category') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkCategoryAccess($row);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle articles here.
        if ($context === 'com_j2store.article' || $context === 'com_j2store.product') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method called after an item has been indexed by Smart Search.
     *
     * This is triggered for ALL items indexed, not just J2Store items.
     * We use this to detect when plg_finder_content indexes an article
     * that has an associated J2Store product, and remove the duplicate
     * article entry from the index.
     *
     * @param   \Joomla\Event\Event  $event  The event object containing item and linkId.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function onFinderIndexAfterIndex(\Joomla\Event\Event $event): void
    {
        // Only process if exclusion is enabled
        if (!$this->params->get('exclude_linked_articles', 0)) {
            return;
        }

        // Extract arguments from the event
        // Event is triggered with: triggerEvent('onFinderIndexAfterIndex', [$item, $linkId])
        $arguments = $event->getArguments();
        $item = $arguments[0] ?? null;
        $linkId = (int) ($arguments[1] ?? 0);

        if (!$item instanceof Result || $linkId <= 0) {
            return;
        }

        // Check if this is a com_content article (indexed by plg_finder_content)
        // The context is set by plg_finder_content when indexing articles
        if (!isset($item->context) || $item->context !== 'com_content.article') {
            return;
        }

        // Get the article ID from the item
        $articleId = (int) ($item->id ?? 0);

        if ($articleId <= 0) {
            return;
        }

        // Check if this article has an associated J2Store product
        if ($this->hasJ2StoreProduct($articleId)) {
            // Remove this article link from the index - it was just indexed by plg_finder_content
            // but we want only the J2Store product to appear in search results
            //$this->indexer->remove($linkId); // The $this->indexer property is not available in this context.

            // Remove directly from database to avoid indexer conflicts during indexing
            $db = $this->getDatabase();

            // Delete from finder_links_terms
            $query = $db->createQuery()
                ->delete($db->quoteName('#__finder_links_terms'))
                ->where($db->quoteName('link_id') . ' = :linkId')
                ->bind(':linkId', $linkId, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            // Delete from finder_taxonomy_map
            $query = $db->createQuery()
                ->delete($db->quoteName('#__finder_taxonomy_map'))
                ->where($db->quoteName('link_id') . ' = :linkId')
                ->bind(':linkId', $linkId, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            // Delete from finder_links
            $query = $db->createQuery()
                ->delete($db->quoteName('#__finder_links'))
                ->where($db->quoteName('link_id') . ' = :linkId')
                ->bind(':linkId', $linkId, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Method to get the page title of any menu item that is linked to the
     * content item, if it exists and is set.
     *
     * @param   string  $url  The url of the item.
     *
     * @return  mixed  The title on success, null if not found.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function getItemMenuTitle($url)
    {
        $return = null;
        $db     = $this->getDatabase();

        // Set variables
        $user   = $this->getApplication()->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        // Build a query to get the menu params.
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' = ' . $db->quote($url))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('access') . ' IN (' . $groups . ')');

        // Get the menu params from the database.
        $db->setQuery($query);
        $params = $db->loadResult();

        // Check the results.
        if (empty($params)) {
            return $return;
        }

        // Instantiate the params.
        $params = json_decode($params);

        // Get the page title if it is set.
        if (isset($params->page_title) && $params->page_title) {
            $return = $params->page_title;
        }

        return $return;
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        $item->setLanguage();

        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        // Initialize the item parameters
        $registry = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_j2store', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params, $item);
        $item->body    = Helper::prepareContent($item->body, $item->params, $item);

        // Determine URL based on redirect setting
        $redirectTo = $this->params->get('redirect_to', 'j2store');

        if ($redirectTo === 'article') {
            // Redirect to the article view
            $item->url = $this->getUrl($item->id, 'com_content', 'article');
            $item->route = RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language);
            $item->path = $this->getContentPath($item->route);
        } else {
            // Redirect to J2Store product view
            $productId = (int) ($item->j2store_product_id ?? 0);

            if ($productId > 0) {
                // Get the fallback menu ID from plugin params
                $menuId = (int) $this->params->get('menuitem_id', 0);

                // Load the router helper (must be done after j2store.php is loaded in setup())
                require_once JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/router.php';

                // Use J2StoreRouterHelper to find the correct menu item for this product
                $qoptions = [
                    'option' => 'com_j2store',
                    'view'   => 'products',
                    'task'   => 'view',
                    'id'     => $productId,
                ];
                $productMenu = \J2StoreRouterHelper::findProductMenu($qoptions);

                // Use product-specific menu if found, otherwise fall back to configured menu
                $menuId = isset($productMenu->id) ? $productMenu->id : $menuId;

                $item->url = $this->getJ2StoreUrl($productId, $this->extension, $this->layout);
                $item->route = 'index.php?option=com_j2store&view=products&task=view&id=' . $productId . '&Itemid=' . $menuId;
                $item->path = $this->getContentPath($item->route);
            } else {
                // Fallback to article route if no product ID
                $item->url = $this->getUrl($item->id, 'com_content', 'article');
                $item->route = RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language);
                $item->path = $this->getContentPath($item->route);
            }
        }

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        // Add the meta-author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the meta-data processing instructions.
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');
        $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Add SKU and UPC fields to the meta context for searching
        $productId = (int) ($item->j2store_product_id ?? 0);
        if ($productId > 0) {
            $variantData = $this->getProductVariantData($productId);

            if (!empty($variantData['skus'])) {
                $item->addInstruction(Indexer::META_CONTEXT, 'skus');
                $item->skus = implode(' ', $variantData['skus']);
            }

            if (!empty($variantData['upcs'])) {
                $item->addInstruction(Indexer::META_CONTEXT, 'upcs');
                $item->upcs = implode(' ', $variantData['upcs']);
            }
        }

        // Set product image (thumbnail first, then main image)
        if ($this->params->get('show_product_image', 1)) {
            $imagePath = null;

            if (!empty($item->thumb_image)) {
                $imagePath = $item->thumb_image;
            } elseif (!empty($item->main_image)) {
                $imagePath = $item->main_image;
            }

            if ($imagePath) {
                $item->imageUrl = $imagePath;
                $item->imageAlt = $item->main_image_alt ?? $item->title;
            }
        }

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', $this->type_title);

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy(
                'Author',
                !empty($item->created_by_alias) ? $item->created_by_alias : $item->author
            );
        }

        // Add the category taxonomy data.
        $item->addTaxonomy('J2Store Category', $item->category, $item->cat_state, $item->cat_access);

        // Add the Brand taxonomy data if available
        if (!empty($item->brand)) {
            $item->addTaxonomy('J2Store Brand', $item->brand);
        }

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A QueryInterface object or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->createQuery()
            ->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body')
            ->select('a.state, a.catid, a.created AS start_date, a.created_by')
            ->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params')
            ->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access')
            ->select('p.*');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAs('CHAR', 'a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id = $query->castAs('CHAR', 'c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias);

        $query->select('u.name AS author')
            ->from('#__content AS a');

        // J2Store product join - only index enabled products
        $query->join(
            'INNER',
            '#__j2store_products AS p ON p.product_source = ' . $db->quote('com_content')
            . ' AND p.product_source_id = a.id AND p.enabled = 1'
        );

        // Manufacturer/brand joins
        $query->select('m.*');
        $query->join(
            'LEFT',
            '#__j2store_manufacturers AS m ON m.j2store_manufacturer_id = p.manufacturer_id'
        );
        $query->select('addr.company as brand');
        $query->join(
            'LEFT',
            '#__j2store_addresses AS addr ON addr.j2store_address_id = m.address_id'
        );

        // Category and user joins
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid')
            ->join('LEFT', '#__users AS u ON u.id = a.created_by');

        // Product images join
        $query->select('img.main_image, img.thumb_image, img.main_image_alt');
        $query->join(
            'LEFT',
            '#__j2store_productimages AS img ON img.product_id = p.j2store_product_id'
        );

        return $query;
    }

    /**
     * Method to get a J2Store product URL.
     *
     * @param   integer  $id         The id of the item.
     * @param   string   $extension  The extension the item belongs to.
     * @param   string   $view       The view for the URL.
     *
     * @return  string  The URL of the item.
     *
     * @since   2.5
     */
    protected function getJ2StoreUrl($id, $extension, $view)
    {
        return 'index.php?option=' . $extension . '&view=' . $view . '&task=view&id=' . $id;
    }

    /**
     * Method to get the content path for a route.
     *
     * This generates a relative URL path from a route using the site router.
     *
     * @param   string  $url  The route URL.
     *
     * @return  string  The relative content path.
     *
     * @since   4.1.0
     */
    protected function getContentPath($url)
    {
        $router = \Joomla\CMS\Router\Router::getInstance('site');

        // Build the relative route
        $uri = $router->build($url);
        $route = $uri->toString(['path', 'query', 'fragment']);
        $route = str_replace(\Joomla\CMS\Uri\Uri::base(true) . '/', '', $route);

        return $route;
    }

    /**
     * Get SKU and UPC data for a product from the variants table.
     *
     * @param   int  $productId  The J2Store product ID.
     *
     * @return  array{skus: array, upcs: array}  Arrays of SKUs and UPCs for the product.
     *
     * @since   4.1.0
     */
    protected function getProductVariantData(int $productId): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query->select($db->quoteName(['sku', 'upc']))
            ->from($db->quoteName('#__j2store_variants'))
            ->where($db->quoteName('product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);
        $variants = $db->loadObjectList();

        $skus = [];
        $upcs = [];

        foreach ($variants as $variant) {
            if (!empty($variant->sku)) {
                $skus[] = $variant->sku;
            }
            if (!empty($variant->upc)) {
                $upcs[] = $variant->upc;
            }
        }

        return [
            'skus' => $skus,
            'upcs' => $upcs,
        ];
    }

    /**
     * Remove an article from the Smart Search index.
     *
     * This is called when an article is saved and has an associated
     * J2Store product, to prevent duplicate search results.
     *
     * @param   int  $articleId  The article ID
     *
     * @return  void
     *
     * @since   4.1.0
     */
    protected function removeArticleFromIndex(int $articleId): void
    {
        $db = $this->getDatabase();

        // Build the URL that plg_finder_content would use for this article
        $articleUrl = 'index.php?option=com_content&view=article&id=' . $articleId;

        // Find and remove the article from the finder links table
        $query = $db->createQuery()
            ->select($db->quoteName('link_id'))
            ->from($db->quoteName('#__finder_links'))
            ->where($db->quoteName('url') . ' = :url')
            ->bind(':url', $articleUrl);

        $db->setQuery($query);
        $linkIds = $db->loadColumn();

        if (!empty($linkIds)) {
            foreach ($linkIds as $linkId) {
                //$this->indexer->remove((int) $linkId); // The $this->indexer property is not available in this context.
                $linkId = (int) $linkId;

                // Delete from finder_links_terms
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__finder_links_terms'))
                    ->where($db->quoteName('link_id') . ' = :linkId')
                    ->bind(':linkId', $linkId, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();

                // Delete from finder_taxonomy_map
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__finder_taxonomy_map'))
                    ->where($db->quoteName('link_id') . ' = :linkId')
                    ->bind(':linkId', $linkId, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();

                // Delete from finder_links
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__finder_links'))
                    ->where($db->quoteName('link_id') . ' = :linkId')
                    ->bind(':linkId', $linkId, ParameterType::INTEGER);
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
     * Check if an article has an associated J2Store product
     *
     * @param   int  $articleId  The article ID
     *
     * @return  bool
     *
     * @since   4.1.0
     */
    protected function hasJ2StoreProduct(int $articleId): bool
    {
        $db = $this->getDatabase();

        $query = $db->createQuery()
            ->select('1')
            ->from($db->quoteName('#__j2store_products'))
            ->where($db->quoteName('product_source') . ' = ' . $db->quote('com_content'))
            ->where($db->quoteName('product_source_id') . ' = :articleId')
            ->where($db->quoteName('enabled') . ' = 1')
            ->bind(':articleId', $articleId, ParameterType::INTEGER)
            ->setLimit(1);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }
}

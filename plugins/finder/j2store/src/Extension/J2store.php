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

namespace J2Commerce\Plugin\Finder\J2store\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

require_once JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/j2store.php';
require_once JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/router.php';

/**
 * Smart Search adapter for com_j2store.
 */
final class J2store extends Adapter
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     */
    protected $context = 'J2Store';

    /**
     * The extension name.
     *
     * @var    string
     */
    protected $extension = 'com_j2store';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     */
    protected $layout = 'products';

    protected $task = 'view';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     */
    protected $type_title = 'J2Store Products';

    /**
     * The table name.
     *
     * @var    string
     */
    protected $table = '#__content';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     */
    protected function setup()
    {
        // Load dependent classes.
        include_once JPATH_SITE . '/components/com_j2store/router.php';

        return true;
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     */
    public function onFinderCategoryChangeState($extension, $pks, $value)
    {
        // Make sure we're handling com_j2store categories.
        if ($extension === 'com_j2store') {
            $this->categoryStateChange($pks, $value);
        }
    }

    /**
     * Method to update index data on category access level changes
     *
     * @param   array    $pks    A list of primary key ids of the content that has changed state.
     * @param   integer  $value  The value of the state that the content has been changed to.
     *
     * @return  void
     */
    protected function categoryStateChange($pks, $value)
    {
        // The item's published state is tied to the category published state so we need to look up all published states before we change anything.
        $db = $this->getDatabase();

        foreach ($pks as $pk) {
            $query = clone($this->getStateQuery());

            $query->where($db->quoteName('c.id') . ' = ' . $db->quote((int) $pk));
            $query->select($db->quoteName('#__j2store_products') . '.*');
            $query->join(
                'INNER',
                $db->quoteName('#__j2store_products'),
                $db->quoteName('#__j2store_products.product_source') . ' = ' . $db->quote('com_content') . ' AND ' .
                $db->quoteName('#__j2store_products.product_source_id') . ' = ' . $db->quoteName('c.id') . ' AND ' .
                $db->quoteName('#__j2store_products.enabled') . ' = 1'
            );

            $this->db->setQuery($query);
            $items = $this->db->loadObjectList();

            // Adjust the state for each item within the category.
            foreach ($items as $item) {
                // Translate the state.
                $temp = $this->translateState($item->state, $value);

                // Update the item.
                $this->change($item->j2store_product_id, 'state', $temp);

                //$this->reindex($item->id);
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
     * @throws    \Exception on database error.
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
        $query = $db->getQuery(true)
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
     * @param   string  $context  The context of the action being performed.
     * @param   Table  $table    A JTable object containing the record to be deleted
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete($context, $table)
    {
        if ($context === 'com_j2store.article' || $context === 'com_j2store.product') {
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
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table   $row      A JTable object.
     * @param   boolean  $isNew    True if the content has just been created.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew)
    {
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
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table   $row      A JTable object.
     * @param   boolean  $isNew    If the content is just about to be created.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
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
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     */
    public function onFinderChangeState($context, $pks, $value)
    {
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
     * Method to get the page title of any menu item that is linked to the
     * content item, if it exists and is set.
     *
     * @param   string  $url  The url of the item.
     *
     * @return  mixed  The title on success, null if not found.
     *
     * @throws  \Exception on database error.
     */
    protected function getItemMenuTitle($url)
    {
        $return = null;

        // Set variables
        $user = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        $db = $this->getDatabase();

        // Build a query to get the menu params.
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' = :url')
            ->where($db->quoteName('published') . ' = 1')
            ->whereIn($db->quoteName('access'), $groups)
            ->bind(':url', $url, ParameterType::STRING);

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
        if ($params->page_title) {
            $return = $params->page_title;
        }

        return $return;
    }

    /**
     * Method to index an item. The item must be a IndexerResult object.
     *
     * @param   Result  $item    The item to index as a Result object.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        $item->setLanguage();
        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $registry     = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_j2store', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params);
        $item->body = Helper::prepareContent($item->body, $item->params);

        //let us get the redirect choice
        if ($this->params->get('redirect_to','j2store') ==='article') {
            // Build the necessary route and path information.
            $item->url = $this->getURL($item->id, $this->extension, $this->layout);
            $item->route = ContentRouteHelper::getArticleRoute($item->slug, $item->catslug, $item->language);
            $item->path = $this->getContentPath($item->route);
        } else {
            $menu_id =  $this->params->get('menuitem_id');

            $qoptions = array (
                'option' => 'com_j2store',
                'view' => 'products',
                'task' => 'view',
                'id' => $item->j2store_product_id
            );

            $pro_menu = \J2StoreRouterHelper::findProductMenu($qoptions);
            $menu_id = isset($pro_menu->id) ? $pro_menu->id : $menu_id;
            $item->url =  $this->getJ2StoreURL($item->j2store_product_id, $this->extension, $this->layout);
            $item->route = 'index.php?option=com_j2store&view=products&task=view&id='.$item->j2store_product_id.'&Itemid='.$menu_id;
            $item->path = $this->getContentPath($item->route);
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

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', $this->type_title);

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
        }

        // Add the category taxonomy data.
        $item->addTaxonomy('J2Store Category', $item->category, $item->cat_state, $item->cat_access);
        $brandmodel = \J2Store::fof()->getModel('Manufacturers' ,'J2StoreModel');
        $brandmodel->enabled(1);

        Helper::getContentExtras($item);

        // Add the Brand taxonomy data.
        $item->addTaxonomy('J2Store Brand', $item->brand);

        // Index the item.
        $this->indexer->index($item);
    }

    protected function getContentPath($url)
    {
        $router = Factory::getContainer()->get(SiteRouter::class);
        // Build the relative route.
        $uri   = $router->build($url);
        $route = $uri->toString(array('path', 'query', 'fragment'));
        $route = str_replace(Uri::base(true) . '/', '', $route);

        return $route;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
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
        $a_id = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id = $query->castAsChar('c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias);

        $query->from('#__content AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid')
            ->join('LEFT', '#__users AS u ON u.id = a.created_by')
            ->join('INNER', $db->quoteName('#__j2store_products', 'p'), $db->quoteName('p.product_source') . ' = ' . $db->quote('com_content') . ' AND ' . $db->quoteName('p.product_source_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p.enabled') . ' = 1')
            ->join('LEFT', '#__j2store_manufacturers as m ON m.j2store_manufacturer_id = p.manufacturer_id')
            ->join('LEFT', '#__j2store_addresses as ad ON ad.j2store_address_id= m.address_id');

        $query->select('u.name AS author');
        $query->select('m.*');
        $query->select('ad.company as brand');

        return $query;
    }

    protected function getJ2StoreURL($id, $extension, $view)
    {
        return 'index.php?option=' . $extension . '&view=' . $view . '&task=view&id=' . $id;
    }

    protected function getURL($id, $extension, $view)
    {
        return 'index.php?option=' . $extension . '&view=' . $view . '&id=' . $id;
    }
}

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  system_j2canonical
 *
 * @copyright Copyright (C) 2024-2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

namespace J2Commerce\Plugin\System\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Log\Log;



// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

if (!defined('F0F_INCLUDED'))
{
    include_once JPATH_LIBRARIES . '/f0f/include.php';
}
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');


final class J2canonical extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Cache of product→category mappings
     *
     * @var   array
     */
    protected static $j2_products = [];
	protected $app;
	protected $autoloadLanguage = true;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher object.
     * @param   array                $config      An optional associative array of configuration settings.
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead'  => 'onBeforeCompileHead',
            'onAfterRoute'  => 'onAfterRoute',
        ];
    }

    public function onBeforeCompileHead()
    {
        $app = Factory::getApplication();
        if ($app->isClient('administrator')) {
            return;
        }

        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $task = $app->input->get('task');

        if($option == 'com_j2store' && in_array($view, array('products','producttags'))  && $task == 'view' && $this->canonical) {
            $doc = Factory::getApplication()->getDocument();

            foreach ( $doc->_links as $k => $array ) {
                if ( $array['relation'] == 'canonical' ) {
                    unset($doc->_links[$k]);
                }
            }
            if(!empty($this->canonical)){
                $doc->addHeadLink(htmlspecialchars($this->canonical), 'canonical');
            }
        }
    }


    public function onAfterRoute()
    {
        // Get the application object
        $app = Factory::getApplication();
        if ($app->isClient('administrator')) {
            return;
        }
        $fof_helper = \J2Store::fof();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $task = $app->input->get('task');
        $item_id = $app->input->get('Itemid',0);

        if($option == 'com_j2store' && in_array($view, array('products','producttags')) && $task == 'view' && $item_id) {

            //get the product id
            $j2_product_id = $app->input->getInt('id');
            $menu = $app->getMenu();
            //current menu item
            $current_item = $menu->getItem( $item_id );
            $menu_params = $current_item->getParams();
            $canonical_menu = $menu_params->get('canonical_menu',0);
            //canonical menu form current menu
            $canonical_item = '';
            if($canonical_menu > 0){
                $canonical_item = $menu->getItem( $canonical_menu );
            }

            //find the article id
            $j2prod = $fof_helper->loadTable('Products','J2StoreTable');
            $j2prod->load($j2_product_id);
            if($j2prod->j2store_product_id == $j2_product_id){
                $base = trim(Uri::base(),'/');
                $url = '';
                $current_url_canonical = $this->params->get('current_url_canonical',1);
                if($view == 'products'){
                    $cat_ids = $this->getProductCatId($j2prod->j2store_product_id);
                    // for multi category
                    if($cat_ids){
                        $cat_ids = explode(',',$cat_ids);
                    } else {
                        $cat_ids = array(); // Empty array when $cat_ids is 0 or empty
                    }

                    if(empty($canonical_item) && $current_url_canonical){
                        $url = 'index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$current_item->id;
                    }elseif (!empty($canonical_item)){
                        if(isset($canonical_item->query['catid']) && !empty($canonical_item->query['catid'])){
                            foreach ($cat_ids as $key=>$catid){
                                if(in_array($catid,$canonical_item->query['catid'])){
                                    $url = 'index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$canonical_item->id;
                                    break;
                                }
                            }
                        }
                        if (empty($url) && $current_url_canonical){
                            $url = 'index.php?option=com_j2store&view=products&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$current_item->id;
                        }
                    }

                }elseif ($view == 'producttags'){

                    $tag_list = $this->getProductTags($j2prod->product_source_id,$j2prod->product_source);

                    if(empty($canonical_item) && $current_url_canonical){
                        $url = 'index.php?option=com_j2store&view=producttags&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$current_item->id;
                    }elseif (!empty($canonical_item)){
                        if(isset($canonical_item->query['tag']) && !empty($canonical_item->query['tag'])){
                            if(in_array($canonical_item->query['tag'],$tag_list)){
                                $url = 'index.php?option=com_j2store&view=producttags&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$canonical_item->id;
                            }
                        }
                    }
                    if (empty($url) && $current_url_canonical){
                        $url = 'index.php?option=com_j2store&view=producttags&task=view&id='.$j2prod->j2store_product_id.'&Itemid='.$current_item->id;
                    }
                }

                if ( !empty($url) ) {
                    $url = Route::_($url);
                    $this->canonical = $base.'/'.trim($url,'/');
                }
            }
        }
    }

    /**
     * Get the products and their category ids mapped in a static variable
     * @param int $product_id J2Store product id
     * @return string category ids as csv
     * */
    public function getProductCatId($product_id) {
        if ( !empty(self::$j2_products) && isset(self::$j2_products[$product_id]) ) {
            return self::$j2_products[$product_id];
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $qry = $db->getQuery(true);
        $qry -> select('jp.j2store_product_id,c.catid')
            -> from('#__j2store_products jp')
            -> where('jp.product_source='.$db->q('com_content'))
            -> join('LEFT','#__content c ON c.id=jp.product_source_id');
        $db->setQuery($qry);
        self::$j2_products = $db->loadAssocList('j2store_product_id', 'catid');

        if ( !empty(self::$j2_products) && isset(self::$j2_products[$product_id]) ) {
            return self::$j2_products[$product_id];
        }else {
            return 0;
        }
    }

    public function getProductTags($source_id,$source_type){
        $tag_list = array();
        if($source_type == 'com_content'){
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('tmap.tag_id,tag.alias')->from('#__contentitem_tag_map as tmap')
                ->join('LEFT','#__tags as tag ON tmap.tag_id = tag.id')
                ->where('tmap.content_item_id ='.$db->q($source_id))
                ->where('tmap.type_alias ='.$db->q('com_content.article'));
            $db->setQuery($query);
            $list = $db->loadObjectList();
            foreach ($list as $tag){
                $tag_list[] = $tag->alias;
            }
        }

        return $tag_list;
    }
}

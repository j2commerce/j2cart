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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/productbase.php');

class J2StoreControllerProducttags extends J2StoreControllerProductsBase
{
	protected $view = 'producttags';
	protected $cacheableTasks = [];
	var $_catids = [];

	public function browse()
    {
		//first clear cache
		$utility = J2Store::utilities();
		$utility->nocache();
		$utility->clear_cache();

		$app = Factory::getApplication();
		$session = $app->getSession();
        $db = Factory::getContainer()->get('DatabaseDriver');
		$active	= $app->getMenu()->getActive();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$document = $app->getDocument();
		$lang = $app->getLanguage();

		$manufacturer_ids = $app->input->get('manufacturer_ids', array(), 'ARRAY');
		$vendor_ids = $app->input->get('vendor_ids', array(), 'ARRAY');
		$productfilter_ids = $app->input->get('productfilter_ids', array(), 'ARRAY');

		$ns = 'com_j2store.'.$this->getName();
		$params = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getMergedParams();
		if($params->get('list_show_vote', 0)) {
			$params->set('show_vote', 1);
		}
		$view = $this->getThisView();

		$model = $this->getModel('Producttags');

		$view->setModel($model);

		$states = $this->getSFFilterStates();

		if(!empty($manufacturer_ids)){
			$session->set('manufacturer_ids', $manufacturer_ids, 'j2store');
			$states['manufacturer_id'] = implode(',',$utility->cleanIntArray($manufacturer_ids, $db));
		}else{
			$session->clear('manufacturer_ids', 'j2store');
			$states['manufacturer_id'] = '';
		}
		if(!empty($vendor_ids)){
			$session->set('vendor_ids', $vendor_ids, 'j2store');
			$states['vendor_id']= implode(',',$utility->cleanIntArray($vendor_ids, $db));
		}else{
			$session->clear('vendor_ids', 'j2store');
			$states['vendor_id']= '';
		}
		if(!empty($productfilter_ids)){
			$session->set('productfilter_ids', $productfilter_ids, 'j2store');
			//set filter search condition
			$session->set('list_product_filter_search_logic_rel', $params->get('list_product_filter_search_logic_rel', 'OR'), 'j2store');
			$states['productfilter_id'] = implode(',',$utility->cleanIntArray($productfilter_ids, $db));

		}else{
			$session->clear('productfilter_ids', 'j2store');
			$session->clear('list_product_filter_search_logic_rel', 'j2store');
			$states['productfilter_id'] ='';
		}

		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		// Get the pagination request variables
		$limit		= $params->get('page_limit');
		$show_feature_only	= $params->get('show_feature_only',0);
		$model->setState('show_feature_only', $show_feature_only);
		$model->setState('list.limit', $limit);
		$limitstart = $app->input->get('limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$model->setState('list.start', $limitstart);

		$orderCol = $app->getUserStateFromRequest('com_j2store.producttag.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $model->get_filter_fields()))
		{
			$orderCol = 'a.ordering';
		}
		$model->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_j2store.producttag.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$model->setState('list.direction', $listOrder);

		foreach($states as $key => $value){
			$model->setState($key,$value);
		}

		$filter_tag = $app->input->getString('filter_tag',$session->get('filter_tag' ,'','j2store'));

		if(!empty( $filter_tag )){
			$tag_id = $this->getTagId($filter_tag);
			$model->setState('tagid', $tag_id);
		}else{
			$filter_tag = $app->input->getString ( 'tag','' );
			$tag_id = $this->getTagId($filter_tag);
			$model->setState('tagid', $tag_id);
		}

		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subtag_content', '0');

		if ($showSubcategories)
		{
			$model->setState('filter.max_tag_levels', $params->get('show_subtag_content', '1'));
			$model->setState('filter.subtags', true);
		}

		$model->setState('filter.language', Multilanguage::isEnabled());

		$model->setState('enabled', 1);
		$model->setState('visible', 1);
		$search = $app->input->getString('search', '');
		$model->setState('search', $search);

		//set product ids
		$items = $model->getSFProducts();
		$filter_items = $model->getSFAllProducts();

		$filters = [];
		$filters = $this->getFilters($filter_items);
		if(count($items)) {
			foreach($items as &$item) {
				//run the content plugins
				$model->executePlugins($item, $params, 'com_content.category.productlist');
			}
			//process the raw items as products
			$this->processProducts($items);

			$pagination = $model->getSFPagination();
			//only do this if it is the default home page
			if($active == $menus->getDefault($lang->getTag())) {
				$post_data = $app->input->getArray($_GET);

				foreach($post_data as $key=>$value){
					if(is_array($value)){
						foreach($value as $key_i=>$value_i){
							$pagination->setAdditionalUrlParam($key.'['.$key_i.']',$value_i);
						}
					}else{
						$pagination->setAdditionalUrlParam($key,$value);
					}
				}
			}
            J2Store::plugin()->event('ViewProductTagPagination', array(&$items, &$pagination, &$params, $model));
			$view->set('pagination', $pagination);
		}

		$filters['pricefilters'] = $this->getPriceRanges($items);

		//set up document
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$item_id = $app->input->get('Itemid',0);
		if($item_id){
			$menu = $menus->getItem($item_id);
		}else{
			$menu = $menus->getActive();
		}

		if(empty( $menu )){
			// some time without menu accessing page
			require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/router.php');
			$qoptions = array (
				'option' => 'com_j2store',
				'view' => 'producttags',
				'task' => 'browse',
				'tag' => $filter_tag,

			);
			$menu = \J2Commerce\Helper\J2StoreRouterHelper::findProductTagsMenu( $qoptions );
		}

		if ($menu)
		{
			$params->def('page_heading', $params->get('page_title', $menu->title));
		}

		$title = $params->get('page_title', '');

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) === 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) === 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		$document->setTitle($title);

		$meta_description = $params->get('menu-meta_description');
		$document->setDescription($meta_description);

		$keywords = $params->get('menu-meta_keywords');
		$document->setMetaData('keywords', $keywords);

		$robots = $params->get('robots');
		$document->setMetaData('robots', $robots);

		// Set Facebook meta data

		$uri = Uri::getInstance();
		$document->setMetaData('og:title', $document->getTitle(),'property');
		$document->setMetaData('og:site_name', $app->get('sitename'),'property');
		$document->setMetaData('og:description', strip_tags($document->getDescription()),'property');
		$document->setMetaData('og:url', $uri->toString(),'property');
		$document->setMetaData('og:type', 'product.group','property');

		//add custom styles
		$custom_css = $params->get('custom_css', '');
		$document->addStyleDeclaration(strip_tags($custom_css));

		if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

		// Look for template files in component folders
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates');
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.'tag_default');

		// Look for overrides in template folder (J2 template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.'tag_default');

		// Look for overrides in template folder (Joomla! template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'tag_default');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store');

		// Look for specific J2 theme files
		if ($params->get('subtemplate'))
		{
			$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$params->get('subtemplate'));
		}

		//allow plugins to modify the data
		J2Store::plugin()->event('ViewProductList', array(&$items, &$view, &$params, $model));

		$view->set('products',$items);
		$view->set('state', $model->getState());
		$view->set('params',$params);
		$view->set('filters',$filters);
		$view->set('filter_tag',$filter_tag);
		$view->set('currency', J2store::currency());

		$view->set('active_menu', $menu) ;
		$content ='var j2store_product_base_link ="'. $menu->link.'&Itemid='.$menu->id .'";';
        $document->addScriptDeclaration($content);

        $view_html = '';
        J2Store::plugin()->event('ViewProductListTagHtml', array(&$view_html, &$view, $model));
        echo $view_html;
		return true;
	}

	public function getTagId($tag_alias)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery (true);
		$query->select ( 'id' )->from ( '#__tags' )->where ( 'alias ='.$db->q($tag_alias) );
		$db->setQuery ( $query );
		return $db->loadResult ();
	}

	public function processProducts(&$items)
    {
		foreach ($items as &$item) {

			$item->product_short_desc = $item->introtext;
			$item->product_long_desc = $item->fulltext;
            $need_to_run_behaviour = true;
            J2Store::plugin()->event('ProcessProductBehaviour',array(&$need_to_run_behaviour,$item));
            if($need_to_run_behaviour){
                J2Store::fof()->getModel('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($item);
            }
			$item->product_name = $item->title;
		}
	}

	/**
	 * Method to get Filters and to assign in the browse view
	 */
	public function getFilters($items)
    {
		//filters
		$filters = [];
		$filter_tag = [];
		$params = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getMergedParams();
		//now set the categories
		$filters['filter_tag'] = [];

		if($params->get('list_filter_selected_tags')){
			$filter_tag = $params->get('list_filter_selected_tags');
			$filters['filter_tag'] = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getTags($filter_tag);
		}

		//to show the product filter for the existing products in the product layout view
		//should not fetch all product filters
		$product_ids = [];
		foreach($items as $item){
			$product_ids[] =$item->j2store_product_id;
		}
		$filters['sorting'] = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getSortFields();

		$filters['productfilters'] = [];
		$product_model = J2Store::fof()->getModel('Producttags', 'J2StoreModel');
		//fetch the pfilters when show product filter is enabled
		if($params->get('list_show_product_filter',1)){
			//this option will list all the productfilter added
			if($params->get('list_product_filter_list_type','selected') === 'all'){
				$filters['productfilters'] = J2Store::fof()->loadTable('ProductFilter', 'J2StoreTable')->getFilters();
			}elseif($params->get('list_product_filter_list_type','selected') === 'selected'){
				// this option will list productfilter related to the products selected
				$filters['productfilters'] = $product_model->getProductFilters($product_ids);
			}
		}

		//fetch the Manufacturers when Show manufacturer filter is enabled
		if($params->get('list_show_manfacturer_filter',1)){
			if($params->get('list_manufacturer_filter_list_type','selected') === 'all'){
				$filters['manufacturers'] =$product_model->getManufacturers();
			}else{
				$filters['manufacturers'] = $product_model->getManfucaturersByProduct($product_ids);
			}
		}
		//fetch the Vendors when Show vendor filter is enabled
		if($params->get('list_show_vendor_filter',1)){
			if($params->get('list_vendor_filter_list_type','selected') === 'all'){
				$filters['vendors'] = $product_model->getVendors();
			}else{
				$filters['vendors'] =$product_model->getVendorsByProduct($product_ids);
			}
		}
		return $filters;
	}

	public function getPriceRanges($items)
    {
		//get the active menu details
		$params = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getMergedParams();
		$ranges = [];
		//get the highest price
		$priceHigh = abs($params->get('list_price_filter_upper_limit', '1000'));

		$priceLow = 0;
        $range = ( abs( $priceHigh ) - abs( $priceLow ) )/4;
		$ranges['max_price'] = $priceHigh;
		$ranges['min_price'] = $priceLow;
		$ranges['range'] = $range;
		return $ranges;
	}

	public function view()
    {
		$app = Factory::getApplication();
		$product_id = $app->input->getInt('id');

		if(!$product_id) {
			$app->redirect(Route::_('index.php'), 301);
			return;
		}

		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$ns = 'com_j2store.'.$this->getName();

		$params = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getMergedParams();
		if($params->get('item_show_vote', 0)) {
			$params->set('show_vote', 1);
		}
		$product_helper = J2Store::product();

		//get product
		$product = $product_helper->setId($product_id)->getProduct();
        $user = $app->getIdentity();
        //access
        $access_groups = $user->getAuthorisedViewLevels();
        if(!isset($product->source->access) || empty($product->source->access) || !in_array($product->source->access,$access_groups) ){
            $app->redirect(Route::_('index.php'), 301);
            return;
        }
		J2Store::fof()->getModel('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);

		if((isset($product->exists) && $product->exists === 0) || ($product->visibility !==1) || ($product->enabled !==1) ){
            J2Store::platform()->redirect('index.php',Text::_('J2STORE_PRODUCT_NOT_ENABLED_CONTACT_SITE_ADMIN_FOR_MORE_DETAILS'),'warning');
			return;
		}

		//run plugin events
		$model->executePlugins($product->source, $params, 'com_content.article.productlist');
		$text = $product->product_short_desc .":j2storesplite:".$product->product_long_desc;
		$text = $model->runPrepareEventOnDescription($text, $product->product_source_id, $params, 'com_content.article.productlist');
		$desc_array = explode ( ':j2storesplite:', $text );
		if(isset( $desc_array[0] )){
			$product->product_short_desc = $desc_array[0];
		}
		if(isset( $desc_array[1] )){
			$product->product_long_desc = $desc_array[1];
		}
		//get filters / specs by product
		$filters = J2Store::fof()->getModel('Producttags', 'J2StoreModel')->getProductFilters($product->j2store_product_id);

		//upsells
		$up_sells = [];
		if($params->get('item_show_product_upsells', 0) && !empty($product->up_sells)) {
			$up_sells = $product_helper->getUpsells($product);
		}

		//cross sells
		$cross_sells = [];
		if($params->get('item_show_product_cross_sells', 0) && !empty($product->cross_sells)) {
			$cross_sells = $product_helper->getCrossSells($product);
		}

		if(!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
		// Look for template files in component folders
		$view->addTemplatePath(JPATH_SITE.'/components/com_j2store/templates');
		$view->addTemplatePath(JPATH_SITE.'/components/com_j2store/templates/tag_default');

		// Look for overrides in template folder (J2 template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.'tag_default');

		// Look for overrides in template folder (Joomla! template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'tag_default');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store');

		// Look for specific J2 theme files
		if ($params->get('subtemplate'))
		{
			$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$params->get('subtemplate'));
		}

		//set up document
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match

		$active	= $app->getMenu()->getActive();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$document   = $app->getDocument();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if(empty( $menu )){
			$filter_tag = $app->input->getString('filter_tag','');

			if(empty( $filter_tag )){
				$filter_tag = $app->input->getString ( 'tag','' );
			}
			// without menu access product view page
			require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/router.php');
			$qoptions = array (
				'option' => 'com_j2store',
				'view' => 'producttags',
				'task' => 'view',
				'tag' => $filter_tag,
			);
			$menu = \J2Commerce\Helper\J2StoreRouterHelper::findProductTagsMenu( $qoptions );
		}
		$params->def('page_heading', $product->product_name);
		$params->set('page_title', $product->product_name);

		$title = $params->get('page_title', '');

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) === 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) === 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$document->setTitle($title);

		if ($product->source->metadesc)
		{
			$document->setDescription($product->source->metadesc);
		}
		else
		{
			$metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $product->source->introtext.' '.$product->source->fulltext);
			$metaDescItem = strip_tags($metaDescItem);
			$metaDescItem = J2Store::utilities()->characterLimit($metaDescItem, 150);
			$document->setDescription(html_entity_decode($metaDescItem));
		}

		if ($product->source->metakey)
		{
			$document->setMetadata('keywords', $product->source->metakey);
		} else {

			$keywords = $params->get('menu-meta_keywords');
			$document->setMetaData('keywords', $keywords);
		}

		$metadata = json_decode($product->source->metadata);
		if(isset($metadata->robots)) {
			$document->setMetaData('robots', $metadata->robots);
		}else {
			$robots = $params->get('robots');
			$document->setMetaData('robots', $robots);
		}

		// Set Facebook meta data

		$uri = Uri::getInstance();
		$document->setMetaData('og:title', $document->getTitle(),'property');
		$document->setMetaData('og:site_name', $app->get('sitename'),'property');
		$document->setMetaData('og:description', strip_tags($document->getDescription()),'property');
		$document->setMetaData('og:url', $uri->toString(),'property');
		$document->setMetaData('og:type', 'product','property');

		if(isset($product->main_image)) {
			$facebookImage = $product->main_image;
		}else {
			$facebookImage = $product->thumb_image;
		}
		if (!empty($facebookImage))
		{
			if (file_exists(JPATH_SITE.'/'.$facebookImage))
			{
				$image = substr(Uri::root(), 0, -1).'/'.str_replace(Uri::root(true), '', $facebookImage);
				$document->setMetaData('og:image', $image,'property');
				$document->setMetaData('image', $image);
			}
		}

        // 1. Find product canonical url
        if(isset($product->main_tag) && !empty($product->main_tag)){

            $tag_menus = Factory::getContainer()->get(MenuFactoryInterface::class)->createMenu('site');
            $lang = Factory::getApplication()->getLanguage();

            $tag_menu_id = '';
            foreach ($tag_menus->getMenu() as $tag_menu){

                if(isset($tag_menu->type) && isset($tag_menu->component) && isset($tag_menu->query['view']) && isset($tag_menu->query['tag'])
                    && $tag_menu->type === 'component' && $tag_menu->component === 'com_j2store' && $tag_menu->query['view'] === 'producttags'
                    && $tag_menu->query['tag'] == $product->main_tag) {

                    if ($lang->getTag() == $tag_menu->language) {
                        $tag_menu_id = $tag_menu->id;
                        break;
                    }
                    if ($tag_menu->language === '*') {
                        $tag_menu_id = $tag_menu->id;
                        break;
                    }
                }
            }

            if(isset($tag_menu_id) && $tag_menu_id > 0){
                // 2. remove all other canonical url
                foreach($document->_links as $key=> $value)
                {
                    if(is_array($value))
                    {
                        if(array_key_exists('relation', $value))
                        {
                            if($value['relation'] === 'canonical')
                            {
                                // we found the document link that contains the canonical url
                                // remove it
                                unset($document->_links[$key]);
                                break;
                            }
                        }
                    }
                }
                // 3. set product canonical url
                $doc = $app->getDocument();
                $canonical = trim(Uri::root(),'/').'/'.ltrim(J2Store::platform()->getProductUrl(array('task' => 'view','id' => $product->j2store_product_id,'Itemid' => $tag_menu_id),true),'/');
                $doc->addHeadLink(htmlspecialchars($canonical), 'canonical');
            }
        }

		$back_link = "";
		$back_link_title = "";
		$item_id = "";
		if(!empty($active)){
			$back_link = isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']: '';
			if(empty($_SERVER['HTTP_REFERER'])){
				$back_link = $active->link;
			}
			$back_link_title = $active->title;
			$item_id = $active->id;
		}

		if(isset($back_link) && !empty($back_link_title)){
			$view->set('back_link' , Route::_($back_link));
			$view->set('back_link_title' ,$back_link_title);
		}

		//add a pathway
		$pathway	= $app->getPathway();
		$path = array(array('title' => $product->product_name, 'link' => ''));

		$path = array_reverse($path);

		//get the names already in the path way
		$names = $pathway->getPathwayNames();

		foreach ($path as $item)
		{
			if($params->get('breadcrumb_category_inclusion', 1)) {
				$pathway->addItem($item['title'], $item['link']);
			}else {
				//eliminate if the same names that are already there.
				if (!in_array($item['title'], $names)) {
					$pathway->addItem($item['title'], $item['link']);
				}
			}
		}

		//add class to the module for better styling control.
		$script = '
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof window.jQuery !== "undefined") {
                    document.body.classList.add(
                        "j2store-single-product-view",
                        "view-product-' . $product->j2store_product_id . '",
                        "' . $product->source->alias . '"
                    );
                }
		  });
		';
		$document->addScriptDeclaration($script);

		//add custom styles
		$custom_css = $params->get('custom_css', '');
		$document->addStyleDeclaration(strip_tags($custom_css));
		$view->set('params', $params);
		$view->set('filters', $filters);
		$view->set('up_sells', $up_sells);
		$view->set('cross_sells', $cross_sells);
		$view->set('product_helper', $product_helper);
		$view->set('currency', J2store::currency());
		//allow plugins to modify the data
		J2Store::plugin()->event('ViewProduct', array(&$product, &$view));

		$view->set('product', $product);
        $view_html = '';
        J2Store::plugin()->event('ViewProductTagHtml', array(&$view_html, &$view, $model));
        echo $view_html;
        return true;
	}

	/**
	 * Method to direct to compare layout when
	 * product added to compare
	 */
	function compare()
    {
		$model = $this->getModel('Products');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$view->setLayout('compare');
		$view->display();
	}

	/**
	 * Method to direct to compare layout when
	 * product added to compare
	 */
	function wishlist()
    {
		$model = $this->getModel('Products');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$view->setLayout('wishlist');
		$view->display();
	}
}

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
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/productbase.php');

class J2StoreControllerProducts extends J2StoreControllerProductsBase
{

	protected $cacheableTasks = [];
	var $_catids = [];

	public function browse()
    {
		//first clear cache
		$utility = J2Store::utilities();
		$utility->nocache();
		$utility->clear_cache();

        $platform = J2Store::platform();
		$app = Factory::getApplication();
		$session = $app->getSession();
        $db = Factory::getContainer()->get('DatabaseDriver');
		$active	= $app->getMenu()->getActive();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$document = $app->getDocument();
		$lang = $app->getLanguage();

		$manufacturer_ids = $this->input->get('manufacturer_ids', array(), 'ARRAY');
		$vendor_ids = $this->input->get('vendor_ids', array(), 'ARRAY');
		$productfilter_ids = $this->input->get('productfilter_ids', array(), 'ARRAY');

		$ns = 'com_j2store.'.$this->getName();
		$params = J2Store::fof()->getModel('Products', 'J2StoreModel')->getMergedParams();
		if($params->get('list_show_vote', 0)) {
			$params->set('show_vote', 1);
		}
		$view = $this->getThisView();
		$model = $this->getModel('Products');

		//$model->clearState();
		$view->setModel($model);

		//$states = $this->getFilterStates();
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

			//$model->setState('productfilter_id' ,implode(',',$vendor_ids));
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

		$model->setState('list.start', $limitstart);

		$orderCol = $app->getUserStateFromRequest('com_j2store.product.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $model->get_filter_fields()))
		{
			$orderCol = 'a.ordering';
		}
		$model->setState('list.ordering', $orderCol);

        $listOrder = $params->get('list_order_direction','ASC');
		$model->setState('list.direction', $listOrder);
        $listOrder = $params->get('category_order_direction','ASC');
        $model->setState('category_order_direction', $listOrder);

		foreach($states as $key => $value){
			$model->setState($key,$value);
		}

		$filter_catid = $app->input->getInt('filter_catid',$session->get('filter_catid' ,'','j2store'));

		$catids = $this->input->get('catid',array(),'Array');
		$this->_catids = $catids;
		if(!empty($filter_catid)){
			$model->setState('catids', $filter_catid);
		}elseif(empty($filter_catid)){
			$model->setState('catids', $this->_catids);
		}elseif($params->get('list_show_filter_category_all',1) && empty($filter_catid)){
			$model->setState('catids', $this->_catids[0]);
		}
		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');

		if ($showSubcategories)
		{
			$model->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$model->setState('filter.subcategories', true);
		}

		$model->setState('filter.language', Multilanguage::isEnabled());

		$model->setState('enabled', 1);
		$model->setState('visible', 1);
		$search = $this->input->getString('search', '');
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
	                        //print_r($key_i);
	                        $pagination->setAdditionalUrlParam($key.'['.$key_i.']',$value_i);
	                    }
	                }else{
	                    $pagination->setAdditionalUrlParam($key,$value);
	                }
	            }
			}
            J2Store::plugin()->event('ViewProductListPagination', array(&$items, &$pagination, &$params, $model));
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
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
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
        $document->setMetaData('og:description', strip_tags($document->getDescription() ?? ''),'property');
		$document->setMetaData('og:url', $uri->toString(),'property');
		$document->setMetaData('og:type', 'product.group','property');

		$catids = $model->getState('catids', array());
		$category = false;
		if(is_array($catids)) {
			if(count($catids) === 1) {
				$category = $catids[0];
			}
		}elseif(is_numeric($catids)) {
			$category = $catids;
		}
		if ($category) {
			// we have a single category. So we can add a breadcrumb
			$category_data = J2Store::article()->getCategoryById($category);
			if (isset ( $category_data->title ) && $params->get('breadcrumb_category_inclusion', 1)) {
				$pathway = $app->getPathway ();

				$path = array (
						array (
								'title' => $category_data->title,
								'link' => ''
						)
				);
				$path = array_reverse ( $path );

				foreach ( $path as $singlepath ) {
					$pathway->addItem ( $singlepath ['title'], $singlepath ['link'] );
				}
			}
		}

		//add custom styles
		$custom_css = $params->get('custom_css', '');
		$document->addStyleDeclaration(strip_tags($custom_css));

		//allow plugins to modify the data
		J2Store::plugin()->event('ViewProductList', array(&$items, &$view, &$params, $model));
		$view->set('products',$items);
		$view->set('state', $model->getState());
		$view->set('params',$params);
		$view->set('filters',$filters);
		$view->set('filter_catid',$filter_catid);
		$view->set('currency', J2store::currency());
		$view->set('active_menu', $menu) ;
		if(isset($menu->link) && isset($menu->id)){
            $content ='var j2store_product_base_link ="'. $menu->link.'&Itemid='.$menu->id .'";';
        }else{
            $content ='var j2store_product_base_link = "";';
        }
        $platform->addInlineScript($content);
        $view_html = '';
        J2Store::plugin()->event('ViewProductListHtml', array(&$view_html, &$view, $model));
        echo $view_html;
		return true;
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
	 * ACL check before editing a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeEdit()
	{
		if(parent::onBeforeEdit()) {

			$task = $this->input->getString('task');
			if($task === 'edit'){
				$view = $this->getThisView();
				$this->form_prefix = $this->input->getString('form_prefix');
				// Get/Create the model
				if ($model = $this->getThisModel())
				{
					// Push the model into the view (as default)
					$view->setModel($model, true);
				}
				// Set the layout
				$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);
				if($task === 'edit'){
					$this->item = $model->runMyBehaviorFlag(true)->getItem();
					$view->item = $this->item;
					$view->setLayout('form');
					$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
					$view->set('form_prefix' ,$this->form_prefix);

					$view->product_types = HTMLHelper::_('select.genericlist', $model->getProductTypes(), $this->form_prefix.'[product_type]', array('class'=>'form-select'), 'value', 'text', $this->item->product_type);

					$view->manufacturers = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[manufacturer_id]')
					->value($this->item->manufacturer_id)
                    ->attribs(array('class'=>'form-select'))
                    ->setPlaceHolders(
							array(''=>Text::_('J2STORE_SELECT_OPTION'))
					)
					->hasOne('Manufacturers')
					->setRelations( array(
							'fields' => array (
									'key' => 'j2store_manufacturer_id',
									'name' => array('company')
							)
					)
					)->getHtml();

					//vendor
					$view->vendors = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[vendor_id]')
					->value($this->item->vendor_id)
                    ->attribs(array('class'=>'form-select'))
                    ->setPlaceHolders(array(''=>Text::_('J2STORE_SELECT_OPTION')))
					->hasOne('Vendors')
					->setRelations(
							array (
									'fields' => array
									(
											'key'=>'j2store_vendor_id',
											'name'=>array('first_name','last_name')
									)
							)
					)->getHtml();

					//tax profiles
					$view->taxprofiles = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[taxprofile_id]')
                    ->attribs(array('class'=>'form-select'))
                    ->value($this->item->taxprofile_id)
					->setPlaceHolders(array(''=>Text::_('J2STORE_NOT_TAXABLE')))
					->hasOne('Taxprofiles')
					->setRelations(
							array (
									'fields' => array (
											'key'=>'j2store_taxprofile_id',
											'name'=>'taxprofile_name'
									)
							)
					)->getHtml();

					$view->item->price_calculator = isset($this->item->price_calculator) && !empty($this->item->price_calculator) ? $this->item->price_calculator : 'standard';

					//pricing options
					$view->pricing_calculator = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[pricing_calculator]')
                    ->attribs(array('class'=>'form-select'))
					->value($this->item->price_calculator)
					->setPlaceHolders(J2Store::product()->getPricingCalculators())
					->getHtml();

					//$view->product_filters = J2Store::fof()->loadTable('ProductFilter', 'J2StoreTable')->getFiltersByProduct($this->item->j2store_product_id);
                    $tags = new TagsHelper;
                    $tags->getItemTags('com_content.article', $this->item->product_source_id);
                    $tag_options = [];
                    $tag_options[''] = Text::_('J2STORE_SELECT_TAG');
                    if(count($tags->itemTags) > 0){
                        foreach($tags->itemTags as $product_tag) {
                            $tag_options[$product_tag->alias] =  Text::_($product_tag->title);
                        }
                    }

                    $view->tag_lists = J2Html::select()->clearState()
                        ->type('genericlist')
                        ->name($this->form_prefix.'[main_tag]')
                        ->attribs(array('class'=>'form-select'))
                        ->value($this->item->main_tag)
                        ->setPlaceHolders($tag_options)
                        ->getHtml();

                    $productfilter_model = J2Store::fof()->getModel('ProductFilters', 'J2StoreModel');
                    $productfilter_model->setState('limit',10);
                    $view->filter_limit = 10;
                    if($this->item->j2store_product_id > 0) {
                        $productfilter_model->setState('product_id',$this->item->j2store_product_id);
                        $productfilter_list = $productfilter_model->getList();
                        $product_filters = [];
                        foreach($productfilter_list as $row) {
                            if(!isset($product_filters[$row->group_id])){
                                $product_filters[$row->group_id] = [];
                            }
                            $product_filters[$row->group_id]['group_name'] = $row->group_name;
                            $product_filters[$row->group_id]['filters'][] = $row;
                        }
                        $this->product_filters = $product_filters;//J2Store::fof()->loadTable('ProductFilter', 'J2StoreTable')->getFiltersByProduct($this->item->j2store_product_id);
                    }else {
                        $this->product_filters = [];
                    }
                    $this->item->productfilter_pagination = $productfilter_model->getPagination();
                    $view->product_option_list =  $this->getProductOptionList($this->item->product_type);
				}
			}elseif($task === 'setproductprice'){
				$this->setproductprice();
			}
			return true;
		}
		return false;
	}

    public function getProductOptionList($product_type)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('j2store_option_id, option_unique_name, option_name');
        $query->from('#__j2store_options');
        //based on the product type
        if(isset($product_type) && in_array($product_type,array('variable','flexivariable'))){
            $query->where("type IN ('select' , 'radio' ,'checkbox')");
        }
        $query->where('enabled=1');
        $db->setQuery($query);
        return $db->loadObjectList();
    }

	/**
	 * Method to get Filters and to assign in the browse view
	 */
	public function getFilters($items)
    {
		//filters
		$filters = [];
		$filter_categories = [];
		$params = J2Store::fof()->getModel('Products', 'J2StoreModel')->getMergedParams();
		//now set the categories
		$filters['filter_categories'] = [];
		if($params->get('list_filter_selected_categories')){
			$filter_categories = $params->get('list_filter_selected_categories');
			$filters['filter_categories'] = J2Store::fof()->getModel('Products', 'J2StoreModel')->getCategories($filter_categories);
		}

		//to show the product filter for the existing products in the product layout view
		//should not fetch all product filters
		$product_ids = [];
		foreach($items as $item){
			$product_ids[] =$item->j2store_product_id;
		}
		$filters['sorting'] = J2Store::fof()->getModel('Products', 'J2StoreModel')->getSortFields();

		$filters['productfilters'] = [];
		$product_model = J2Store::fof()->getModel('Products', 'J2StoreModel');
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
		$params = J2Store::fof()->getModel('Products', 'J2StoreModel')->getMergedParams();
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

		$params = J2Store::fof()->getModel('Products', 'J2StoreModel')->getMergedParams();
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

		if( (isset($product->exists) && $product->exists === 0) || ($product->visibility !==1) || ($product->enabled !==1) ){
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
		$filters = J2Store::fof()->getModel('Products', 'J2StoreModel')->getProductFilters($product->j2store_product_id);

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

		//set canonical url

		foreach($document->_links as $key=> $value)
		{
			if(is_array($value))
			{
				if(array_key_exists('relation', $value))
				{
					if($value['relation'] === 'canonical')
					{
						// we found the document link that contains the canonical url
						// change it!
						$canonicalUrl = $uri->toString();

						$document->_links[$canonicalUrl] = $value;
						unset($document->_links[$key]);
						break;
					}
				}
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
		if(isset($product->source->category_title) && $params->get('breadcrumb_category_inclusion', 1)) {
			$path[] =
			array (
					'title' => $product->source->category_title,
					'link' => J2Store::platform()->getProductUrl(array('filter_catid' => $product->source->catid,'Itemid' => $item_id))
			);
		}
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
                document.body.classList.add(
                    "j2store-single-product-view",
                    "view-product-' . $product->j2store_product_id . '",
                    "' . $product->source->alias . '"
                );
            });
        ';

        J2Store::platform()->addInlineScript($script);

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
		$view->setLayout('view');
        $view_html = '';
        J2Store::plugin()->event('ViewProductHtml', array(&$view_html, &$view, $model));
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

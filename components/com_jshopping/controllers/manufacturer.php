<?php
/**
* @version      4.16.1 31.05.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingControllerManufacturer extends JshoppingControllerBase{
    
    function __construct($config = array()){
        parent::__construct($config);
        JPluginHelper::importPlugin('jshoppingproducts');
        JDispatcher::getInstance()->trigger('onConstructJshoppingControllerManufacturer', array(&$this));
    }
	
	function display($cachable = false, $urlparams = false){        
        $params = JFactory::getApplication()->getParams();        
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        
		$manufacturer = JSFactory::getTable('manufacturer', 'jshop');
        $manufacturer->getDescription();
        
		$rows = $manufacturer->getAllManufacturers(1, $manufacturer->getFieldListOrdering(), $manufacturer->getSortingDirection());

        $dispatcher->trigger('onBeforeDisplayListManufacturers', array(&$rows, &$params));

        JshopHelpersMetadata::listManufacturers($params);
        
        $view = $this->getView('manufacturer');
		$view->setLayout("manufacturers");
		$view->assign("rows", $rows);
		$view->assign("image_manufs_live_path", $jshopConfig->image_manufs_live_path);
        $view->assign('noimage', $jshopConfig->noimage);
        $view->assign('count_manufacturer_to_row', $manufacturer->getCountToRow());
        $view->assign('params', $params);        
		$view->assign('manufacturer', $manufacturer);
        $dispatcher->trigger('onBeforeDisplayManufacturerView', array(&$view) );
		$view->display();
	}	
	
	function view(){
	    $dispatcher = JDispatcher::getInstance();
		$jshopConfig = JSFactory::getConfig();        
        $manufacturer_id = $this->input->getInt('manufacturer_id');

		JSFactory::getModel('productShop', 'jshop')->storeEndPages();
		
		$manufacturer = JSFactory::getTable('manufacturer', 'jshop');		
		$manufacturer->load($manufacturer_id);
		$manufacturer->getDescription();

        $dispatcher->trigger('onBeforeDisplayManufacturer', array(&$manufacturer));
        
        if (!$manufacturer->checkView()){
            JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
            return;
        }

        JshopHelpersMetadata::manufacturer($manufacturer);
        
        $productlist = JSFactory::getModel('productList', 'jshop');
        $productlist->setModel($manufacturer);
        $productlist->load();
        
        $orderby = $productlist->getOrderBy();
        $image_sort_dir = $productlist->getImageSortDir();
        $filters = $productlist->getFilters();
        $action = $productlist->getAction();
        $products = $productlist->getProducts();
        $pagination = $productlist->getPagination();
        $pagenav = $productlist->getPagenav();
        $sorting_sel = $productlist->getHtmlSelectSorting();
        $product_count_sel = $productlist->getHtmlSelectCount();        
        $willBeUseFilter = $productlist->getWillBeUseFilter();
        $display_list_products = $productlist->getDisplayListProducts();        
        $categorys_sel = $productlist->getHtmlSelectFilterCategory();
        $allow_review = $productlist->getAllowReview();

        $view = $this->getView('manufacturer');
		$view->setLayout("products");
        $view->assign('config', $jshopConfig);
        $view->assign('template_block_list_product', $productlist->getTmplBlockListProduct());
        $view->assign('template_no_list_product', $productlist->getTmplNoListProduct());
        $view->assign('template_block_form_filter', $productlist->getTmplBlockFormFilter());
        $view->assign('template_block_pagination', $productlist->getTmplBlockPagination());
        $view->assign('path_image_sorting_dir', $jshopConfig->live_path.'images/'.$image_sort_dir);
        $view->assign('filter_show', 1);
        $view->assign('filter_show_category', 1);
        $view->assign('filter_show_manufacturer', 0);
        $view->assign('pagination', $pagenav);
		$view->assign('pagination_obj', $pagination);
        $view->assign('display_pagination', $pagenav!="");
		$view->assign("rows", $products);
		$view->assign("count_product_to_row", $productlist->getCountProductsToRow());
		$view->assign("manufacturer", $manufacturer);
        $view->assign('action', $action);
        $view->assign('allow_review', $allow_review);
		$view->assign('orderby', $orderby);		
		$view->assign('product_count', $product_count_sel);
        $view->assign('sorting', $sorting_sel);
        $view->assign('categorys_sel', $categorys_sel);
        $view->assign('filters', $filters);
        $view->assign('willBeUseFilter', $willBeUseFilter);
        $view->assign('display_list_products', $display_list_products);
        $view->assign('shippinginfo', SEFLink($jshopConfig->shippinginfourl,1));
        $view->assign('total', $productlist->getTotal());
        $dispatcher->trigger('onBeforeDisplayProductListView', array(&$view, &$productlist));
		$view->display();
	}	
}
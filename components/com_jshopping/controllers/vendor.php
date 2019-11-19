<?php
/**
* @version      4.16.1 05.11.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
jimport('joomla.html.pagination');

class JshoppingControllerVendor extends JshoppingControllerBase{
    
    function __construct($config = array()){
        parent::__construct($config);
        JPluginHelper::importPlugin('jshoppingproducts');
        JDispatcher::getInstance()->trigger('onConstructJshoppingControllerVendor', array(&$this));
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams();        
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
		$model = JSFactory::getModel('vendorList', 'jshop');
        
        JshopHelpersMetadata::listVendors();
		
		$model->load();
		$rows = $model->getList();
		$pagination = $model->getPagination();
		$pagenav = $pagination->getPagesLinks();
		
        $view = $this->getView('vendor');
        $view->setLayout("vendors");
        $view->assign("rows", $rows);        
        $view->assign('count_to_row', $model->getCountToRow());
        $view->assign('params', $params);
        $view->assign('pagination', $pagenav);
        $view->assign('display_pagination', $pagenav!="");
        $dispatcher->trigger('onBeforeDisplayVendorView', array(&$view) );
        $view->display();
    }  

    function info(){
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
		$vendor_id = $this->input->getInt("vendor_id");
		
        if (!$jshopConfig->product_show_vendor_detail){
            JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
            return;
        }

        $vendor = JSFactory::getTable('vendor', 'jshop');
        $vendor->load($vendor_id);
        if (!$vendor->id){
            JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
            return;
        }
        
        $dispatcher->trigger('onBeforeDisplayVendorInfo', array(&$vendor));
                
        $header = $vendor->shop_name;
		
		JshopHelpersMetadata::vendorInfo($vendor);        

        $vendor->country = $vendor->getCountryName();

        $view = $this->getView('vendor');
        $view->setLayout("info");
        $view->assign('vendor', $vendor);
        $view->assign('header', $header);
        $dispatcher->trigger('onBeforeDisplayVendorInfoView', array(&$view) );
        $view->display();        
    }
    
    function products(){
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
        $vendor_id = $this->input->getInt("vendor_id");

		JSFactory::getModel('productShop', 'jshop')->storeEndPages();
        
        $vendor = JSFactory::getTable('vendor', 'jshop');
        $vendor->load($vendor_id);
        if (!$vendor->id){
            JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
            return;
        }

        $dispatcher->trigger('onBeforeDisplayVendor', array(&$vendor));
        
        JshopHelpersMetadata::vendorProducts($vendor);

        $productlist = JSFactory::getModel('productList', 'jshop');
        $productlist->setModel($vendor);
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
        $manufacuturers_sel = $productlist->getHtmlSelectFilterManufacturer(1);
        $categorys_sel = $productlist->getHtmlSelectFilterCategory(1);
        $allow_review = $productlist->getAllowReview();

        $view = $this->getView('vendor');
        $view->setLayout("products");
        $view->assign('config', $jshopConfig);
        $view->assign('template_block_list_product', $productlist->getTmplBlockListProduct());
        $view->assign('template_no_list_product', $productlist->getTmplNoListProduct());
        $view->assign('template_block_form_filter', $productlist->getTmplBlockFormFilter());
        $view->assign('template_block_pagination', $productlist->getTmplBlockPagination());
        $view->assign('path_image_sorting_dir', $jshopConfig->live_path.'images/'.$image_sort_dir);
        $view->assign('filter_show', 1);
        $view->assign('filter_show_category', 1);
        $view->assign('filter_show_manufacturer', 1);
        $view->assign('pagination', $pagenav);
		$view->assign('pagination_obj', $pagination);
        $view->assign('display_pagination', $pagenav!="");
        $view->assign("rows", $products);
        $view->assign("count_product_to_row", $productlist->getCountProductsToRow());
        $view->assign("vendor", $vendor);
        $view->assign('action', $action);
        $view->assign('allow_review', $allow_review);
        $view->assign('orderby', $orderby);
        $view->assign('product_count', $product_count_sel);
        $view->assign('sorting', $sorting_sel);
        $view->assign('categorys_sel', $categorys_sel);
        $view->assign('manufacuturers_sel', $manufacuturers_sel);
        $view->assign('filters', $filters);
        $view->assign('willBeUseFilter', $willBeUseFilter);
        $view->assign('display_list_products', $display_list_products);
        $view->assign('shippinginfo', SEFLink($jshopConfig->shippinginfourl,1));
        $view->assign('total', $productlist->getTotal());
        $dispatcher->trigger('onBeforeDisplayProductListView', array(&$view, &$productlist));
        $view->display();
    }
    
}
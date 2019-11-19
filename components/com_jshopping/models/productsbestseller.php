<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once JPATH_JOOMSHOPPING.'/models/productlistinterface.php';

class jshopProductsBestseller implements jshopProductListInterface{
    
    function getCountProducts($filters, $order = null, $orderby = null){
		return 0;
	}
	
	function getProducts($filters, $order = null, $orderby = null, $limitstart = 0, $limit = 0){
        $product = JSFactory::getTable('product', 'jshop');
        return $product->getBestSellers($this->getCountProductsPerPage(), null, $filters);		
	}
    
	function getDefaultProductSorting(){
        return JSFactory::getConfig()->product_sorting;
    }
    
    function getDefaultProductSortingDirection(){
        return JSFactory::getConfig()->product_sorting_direction;
    }
    
    function getCountProductsPerPage(){       
        return JSFactory::getConfig()->count_products_to_page_bestseller;
    }
    
    function getCountProductsToRow(){
        return JSFactory::getConfig()->count_products_to_row_bestseller;
    }
    
    function getProductFieldSorting($order){
        if ($order==4){
            $order = 1;
        }
        return JSFactory::getConfig()->sorting_products_field_s_select[$order];
    }
    
    public function getContext(){
        return "jshoping.list.front.product.bestseller";
    }
    
    public function getContextFilter(){
        return "jshoping.list.front.product.bestseller";
    }
    
    public function getNoFilterListProduct(){
        return array();
    }
    
    public function getProductListName(){
        return 'bestseller';
    }
    
    public function getProductsOrderingTypeList(){
        return 0;
    }
	
	public function getFilterListProduct(){
		return getBuildFilterListProduct($this->getContextFilter(), $this->getNoFilterListProduct());
	}
    
}
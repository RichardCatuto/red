<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

interface jshopProductListInterface{
    
    public function getCountProducts($filters, $order = null, $orderby = null);
			
	public function getProducts($filters, $order = null, $orderby = null, $limitstart = 0, $limit = 0);
    
	public function getDefaultProductSorting();
        
    public function getDefaultProductSortingDirection();
        
    public function getCountProductsPerPage();
        
    public function getCountProductsToRow();
        
    public function getProductFieldSorting($order);
        
    public function getContext();
        
    public function getContextFilter();
        
    public function getNoFilterListProduct();
        
    public function getProductListName();
        
    public function getProductsOrderingTypeList();

	public function getFilterListProduct();

}
<?php
/**
* @version      4.13.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
jimport('joomla.html.pagination');

class jshopProductList{
    
	protected $model = null;
    protected $multi_page_list = 1;	
	
	public function setModel(jshopProductListInterface $model){
		$this->model = $model;
		extract(js_add_trigger(get_defined_vars(), "after"));
	}
	
	public function getModel(){
		return $this->model;
	}
    
    public function setMultiPageList($multi_page_list){
        $this->multi_page_list = $multi_page_list;
    }
    
    public function getMultiPageList(){
        return $this->multi_page_list;
    }

    protected function setOrderBy(&$orderby){
        $this->orderby = $orderby;
    }
    
    protected function setOrder(&$order){
        $this->order = $order;
    }
	
    protected function setLimit(&$limit){
        $this->limit = $limit;
    }
	
	protected function setLimitStart(&$limitstart){
        $this->limitstart = $limitstart;
    }
	
    protected function setImageSortDir(&$image_sort_dir){
        $this->image_sort_dir = $image_sort_dir;
    }
	
    protected function setFilters(&$filters){
        $this->filters = $filters;
    }
	
    protected function setProducts(&$products){
        $this->products = $products;
    }
	
    protected function setPagination(&$pagination){
        $this->pagination = $pagination;
    }
	
    protected function setPagenav(&$pagenav){
        $this->pagenav = $pagenav;
    }
	
	protected function setTotal(&$total){
        $this->total = $total;
    }
    
    public function getOrderBy(){
        return $this->orderby;
    }
	
    public function getOrder(){
        return $this->order;
    }            
	
    public function getLimit(){
        return $this->limit;
    }
	
	public function getLimitStart(){
        return $this->limitstart;
    }
	
    public function getImageSortDir(){
        return $this->image_sort_dir;
    }
	
    public function getFilters(){
        return $this->filters;
    }
	
    public function getAction(){
		$action = xhtmlUrl($_SERVER['REQUEST_URI']);
        return $action;
    }
	
    public function getProducts(){
        return $this->products;
    }
	
    public function getPagination(){
        return $this->pagination;
    }
	
    public function getPagenav(){
        return $this->pagenav;
    }
	
	public function getTotal(){
        return $this->total;
    }
    
    public function getContext(){
        return $this->getModel()->getContext();
    }
    
    public function getContextFilter(){
        return $this->getModel()->getContextFilter();
    }
	
	public function getCountProductsPerPage(){
		return $this->getModel()->getCountProductsPerPage();
	}
	
	public function getCountProductsToRow(){
		return $this->getModel()->getCountProductsToRow();
	}
	
	protected function loadRequestData(){
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$context = $this->getContext();
		$limitstart = $app->input->getInt('limitstart');
        $orderby = $app->getUserStateFromRequest($context.'orderby', 'orderby', $model->getDefaultProductSortingDirection(), 'int');
        $order = $app->getUserStateFromRequest($context.'order', 'order', $model->getDefaultProductSorting(), 'int');
        $limit = $app->getUserStateFromRequest($context.'limit', 'limit', $this->getCountProductsPerPage(), 'int');
        if (!$limit){
            $limit = $this->getCountProductsPerPage();
        }
		$this->setOrder($order);
		$this->setOrderBy($orderby);
        $this->setLimit($limit);
        $this->setLimitStart($limitstart);
	}
            
	public function load(){        
        $dispatcher = JDispatcher::getInstance();
		$model = $this->getModel();

		$dispatcher->trigger('onBeforeLoadProductList', array());
		
		$this->loadRequestData();
		$limitstart = $this->getLimitStart();
		$orderby = $this->getOrderBy();
		$order = $this->getOrder();
		$limit = $this->getLimit();
		
        $orderbyq = getQuerySortDirection($order, $orderby);		
        $image_sort_dir = getImgSortDirection($order, $orderby);
		$this->setImageSortDir($image_sort_dir);
        $field_order = $model->getProductFieldSorting($order);		
		$filters = $model->getFilterListProduct();
        $this->setFilters($filters);
		
        if ($this->getMultiPageList()){
            $total = $model->getCountProducts($filters, $field_order, $orderbyq);
            $dispatcher->trigger('onBeforeFixLimitstartDisplayProductList', array(&$limitstart, &$total, $model->getProductListName()));
			$this->setTotal($total);
            if ($limitstart>=$total){
                $limitstart = 0;
				$this->setLimitStart($limitstart);
            }
			$pagination = new JPagination($total, $limitstart, $limit);
            $pagenav = $pagination->getPagesLinks();
			$this->setPagination($pagination);
			$this->setPagenav($pagenav);
        }
        
        $products = $model->getProducts($filters, $field_order, $orderbyq, $limitstart, $limit);
		
		$dispatcher->trigger('onBeforeDisplayProductList', array(&$products));        
        $this->setProducts($products);
		
		return 1;
	}
    
    public function getHtmlSelectSorting(){
        return JshopHelpersSelects::getProductsOrdering($this->getModel()->getProductsOrderingTypeList(), $this->getOrder());
    }
    
    public function getHtmlSelectCount(){
        return JshopHelpersSelects::getProductsCount($this->getCountProductsPerPage(), $this->getLimit());
    }
    
    public function getHtmlSelectFilterManufacturer($fulllist = 0){
        if (JSFactory::getConfig()->show_product_list_filters){
            $filters = $this->getFilters();
            if (!$fulllist){
                $filter_manufactures = $this->getModel()->getManufacturers();
            }else{
                $filter_manufactures = JSFactory::getTable('manufacturer', 'jshop')->getList();
            }
            if (isset($filters['manufacturers'][0])){
                $active_manufacturer = $filters['manufacturers'][0];            
            }else{
                $active_manufacturer = 0;
            }
			$manufacuturers_sel = JshopHelpersSelects::getFilterManufacturer($filter_manufactures, $active_manufacturer);
        }else{
			$manufacuturers_sel = '';
		}
        return $manufacuturers_sel;
    }
    
    public function getHtmlSelectFilterCategory($fulllist = 0){
        if (JSFactory::getConfig()->show_product_list_filters){
            $filters = $this->getFilters();
            if (!$fulllist){
                $filter_categorys = $this->getModel()->getCategorys();
            }else{
                $filter_categorys = buildTreeCategory(1);
            }
            if (isset($filters['categorys'][0])){
                $active_category = $filters['categorys'][0];
            }else{
                $active_category = 0;
            }
			$categorys_sel = JshopHelpersSelects::getFilterCategory($filter_categorys, $active_category);
        }else{
            $categorys_sel = '';
        }
        return $categorys_sel;
    }
    
    public function getWillBeUseFilter(){
        return willBeUseFilter($this->getFilters());
    }
    
    public function getDisplayListProducts(){
        $display_list_products = (count($this->getProducts())>0 || $this->getWillBeUseFilter());
        extract(js_add_trigger(get_defined_vars(), "after"));
        return $display_list_products;
    }
    
    public function getAllowReview(){
        $allow_review = JSFactory::getTable('review', 'jshop')->getAllowReview();
        extract(js_add_trigger(get_defined_vars(), "after"));
        return $allow_review;
    }
    
    public function configDisableSortAndFilters(){
        $jshopConfig = JSFactory::getConfig();
        $jshopConfig->show_sort_product = 0;
        $jshopConfig->show_count_select_products = 0;
        $jshopConfig->show_product_list_filters = 0;
    }
	
	public function getTmplBlockListProduct(){
		return JSFactory::getConfig()->default_template_block_list_product;
	}
	
	public function getTmplNoListProduct(){
		return JSFactory::getConfig()->default_template_no_list_product;
	}
	
	public function getTmplBlockFormFilter(){
		return JSFactory::getConfig()->default_template_block_form_filter_product;
	}
	
	public function getTmplBlockPagination(){
		return JSFactory::getConfig()->default_template_block_pagination_product;
	}
    
}
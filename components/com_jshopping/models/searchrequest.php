<?php
/**
* @version      4.17.1 12.03.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopSearchrequest{
	
	protected $request;
	
	public function __construct(){
		$this->loadData();
	}
	
	public function loadData(){
		$session = JFactory::getSession();
		$post = JFactory::getApplication()->input->getArray();
        if (isset($post['setsearchdata']) && $post['setsearchdata']==1){
            $session->set("jshop_end_form_data", $post);
        }else{
            $data = $session->get("jshop_end_form_data");
            if (count($data)){
                $post = $data;
            }
        }
		$this->request = $post; 
	}
	
	public function getData(){
		return $this->request;
	}
	
	public function getCategoryId(){
		return (int)$this->request['category_id'];
	}
	
	public function getManufacturerId(){
		return (int)$this->request['manufacturer_id'];
	}
    
    public function getManufacturers(){
        $manufacturers = array();        
        if (isset($this->request['manufacturers'])){
            $manufacturers = $this->request['manufacturers'];
            $manufacturers = filterAllowValue($manufacturers, "int+");
        }elseif($this->request['manufacturer_id']){            
            $manufacturers[] = $this->getManufacturerId();
        }
		return $manufacturers;
	}
	
	public function getDateTo(){
		if (isset($this->request['date_to'])) 
            $date_to = $this->request['date_to'];
        else 
            $date_to = null;
		return $date_to;
	}
	
	public function getDateFrom(){
		if (isset($this->request['date_from'])) 
            $date_from = $this->request['date_from'];
        else 
            $date_from = null;
		return $date_from;
	}
    
    public function getPriceTo(){
		if (isset($this->request['price_to'])) 
            $price_to = saveAsPrice($this->request['price_to']);
        else 
            $price_to = null;
        if (isset($this->request['fprice_to'])){
            $price_to = saveAsPrice($this->request['fprice_to']);
        }
        return $price_to;        
	}
	
	public function getPriceFrom(){		        
        if (isset($this->request['price_from'])) 
            $price_from = saveAsPrice($this->request['price_from']);
        else 
            $price_from = null;
        if (isset($this->request['fprice_from'])){
            $price_from = saveAsPrice($this->request['fprice_from']);
        }
		return $price_from;
	}
	
	public function getIncludeSubcat(){
		if (isset($this->request['include_subcat']))
            $include_subcat = intval($this->request['include_subcat']);
        else
            $include_subcat = 0;
		return $include_subcat;
	}
	
	public function getSearch(){
		return trim($this->request['search']);
	}
	
	public function getSearchType(){
		$search_type = $this->request['search_type'];
        if (!$search_type) $search_type = "any";
		return $search_type;
	}
	
	public function getExtraFields(){
		$jshopConfig = JSFactory::getConfig();
		if ($jshopConfig->admin_show_product_extra_field){
            if (isset($this->request['extra_fields'])) 
                $extra_fields = $this->request['extra_fields'];
            else
                $extra_fields = array();
            $extra_fields = filterAllowValue($extra_fields, "array_int_k_v+");
        }else{
			$extra_fields = array();
		}
		return $extra_fields;
	}
	
	public function getCategorys(){
		$categorys = array();
		$category_id = $this->getCategoryId();
		$include_subcat = $this->getIncludeSubcat();
        if ($category_id) {
            if ($include_subcat){
                $_category = JSFactory::getTable('category', 'jshop');
                $all_categories = $_category->getAllCategories();
                $cat_search[] = $category_id;
                searchChildCategories($category_id, $all_categories, $cat_search);
                foreach($cat_search as $key=>$value) {
                    $categorys[] = $value;
                }
            }else{
                $categorys[] = $category_id;
            }
        }
		return $categorys;
	}

    public function getLabels(){
        if (isset($this->request['labels']))
            $labels = $this->request['labels'];
        else
            $labels = array();
        return filterAllowValue($labels, "int+");
    }
	
}
<?php
/**
* @version      4.11.0 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');

class jshopSearch{
	
	private $search = '';
	private $filters = array();
	private $date_to;
	private $date_from;
	private $search_type;
	private $order;
	private $orderby;
	private $adv_query;
	private $adv_from;
	private $adv_result;
	private $order_query;
	private $buildAdvQuery;

	public function setSearch($val){
		$this->search = $val;
	}
	
	public function setFilters($val){
		$this->filters = $val;
	}
	
	public function setDateTo($val){
		$this->date_to = $val;
	}
	
	public function setDateFrom($val){
		$this->date_from = $val;
	}
	
	public function setSearchType($val){
		$this->search_type = $val;
	}
	
	public function setOrder($val){
		$this->order = $val;
	}
	
	public function setOrderby($val){
		$this->orderby = $val;
	}	
	
	public function getTotal(){
		$db = JFactory::getDBO();
		$this->buildAdvQuery();
		
		$query = "SELECT count(distinct prod.product_id) FROM `#__jshopping_products` AS prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat ON pr_cat.product_id = prod.product_id
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id                  
                  ".$this->adv_from."
                  WHERE prod.product_publish = '1' AND cat.category_publish='1'
                  ".$this->adv_query;
        $db->setQuery($query);
        return $db->loadResult();
	}
	
	public function getProducts($limitstart = null, $limit = null){
		$db = JFactory::getDBO();
		$this->buildAdvQuery();
		
		$query = "SELECT ".$this->adv_result." FROM `#__jshopping_products` AS prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat ON pr_cat.product_id = prod.product_id
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id                  
                  ".$this->adv_from."
                  WHERE prod.product_publish = '1' AND cat.category_publish='1'
                  ".$this->adv_query."
                  GROUP BY prod.product_id ".$this->order_query;
        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        $rows = listProductUpdateData($rows);
        addLinkToProducts($rows, 0, 1);
		return $rows;
	}
	
	private function buildAdvQuery(){
		if ($this->buildAdvQuery==1) return 0;
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        $db = JFactory::getDBO();		
		$product = JSFactory::getTable('product', 'jshop');
		$orderbyq = getQuerySortDirection($this->order, $this->orderby);        
		$adv_query = "";
		$adv_from = "";
		$adv_result = $product->getBuildQueryListProductDefaultResult();
        $product->getBuildQueryListProduct("search", "list", $this->filters, $adv_query, $adv_from, $adv_result);        

        if ($this->date_to && checkMyDate($this->date_to)) {
            $adv_query .= " AND prod.product_date_added <= '".$db->escape($this->date_to)."'";
        }
        if ($this->date_from && checkMyDate($this->date_from)) {
            $adv_query .= " AND prod.product_date_added >= '".$db->escape($this->date_from)."'";
        }
        
        $where_search = "";
        if ($this->search_type=="exact"){
            $word = addcslashes($db->escape($this->search), "_%");
            $tmp = array();
            foreach($jshopConfig->product_search_fields as $field){
                $tmp[] = "LOWER(".getDBFieldNameFromConfig($field).") LIKE '%".$word."%'";
            }
            $where_search = implode(' OR ', $tmp);
        }else{        
            $words = explode(" ", $this->search);
            $search_word = array();
            foreach($words as $word){
                $word = addcslashes($db->escape($word), "_%");
                $tmp = array();
                foreach($jshopConfig->product_search_fields as $field){
                    $tmp[] = "LOWER(".getDBFieldNameFromConfig($field).") LIKE '%".$word."%'";
                }
                $where_search_block = implode(' OR ', $tmp);
                $search_word[] = "(".$where_search_block.")";
            }
            if ($this->search_type=="any"){
                $where_search = implode(" OR ", $search_word);
            }else{
                $where_search = implode(" AND ", $search_word);
            }
        }
        if ($where_search){
			$adv_query .= " AND ($where_search)";
		}
		
		$orderbyf = $jshopConfig->sorting_products_field_s_select[$this->order];
        $order_query = $product->getBuildQueryOrderListProduct($orderbyf, $orderbyq, $adv_from);
		
		$dispatcher->trigger('onBeforeQueryGetProductList', array("search", &$adv_result, &$adv_from, &$adv_query, &$order_query, &$this->filters) );
		
		$this->adv_result = $adv_result;
		$this->adv_from = $adv_from;
		$this->adv_query = $adv_query;
		$this->order_query = $order_query;
		
		$this->buildAdvQuery==1;
		return 1;
	}
	
}
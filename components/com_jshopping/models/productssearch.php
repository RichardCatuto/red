<?php
/**
* @version      4.17.1 22.03.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once JPATH_JOOMSHOPPING.'/models/productlistinterface.php';

class jshopProductsSearch implements jshopProductListInterface{

	private $buildAdvQuery = 0;
	private $adv_result;
	private $adv_from;
	private $adv_query;
	private $order_query;

    function getCountProducts($filters, $order = null, $orderby = null){
		$db = JFactory::getDBO();
		$this->buildAdvQuery($filters, $order, $orderby);

		$query = "SELECT count(distinct prod.product_id) FROM `#__jshopping_products` AS prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat ON pr_cat.product_id = prod.product_id
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id
                  ".$this->adv_from."
                  WHERE prod.product_publish=1 AND cat.category_publish=1
                  ".$this->adv_query;
        extract(js_add_trigger(get_defined_vars(), 'before'));
        $db->setQuery($query);
        return $db->loadResult();
	}

	function getProducts($filters, $order = null, $orderby = null, $limitstart = 0, $limit = 0){
        $db = JFactory::getDBO();
		$this->buildAdvQuery($filters, $order, $orderby);

		$query = "SELECT ".$this->adv_result." FROM `#__jshopping_products` AS prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat ON pr_cat.product_id = prod.product_id
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id
                  ".$this->adv_from."
                  WHERE prod.product_publish=1 AND cat.category_publish=1
                  ".$this->adv_query."
                  GROUP BY prod.product_id ".$this->order_query;
        extract(js_add_trigger(get_defined_vars(), 'before'));
        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        $rows = listProductUpdateData($rows);
        addLinkToProducts($rows, 0, 1);
		return $rows;
	}

	function getDefaultProductSorting(){
        return JSFactory::getConfig()->product_sorting;
    }

    function getDefaultProductSortingDirection(){
        return JSFactory::getConfig()->product_sorting_direction;
    }

    function getCountProductsPerPage(){
        return JSFactory::getConfig()->count_products_to_page_search;
    }

    function getCountProductsToRow(){
        return JSFactory::getConfig()->count_products_to_row_search;
    }

    function getProductFieldSorting($order){
        if ($order==4){
            $order = 1;
        }
        return JSFactory::getConfig()->sorting_products_field_s_select[$order];
    }

    public function getContext(){
        return "jshoping.searclist.front.product";
    }

    public function getContextFilter(){
        return "jshoping.searclist.front.product";
    }

    public function getNoFilterListProduct(){
        return array();
    }

    public function getProductListName(){
        return 'search';
    }

    public function getProductsOrderingTypeList(){
        return 0;
    }

	public function getFilterListProduct(){
		$jshopConfig = JSFactory::getConfig();
		$request = JSFactory::getModel('searchrequest', 'jshop');

        $date_to = $request->getDateTo();
        $date_from = $request->getDateFrom();
        $price_to = $request->getPriceTo();
		$price_from = $request->getPriceFrom();
        $search = $request->getSearch();
        $search_type = $request->getSearchType();
		$extra_fields = $request->getExtraFields();
        $categorys = $request->getCategorys();
        $labels = $request->getLabels();
        $manufacturers = $request->getManufacturers();

		$filters = array();
        $filters['categorys'] = $categorys;
        $filters['manufacturers']= $manufacturers;
        $filters['price_from'] = $price_from;
        $filters['price_to'] = $price_to;
        if ($jshopConfig->admin_show_product_extra_field){
            $filters['extra_fields'] = $extra_fields;
        }
        $filters['labels'] = $labels;

		$filters['search'] = $search;
		$filters['date_from'] = $date_from;
		$filters['date_to'] = $date_to;
		$filters['search_type'] = $search_type;
		extract(js_add_trigger(get_defined_vars(), "after"));
		return $filters;
	}

	private function buildAdvQuery($filters, $order = null, $orderby = null){
		if ($this->buildAdvQuery==1){
			return 0;
		}
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        $db = JFactory::getDBO();
		$product = JSFactory::getTable('product', 'jshop');

		$adv_query = "";
		$adv_from = "";
		$adv_result = $product->getBuildQueryListProductDefaultResult();
        $product->getBuildQueryListProduct("search", "list", $filters, $adv_query, $adv_from, $adv_result);

        if ($filters['date_to'] && checkMyDate($filters['date_to'])) {
            $adv_query .= " AND prod.product_date_added <= '".$db->escape($filters['date_to'])."'";
        }
        if ($filters['date_from'] && checkMyDate($filters['date_from'])) {
            $adv_query .= " AND prod.product_date_added >= '".$db->escape($filters['date_from'])."'";
        }

        $where_search = "";
        if ($filters['search_type']=="exact"){
            $word = addcslashes($db->escape($filters['search']), "_%");
            $tmp = array();
            foreach($jshopConfig->product_search_fields as $field){
                $tmp[] = "LOWER(".getDBFieldNameFromConfig($field).") LIKE '%".$word."%'";
            }
            $where_search = implode(' OR ', $tmp);
        }else{
            $words = explode(" ", $filters['search']);
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
            if ($filters['search_type']=="any"){
                $where_search = implode(" OR ", $search_word);
            }else{
                $where_search = implode(" AND ", $search_word);
            }
        }
        if ($where_search){
			$adv_query .= " AND ($where_search)";
		}

        $order_query = $product->getBuildQueryOrderListProduct($order, $orderby, $adv_from);

		$dispatcher->trigger('onBeforeQueryGetProductList', array("search", &$adv_result, &$adv_from, &$adv_query, &$order_query, &$filters) );

		$this->adv_result = $adv_result;
		$this->adv_from = $adv_from;
		$this->adv_query = $adv_query;
		$this->order_query = $order_query;

		$this->buildAdvQuery=1;
		return 1;
	}

}
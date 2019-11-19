<?php
/**
* @version      4.13.0 09.01.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once JPATH_JOOMSHOPPING.'/models/productlistinterface.php';

class jshopVendor extends JTableAvto implements jshopProductListInterface{

    function __construct(&$_db){
        parent::__construct('#__jshopping_vendors', 'id', $_db);
        JPluginHelper::importPlugin('jshoppingproducts');
    }
    
    function loadMain(){
        $query = "SELECT id FROM #__jshopping_vendors WHERE `main`=1";
        extract(js_add_trigger(get_defined_vars(), "query"));
        $this->_db->setQuery($query);
        $id = intval($this->_db->loadResult());
        $this->load($id);
    }
    
    function loadFull($id){
        if ($id){
            $this->load($id);
        }else{
            $this->loadMain();
        }
    }
    
	function check(){
        jimport('joomla.mail.helper');
            
	    if(trim($this->f_name) == '') {	    	
		    $this->setError(_JSHOP_REGWARN_NAME);
		    return false;
	    }
        
        if( (trim($this->email == "")) || ! JMailHelper::isEmailAddress($this->email)) {
            $this->setError(_JSHOP_REGWARN_MAIL);
            return false;
        }
        if ($this->user_id){
            $query = "SELECT id FROM #__jshopping_vendors WHERE `user_id`='".$this->_db->escape($this->user_id)."' AND id!=".(int)$this->id;
            $this->_db->setQuery($query);
            $xid = intval($this->_db->loadResult());
            if ($xid){
                $this->setError(sprintf(_JSHOP_ERROR_SET_VENDOR_TO_MANAGER, $this->user_id));
                return false;
            }
        }
        
	return true;
	}
    
    function getAllVendors($publish=1, $limitstart, $limit, $orderby = null) {
        $db = JFactory::getDBO();
        $where = "";
        if (isset($publish)){
            $where = "and `publish`='".$db->escape($publish)."'";
        }
		if (!$orderby){
			$orderby = JSFactory::getConfig()->get_vendors_order_query;
		}
        $query = "SELECT * FROM `#__jshopping_vendors` where 1 ".$where." ORDER BY ".$orderby;
        $db->setQuery($query, $limitstart, $limit);        
        return $db->loadObjectList();
    }
    
    function getCountAllVendors($publish=1){
        $db = JFactory::getDBO(); 
        $where = "";
        if (isset($publish)){
            $where = "and `publish`='".$db->escape($publish)."'";
        }
        $query = "SELECT COUNT(id) FROM `#__jshopping_vendors` where 1 ".$where;
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    function getProducts($filters, $order = null, $orderby = null, $limitstart = 0, $limit = 0){
		$db = JFactory::getDBO();
        $jshopConfig = JSFactory::getConfig();
        $lang = JSFactory::getLang();
        $adv_query = ""; $adv_from = ""; $adv_result = $this->getBuildQueryListProductDefaultResult();
        $this->getBuildQueryListProduct("vendor", "list", $filters, $adv_query, $adv_from, $adv_result);
        
        if ($this->main){
            $query_vendor_id = "(prod.vendor_id=".(int)$this->id." OR prod.vendor_id =0)";
        }else{
            $query_vendor_id = "prod.vendor_id=".(int)$this->id;
        }
        $order_query = $this->getBuildQueryOrderListProduct($order, $orderby, $adv_from);

        JPluginHelper::importPlugin('jshoppingproducts');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeQueryGetProductList', array("vendor", &$adv_result, &$adv_from, &$adv_query, &$order_query, &$filters) );
        
        $query = "SELECT $adv_result FROM `#__jshopping_products` AS prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id                  
                  $adv_from
                  WHERE ".$query_vendor_id." AND prod.product_publish=1 AND cat.category_publish=1 ".$adv_query."
                  GROUP BY prod.product_id ".$order_query;
       if ($limit){
            $db->setQuery($query, $limitstart, $limit);
       }else{
            $db->setQuery($query);
       }
       $products = $db->loadObjectList();
       $products = listProductUpdateData($products, 1);
       return $products;
    }    
    
    function getCountProducts($filters, $order = null, $orderby = null){
		$db = JFactory::getDBO();
        $jshopConfig = JSFactory::getConfig();
        $adv_query = ""; $adv_from = ""; $adv_result = "";
        $this->getBuildQueryListProduct("vendor", "count", $filters, $adv_query, $adv_from, $adv_result);
        
        if ($this->main){
            $query_vendor_id = "(prod.vendor_id=".(int)$this->id." OR prod.vendor_id =0)";
        }else{
            $query_vendor_id = "prod.vendor_id=".(int)$this->id;
        }
        
        JPluginHelper::importPlugin('jshoppingproducts');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeQueryCountProductList', array("vendor", &$adv_result, &$adv_from, &$adv_query, &$filters) );

        $query = "SELECT COUNT(distinct prod.product_id) FROM `#__jshopping_products` as prod
                  LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)
                  LEFT JOIN `#__jshopping_categories` AS cat ON pr_cat.category_id = cat.category_id
                  $adv_from
                  WHERE ".$query_vendor_id." AND prod.product_publish=1 AND cat.category_publish=1 ".$adv_query;
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    function prepareViewListVendor(&$rows){
        $jshopConfig = JSFactory::getConfig();
        foreach($rows as $k=>$v){
            $rows[$k]->link = SEFLink("index.php?option=com_jshopping&controller=vendor&task=products&vendor_id=".$v->id);
            if (!$v->logo){
                $rows[$k]->logo = $jshopConfig->image_vendors_live_path."/".$jshopConfig->noimage;
            }
        }
        return $rows;
    }
	
	function getCountryName(){
		$country = JSFactory::getTable('country', 'jshop');
        $country->load($this->country);
        return $country->getName();
	}
	
	public function getCountPerPage(){
		return JSFactory::getConfig()->count_products_to_page;
	}
	
	public function getCountToRow(){
		return JSFactory::getConfig()->count_category_to_row;
	}
    
    function getDefaultProductSorting(){
        return JSFactory::getConfig()->product_sorting;
    }
    
    function getDefaultProductSortingDirection(){
        return JSFactory::getConfig()->product_sorting_direction;
    }
    
    function getCountProductsPerPage(){
        $count = $this->products_page;
        if (!$count){
		    $count = JSFactory::getConfig()->count_products_to_page; 
        }        
        return $count;
    }
    
    function getCountProductsToRow(){
        $count = $this->products_row;
        if (!$count){
		    $count = JSFactory::getConfig()->count_products_to_row;
        }
        return $count;
    }
    
    function getProductFieldSorting($order){
        if ($order==4){
            $order = 1;
        }
        return JSFactory::getConfig()->sorting_products_field_s_select[$order];
    }
    
    public function getContext(){
        return "jshoping.vendor.front.product";        
    }
    
    public function getContextFilter(){
        return "jshoping.list.front.product.vendor.".$this->id;
    }
    
    public function getNoFilterListProduct(){
        return array("vendors");
    }
    
    public function getProductListName(){
        return 'vendor';
    }
    
    public function getProductsOrderingTypeList(){
        return 0;
    }
	
	public function getFilterListProduct(){
		return getBuildFilterListProduct($this->getContextFilter(), $this->getNoFilterListProduct());
	}
    
}
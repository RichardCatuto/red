<?php
/**
* @version      4.16.3 02.08.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopProductAttribut extends JTable{
    
    function __construct( &$_db ){
        parent::__construct('#__jshopping_products_attr', 'product_attr_id', $_db);
    }
    
    function check(){
        return 1;
    }
    
    function deleteAttributeForProduct(){
        $db = JFactory::getDBO();
        $query = "DELETE FROM `#__jshopping_products_attr` WHERE `product_id`=".(int)$this->product_id;
        $db->setQuery($query);
        $db->query();    
    }
    
    function deleteAttribute($id){
        $db = JFactory::getDBO();
        
        $this->load($id);
        if ($this->ext_attribute_product_id){
			JSFactory::getModel("products")->delete($this->ext_attribute_product_id);
        }
        
        $query = "DELETE FROM `#__jshopping_products_attr` WHERE `product_attr_id`=".(int)$id;
        $db->setQuery($query);
        $db->query();
    }
}
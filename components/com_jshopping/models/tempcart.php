<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopTempCart{
    
    public $savedays = 30;
	public $load_product_temp_cart_type = array('wishlist');
    
    function __construct(){
        JPluginHelper::importPlugin('jshoppingcheckout');
        JDispatcher::getInstance()->trigger('onConstructJshopTempCart', array(&$this));
    }
	
	function checkAccessToTempCart($type_cart){		
		if (!in_array($type_cart, $this->load_product_temp_cart_type)){
            return 0;
        }else{
			return 1;
		}
	}
	
	function getUniqId(){
		return session_id();
	}
    
    function insertTempCart($cart){
        if (!$this->checkAccessToTempCart($cart->type_cart)){
            return 0;
        }
        $id = $this->getUniqId();
		$this->setIdTempCart($id);
		$this->delete($id, $cart->type_cart);
		if (!count($cart->products)){
			return 0;
		}
		$this->save($id, $cart->type_cart, $cart->products);
        return 1;
    }
	
	function save($id, $type, $products){
		$db = JFactory::getDBO();
		$query = "INSERT INTO `#__jshopping_cart_temp` SET 
                    `id_cookie` = '".$db->escape($id)."', 
                    `cart` = '".$db->escape(serialize($products))."',
                    `type_cart` = '".$db->escape($type)."' ";
        $db->setQuery($query);
        $db->query();
	}
	
	function delete($id, $type){
		$db = JFactory::getDBO();
		$query = "DELETE FROM `#__jshopping_cart_temp` WHERE `id_cookie` = '".$db->escape($id)."' AND `type_cart`='".$db->escape($type)."'";
        $db->setQuery($query);
        $db->query();
	}
	
	function getProducts($type){
		return $this->getTempCart($this->getIdTempCart(), $type);
	}
	
	function setIdTempCart($id){
		$patch = "/";
        if (JURI::base(true) != ""){
			$patch = JURI::base(true);
		}
		$time = time() + 3600*24*$this->savedays;
		setcookie('jshopping_temp_cart', $id, $time, $patch);
	}
	
	function getIdTempCart(){
		return (string)$_COOKIE['jshopping_temp_cart'];
	}

    function getTempCart($id_cookie, $type_cart="cart"){
        $db = JFactory::getDBO();
        $query = "SELECT `cart` FROM `#__jshopping_cart_temp`
                  WHERE `id_cookie` = '".$db->escape($id_cookie)."' AND `type_cart`='".$db->escape($type_cart)."' LIMIT 0,1";
        $db->setQuery($query);
        $cart = $db->loadResult();
        if ($cart!="")        
            return (unserialize($cart));
        else
            return array();    
    }
}
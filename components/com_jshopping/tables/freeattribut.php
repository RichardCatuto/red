<?php
/**
* @version      4.11.0 12.03.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopFreeAttribut extends jshopMultilang{
    
    function __construct( &$_db ){
        parent::__construct('#__jshopping_free_attr', 'id', $_db );
    }
    
    function getAll() {
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO(); 
        $query = "SELECT id, `".$lang->get("name")."` as name, required, ordering FROM `#__jshopping_free_attr` ORDER BY `ordering`";
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    function getAllNames(){
        $rows = array();
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO(); 
        $query = "SELECT id, `".$lang->get("name")."` as name FROM `#__jshopping_free_attr` ORDER BY `ordering`";
        $db->setQuery($query);
        $list = $db->loadObjectList();        
        foreach($list as $v){
            $rows[$v->id] = $v->name;
        }
        return $rows;
    }
}
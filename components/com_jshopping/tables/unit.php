<?php
/**
* @version      4.11.1 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopUnit extends jshopMultilang{
    
    function __construct( &$_db ){
        parent::__construct( '#__jshopping_unit', 'id', $_db );
    }
    
    function getAllUnits(){
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO(); 
        $query = "SELECT id, `".$lang->get("name")."` as name, qty FROM `#__jshopping_unit` ORDER BY id";
        $db->setQuery($query);
        $list = $db->loadObjectList();
        $rows = array();
        foreach($list as $row){
             $rows[$row->id] = $row;
        }
        return $rows;
    }
        
}
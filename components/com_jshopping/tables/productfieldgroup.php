<?php
/**
* @version      4.11.0 10.12.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopProductFieldGroup extends jshopMultilang{
    
    function __construct( &$_db ){
        parent::__construct('#__jshopping_products_extra_field_groups', 'id', $_db );
    }
    
    function getList(){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang(); 
        $query = "SELECT id, `".$lang->get("name")."` as name, ordering FROM `#__jshopping_products_extra_field_groups` order by ordering";
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
}
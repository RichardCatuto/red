<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopDeliveryTimes extends jshopMultilang{
    
    function __construct( &$_db ){
        parent::__construct( '#__jshopping_delivery_times', 'id', $_db );
    }
    
    function getDeliveryTimes(){
        $db = JFactory::getDBO();    
        $lang = JSFactory::getLang();     
        $query = "SELECT id, `".$lang->get('name')."` as name FROM `#__jshopping_delivery_times` ORDER BY name";
        $db->setQuery($query);
        return $db->loadObjectList();
    }
        
}
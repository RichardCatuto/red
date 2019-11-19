<?php
/**
* @version      4.11.0 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

abstract class jshopMultilang extends jshopShopBase{

    public function getName($id = null){        
		if (!is_null($id)){
			return $this->getNameForId($id);
		}
		$lang = JSFactory::getLang();
        $field = $lang->get("name");
    return $this->$field;
    }
	
	public function getDescription(){
        $lang = JSFactory::getLang();
        $field = $lang->get("description");
    return $this->$field;
    }
	
	public function getNameForId($id){
		$db = JFactory::getDBO();
        $lang = JSFactory::getLang();		
        $query = "SELECT `".$lang->get("name")."` as name FROM `".$this->_tbl."` WHERE `".$this->_tbl_key."` = '".$db->escape($id)."'";
        $db->setQuery($query);
        return $db->loadResult();
	}
	
}
<?php
/**
* @version      4.11.0 01.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshopHelpersCategory{
	
	public static function getListSubCatsId($ids = array()){
        $db = JFactory::getDBO();
        if (!count($ids)){
            return array();
        }
        $ids = filterAllowValue($ids, 'int+');
        $query = "select category_id from `#__jshopping_categories` where category_parent_id in (".implode(',', $ids).")";
        $db->setQuery($query);
        $list = $db->loadObjectList();
        $rows = array();
        foreach($list as $v){
            $rows[] = $v->category_id;
        }
        return $rows;
    }
    
    public static function getAllChildrenCatsId($id){        
        $rows = array();       
        $list = array($id);
        while(count($list)){            
            $list = self::getListSubCatsId($list);
            $rows = array_merge($rows, $list);
        }
        return array_unique($rows);
    }
	
}
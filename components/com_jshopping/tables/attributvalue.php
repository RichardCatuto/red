<?php
/**
* @version      4.16.4 10.10.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class jshopAttributValue extends jshopMultilang{
    
    function __construct(&$_db){
        parent::__construct('#__jshopping_attr_values', 'value_id', $_db);
    }
    
    function getAllValues($attr_id) {
        $db = JFactory::getDBO(); 
        $lang = JSFactory::getLang();
        $query = "SELECT value_id, image, `".$lang->get("name")."` as name, value_ordering, attr_id FROM `#__jshopping_attr_values` "
                . "where attr_id=".(int)$attr_id." ORDER BY value_ordering, value_id";
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    /**
    * get All Atribute value
    * @param $resulttype (0 - ObjectList, 1 - array {id->name}, 2 - array(id->object) )
    * 
    * @param mixed $resulttype
    */
    function getAllAttributeValues($resulttype=0){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "SELECT value_id, image, `".$lang->get("name")."` as name, attr_id, value_ordering FROM `#__jshopping_attr_values` ORDER BY value_ordering, value_id";
        $db->setQuery($query);
        $attribs = $db->loadObjectList();

        if ($resulttype==2){
            $rows = array();
            foreach($attribs as $k=>$v){
                $rows[$v->value_id] = $v;    
            }
            return $rows;
        }elseif ($resulttype==1){
            $rows = array();
            foreach($attribs as $k=>$v){
                $rows[$v->value_id] = $v->name;    
            }
            return $rows;
        }else{
            return $attribs;
        }        
    }
    
    public function getNextOrder($where = ''){
		$query = $this->_db->getQuery(true)
			->select('MAX(value_ordering)')
			->from($this->_tbl);
		if ($where){
			$query->where($where);
		}
		$this->_db->setQuery($query);
		$max = (int) $this->_db->loadResult();
		return ($max + 1);
	}

    public function reorder($where = ''){
    }
       
}
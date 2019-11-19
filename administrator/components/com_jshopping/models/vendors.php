<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelVendors extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'vendor';

    function getNamesVendors() {
        $db = JFactory::getDBO();
        $query = "SELECT id, f_name, l_name FROM `#__jshopping_vendors` ORDER BY f_name, l_name DESC";
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function getAllVendors($limitstart, $limit, $text_search="") {
        $db = JFactory::getDBO();
        $where = "";
        if ($text_search){
            $search = $db->escape($text_search);
            $where .= " and (f_name like '%".$search."%' or l_name like '%".$search."%' or email like '%".$search."%') ";
        }
        $query = "SELECT * FROM `#__jshopping_vendors` where 1 ".$where." ORDER BY id DESC";
        $db->setQuery($query, $limitstart, $limit);
        return $db->loadObjectList();
    }

    function getCountAllVendors($text_search = "") {
        $db = JFactory::getDBO();
        $where = "";
        if ($text_search){
            $search = $db->escape($text_search);
            $where .= " and (f_name like '%".$search."%' or l_name like '%".$search."%' or email like '%".$search."%') ";
        }
        $query = "SELECT COUNT(id) FROM `#__jshopping_vendors` where 1 ".$where." ORDER BY id DESC";
        $db->setQuery($query);
        return $db->loadResult();
    }

    function getAllVendorsNames($main_id_null = 0){
        $db = JFactory::getDBO();
        $query = "SELECT id, concat(f_name, ' ', l_name) as name, `main` FROM `#__jshopping_vendors` ORDER BY name";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($main_id_null){
            foreach($rows as $k=>$v){
                if ($v->main) $rows[$k]->id = 0;
            }
        }
        return $rows;
    }

    function getIdVendorForUserId($id){
        $db = JFactory::getDBO();
        $query = "SELECT id FROM `#__jshopping_vendors` where user_id='".$db->escape($id)."'";
        $db->setQuery($query);
        return $db->loadResult();
    }

    function save(array $post){
        $vendor = JSFactory::getTable('vendor', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $vendor->load($post["id"]);
        $post['publish'] = (int) $post['publish'];
        $dispatcher->trigger('onBeforeSaveVendor', array(&$post));
        $vendor->bind($post);
        JSFactory::loadLanguageFile();
        if (!$vendor->check()){            
            $this->setError($vendor->getError());
            return 0;
        }
        if (!$vendor->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveVendor', array(&$vendor));
        return $vendor;
    }

    function deleteList(array $cid, $msg = 1){
        $res = array();
        $app = JFactory::getApplication();
        $vendor = JSFactory::getTable('vendor', 'jshop');
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveVendor', array(&$cid));
        foreach($cid as $id) {
            $query = "select count(*) from `#__jshopping_products` where `vendor_id`=" . intval($id);
            $db->setQuery($query);
			if( !$db->loadResult() ){
                $query = "delete from `#__jshopping_vendors` where id='" . $db->escape($id) . "' and main=0";
                $db->setQuery($query);
                $db->query();
                $res[ $id ] = true;
            } else {
                $vendor->load($id);
                if ($msg){
                    $app->enqueueMessage(sprintf(_JSHOP_ITEM_ALREADY_USE, $vendor->f_name . " " . $vendor->l_name), 'error');
                }
                $res[ $id ] = false;
            }
        }
        $dispatcher->trigger('onAfterRemoveVendor', array(&$cid));
        return $res;
    }

}
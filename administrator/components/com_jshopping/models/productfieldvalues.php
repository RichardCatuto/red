<?php
/**
* @version      4.14.0 26.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelProductFieldValues extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'productFieldValue';

	function getList($field_id, $order = null, $orderDir = null, $filter=array()){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $ordering = 'ordering';
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $where = '';
		if ($filter['text_search']){
            $text_search = $filter['text_search'];
            $word = addcslashes($db->escape($text_search), "_%");
            $where =  " and (LOWER(`".$lang->get('name')."`) LIKE '%".$word."%' OR id LIKE '%".$word."%')";
        }
        $query = "SELECT id, `".$lang->get("name")."` as name, ordering FROM `#__jshopping_products_extra_field_values` where field_id='$field_id' ".$where." order by ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function getAllList($display = 0){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "SELECT id, `".$lang->get("name")."` as name, field_id FROM `#__jshopping_products_extra_field_values` order by ordering";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        if ($display==0){
            return $db->loadObjectList();
        }elseif($display==1){
            $rows = $db->loadObjectList();
            $list = array();
            foreach($rows as $k=>$row){
                $list[$row->id] = $row->name;
                unset($rows[$k]);
            }
            return $list;
        }else{
            $rows = $db->loadObjectList();
            $list = array();
            foreach($rows as $k=>$row){
                $list[$row->field_id][$row->id] = $row->name;
                unset($rows[$k]);
            }
            return $list;
        }
    }

    public function save(array $post){
        $productfieldvalue = JSFactory::getTable('productFieldValue', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveProductFieldValue', array(&$post));
        if( !$productfieldvalue->bind($post) ) {
            JError::raiseWarning("",_JSHOP_ERROR_BIND);
            $this->setRedirect("index.php?option=com_jshopping&controller=productfieldvalues");
            return 0;
        }
        if( !$post['id'] ) {
            $productfieldvalue->ordering = null;
            $productfieldvalue->ordering = $productfieldvalue->getNextOrder('field_id="' . $post['field_id'] . '"');
        }
        if( !$productfieldvalue->store() ) {
            JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE);
            $this->setRedirect("index.php?option=com_jshopping&controller=productfieldvalues");
            return 0;
        }
        $dispatcher->trigger('onAfterSaveProductFieldValue', array(&$productfieldvalue));
        return $productfieldvalue;
    }

    public function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        foreach($cid as $value) {
            $query = "DELETE FROM `#__jshopping_products_extra_field_values` WHERE `id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
        }
        JDispatcher::getInstance()->trigger('onAfterRemoveProductFieldValue', array(&$cid));
    }

}
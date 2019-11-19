<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelFreeAttribut extends JshoppingModelBaseadmin{
    
    function getNameAttrib($id) {
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "SELECT `".$lang->get("name")."` as name FROM `#__jshopping_free_attr` WHERE id = '".$db->escape($id)."'";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }

    function getAll($order = null, $orderDir = null) {
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO(); 
        $ordering = 'ordering';
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $query = "SELECT id, `".$lang->get("name")."` as name, ordering, required FROM `#__jshopping_free_attr` ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);        
        return $db->loadObjectList();
    }
    
    public function save(array $post){
        $attribut = JSFactory::getTable('freeattribut', 'jshop');    
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveFreeAtribut', array(&$post));
        if (!$post['id']){
            $attribut->ordering = null;
            $attribut->ordering = $attribut->getNextOrder();            
        }
        $attribut->bind($post);
        if (!$attribut->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveFreeAtribut', array(&$attribut));
        return $attribut;
    }
    
    public function deleteList(array $cid = array(), $msg = 1){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveFreeAtribut', array(&$cid));
		foreach($cid as $id){            
			$query = "DELETE FROM `#__jshopping_free_attr` WHERE `id` = ".(int)$id;
			$db->setQuery($query);
			$db->query();
            
            $query = "delete from `#__jshopping_products_free_attr` where `attr_id` = ".(int)$id;
            $db->setQuery($query);
            $db->query();
		}
        if ($msg){
            $app->enqueueMessage(_JSHOP_ATTRIBUT_DELETED, 'message');
        }
        $dispatcher->trigger('onAfterRemoveFreeAtribut', array(&$cid));
    }
    
}

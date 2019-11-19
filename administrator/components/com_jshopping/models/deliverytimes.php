<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelDeliveryTimes extends JshoppingModelBaseadmin{

    function getDeliveryTimes($order = null, $orderDir = null){
        $db = JFactory::getDBO();    
        $lang = JSFactory::getLang();    
        
        $ordering = "name";
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $query = "SELECT id, `".$lang->get('name')."` as name FROM `#__jshopping_delivery_times` ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    function getCountDeliveryTimes() {
        $db = JFactory::getDBO();         
        $query = "SELECT count(id) FROM `#__jshopping_delivery_times`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    public function save(array $post){
        $deliveryTimes = JSFactory::getTable('deliveryTimes', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveDeliveryTime', array(&$post));        
		$deliveryTimes->bind($post);
		if (!$deliveryTimes->store()){
			$this->setError(_JSHOP_ERROR_SAVE_DATABASE);
			return 0;
		}
        $dispatcher->trigger('onAfterSaveDeliveryTime', array(&$deliveryTimes));
        return $deliveryTimes;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveDeliveryTime', array(&$cid));
        $res = array();
		foreach($cid as $id){
			$query = "DELETE FROM `#__jshopping_delivery_times` WHERE `id` = ".(int)$id;
			$db->setQuery($query);
			if ($db->query()){
                $res[$id] = true;
                if ($msg){
                    $app->enqueueMessage(_JSHOP_DELIVERY_TIME_DELETED, 'message');
                }
            }else{
                $res[$id] = false;
                if ($msg){
                    $app->enqueueMessage(_JSHOP_DELIVERY_TIME_DELETED_ERROR_DELETED, 'error');
                }
            }
		}
        $dispatcher->trigger('onAfterRemoveDeliveryTime', array(&$cid));
        return $res;
    }
    
}
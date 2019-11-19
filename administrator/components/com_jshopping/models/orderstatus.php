<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingModelOrderstatus extends JshoppingModelBaseadmin{
	
	public function save(array $post){
        $order_status = JSFactory::getTable('orderStatus', 'jshop');        
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveOrderStatus', array(&$post));
		$order_status->bind($post);
		if (!$order_status->store()){
			$this->setError(_JSHOP_ERROR_SAVE_DATABASE);			
			return 0;
		}
        $dispatcher->trigger('onAfterSaveOrderStatus', array(&$order_status));
        return $order_status;
	}
    
    public function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveOrderStatus', array(&$cid));
        $res = array();
		foreach($cid as $id){
			$this->delete($id);
            $res[$id] = true;
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
		}
        $dispatcher->trigger('onAfterRemoveOrderStatus', array(&$cid));
        return $res;
    }
    
    public function delete($id){
        $db = JFactory::getDBO();
        $query = "DELETE FROM `#__jshopping_order_status` WHERE `status_id`=".(int)$id;
        $db->setQuery($query);
        return $db->query();
    }

}
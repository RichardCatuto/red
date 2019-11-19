<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelUnits extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'unit';

    function getUnits(){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "SELECT id, `".$lang->get('name')."` as name FROM `#__jshopping_unit` ORDER BY name";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function save(array $post){
		$unit = JSFactory::getTable('unit', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveUnit', array(&$post));
		$unit->bind($post);
		if( !$unit->store() ) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
			return 0;
		}
        $dispatcher->trigger('onAfterSaveUnit', array(&$unit));
        return $unit;
    }

    function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
		$db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveUnit', array(&$cid));
		foreach($cid as $id){
			$query = "DELETE FROM `#__jshopping_unit` WHERE `id` = ".(int)$id;
			$db->setQuery($query);
			$db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
		}
        $dispatcher->trigger('onAfterRemoveUnit', array(&$cid));
    }

}
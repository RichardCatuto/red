<?php
/**
* @version      4.14.0 02.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelAddons extends JModelLegacy{

    function getList($details = 0){
        $db = JFactory::getDBO(); 
        $query = "SELECT * FROM `#__jshopping_addons`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($details){
            foreach($rows as $k=>$v){
                if (file_exists(JPATH_COMPONENT_SITE."/addons/".$v->alias."/config.tmpl.php")){
                    $rows[$k]->config_file_exist = 1;
                }else{
                    $rows[$k]->config_file_exist = 0;
                }
                if (file_exists(JPATH_COMPONENT_SITE."/addons/".$v->alias."/info.tmpl.php")){
                    $rows[$k]->info_file_exist = 1;
                }else{
                    $rows[$k]->info_file_exist = 0;
                }
                if (file_exists(JPATH_COMPONENT_SITE."/addons/".$v->alias."/version.tmpl.php")){
                    $rows[$k]->version_file_exist = 1;
                }else{
                    $rows[$k]->version_file_exist = 0;
                }
            }
        }
        return $rows;
    }
    
    public function save(array $post){
        $row = JSFactory::getTable('addon', 'jshop');
        $params = $post['params'];
        if (!is_array($params)){
            $params = array();
        }
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveAddons', array(&$params, &$post, &$row));
        $row->bind($post);
        $row->setParams($params);
        $row->store();
		$dispatcher->trigger('onAfterSaveAddons', array(&$params, &$post, &$row));
        return $row;
    }
    
    public function delete($id){
        $text = '';
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveAddons', array(&$id));
        $row = JSFactory::getTable('addon', 'jshop');
        $row->load($id);
        if ($row->uninstall){
            include(JPATH_ROOT.$row->uninstall);
        }
        $row->delete();
        $dispatcher->trigger('onAfterRemoveAddons', array(&$id, &$text));
        if ($text){
            JFactory::getApplication()->enqueueMessage($text, 'message');
        }
    }
}
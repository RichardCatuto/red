<?php
/**
* @version      4.14.0 31.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelSeo extends JModelLegacy{ 

    public function getList(){
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO();         
        $query = "SELECT id, alias, `".$lang->get('title')."` as title, `".$lang->get('keyword')."` as keyword, `".$lang->get('description')."` as description FROM `#__jshopping_config_seo` ORDER BY ordering";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    public function save(array $post){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveConfigSeo', array(&$post));        
        $seo = JSFactory::getTable("seo","jshop");
        $seo->load((int)$post['id']);
        $seo->bind($post);
        if (!$post['id']){
            $seo->ordering = null;
            $seo->ordering = $seo->getNextOrder();            
        }
        $seo->store($post);
        $dispatcher->trigger('onAfterSaveConfigSeo', array(&$seo));
        return $seo;
    }
}
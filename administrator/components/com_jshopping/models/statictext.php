<?php
/**
* @version      4.14.0 26.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelStaticText extends JModelLegacy{ 
    
    function getList($use_for_return_policy = 0){
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO(); 
        $where = $use_for_return_policy?' WHERE use_for_return_policy=1 ':'';
        $query = "SELECT id, alias, use_for_return_policy FROM `#__jshopping_config_statictext` ".$where." ORDER BY id";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    public function getPrepareDataSave($input){
        $post = $input->post->getArray();
        $languages = JSFactory::getModel("languages")->getAllLanguages(1);
        foreach($languages as $lang){
            $post['text_'.$lang->language] = $input->get('text'.$lang->id, '', 'RAW');
        }
		if (!isset($post['use_for_return_policy'])){
			$post['use_for_return_policy'] = 0;
		}
        return $post;
    }
    
    public function save(array $post){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveConfigStaticPage', array(&$post));
        $statictext = JSFactory::getTable("statictext","jshop");
        $statictext->load((int)$post['id']);
        $statictext->bind($post);        
        $statictext->store();
        $dispatcher->trigger('onAfterSaveConfigStaticPage', array(&$statictext));
        return $statictext;
    }
    
    public function delete($id){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDeleteConfigStaticPage', array(&$id));
        $statictext = JSFactory::getTable("statictext","jshop");
        $statictext->load($id);
        $statictext->delete();
        $dispatcher->trigger('onAfterDeleteConfigStaticPage', array(&$id));
    }
    
    public function useReturnPolicy(array $cid, $flag){
        $db = JFactory::getDBO();        
        foreach($cid as $value){
            $query = "UPDATE `#__jshopping_config_statictext` SET `use_for_return_policy` = '" . $db->escape($flag) . "' "
                    . "WHERE `id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
        }
    }
}
<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelUsergroups extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'userGroup';

    function getAllUsergroups($order = null, $orderDir = null){
        $ordering = "usergroup_id";
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__jshopping_usergroups` ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function resetDefaultUsergroup(){
        $db = JFactory::getDBO();
        $query = "SELECT `usergroup_id` FROM `#__jshopping_usergroups` WHERE `usergroup_is_default`= '1'";
        $db->setQuery($query);
        $usergroup_default = $db->loadResult();
        $query = "UPDATE `#__jshopping_usergroups` SET `usergroup_is_default` = '0'";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        $db->query();
    }

    function setDefaultUsergroup($usergroup_id){
        $db = JFactory::getDBO();
        $query = "UPDATE `#__jshopping_usergroups` SET `usergroup_is_default` = '1' WHERE `usergroup_id`= '".$db->escape($usergroup_id)."'";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        $db->query();
    }

    function getDefaultUsergroup(){
        $db = JFactory::getDBO();
        $query = "SELECT `usergroup_id` FROM `#__jshopping_usergroups` WHERE `usergroup_is_default`= '1'";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getPrepareDataSave($input){
        $post = $input->post->getArray();
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $lang = JSFactory::getLang();
        foreach($languages as $v){
            $post['name_' . $v->language] = trim($post['name_' . $v->language]);
            $post['description_' . $v->language] = $input->get('description' . $v->id, '', 'RAW');
        }
        $post['usergroup_name'] = $post[$lang->get("name")];
        $post['usergroup_description'] = $post[$lang->get("description")];
        return $post;
    }

    function save(array $post){
		$usergroup = JSFactory::getTable('userGroup', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveUserGroup', array(&$post));
		$usergroup->bind($post);
		if ($usergroup->usergroup_is_default){
			$default_usergroup_id = $this->resetDefaultUsergroup();
		}
		if (!$usergroup->store()){
			$this->setDefaultUsergroup($default_usergroup_id);
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
		}
        $dispatcher->trigger('onAfterSaveUserGroup', array(&$usergroup));
        return $usergroup;
    }

    function deleteList(array $cid, $msg = 1){
		$db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveUserGroup', array(&$cid));
		$res = array();
		foreach($cid as $id){
			$query = "SELECT `usergroup_name` FROM `#__jshopping_usergroups` WHERE `usergroup_id` = ".(int)$id;
			$db->setQuery($query);
			$usergroup_name = $db->loadResult();
            
			$query = "DELETE FROM `#__jshopping_usergroups` WHERE `usergroup_id` = ".(int)$id;
			$db->setQuery($query);
			$db->query();
            
            if ($msg){
                $app->enqueueMessage(sprintf(_JSHOP_USERGROUP_DELETED, $usergroup_name), 'message');
            }
            $res[$id] = true;
		}
        $dispatcher->trigger('onAfterRemoveUserGroup', array(&$cid));
        return $res;
    }

}
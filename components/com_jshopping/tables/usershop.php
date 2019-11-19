<?php
/**
* @version      4.13.0 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('');
include_once(JPATH_ROOT."/components/com_jshopping/tables/usershopbase.php");

class jshopUserShop extends jshopUserShopBase{

    function __construct(&$_db){
        parent::__construct($_db);
    }
    
	function isUserInShop($id) {
		$query = "SELECT user_id FROM `#__jshopping_users` WHERE `user_id`='".$this->_db->escape($id)."'";
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		return $this->_db->getNumRows($res);
	}
    	
	function addUserToTableShop($user){
		$db = JFactory::getDBO();
		$this->u_name = $user->username;
		$this->email = $user->email;
		$this->user_id = $user->id;
        $number = $this->getNewUserNumber();
        $default_usergroup = JSFactory::getTable('usergroup', 'jshop')->getDefaultUsergroup();
        
		$query = "INSERT INTO `#__jshopping_users` SET `usergroup_id`='".$default_usergroup."', `u_name`='".$db->escape($user->username)."', 
				 `email`='".$db->escape($user->email)."', `user_id`='".$db->escape($user->id)."', f_name='".$db->escape($user->name)."', 
				 `number`='".$db->escape($number)."'";
		$db->setQuery($query);
		$db->query();
        JDispatcher::getInstance()->trigger('onAfterAddUserToTableShop', array(&$this));
	}
    
    function store($updateNulls = false){
		if (isset($this->preparePrint) && $this->preparePrint==1){
            throw new Exception('Error jshopUserShop::store()');
        }
        $tmp = $this->percent_discount;
        unset($this->percent_discount);
        JDispatcher::getInstance()->trigger('onBeforeStoreTableShop', array(&$this));
        $res = parent::store($updateNulls);
        $this->percent_discount = $tmp;
        return $res;
    }
    
	function getCountryId($id_user) {
		$db = JFactory::getDBO();
		$query = "SELECT country FROM `#__jshopping_users` WHERE user_id=".(int)$id_user;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function getDiscount(){
		$db = JFactory::getDBO(); 
		$query = "SELECT usergroup.usergroup_discount FROM `#__jshopping_usergroups` AS usergroup
				  INNER JOIN `#__jshopping_users` AS users ON users.usergroup_id = usergroup.usergroup_id
				  WHERE users.user_id = '".$db->escape($this->user_id)."' ";
		$db->setQuery($query);
		return floatval($db->loadResult());
	}
	
	function getIdUserFromField($field, $value){
		$db = JFactory::getDBO();
		$query = "SELECT id FROM `#__users` WHERE ".$db->quoteName($field)." = '".$db->escape($value)."'";
		$db->setQuery($query);
		return $db->loadResult();
	}
    
    function getNewUserNumber(){
        $number = $this->user_id;
        JDispatcher::getInstance()->trigger('onBeforeGetNewUserNumber', array(&$this, &$number));
        return $number;
    }
	
	function prepareUserPrint(){
		$this->preparePrint = 1;
		
		if (!isset($this->country_id)){
            $this->country_id = $this->country;
            $this->d_country_id = $this->d_country;
        }
		
		$country = JSFactory::getTable('country', 'jshop');
        $country->load($this->country_id);
        $this->country = $country->getName();
        
        $d_country = JSFactory::getTable('country', 'jshop');
        $d_country->load($this->d_country_id);
        $this->d_country = $d_country->getName();
		
		$group = JSFactory::getTable('userGroup', 'jshop');
        $group->load($this->usergroup_id);
		$this->groupname = $group->getName();	
        $this->discountpercent = floatval($group->usergroup_discount);
	}
	
	function checkUserExistAjax($username='', $email=''){
		$dispatcher = JDispatcher::getInstance();
        $mes = array();
        $dispatcher->trigger('onBeforeUserCheck_user_exist_ajax', array(&$mes, &$username, &$email));        
		if ($username && $this->getIdUserFromField('username', $username)){
			$mes[] = sprintf(_JSHOP_USER_EXIST, $username);			
		}
        if ($email && $this->getIdUserFromField('email', $email)){			
			$mes[] = sprintf(_JSHOP_USER_EXIST_EMAIL, $email);			
		}
        $dispatcher->trigger('onAfterUserCheck_user_exist_ajax', array(&$mes, &$username, &$email));
        if (count($mes)==0){
            return "1";
        }else{
            return implode("\n", $mes);
        }
	}
}
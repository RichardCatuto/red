<?php
/**
* @version      4.16.1 20.01.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(dirname(__FILE__)."/userbase.php");

class jshopUseredit extends jshopUserBase{
        
    private $data = array();
    private $user;
    private $user_joomla;
    private $user_id = 0;
	private $admin_registration = 0;
    
    public function __construct(){
        $this->loadUserParams();
        JDispatcher::getInstance()->trigger('onConstructJshopUseredit', array(&$this));
    }
	
	public function setUserId($id){
		$this->user_id = $id;
		$this->user = JSFactory::getTable('userShop', 'jshop');
		$this->user->load($this->user_id);
	}
	
	public function setUser($user){
		$this->user = $user;
		$this->user_id = $user->user_id;
	}
	
	public function getUser(){
		return $this->user;
	}

    public function prepateData(&$post){
		$jshopConfig = JSFactory::getConfig();
		if (!isset($post['password'])) $post['password'] = '';
        if (!isset($post['password_2'])) $post['password_2'] = '';
		if ($post['password2']) $post['password_2'] = $post['password2'];
        if ($post['birthday']) $post['birthday'] = getJsDateDB($post['birthday'], $jshopConfig->field_birthday_format);
        if ($post['d_birthday']) $post['d_birthday'] = getJsDateDB($post['d_birthday'], $jshopConfig->field_birthday_format);
        unset($post['user_id']);
		if (!$this->admin_registration){
			$post['lang'] = $jshopConfig->getLang();
			unset($post['usergroup_id']);
			if (!$jshopConfig->not_update_user_joomla && $post['email']){
				$field = $jshopConfig->getListFieldsRegisterType('register');
				if ($field['u_name']['display'] == 0){
					$post['u_name'] = $post['email'];
				}
			}
		}
	}
	
	public function setData(&$data){
		$this->prepateData($data);
        $this->data = &$data;
        $this->user->bind($data);
	}
	
	public function check($type){
		if (!count($this->data)){
			$this->setError(_JSHOP_ERROR_DATA);
			return 0;
		}
        $this->user->password = $this->data['password'];
        $this->user->password2 = $this->data['password_2'];
		if (!$this->user->check($type)){
            $this->setError($this->user->getError());
			$res = 0;
		}else{
			$res = 1;
		}
		unset($this->user->password);
        unset($this->user->password2);
		return $res;
    }
	
	public function userSave(){
		return $this->user->store();
	}
	
	public function userJoomlaSave(){
		$jshopConfig = JSFactory::getConfig();
        $post = $this->data;
		$user_shop = $this->user;
		if ($user_shop->user_id<=0){
			return 2;
		}
		$user = new JUser($user_shop->user_id);
		if (!$jshopConfig->not_update_user_joomla){
			if ($user_shop->email){
				$user->email = $user_shop->email;
			}
			if ($user_shop->u_name && $jshopConfig->update_username_joomla){
				$user->username = $user_shop->u_name;
			}
			if ($user_shop->f_name || $user_shop->l_name){
				$user->name = $user_shop->f_name.' '.$user_shop->l_name;
			}
		}
        if ($post['password']!=''){
            $data = array("password"=>$post['password'], "password2"=>$post['password']);
            $user->bind($data);
        }
		if ($this->admin_registration){
			$user->username = $post['u_name'];
			$user->block = $post['block'];
		}
        if ($user->save()){
			$this->user_joomla = $user;
			return 1;
		}else{
			$this->setError($user->getError());
			return 0;
		}
	}
	
	public function save(){
		if (!$this->userSave()){
			return 0;
		}
		if (!$this->userJoomlaSave()){
			return 0;
		}
		return 1;
	}
	
	public function updateJoomlaUserCurrentProfile(){
		if (!$this->user_joomla){
			return 0;
		}
		$app = JFactory::getApplication();
		$data = array();
        $data['email'] = $this->user_joomla->email;
        $data['name'] = $this->user_joomla->name;
        $app->setUserState('com_users.edit.profile.data', $data);
		return 1;
	}
	
	public function getUserJoomla(){
        return $this->user_joomla;
    }
	
	public function setAdminRegistration($val){
		$this->admin_registration = $val;
	}
	
	public function getAdminRegistration(){
		return $this->admin_registration;
	}
	
}
<?php
/**
* @version      4.11.4 28.11.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(JPATH_ROOT."/components/com_jshopping/tables/usershopbase.php");

class jshopUserGust extends jshopUserShopBase{
    
    function __construct(){
        $db = JFactory::getDBO();
		parent::__construct($db);
    }
    
    function load($keys = null, $reset = true){
        $jshopConfig = JSFactory::getConfig();
        $session = JFactory::getSession();
        $objuser = $session->get('user_shop_guest');
        if (isset($objuser) && $objuser!=''){
            $tmp = unserialize($objuser);
            foreach($tmp as $k=>$v){
                $this->$k = $v;
            }
        }
        $this->user_id = -1;
        $usergroup = JSFactory::getTable('usergroup', 'jshop');
        $this->usergroup_id = intval($jshopConfig->default_usergroup_id_guest);
        JDispatcher::getInstance()->trigger('onLoadJshopUserGust', array(&$this));
    return true;
    }
    	
    function store($updateNulls = false){
        $this->user_id = -1;
        $session = JFactory::getSession();
        $properties = $this->getProperties();
        $session->set('user_shop_guest', serialize($properties));
        JDispatcher::getInstance()->trigger('onAfterStoreJshopUserGust', array(&$this));
    return true;
    }
	
	function check(){
		$args = func_get_args();
		if (isset($args[0])){
			$type = $args[0];
		}else{
			$type = '';
		}		
		return $this->checkData($type, 0);
	}

}
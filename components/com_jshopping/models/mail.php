<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

abstract class jshopMail extends jshopBase{
	
    protected $params;
    protected $data = array();

    public function getParams(){
        return $this->params;
    }
    
    public function setParams($params){
        return $this->params = $params;
    }
    
    public function setData($data){
        $this->data = $data;
    }
    
    public function getData(){
        return $this->data;
    }
	
	public function getListAdminUserSendEmail(){
        $db = JFactory::getDBO();
        $query = 'SELECT name, email, sendEmail FROM #__users WHERE sendEmail=1';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }
    
    abstract public function send();
        
}
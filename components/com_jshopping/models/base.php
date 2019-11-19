<?php
/**
* @version      4.11.1 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(JPATH_JOOMSHOPPING."/controllers/base.php");

abstract class jshopBase{

	private $error;
	
	public function setError($error){
        $this->error = $error;
    }
    
    public function getError(){
        return $this->error;
    }
    
    public function getView($name){
		$jshopConfig = JSFactory::getConfig();		
		include_once(JPATH_JOOMSHOPPING."/views/".$name."/view.html.php");
		$config = array("template_path"=>$jshopConfig->template_path.$jshopConfig->template."/".$name);
		$viewClass = 'JshoppingView'.$name;
        $view = new $viewClass($config);
        return $view;
    }
	
}
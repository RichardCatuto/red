<?php
/**
* @version      4.14.0 10.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');

class JshoppingControllerBase extends JControllerLegacy{

	public function getView($name = '', $type = '', $prefix = '', $config = array()){
		$jshopConfig = JSFactory::getConfig();
		if ($type==''){
			$type = getDocumentType();
		}
		if (empty($config)){
			$config = array("template_path"=>$jshopConfig->template_path.$jshopConfig->template."/".$name);
		}
		return parent::getView($name, $type, $prefix, $config);
	}
	
	public function getViewAddon($name ='', $type = '', $prefix = '', $viewName = 'addons'){
		$config = array("template_path" => JSFactory::getConfig()->template_path.'addons/'.$name);		
		return $this->getView($viewName, $type, $prefix, $config);
	}
	
}
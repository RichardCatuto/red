<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopImportExport extends JTable {
    
    function __construct(&$_db){
        parent::__construct('#__jshopping_import_export', 'id', $_db);
    }
	
	public function getParams(){
		return (array)json_decode($this->params, 1);
	}
	
	public function setParams(array $params){
		$this->params = json_encode($params);
	}

}
<?php
/**
* @version      4.13.1 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopSeo extends JTable{
    
    function __construct( &$_db ){
        parent::__construct('#__jshopping_config_seo', 'id', $_db);
    }
    
    function loadData($alias){
        $lang = JSFactory::getLang();
        $db = JFactory::getDBO();
        $query = "SELECT id, alias, `".$lang->get('title')."` as title, `".$lang->get('keyword')."` as keyword, `".$lang->get('description')."` as description FROM `#__jshopping_config_seo` where alias='".$db->escape($alias)."'";
        $db->setQuery($query);
		$data = $db->loadObject();
		if (!isset($data)){
            $data = new stdClass();
            $data->title = '';
            $data->keyword = '';
            $data->description = '';
        }
	return $data;
    }
    
}
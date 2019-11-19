<?php
/**
* @version      4.13.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopImportExportStart{
	
	public function checkKey($key){	
		return JSFactory::getConfig()->securitykey == $key;
	}
	
	public function getListStart($time, $filterAlias = ''){
		$db = JFactory::getDBO();
        if ($filterAlias){
            $adv_query = " and `alias`='".$db->escape($filterAlias)."'";
        }else{
            $adv_query = '';
        }
        $query = "SELECT * FROM `#__jshopping_import_export` where `steptime`>0 and (endstart + steptime < ".(int)$time.") ".$adv_query."  ORDER BY id";
        $db->setQuery($query);
        return $db->loadObjectList();
	}
	
	public function executeList($time = null, $print_alias = 1, $filterAlias = ''){
		if (is_null($time)){
			$time = time();
		}        
        $list = $this->getListStart($time, $filterAlias);

        foreach($list as $ie){
            $alias = $ie->alias;
            if (!file_exists(JPATH_COMPONENT_ADMINISTRATOR."/importexport/".$alias."/".$alias.".php")){
                print sprintf(_JSHOP_ERROR_FILE_NOT_EXIST, "/importexport/".$alias."/".$alias.".php");
                return 0;
            }            
			$this->execute($alias, $ie->id);
			if ($print_alias){
				print $alias."\n";
			}
        }
	}
	
	public function execute($alias, $id){
		$_importexport = JSFactory::getTable('ImportExport', 'jshop'); 
        $_importexport->load($id);
		
		include_once(JPATH_COMPONENT_ADMINISTRATOR."/importexport/".$alias."/".$alias.".php");
		$classname = 'Ie'.$alias;
		$controller = new $classname(array(
            'ie_id' => $id,
            'alias' => $alias,
            'params' => $_importexport->get('params')
        ));		
		$controller->save();
	}
	
}
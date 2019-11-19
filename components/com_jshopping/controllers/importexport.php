<?php
/**
* @version      4.13.0 25.03.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();
JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');
include_once(JPATH_COMPONENT_ADMINISTRATOR."/importexport/iecontroller.php");

class JshoppingControllerImportExport extends JshoppingControllerBase{
    
    function display($cachable = false, $urlparams = false){
        JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
    }

    function start(){
		$_GET['noredirect'] = 1;
		$_POST['noredirect'] = 1;
		$_REQUEST['noredirect'] = 1;
		$key = $this->input->getVar("key");
        $alias = $this->input->getVar("alias");
		$model = JSFactory::getModel('importExportStart', 'jshop');
		if ($model->checkKey($key)){
			$model->executeList(null, 1, $alias);
		}
        die();
    }
}
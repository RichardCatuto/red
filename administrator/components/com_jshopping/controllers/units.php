<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerUnits extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("units");
        addSubmenu("other");
    }

	function display($cachable = false, $urlparams = false){		
		$rows = JSFactory::getModel("units")->getUnits();

		$view = $this->getView("units", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->sidebar = JHtmlSidebar::render();        
        JDispatcher::getInstance()->trigger('onBeforeDisplayUnits', array(&$view));
		$view->displayList();
	}

    function edit(){
        $id = $this->input->getInt("id");
        $units = JSFactory::getTable('unit', 'jshop');
        $units->load($id);
        $edit = ($id)?(1):(0);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        if (!$units->qty){
            $units->qty = 1;
        }

        JFilterOutput::objectHTMLSafe( $units, ENT_QUOTES);

		$view = $this->getView("units", 'html');
        $view->setLayout("edit");
        $view->assign('units', $units);
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditUnitss', array(&$view));
		$view->displayEdit();
	}

}
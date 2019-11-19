<?php
/**
* @version      4.15.1 08.09.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined( '_JEXEC' ) or die();

class JshoppingControllerLogs extends JControllerLegacy{
    
    function __construct( $config = array() ){
        parent::__construct( $config );
        checkAccessController("logs");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $model = JSFactory::getModel("logs");
        $rows = $model->getList();
        
		$view = $this->getView("logs", 'html');
        $view->setLayout("list");	
        $view->assign('rows', $rows);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayLogs', array(&$view));
		$view->displayList();
    }
    
    function edit(){
        $id = $this->input->getVar('id');
        $filename = str_replace(array('..', '/', ':'), '', $id);
        $model = JSFactory::getModel("logs");
        $data = $model->read($filename);        
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                
        $view = $this->getView("logs", 'html');
        $view->setLayout("edit");        
        $view->assign('filename', $filename);                
        $view->assign('data', $data);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditLogs', array(&$view));
        $view->displayEdit();
    }
}
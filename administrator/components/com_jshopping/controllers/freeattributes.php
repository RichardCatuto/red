<?php
/**
* @version      4.14.0 26.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerFreeAttributes extends JshoppingControllerBaseadmin{
    
    protected $nameModel = 'freeattribut';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("freeattributes");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.freeattributes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
    	$freeattributes = JSFactory::getModel("freeattribut");    	
        $rows = $freeattributes->getAll($filter_order, $filter_order_Dir);
        $view = $this->getView("freeattributes", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayFreeAttributes', array(&$view));
        $view->displayList();
    }
	
    function edit(){
        $id = $this->input->getInt("id");
	
        $attribut = JSFactory::getTable('freeattribut', 'jshop');
        $attribut->load($id);
    
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        
        JFilterOutput::objectHTMLSafe($attribut, ENT_QUOTES);		

        $view = $this->getView("freeattributes", 'html');
        $view->setLayout("edit");
        $view->assign('attribut', $attribut);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onBeforeEditFreeAtribut', array(&$view, &$attribut) );
        $view->displayEdit();
		
	}
	
}
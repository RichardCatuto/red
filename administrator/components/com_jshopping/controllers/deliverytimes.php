<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerDeliveryTimes extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("deliverytimes");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.deliverytimes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "name", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
        $_deliveryTimes = JSFactory::getModel("deliveryTimes");
        $rows = $_deliveryTimes->getDeliveryTimes($filter_order, $filter_order_Dir);
        $view=$this->getView("deliverytimes", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows); 
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayDeliveryTimes', array(&$view));
        $view->displayList();
    }
	
	function edit(){
		$id = $this->input->getInt("id");
		$deliveryTimes = JSFactory::getTable('deliveryTimes', 'jshop');
		$deliveryTimes->load($id);
		$edit = ($id)?(1):(0);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        JFilterOutput::objectHTMLSafe($deliveryTimes, ENT_QUOTES);

		$view = $this->getView("deliverytimes", 'html');
        $view->setLayout("edit");
        $view->assign('deliveryTimes', $deliveryTimes);        
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditDeliverytimes', array(&$view));
		$view->displayEdit();
	}
    
}

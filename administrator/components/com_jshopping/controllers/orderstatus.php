<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerOrderStatus extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'status_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("orderstatus");
        addSubmenu("other");
    }

	function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.orderstatus";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "status_id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
		$_order = JSFactory::getModel("orders");
		$rows = $_order->getAllOrderStatus($filter_order, $filter_order_Dir);

		$view = $this->getView("orderstatus", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);        
		$view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayOrderStatus', array(&$view));
		$view->displayList();
	}
	
	function edit(){
		$status_id = $this->input->getInt("status_id");
		$order_status = JSFactory::getTable('orderStatus', 'jshop');
		$order_status->load($status_id);
		$edit = ($status_id)?($edit = 1):($edit = 0);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        
        JFilterOutput::objectHTMLSafe($order_status, ENT_QUOTES);

		$view = $this->getView("orderstatus", 'html');
        $view->setLayout("edit");		
        $view->assign('order_status', $order_status);        
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('etemplatevar', '');
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditOrderStatus', array(&$view));
		$view->displayEdit();
	}

}
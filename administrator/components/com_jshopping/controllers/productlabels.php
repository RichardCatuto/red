<?php
/**
* @version      4.14.0 24.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerProductLabels extends JshoppingControllerBaseadmin{
    
    protected $modelSaveItemFileName = 'image';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("productlabels");
        addSubmenu("other");
    }

	function display($cachable = false, $urlparams = false){
        $jshopConfig = JSFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.productlabels";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "name", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
		$_productLabels = JSFactory::getModel("productLabels");
		$rows = $_productLabels->getList($filter_order, $filter_order_Dir);
        
		$view = $this->getView("product_labels", 'html');
        $view->setLayout("list");		
        $view->assign('rows', $rows);
        $view->assign('config', $jshopConfig);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayProductLabels', array(&$view));		
		$view->displayList();
	}
	
	function edit(){
        $jshopConfig = JSFactory::getConfig();
		$id = $this->input->getInt("id");
		$productLabel = JSFactory::getTable('productLabel', 'jshop');
		$productLabel->load($id);
		$edit = ($id)?(1):(0);
		$_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        JFilterOutput::objectHTMLSafe($productLabel, ENT_QUOTES);

		$view = $this->getView("product_labels", 'html');
        $view->setLayout("edit");
        $view->assign('productLabel', $productLabel);
        $view->assign('config', $jshopConfig);
        $view->assign('edit', $edit);
		$view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditProductLabels', array(&$view));
		$view->displayEdit();
	}
    
    function delete_foto(){
        $jshopConfig = JSFactory::getConfig();
        $id = $this->input->getInt("id");
        $productLabel = JSFactory::getTable('productLabel', 'jshop');
        $productLabel->load($id);
        @unlink($jshopConfig->image_labels_path."/".$productLabel->image);
        $productLabel->image = "";
        $productLabel->store();
        die();               
    }

}
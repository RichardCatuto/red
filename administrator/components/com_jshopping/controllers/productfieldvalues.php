<?php
/**
* @version      4.14.0 26.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerProductFieldValues extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("productfieldvalues");
        addSubmenu("other");
    }
    
    public function getUrlListItems(){
        $field_id = $this->input->getInt('field_id');
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&field_id=".$field_id;
    }
    
    public function getUrlEditItem($id){
        $field_id = $this->input->getInt('field_id');
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&task=edit&field_id=".$field_id."&id=".$id;
    }

    function display($cachable = false, $urlparams = false){
        $field_id = $this->input->getInt("field_id");
        $_productfieldvalues = JSFactory::getModel("productFieldValues");
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.productfieldvalues";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        $text_search = $mainframe->getUserStateFromRequest($context.'text_search', 'text_search', '');

        $filter = array("text_search"=>$text_search);

        $rows = $_productfieldvalues->getList($field_id, $filter_order, $filter_order_Dir, $filter);

        $view = $this->getView("product_field_values", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('field_id', $field_id);
		$view->assign('text_search', $text_search);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayProductFieldValues', array(&$view));
        $view->displayList();
    }

    function edit(){
        $field_id = $this->input->getInt("field_id");
        $id = $this->input->getInt("id");

        $productfieldvalue = JSFactory::getTable('productFieldValue', 'jshop');
        $productfieldvalue->load($id);

        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;

        $view = $this->getView("product_field_values", 'html');
        $view->setLayout("edit");
        JFilterOutput::objectHTMLSafe($productfieldvalue, ENT_QUOTES);
        $view->assign('row', $productfieldvalue);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('field_id', $field_id);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditProductFieldValues', array(&$view));
        $view->displayEdit();
    }

    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=productfields");
    }
    
    protected function getOrderWhere(){
        $field_id = $this->input->getInt("field_id");
        return 'field_id='.(int)$field_id;
    }
    
    protected function getSaveOrderWhere(){
        $field_id = $this->input->getInt("field_id");
        return 'field_id='.(int)$field_id;
    }

}
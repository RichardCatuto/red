<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerAttributesValues extends JshoppingControllerBaseadmin{
    
    protected $nameModel = 'attributvalue';
    protected $modelSaveItemFileName = 'image';

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("attributesvalues");
        addSubmenu("other");
    }
    
    public function getUrlListItems(){
        $attr_id = $this->input->getInt("attr_id");
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&attr_id=".$attr_id;
    }
    
    public function getUrlEditItem($id){
        $attr_id = $this->input->getInt("attr_id");    
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&task=edit&attr_id=".$attr_id."&value_id=".$id;
    }

    function display($cachable = false, $urlparams = false){
		$attr_id = $this->input->getInt("attr_id");
        $jshopConfig = JSFactory::getConfig();
        
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.attr_values";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "value_ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
		$attributValues = JSFactory::getModel("AttributValue");
		$rows = $attributValues->getAllValues($attr_id, $filter_order, $filter_order_Dir);
		$attribut = JSFactory::getModel("attribut");
		$attr_name = $attribut->getName($attr_id);
        
		$view = $this->getView("attributesvalues", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);        
        $view->assign('attr_id', $attr_id);
        $view->assign('config', $jshopConfig);
        $view->assign('attr_name', $attr_name);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayAttributesValues', array(&$view));
		$view->displayList(); 
	}
	
	function edit(){
		$value_id = $this->input->getInt("value_id");
		$attr_id = $this->input->getInt("attr_id");
        
		$jshopConfig = JSFactory::getConfig();	
        
        $attributValue = JSFactory::getTable('attributValue', 'jshop');
        $attributValue->load($value_id);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;	
        
        JFilterOutput::objectHTMLSafe($attributValue, ENT_QUOTES);
		
		$view = $this->getView("attributesvalues", 'html');
        $view->setLayout("edit");		
        $view->assign('attributValue', $attributValue);        
        $view->assign('attr_id', $attr_id);        
        $view->assign('config', $jshopConfig);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditAtributesValues', array(&$view));
		$view->displayEdit();
	}
    
    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=attributes");
    }
    
    function delete_foto(){
        $id = $this->input->getInt("id");
        $this->getAdminModel()->deleteFoto($id);
        die();               
    }
    
    protected function getOrderWhere(){
        $attr_id = $this->input->getInt("attr_id");
        return 'attr_id='.(int)$attr_id;
    }

}
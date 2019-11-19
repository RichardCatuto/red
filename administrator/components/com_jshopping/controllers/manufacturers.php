<?php
/**
* @version      4.14.0 22.07.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerManufacturers extends JshoppingControllerBaseadmin{
    
    protected $modelSaveItemFileName = 'manufacturer_logo';

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("manufacturers");
        addSubmenu("other");
    }
    
    public function getUrlEditItem($id){
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&task=edit&man_id=".$id;
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshopping.list.admin.manufacturers";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        $manufacturer = JSFactory::getModel("manufacturers");
        $rows = $manufacturer->getAllManufacturers(0, $filter_order, $filter_order_Dir);        
        $view = $this->getView("manufacturer", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayManufacturers', array(&$view));
        $view->displayList();
    }

    function edit() {
        $man_id = $this->input->getInt("man_id");
        $manufacturer = JSFactory::getTable('manufacturer', 'jshop');
        $manufacturer->load($man_id);
        $edit = ($man_id)?(1):(0);
        
        if (!$man_id){
            $manufacturer->manufacturer_publish = 1;
        }
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        
        $nofilter = array();
        JFilterOutput::objectHTMLSafe($manufacturer, ENT_QUOTES, $nofilter);

        $view=$this->getView("manufacturer", 'html');
        $view->setLayout("edit");
        $view->assign('manufacturer', $manufacturer);        
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('etemplatevar', '');
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditManufacturers', array(&$view));        
        $view->displayEdit();
    }

    function delete_foto(){
        $id = $this->input->getInt("id");
        JSFactory::getModel('manufacturers')->deleteFoto($id);        
        die();
    }

}
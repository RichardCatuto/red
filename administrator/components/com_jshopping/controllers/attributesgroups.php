<?php
/**
* @version      4.14.0 10.15.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerAttributesGroups extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("attributesgroups");
        addSubmenu("other");
    }
    
    function display($cachable = false, $urlparams = false){
        $model = JSFactory::getModel("attributesGroups");
        $rows = $model->getList();
        
        $view = $this->getView("attributesgroups", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayAttributesGroups', array(&$view));
        $view->displayList();
    }
    
    function edit(){        
        $id = $this->input->getInt("id");
        $row = JSFactory::getTable('attributesgroup', 'jshop');
        $row->load($id);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        JFilterOutput::objectHTMLSafe($row, ENT_QUOTES);
                
        $view = $this->getView("attributesgroups", 'html');
        $view->setLayout("edit");
        $view->assign('row', $row);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditAttributesGroups', array(&$view));
        $view->displayEdit();
    }

    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=attributes");
    }
    
}
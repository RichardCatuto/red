<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerProductFieldGroups extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct( $config );
        checkAccessController("productfieldgroups");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $_productfieldgroups = JSFactory::getModel("productFieldGroups");
        $rows = $_productfieldgroups->getList();

        $view = $this->getView("product_field_groups", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayProductsFieldGroups', array(&$view));
        $view->displayList();
    }

    function edit(){
        $id = $this->input->getInt("id");
        $productfieldgroup = JSFactory::getTable('productFieldGroup', 'jshop');
        $productfieldgroup->load($id);

        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;

        $view = $this->getView("product_field_groups", 'html');
        $view->setLayout("edit");
        JFilterOutput::objectHTMLSafe($productfieldgroup, ENT_QUOTES);
        $view->assign('row', $productfieldgroup);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditProductFieldGroups', array(&$view));
        $view->displayEdit();
    }

    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=productfields");
    }

}
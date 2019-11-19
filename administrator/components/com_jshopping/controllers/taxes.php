<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerTaxes extends JshoppingControllerBaseadmin{

    protected $urlEditParamId = 'tax_id';
    
    function __construct($config = array()){
        parent::__construct( $config );
        checkAccessController("taxes");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.taxes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "tax_name", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');

        $taxes = JSFactory::getModel("taxes");
        $rows = $taxes->getAllTaxes($filter_order, $filter_order_Dir);

        $view = $this->getView("taxes", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayTaxes', array(&$view));
        $view->displayList();
    }

    function edit() {
        $tax_id = $this->input->getInt("tax_id");
        $tax = JSFactory::getTable('tax', 'jshop');
        $tax->load($tax_id);
        $edit = ($tax_id)?($edit = 1):($edit = 0);

        $view=$this->getView("taxes", 'html');
        $view->setLayout("edit");
        JFilterOutput::objectHTMLSafe( $tax, ENT_QUOTES);
        $view->assign('tax', $tax);
        $view->assign('edit', $edit);
        $view->assign('etemplatevar', '');

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditTaxes', array(&$view));
        $view->displayEdit();
    }

}
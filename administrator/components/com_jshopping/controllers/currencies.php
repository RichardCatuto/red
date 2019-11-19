<?php
/**
* @version      4.14.0 03.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerCurrencies extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'currency_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("currencies");
        addSubmenu("other");
    }
        
    function display($cachable = false, $urlparams = false) {        
        $jshopConfig = JSFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.currencies";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "currency_ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');

        $current_currency = JSFactory::getTable('currency', 'jshop');
        $current_currency->load($jshopConfig->mainCurrency);
        if ($current_currency->currency_value!=1){
            JError::raiseWarning("",_JSHOP_ERROR_MAIN_CURRENCY_VALUE);    
        }
        
        $currencies = JSFactory::getModel("currencies");
        $rows = $currencies->getAllCurrencies(0, $filter_order, $filter_order_Dir);
        
        $view = $this->getView("currencies", 'html');
        $view->setLayout("list");        
        $view->assign('rows', $rows);        
        $view->assign('config', $jshopConfig);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayCourencies', array(&$view));
        $view->displayList();
    }
    
    function edit(){
        $currency = JSFactory::getTable('currency', 'jshop');
        $currencies = JSFactory::getModel("currencies");
        $currency_id = $this->input->getInt('currency_id');
        $currency->load($currency_id);
        if ($currency->currency_value==0){
            $currency->currency_value = 1;
        }
        $first[] = JHTML::_('select.option', '0',_JSHOP_ORDERING_FIRST,'currency_ordering','currency_name');
        $rows = array_merge($first, $currencies->getAllCurrencies(0));
        $lists['order_currencies'] = JHTML::_('select.genericlist', $rows,'currency_ordering','class="inputbox" ','currency_ordering','currency_name',$currency->currency_ordering);
        $edit = ($currency_id)?($edit = 1):($edit = 0);
        JFilterOutput::objectHTMLSafe($currency, ENT_QUOTES);
        
        $view = $this->getView("currencies", 'html');
        $view->setLayout("edit");
        $view->assign('currency', $currency);        
        $view->assign('lists', $lists);        
        $view->assign('edit', $edit);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditCurrencies', array(&$view));        
        $view->displayEdit();
    }
    
    function setdefault(){
        $jshopConfig = JSFactory::getConfig();
        $cid = $this->input->getVar("cid");
        $db = JFactory::getDBO();
        if ($cid[0]){
            $config = new jshopConfig($db);
            $config->id = $jshopConfig->load_id;
            $config->mainCurrency = $cid[0];
            $config->store();
        }
        $this->setRedirect("index.php?option=com_jshopping&controller=currencies");
    }
    
}
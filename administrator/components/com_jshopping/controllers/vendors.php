<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerVendors extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("vendors");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $app = JFactory::getApplication();
        $context = "jshopping.list.admin.vendors";
        $limit = $app->getUserStateFromRequest( $context.'limit', 'limit', $app->getCfg('list_limit'), 'int' );
        $limitstart = $app->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int' );
        $text_search = $app->getUserStateFromRequest( $context.'text_search', 'text_search', '' );

        $vendors = JSFactory::getModel("vendors");
        $total = $vendors->getCountAllVendors($text_search);

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $rows = $vendors->getAllVendors($pageNav->limitstart, $pageNav->limit, $text_search);

        $view = $this->getView("vendors", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('pageNav', $pageNav);
        $view->assign('text_search', $text_search);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayVendors', array(&$view));
        $view->displayList();
    }

    function edit(){
        $id = $this->input->getInt("id");
        $vendor = JSFactory::getTable('vendor', 'jshop');
        $vendor->load($id);
        if (!$id){
            $vendor->publish = 1;
        }
        $lists['country'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(0), 'country','class = "inputbox"','country_id','name', $vendor->country);

        $nofilter = array();
        JFilterOutput::objectHTMLSafe( $vendor, ENT_QUOTES, $nofilter);

        $view = $this->getView("vendors", 'html');
        $view->setLayout("edit");
        $view->assign('vendor', $vendor);
        $view->assign('lists', $lists);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditVendors', array(&$view));
        $view->displayEdit();
    }

}
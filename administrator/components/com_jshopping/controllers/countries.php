<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerCountries extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'country_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("countries");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){  	        		
        $mainframe = JFactory::getApplication();
		$context = "jshoping.list.admin.countries";
        $limit = $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int' );
        $publish = $mainframe->getUserStateFromRequest( $context.'publish', 'publish', 0, 'int' );
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');        
		
		$countries = JSFactory::getModel("countries");
		$total = $countries->getCountAllCountries();
        if ($publish == 0){
            $total = $countries->getCountAllCountries();
        } else {
            $total = $countries->getCountPublishCountries($publish % 2);
        }
		
		jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);		
        $rows = $countries->getAllCountries($publish, $pageNav->limitstart,$pageNav->limit, 0, $filter_order, $filter_order_Dir);
        $filter = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPublish(4), 'publish', 'class="chosen-select" onchange="document.adminForm.submit();"', 'id', 'name', $publish);
                
		$view = $this->getView("countries", 'html');
        $view->setLayout("list");		
        $view->assign('rows', $rows); 
        $view->assign('pageNav', $pageNav);       
        $view->assign('filter', $filter);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayCountries', array(&$view));
		$view->displayList(); 
    }
    
   	function edit() {
		$country_id = $this->input->getInt("country_id");
		$country = JSFactory::getTable('country', 'jshop');
		$country->load($country_id);
		$lists['order_countries'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountryOrdering(), 'ordering','class="inputbox"','ordering','name', $country->ordering);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;        
        
		$edit = ($country_id)?($edit = 1):($edit = 0);                
        
        JFilterOutput::objectHTMLSafe( $country, ENT_QUOTES);

		$view=$this->getView("countries", 'html');
        $view->setLayout("edit");		
        $view->assign('country', $country); 
        $view->assign('lists', $lists);       
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('etemplatevar', '');
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditCountries', array(&$view));
		$view->displayEdit();
	}

}
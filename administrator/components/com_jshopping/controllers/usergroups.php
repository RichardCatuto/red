<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerUserGroups extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'usergroup_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("usergroups");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.usergroups";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "usergroup_id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');

		$usergroups = JSFactory::getModel("usergroups");
		$rows = $usergroups->getAllUsergroups($filter_order, $filter_order_Dir);

        $view = $this->getView("usergroups", 'html');
        $view->setLayout("list");
        $view->assign("rows", $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayUserGroups', array(&$view));
        $view->displayList();
    }

	function edit(){
		$usergroup_id = $this->input->getInt("usergroup_id");
		$usergroup = JSFactory::getTable('userGroup', 'jshop');
		$usergroup->load($usergroup_id);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;

        $edit = ($usergroup_id) ? 1 : 0;
        JFilterOutput::objectHTMLSafe($usergroup, ENT_QUOTES, "usergroup_description");

		$view = $this->getView("usergroups", 'html');
        $view->setLayout("edit");
        $view->assign("usergroup", $usergroup);
        $view->assign('etemplatevar', '');
        $view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditUserGroups', array(&$view));
        $view->displayEdit();
	}

}
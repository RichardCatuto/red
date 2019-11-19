<?php
/**
* @version      4.14.4 31.05.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerUsers extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'user_id';
    protected $checkToken = array('save' => 1, 'remove' => 1);
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("users");
        addSubmenu("users");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshopping.list.admin.users";
        $limit = $mainframe->getUserStateFromRequest($context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');
        $text_search = $mainframe->getUserStateFromRequest($context.'text_search', 'text_search', '');
        $usergroup_id = $mainframe->getUserStateFromRequest($context.'usergroup_id', 'usergroup_id', 0, 'int');
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "u_name", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        $e_name = $this->input->getCmd("e_name");
        $select_user = $this->input->getInt('select_user');

        $filter = array();
        if ($usergroup_id){
            $filter['usergroup_id'] = $usergroup_id;
        }
        
        $users = JSFactory::getModel("users");        
        $total = $users->getCountAllUsers($text_search, $filter);
        
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);
        $rows = $users->getAllUsers($pageNav->limitstart, $pageNav->limit, $text_search, $filter_order, $filter_order_Dir, $filter);

        $select_group = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getUserGroups(1), 'usergroup_id', 'class="chosen-select" onchange="document.adminForm.submit();"', 'usergroup_id', 'usergroup_name', $usergroup_id);
        
        $view=$this->getView("users", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('pageNav', $pageNav);
        $view->assign('text_search', $text_search);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->assign('e_name', $e_name);
        $view->assign('select_user', $select_user);
        $view->assign('select_group', $select_group);
        $view->sidebar = JHtmlSidebar::render();
		
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayUsers', array(&$view));
        $view->displayList();
    }
    
    function edit(){
        $jshopConfig = JSFactory::getConfig();        
        $me =  JFactory::getUser();
        $user_id = $this->input->getInt("user_id");
        $user = JSFactory::getTable('userShop', 'jshop');
        $user->load($user_id);
        $user->loadDataFromEdit();
		
        $user_site = new JUser($user_id);
		
		$lists['country'] = JshopHelpersSelects::getCountry($user->country);
		$lists['d_country'] = JshopHelpersSelects::getCountry($user->d_country, 'class = "inputbox endes"', 'd_country');
		$lists['select_titles'] = JshopHelpersSelects::getTitle($user->title);
		$lists['select_d_titles'] = JshopHelpersSelects::getTitle($user->d_title, 'class = "inputbox endes"', 'd_title');
		$lists['select_client_types'] = JshopHelpersSelects::getClientType($user->client_type);
        $lists['usergroups'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getUserGroups(), 'usergroup_id', 'class = "inputbox"', 'usergroup_id', 'usergroup_name', $user->usergroup_id);
        $lists['block'] = JHTML::_('select.booleanlist',  'block', 'class="inputbox"', $user_site->get('block') );  
        
        filterHTMLSafe($user, ENT_QUOTES);
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields['editaccount'];
        $count_filed_delivery = $jshopConfig->getEnableDeliveryFiledRegistration('editaccount');
        
		JHTML::_('behavior.calendar');
		
        $view=$this->getView("users", 'html');
        $view->setLayout("edit");
		$view->assign('config', $jshopConfig);
        $view->assign('user', $user);  
        $view->assign('me', $me);       
        $view->assign('user_site', $user_site);
        $view->assign('lists', $lists);
        $view->assign('etemplatevar', '');
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        JDispatcher::getInstance()->trigger('onBeforeEditUsers', array(&$view));
        $view->displayEdit();        
    }
    
    function get_userinfo(){
        $db = JFactory::getDBO();
        $id = $this->input->getInt('user_id');
        if (!$id){
            print '{}';
            die;
        }
        $query = 'SELECT * FROM `#__jshopping_users` WHERE `user_id`='.(int)$id;
        $db->setQuery($query);
        $user = $db->loadAssoc();
        echo json_encode((array)$user);
        die();
    }

}
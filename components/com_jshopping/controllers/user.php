<?php
/**
* @version      4.17.1 30.03.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingControllerUser extends JshoppingControllerBase{

    function __construct($config = array()){
        parent::__construct($config);
        JPluginHelper::importPlugin('jshoppingcheckout');
        JPluginHelper::importPlugin('jshoppingorder');
        JDispatcher::getInstance()->trigger('onConstructJshoppingControllerUser', array(&$this));
    }
    
    function display($cachable = false, $urlparams = false){
        $this->myaccount();
    }
    
    function login(){
        $jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
		$model = JSFactory::getModel('userlogin', 'jshop');		
		
        if (JFactory::getUser()->id){   
            $this->logoutpage();
            return 0;
        }
		
        $checkout_navigator = JSFactory::getModel('checkout', 'jshop')->showCheckoutNavigation('1');
   
		$return = $model->getUrlHash();
        $show_pay_without_reg = $model->getPayWithoutReg();
        
        JshopHelpersMetadata::userLogin();

		$modelUserRegister = JSFactory::getModel('userregister', 'jshop');
        $adv_user = $modelUserRegister->getRegistrationDefaultData();
        
		$select_countries = JshopHelpersSelects::getCountry($adv_user->country);
		$select_titles = JshopHelpersSelects::getTitle($adv_user->title);
		$select_client_types = JshopHelpersSelects::getClientType($adv_user->client_type);
        
		$config_fields = $jshopConfig->getListFieldsRegisterType('register');
		$allowUserRegistration = $modelUserRegister->getUserParams()->get('allowUserRegistration');

        $dispatcher->trigger('onBeforeDisplayLogin', array() );
		if ($jshopConfig->show_registerform_in_logintemplate){
            $dispatcher->trigger('onBeforeDisplayRegister', array());
        }
		if ($jshopConfig->show_registerform_in_logintemplate && $config_fields['birthday']['display']){
            JHTML::_('behavior.calendar');
        }

        $view = $this->getView('user');
        $view->setLayout("login");
        $view->assign('href_register', SEFLink('index.php?option=com_jshopping&controller=user&task=register',1,0, $jshopConfig->use_ssl));
        $view->assign('href_lost_pass', SEFLInk('index.php?option=com_users&view=reset',0,0, $jshopConfig->use_ssl));
        $view->assign('return', $return);
        $view->assign('Itemid', $this->input->getVar('Itemid'));
        $view->assign('config', $jshopConfig);
        $view->assign('show_pay_without_reg', $show_pay_without_reg);
        $view->assign('select_client_types', $select_client_types);
        $view->assign('select_titles', $select_titles);
        $view->assign('select_countries', $select_countries);
        $view->assign('config_fields', $config_fields);
        $view->assign('live_path', JURI::base());
        $view->assign('urlcheckdata', SEFLink("index.php?option=com_jshopping&controller=user&task=check_user_exist_ajax&ajax=1", 1, 1, $jshopConfig->use_ssl));
        $view->assign('urlcheckpassword', SEFLink("index.php?option=com_jshopping&controller=user&task=check_user_password&ajax=1",1,1, $jshopConfig->use_ssl));
        $view->assign('checkout_navigator', $checkout_navigator);
        $view->assign('allowUserRegistration', $allowUserRegistration);
        $dispatcher->trigger('onBeforeDisplayLoginView', array(&$view));
		if ($jshopConfig->show_registerform_in_logintemplate){
            $dispatcher->trigger('onBeforeDisplayRegisterView', array(&$view));
        }
        $view->display();
    }
    
    function loginsave(){        
        $app = JFactory::getApplication();        
		JDispatcher::getInstance()->trigger('onBeforeLoginSave', array());
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $method = $this->input->getMethod();        
        $remember = $this->input->getBool('remember', false);
        $username = $this->input->$method->get('username', '', 'USERNAME');
        $password = (string)$this->input->$method->get('passwd', '', 'RAW');        

        $model = JSFactory::getModel('userlogin', 'jshop');
        if ($model->login($username, $password, array('remember'=>$remember))){
			setNextUpdatePrices();
            $app->redirect($model->getReturnUrl());
        }else{            
            $app->redirect($model->getUrlBackToLogin());
        }
    }
    
    function check_user_exist_ajax(){
        $username = $this->input->getVar("username");
        $email = $this->input->getVar("email");
		print JSFactory::getTable('userShop', 'jshop')->checkUserExistAjax($username, $email);
        die();
    }
    
    function check_user_password(){
        $pass = $this->input->getVar("pass");
        JFactory::getLanguage()->load('com_users');
        $user = JSFactory::getTable('userShop', 'jshop');
        $res = (int)$user->passwordTest($pass);
        print json_encode(array('res'=>$res, 'msg'=>$user->_error_password));
        die();
    }
    
    function register(){
        $jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        $model = JSFactory::getModel('userregister', 'jshop');
        $adv_user = $model->getRegistrationDefaultData();

        JshopHelpersMetadata::userRegister();
        
        if ($model->getUserParams()->get('allowUserRegistration') == '0'){
            JError::raiseError(403, JText::_('Access Forbidden - Allowing user registration in Joomla configuration'));
            return;
        }
        
		$select_countries = JshopHelpersSelects::getCountry($adv_user->country);
		$select_titles = JshopHelpersSelects::getTitle($adv_user->title);
		$select_client_types = JshopHelpersSelects::getClientType($adv_user->client_type);
        
		$config_fields = $jshopConfig->getListFieldsRegisterType('register');

        $dispatcher->trigger('onBeforeDisplayRegister', array(&$adv_user));
        
        filterHTMLSafe($adv_user, ENT_QUOTES);
        
        $checkout_navigator = JSFactory::getModel('checkout', 'jshop')->showCheckoutNavigation('1');
        
		if ($config_fields['birthday']['display']){
            JHTML::_('behavior.calendar');
        }
        
        $view = $this->getView('user');
        $view->setLayout("register"); 
        $view->assign('config', $jshopConfig);
        $view->assign('select_client_types', $select_client_types);
        $view->assign('select_titles', $select_titles);
        $view->assign('select_countries', $select_countries);
        $view->assign('config_fields', $config_fields);
        $view->assign('user', $adv_user);
        $view->assign('live_path', JURI::base());        
        $view->assign('urlcheckdata', SEFLink("index.php?option=com_jshopping&controller=user&task=check_user_exist_ajax&ajax=1",1,1));
        $view->assign('urlcheckpassword', SEFLink("index.php?option=com_jshopping&controller=user&task=check_user_password&ajax=1",1,1));
        $view->assign('checkout_navigator', $checkout_navigator);
        $dispatcher->trigger('onBeforeDisplayRegisterView', array(&$view));
        $view->display();
    }
    
    function registersave(){
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));        
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
        JFactory::getLanguage()->load('com_users');
        $model = JSFactory::getModel('userregister', 'jshop');
        $params = $model->getUserParams();
        $useractivation = $params->get('useractivation');
        $post = $this->input->post->getArray();

        if ($params->get('allowUserRegistration')==0){
            JError::raiseError(403, JText::_('Access Forbidden'));
            return;
        }
		
		$back_url = SEFLink("index.php?option=com_jshopping&controller=user&task=register&lrd=1",1,1, $jshopConfig->use_ssl);

        $model->setData($post);
        
        if (!$model->check()){
            JError::raiseWarning('', $model->getError());
            $this->setRedirect($back_url);
            return 0;
        }
		if (!$model->save()){
            JError::raiseWarning('', $model->getError());            
            $this->setRedirect($back_url);
            return 0;
        }
        $model->mailSend();
        
        $user = $model->getUserJoomla();
        $usershop = $model->getUser();
        
        $dispatcher->trigger('onAfterRegister', array(&$user, &$usershop, &$post, &$useractivation));

        $message = $model->getMessageUserRegistration($useractivation);
        $return = SEFLink("index.php?option=com_jshopping&controller=user&task=login",1,1,$jshopConfig->use_ssl);

        $this->setRedirect($return, $message);
    }
    
    function activate(){
        $jshopConfig = JSFactory::getConfig();
		$model = JSFactory::getModel('useractivate', 'jshop');
        JFactory::getLanguage()->load('com_users');		
		$token = $this->input->getVar('token');

        if (JFactory::getUser()->get('id')){
            $this->setRedirect('index.php');
            return true;
        }
		if (!$model->check($token)){
			JError::raiseError(403, $model->getError());
            return false;
		}

        $return = $model->activate($token);

        if ($return === false){
            $this->setMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
            $this->setRedirect('index.php');
            return false;
        }
		
		$msg = $model->getMessageUserActivation($return);
        $this->setMessage($msg);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=user&task=login",0,1,$jshopConfig->use_ssl));
        return true;
    }
    
    function editaccount(){
        checkUserLogin();        
        $adv_user = JSFactory::getUserShop()->loadDataFromEdit();
        $jshopConfig = JSFactory::getConfig();
            
        JshopHelpersMetadata::userEditaccount();
		
		$select_countries = JshopHelpersSelects::getCountry($adv_user->country);
		$select_d_countries = JshopHelpersSelects::getCountry($adv_user->d_country, null, 'd_country');
		$select_titles = JshopHelpersSelects::getTitle($adv_user->title);
		$select_d_titles = JshopHelpersSelects::getTitle($adv_user->d_title, null, 'd_title');
		$select_client_types = JshopHelpersSelects::getClientType($adv_user->client_type);
        
		$config_fields = $jshopConfig->getListFieldsRegisterType('editaccount');
        $count_filed_delivery = $jshopConfig->getEnableDeliveryFiledRegistration('editaccount');

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayEditUser', array(&$adv_user));
        
        filterHTMLSafe( $adv_user, ENT_QUOTES);        
        
		if ($config_fields['birthday']['display'] || $config_fields['d_birthday']['display']){
            JHTML::_('behavior.calendar');
        }
		
        $view = $this->getView('user');
        $view->setLayout("editaccount");        
		$view->assign('config',$jshopConfig);
        $view->assign('select_countries',$select_countries);
        $view->assign('select_d_countries',$select_d_countries);
        $view->assign('select_titles',$select_titles);
        $view->assign('select_d_titles',$select_d_titles);
        $view->assign('select_client_types', $select_client_types);
        $view->assign('live_path', JURI::base());
        $view->assign('user', $adv_user);
        $view->assign('delivery_adress', $adv_user->delivery_adress);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=user&task=accountsave',0,0,$jshopConfig->use_ssl));
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $dispatcher->trigger('onBeforeDisplayEditAccountView', array(&$view));
        $view->display();
    }
    
    function accountsave(){
        checkUserLogin();
		$post = $this->input->post->getArray();
        $jshopConfig = JSFactory::getConfig();
		JFactory::getLanguage()->load('com_users');
		$model = JSFactory::getModel('useredit', 'jshop');
		
		$error_back_url = SEFLink("index.php?option=com_jshopping&controller=user&task=editaccount",1,1, $jshopConfig->use_ssl);
		
		JDispatcher::getInstance()->trigger('onBeforeAccountSave', array(&$post));
		
		$model->setUserId(JFactory::getUser()->id);
		$model->setData($post);
		if (!$model->check("editaccount")){
            JError::raiseWarning('', $model->getError());
            $this->setRedirect($error_back_url);
            return 0;
        }
		if (!$model->save()){
            JError::raiseWarning('500', _JSHOP_REGWARN_ERROR_DATABASE);
            $this->setRedirect($error_back_url);
            return 0;
        }
		$model->updateJoomlaUserCurrentProfile();
        
        setNextUpdatePrices();
        JDispatcher::getInstance()->trigger('onAfterAccountSave', array(&$model));
                
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=user&task=myaccount",0,1,$jshopConfig->use_ssl), _JSHOP_ACCOUNT_UPDATE);
    }
    
    function orders(){
        $jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        checkUserLogin();
		$model = JSFactory::getModel('userOrders', 'jshop');
        
        JshopHelpersMetadata::userOrders();
		
		$model->setUserId(JFactory::getUser()->id);
		$orders = $model->getListOrders();
		$total = $model->getTotal();

        $dispatcher->trigger('onBeforeDisplayListOrder', array(&$orders, &$model));

        $view = $this->getView('order');
        $view->setLayout("listorder");
        $view->assign('orders', $orders);
        $view->assign('image_path', $jshopConfig->live_path."images");
        $view->assign('total', $total);
        $dispatcher->trigger('onBeforeDisplayOrdersView', array(&$view));
        $view->display();
    }
    
    function order(){
        $jshopConfig = JSFactory::getConfig();
        checkUserLogin();
        $user = JFactory::getUser();
        $dispatcher = JDispatcher::getInstance();
        
        $order_id = $this->input->getInt('order_id');
		
        $order = JSFactory::getTable('order', 'jshop');
        $order->load($order_id);
        $dispatcher->trigger('onAfterLoadOrder', array(&$order, &$user));
		
		JshopHelpersMetadata::userOrder($order);
        
        if ($user->id!=$order->user_id){
            JError::raiseError(500, "Error order number. You are not the owner of this order");
        }
		
		$order->prepareOrderPrint('order_show');
		$allow_cancel = $order->getClientAllowCancel();        
        $show_percent_tax = $order->getShowPercentTax();
        $hide_subtotal = $order->getHideSubtotal();
        $text_total = $order->getTextTotal();
		$order->fixConfigShowWeightOrder();        
		$config_fields = $jshopConfig->getListFieldsRegisterType('address');
        $count_filed_delivery = $jshopConfig->getEnableDeliveryFiledRegistration('address');
        $order->loadItemsNewDigitalProducts();

        $dispatcher->trigger('onBeforeDisplayOrder', array(&$order));

        $view = $this->getView('order');
        $view->setLayout("order");
        $view->assign('order', $order);
        $view->assign('config', $jshopConfig);
        $view->assign('text_total', $text_total);
        $view->assign('show_percent_tax', $show_percent_tax);
        $view->assign('hide_subtotal', $hide_subtotal);
        $view->assign('image_path', $jshopConfig->live_path."images");
        $view->assign('config_fields', $config_fields);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('allow_cancel', $allow_cancel);
        $dispatcher->trigger('onBeforeDisplayOrderView', array(&$view));
        $view->display();
    }
    
    function cancelorder(){
        $jshopConfig = JSFactory::getConfig();
        checkUserLogin();
		$order_id = $this->input->getInt('order_id');
		$back_url = SEFLink("index.php?option=com_jshopping&controller=user&task=order&order_id=".$order_id,0,1,$jshopConfig->use_ssl);
		
		$model = JSFactory::getModel('userOrder', 'jshop');
		$model->setUserId(JFactory::getUser()->id);
		$model->setOrderId($order_id);
		if (!$model->userOrderCancel()){
			JError::raiseWarning('', $model->getError());
			$this->setRedirect($back_url);
			return 0;
		}
		
		$this->setRedirect($back_url, _JSHOP_ORDER_CANCELED);
    }

    function myaccount(){
        $jshopConfig = JSFactory::getConfig();
        checkUserLogin();

        $adv_user = JSFactory::getUserShop();
		$adv_user->prepareUserPrint();

        JshopHelpersMetadata::userMyaccount();
        
		$config_fields = $jshopConfig->getListFieldsRegisterType('editaccount');

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayMyAccount', array(&$adv_user, &$config_fields));
		
        $view = $this->getView('user');
        $view->setLayout("myaccount");
        $view->assign('config', $jshopConfig);
        $view->assign('user', $adv_user);
        $view->assign('config_fields', $config_fields);
        $view->assign('href_user_group_info', SEFLink('index.php?option=com_jshopping&controller=user&task=groupsinfo'));
        $view->assign('href_edit_data', SEFLink('index.php?option=com_jshopping&controller=user&task=editaccount',0,0,$jshopConfig->use_ssl));
        $view->assign('href_show_orders', SEFLink('index.php?option=com_jshopping&controller=user&task=orders',0,0,$jshopConfig->use_ssl));
        $view->assign('href_logout', SEFLink('index.php?option=com_jshopping&controller=user&task=logout'));
        $dispatcher->trigger('onBeforeDisplayMyAccountView', array(&$view));
        $view->display();
    }
    
    function groupsinfo(){
        $jshopConfig = JSFactory::getConfig();
        JshopHelpersMetadata::userGroupsinfo();
        
        $group = JSFactory::getTable('userGroup', 'jshop');
        $list = $group->getList();

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayGroupsInfo', array());

        $view = $this->getView('user');
        $view->setLayout("groupsinfo");
        $view->assign('rows', $list);
        $dispatcher->trigger('onBeforeDisplayGroupsInfoView', array(&$view));
        $view->display();
    }
    
    function logout(){
		$model = JSFactory::getModel('userlogin', 'jshop');
		$model->logout();
		setNextUpdatePrices();
		JFactory::getApplication()->redirect($model->getReturnUrl());
    }
	
	function logoutpage(){        
        $checkout_navigator = JSFactory::getModel('checkout', 'jshop')->showCheckoutNavigation('1');

		$view = $this->getView('user');
		$view->setLayout("logout");
		$view->assign('checkout_navigator', $checkout_navigator);            
		$view->display();
	}
    
}
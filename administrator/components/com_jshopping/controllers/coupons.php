<?php
/**
* @version      4.15.0 22.12.2012
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerCoupons extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'coupon_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("coupons");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.coupons";
        $limit = $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int' );
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "C.coupon_code", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        $text_search = $mainframe->getUserStateFromRequest( $context.'text_search', 'text_search', '' );
        
        $jshopConfig = JSFactory::getConfig();
        $coupons = JSFactory::getModel("coupons");
        $total = $coupons->getCountCoupons($text_search);
        
        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);        
        $rows = $coupons->getAllCoupons($pageNav->limitstart, $pageNav->limit, $filter_order, $filter_order_Dir, $text_search);
        
        $currency = JSFactory::getTable('currency', 'jshop');
        $currency->load($jshopConfig->mainCurrency);
                        
		$view = $this->getView("coupons", 'html');
        $view->setLayout("list");		
        $view->assign('rows', $rows);        
        $view->assign('currency', $currency->currency_code);
        $view->assign('pageNav', $pageNav);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->assign('text_search', $text_search);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayCoupons', array(&$view));		
		$view->displayList(); 
    }
    
    function edit() {
        $coupon_id = $this->input->getInt('coupon_id');
        $coupon = JSFactory::getTable('coupon', 'jshop'); 
        $coupon->load($coupon_id);
        $edit = $coupon_id ? 1 : 0;
        
        if (!$coupon_id){
          $coupon->coupon_type = 0;  
          $coupon->finished_after_used = 1;
          $coupon->for_user_id = 0;
        }
        if (datenull($coupon->coupon_start_date)){
            $coupon->coupon_start_date = '';
        }
        if (datenull($coupon->coupon_expire_date)){
            $coupon->coupon_expire_date = '';
        }
        $currency_code = getMainCurrencyCode();
        $lists['coupon_type'] = JHTML::_('select.radiolist', JshopHelpersSelectOptions::getCouponType(), 'coupon_type', 'onchange="changeCouponType()"', 'id', 'name', $coupon->coupon_type);
        $lists['tax'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getTaxs(), 'tax_id', 'class = "inputbox"', 'tax_id', 'tax_name', $coupon->tax_id);        
        
        $view = $this->getView("coupons", 'html');
        $view->setLayout("edit");        
        $view->assign('coupon', $coupon);        
        $view->assign('lists', $lists);        
        $view->assign('edit', $edit);
        $view->assign('currency_code', $currency_code);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditCoupons', array(&$view));
        $view->displayEdit();
    }
        
}
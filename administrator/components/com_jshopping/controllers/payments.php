<?php
/**
* @version      4.14.0 22.10.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();
include_once(JPATH_COMPONENT_SITE."/payments/payment.php");

class JshoppingControllerPayments extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'payment_id';

    function __construct($config = array()){
        parent::__construct( $config );
        checkAccessController("payments");
        addSubmenu("other");
    }
	
    function display($cachable = false, $urlparams = false) {
        $jshopConfig = JSFactory::getConfig();
        $payments = JSFactory::getModel("payments");
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.payments";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "payment_ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
        $rows = $payments->getAllPaymentMethods(0, $filter_order, $filter_order_Dir);
        $view = $this->getView("payments", 'html');
        $view->setLayout("list");
	    $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->assign('config', $jshopConfig);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayPayments', array(&$view));
        $view->displayList();
    }
	
    function edit(){
        $jshopConfig = JSFactory::getConfig();
        $payment_id = $this->input->getInt("payment_id");
        $payment = JSFactory::getTable('paymentMethod', 'jshop');
        $payment->load($payment_id);
        $parseString = new parseString($payment->payment_params);
        $params = $parseString->parseStringToParams();
        $edit = ($payment_id)?($edit = 1):($edit = 0);
                
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
		
        if ($edit){
            $paymentsysdata = $payment->getPaymentSystemData();
            if ($paymentsysdata->paymentSystem){
                ob_start();                
                $paymentsysdata->paymentSystem->showAdminFormParams($params);
                $lists['html'] = ob_get_contents();
                ob_get_clean();
            }else{
                $lists['html'] = '';
            }
		} else {
			$lists['html'] = '';
        }
        
        if ($jshopConfig->tax){
            $lists['tax'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getTaxs(0, 0, array('product_tax_rate'=>1)), 'tax_id', 'class = "inputbox"','tax_id','tax_name', $payment->tax_id);
        }

        $lists['price_type'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPaymentPriceTypes(), 'price_type', 'class = "inputbox"', 'id', 'name', $payment->price_type);

        if ($jshopConfig->shop_mode==0 && $payment_id){
            $disabled = 'disabled';
        }else{
            $disabled = '';
        }
        $lists['type_payment'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPaymentType(), 'payment_type','class = "inputbox" '.$disabled, 'id','name', $payment->payment_type);
        
        $nofilter = array();
        JFilterOutput::objectHTMLSafe($payment, ENT_QUOTES, $nofilter);
        
        $view = $this->getView("payments", 'html');
        $view->setLayout("edit");
        $view->assign('payment', $payment);
        $view->assign('edit', $edit);
        $view->assign('params', $params);
        $view->assign('lists', $lists);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('config', $jshopConfig);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditPayments', array(&$view));
        $view->displayEdit();
    }
	   
}
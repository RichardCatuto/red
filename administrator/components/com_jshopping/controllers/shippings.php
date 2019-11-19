<?php
/**
* @version      4.14.0 24.07.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerShippings extends JshoppingControllerBaseadmin{
    
    protected $urlEditParamId = 'shipping_id';

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("shippings");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.shippings";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');

		$shippings = JSFactory::getModel("shippings");
		$rows = $shippings->getAllShippings(0, $filter_order, $filter_order_Dir);

        $not_set_price = array();
        $rowsprices = $shippings->getAllShippingPrices(0);
        $shippings_prices = array();
        foreach($rowsprices as $row){
            $shippings_prices[$row->shipping_method_id][] = $row;
        }
        foreach($rows as $k=>$v){
            if (is_array($shippings_prices[$v->shipping_id])){
                $rows[$k]->count_shipping_price = count($shippings_prices[$v->shipping_id]);
            }else{
				$not_set_price[] = '<a href="index.php?option=com_jshopping&controller=shippingsprices&task=edit&shipping_id_back='.$rows[$k]->shipping_id.'">'.$rows[$k]->name.'</a>';
                $rows[$k]->count_shipping_price = 0;
            }
        }

        if ($not_set_price){
            JError::raiseNotice("", _JSHOP_CERTAIN_METHODS_DELIVERY_NOT_SET_PRICE.' ('.implode(', ',$not_set_price).')!');
        }

		$view = $this->getView("shippings", 'html');
        $view->setLayout("list");
		$view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayShippings', array(&$view));
		$view->displayList();
	}

	function edit(){
		$jshopConfig = JSFactory::getConfig();
		$shipping_id = $this->input->getInt("shipping_id");
		$shipping = JSFactory::getTable('shippingMethod', 'jshop');
		$shipping->load($shipping_id);
		$edit = ($shipping_id)?($edit = 1):($edit = 0);
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
		$params = $shipping->getParams();

        $active_payments = $shipping->getPayments();
        if (!count($active_payments)){
            $active_payments = array(0);
        }

        $lists['payments'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPayments(_JSHOP_ALL), 'listpayments[]', 'class="inputbox" size="10" multiple = "multiple"', 'payment_id', 'name', $active_payments);

        $nofilter = array();
        JFilterOutput::objectHTMLSafe($shipping, ENT_QUOTES, $nofilter);

		$view = $this->getView("shippings", 'html');
        $view->setLayout("edit");
		$view->assign('params', $params);
		$view->assign('shipping', $shipping);
		$view->assign('edit', $edit);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('lists', $lists);
		$view->assign('config', $jshopConfig);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditShippings', array(&$view));
		$view->displayEdit();
	}

    function ext_price_calc(){
        $this->setRedirect("index.php?option=com_jshopping&controller=shippingextprice");
    }

}
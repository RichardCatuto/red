<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerShippingsPrices extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("shippingsprices");
        addSubmenu("other");
    }
    
    public function getUrlListItems(){
        $shipping_id_back = $this->input->getInt("shipping_id_back");
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&shipping_id_back=".$shipping_id_back;
    }
    
    public function getUrlEditItem($id){
        return $this->getUrlListItems()."&task=edit&sh_pr_method_id=".$id;
    }
    
    function display($cachable = false, $urlparams = false){
		$db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $jshopConfig = JSFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.shippingsprices";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "shipping_price.sh_pr_method_id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');

        $shipping_id_back = $this->input->getInt("shipping_id_back");
        $shippings = JSFactory::getModel("shippings");
        $rows = $shippings->getAllShippingPrices(0, $shipping_id_back, $filter_order, $filter_order_Dir, 1);
        $currency = JSFactory::getTable('currency', 'jshop');
        $currency->load($jshopConfig->mainCurrency);

		$view = $this->getView("shippingsprices", 'html');
        $view->setLayout("list");
		$view->assign('rows', $rows);
        $view->assign('currency', $currency);
        $view->assign('shipping_id_back', $shipping_id_back);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayShippngsPrices', array(&$view));
		$view->displayList();
	}

    function edit(){
        $jshopConfig = JSFactory::getConfig();
        $sh_pr_method_id = $this->input->getInt('sh_pr_method_id');
        $shipping_id_back = $this->input->getInt("shipping_id_back");
        $sh_method_price = JSFactory::getTable('shippingMethodPrice', 'jshop');
        $sh_method_price->load($sh_pr_method_id);
        $sh_method_price->prices = $sh_method_price->getPrices();
        if ($jshopConfig->tax){
            $list_tax = JshopHelpersSelectOptions::getTaxs(0, 0, array('product_tax_rate'=>1));
            $lists['taxes'] = JHTML::_('select.genericlist', $list_tax, 'shipping_tax_id','class="inputbox"','tax_id','tax_name',$sh_method_price->shipping_tax_id);
            $lists['package_taxes'] = JHTML::_('select.genericlist', $list_tax, 'package_tax_id','class="inputbox"','tax_id','tax_name',$sh_method_price->package_tax_id);
        }
        $actived = $sh_method_price->shipping_method_id;
        if (!$actived) $actived = $shipping_id_back;
		$lists['shipping_methods'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getShippings(0),'shipping_method_id','class = "inputbox"','shipping_id','name', $actived);
		$lists['countries'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(0), 'shipping_countries_id[]','class = "inputbox" size = "10", multiple = "multiple"','country_id','name', $sh_method_price->getCountries());
        if ($jshopConfig->admin_show_delivery_time){
            $lists['deliverytimes'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getDeliveryTimes(), 'delivery_times_id','class = "inputbox"','id','name', $sh_method_price->delivery_times_id);
        }

        $currency = JSFactory::getTable('currency', 'jshop');
        $currency->load($jshopConfig->mainCurrency);

        $extensions = JSFactory::getShippingExtList($actived);

		$view = $this->getView("shippingsprices", 'html');
        $view->setLayout("edit");
		$view->assign('sh_method_price', $sh_method_price);
		$view->assign('lists', $lists);
        $view->assign('shipping_id_back', $shipping_id_back);
        $view->assign('currency', $currency);
        $view->assign('extensions', $extensions);
        $view->assign('config', $jshopConfig);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditShippingsPrices', array(&$view));
        $view->displayEdit();
    }

    function back(){
        $this->setRedirect('index.php?option=com_jshopping&controller=shippings');
    }

}
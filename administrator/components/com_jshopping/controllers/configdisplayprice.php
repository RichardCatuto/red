<?php
/**
* @version      4.14.0 31.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerConfigDisplayPrice extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("configdisplayprice");
        addSubmenu("config");
    }
    
    function display($cachable = false, $urlparams = false){
        $model = JSFactory::getModel("configDisplayPrice");
        $rows = $model->getList(1);
        $typedisplay = $model->getPriceType();
        
        $view = $this->getView("config_display_price", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('typedisplay', $typedisplay);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayConfigDisplayPrice', array(&$view)); 
        $view->displayList();
    }
    
    function edit() {        
        $id = $this->input->getInt("id");
        
        $configdisplayprice = JSFactory::getTable('configDisplayPrice', 'jshop');
        $configdisplayprice->load($id);
        
        $list_c = $configdisplayprice->getZones();
        $zone_countries = array();        
        foreach($list_c as $v){
            $obj = new stdClass();
            $obj->country_id = $v;
            $zone_countries[] = $obj;
        }
        
        $display_price_list = JshopHelpersSelectOptions::getPriceType();        
        $lists['display_price'] = JHTML::_('select.genericlist', $display_price_list, 'display_price', '', 'id', 'name', $configdisplayprice->display_price);
        $lists['display_price_firma'] = JHTML::_('select.genericlist', $display_price_list, 'display_price_firma', '', 'id', 'name', $configdisplayprice->display_price_firma);
        $lists['countries'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(0), 'countries_id[]', 'size = "10", multiple = "multiple"', 'country_id', 'name', $zone_countries);
        
        JFilterOutput::objectHTMLSafe($configdisplayprice, ENT_QUOTES);

        $view = $this->getView("config_display_price", 'html');
        $view->setLayout("edit");
        $view->assign('row', $configdisplayprice);
        $view->assign('lists', $lists);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigDisplayPrice', array(&$view));
        $view->displayEdit();
    }
    
    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=config&task=general");
    }
    
}
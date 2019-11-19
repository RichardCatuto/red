<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined( '_JEXEC' ) or die();

class JshoppingControllerExtTaxes extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("exttaxes");
        addSubmenu("other");
    }
    
    public function getUrlListItems(){
        $back_tax_id = $this->input->getInt("back_tax_id");
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&back_tax_id=".$back_tax_id;
    }
    
    public function getUrlEditItem($id){
        $back_tax_id = $this->input->getInt("back_tax_id");
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&task=edit&id=".$id."&back_tax_id=".$back_tax_id;
    }

    function display($cachable = false, $urlparams = false){
        $jshopConfig = JSFactory::getConfig();
        $back_tax_id = $this->input->getInt("back_tax_id");
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.exttaxes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ET.id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
        $taxes = JSFactory::getModel("taxes");
        $rows = $taxes->getExtTaxes($back_tax_id, $filter_order, $filter_order_Dir);
        
        $countries = JSFactory::getModel("countries");
        $list = $countries->getAllCountries(0);
        $countries_name = array();
        foreach($list as $v){
            $countries_name[$v->country_id] = $v->name;
        }

        foreach($rows as $k=>$v){
            $list = unserialize($v->zones);

            foreach($list as $k2=>$v2){
                $list[$k2] = $countries_name[$v2];
            }
            if (count($list) > 10){
                $tmp = array_slice($list, 0, 10);
                $rows[$k]->countries = implode(", ", $tmp)."...";
            }else{
                $rows[$k]->countries = implode(", ", $list);
            }
        }

        $view = $this->getView("taxes_ext", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows); 
        $view->assign('back_tax_id', $back_tax_id);
        $view->assign('config', $jshopConfig);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforedisplayExtTax', array(&$view)); 
        $view->displayList();
    }

    function edit(){
        $jshopConfig = JSFactory::getConfig();
        $back_tax_id = $this->input->getInt("back_tax_id");
        $id = $this->input->getInt("id");
        
        $tax = JSFactory::getTable('taxExt', 'jshop');
        $tax->load($id);
        
        if (!$tax->tax_id && $back_tax_id){
            $tax->tax_id = $back_tax_id;
        }

        $list_c = $tax->getZones();
        $zone_countries = array();
        foreach($list_c as $v){
            $obj = new stdClass();
            $obj->country_id = $v;
            $zone_countries[] = $obj;
        }

        $lists['taxes'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getTaxs(), 'tax_id', '', 'tax_id', 'tax_name', $tax->tax_id);
        $lists['countries'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(0), 'countries_id[]', 'size = "10", multiple = "multiple"', 'country_id', 'name', $zone_countries);        

        $view = $this->getView("taxes_ext", 'html');
        $view->setLayout("edit");
        JFilterOutput::objectHTMLSafe($tax, ENT_QUOTES);
        $view->assign('tax', $tax);
        $view->assign('back_tax_id', $back_tax_id);
        $view->assign('lists', $lists);
        $view->assign('config', $jshopConfig);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditExtTax', array(&$view));
        $view->displayEdit();
    }

    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=taxes");
    }

}
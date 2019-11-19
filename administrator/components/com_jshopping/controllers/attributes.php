<?php
/**
* @version      4.14.0 24.07.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerAttributes extends JshoppingControllerBaseadmin{
    
    protected $nameModel = 'attribut';
    protected $urlEditParamId = 'attr_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("attributes");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.attributes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "A.attr_ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
    	$attributes = JSFactory::getModel("attribut");
    	$attributesvalue = JSFactory::getModel("attributValue");
        $rows = $attributes->getAllAttributes(0, null, $filter_order, $filter_order_Dir);
        foreach($rows as $key => $value){
            $rows[$key]->values = splitValuesArrayObject($attributesvalue->getAllValues($rows[$key]->attr_id), 'name');
            $rows[$key]->count_values = count($attributesvalue->getAllValues($rows[$key]->attr_id));
        }        
        $view = $this->getView("attributes", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayAttributes', array(&$view));
        $view->displayList();
    }

    function edit(){
        $attr_id = $this->input->getInt("attr_id");
        $attribut = JSFactory::getTable('attribut', 'jshop');
        $attribut->load($attr_id);
        if (!$attribut->independent){
            $attribut->independent = 0;
        }
        if (!isset($attribut->allcats)){
            $attribut->allcats = 1;
        }
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;

        $type_attribut = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getAttributeType(), 'attr_type','class = "inputbox"','attr_type_id','attr_type',$attribut->attr_type);
        $dependent_attribut = JHTML::_('select.radiolist', JshopHelpersSelectOptions::getAttributeDependent(), 'independent','class = "inputbox"','id','name', $attribut->independent);
        $lists['allcats'] = JHTML::_('select.radiolist', JshopHelpersSelectOptions::getAttributeShowCategory(), 'allcats','onclick="PFShowHideSelectCats()"','id','value', $attribut->allcats);
        $categories_selected = $attribut->getCategorys();
        $lists['categories'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCategories(0), 'category_id[]','class="inputbox" size="10" multiple = "multiple"','category_id','name', $categories_selected);
        $lists['group'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getAttributeGroups(),'group','class="inputbox"','id','name', $attribut->group);
        
        JFilterOutput::objectHTMLSafe($attribut, ENT_QUOTES);
	    
        $view = $this->getView("attributes", 'html');
        $view->setLayout("edit");
        $view->assign('attribut', $attribut);
        $view->assign('type_attribut', $type_attribut);
        $view->assign('dependent_attribut', $dependent_attribut);
        $view->assign('etemplatevar', '');    
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('lists', $lists);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditAtribut', array(&$view, &$attribut));
        $view->displayEdit();		
    }
    
    function addgroup(){
        $this->setRedirect("index.php?option=com_jshopping&controller=attributesgroups");
    }

}
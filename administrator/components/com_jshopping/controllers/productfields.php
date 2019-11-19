<?php
/**
* @version      4.16.3 20.06.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerProductFields extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("productfields");
        addSubmenu("other");
    }
    
    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.productfields";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "F.ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        $group = $mainframe->getUserStateFromRequest($context.'group', 'group', 0, 'int');
        $category_id = $mainframe->getUserStateFromRequest($context.'category_id', 'category_id', 0, 'int');
        $text_search = $mainframe->getUserStateFromRequest($context.'text_search', 'text_search', '');
        
        $filter = array("group"=>$group, "text_search"=>$text_search, 'category_id'=>$category_id);
        
        $_productfields = JSFactory::getModel("productFields");
		$rows = $_productfields->getList(0, $filter_order, $filter_order_Dir, $filter, 1);
        
        $_productfieldvalues = JSFactory::getModel("productFieldValues");
        $vals = $_productfieldvalues->getAllList(2);
    
        foreach($rows as $k=>$v){
            if (isset($vals[$v->id])){
                if (is_array($vals[$v->id])){
                    $rows[$k]->count_option = count($vals[$v->id]);
                }else{
                    $rows[$k]->count_option = 0;
                }
            }else{
                $rows[$k]->count_option = 0;
            }    
        }
		$lists = array();
        $lists['group'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getProductFieldGroups(), 'group', 'class="chosen-select" onchange="document.adminForm.submit();"', 'id', 'name', $group);
        $lists['treecategories'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCategories(), 'category_id', 'class="chosen-select" onchange="document.adminForm.submit();"', 'category_id', 'name', $category_id);
        $types = array(_JSHOP_LIST, _JSHOP_TEXT);

        $view = $this->getView("product_fields", 'html');
        $view->setLayout("list");
		$view->assign('lists', $lists);
        $view->assign('rows', $rows);
        $view->assign('vals', $vals);
        $view->assign('types', $types);
		$view->assign('text_search', $text_search);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayProductField', array(&$view));
        $view->displayList();
    }
    
    function edit(){        
        $id = $this->input->getInt("id");
        $productfield = JSFactory::getTable('productField', 'jshop');
        $productfield->load($id);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        $categories_selected = $productfield->getCategorys();
        $categories = JshopHelpersSelectOptions::getCategories(0);
        if (!isset($productfield->type)) $productfield->type = 0;
        if ($productfield->multilist) $productfield->type = -1;
        if (!isset($productfield->allcats)) $productfield->allcats = 1;
        
        $lists['allcats'] = JHTML::_('select.radiolist', JshopHelpersSelectOptions::getProductFieldShowCategory(), 'allcats', 'onclick="PFShowHideSelectCats()"', 'id', 'value', $productfield->allcats);
        $lists['categories'] = JHTML::_('select.genericlist', $categories, 'category_id[]', 'class="inputbox" size="10" multiple = "multiple"', 'category_id', 'name', $categories_selected);
        $lists['type'] = JHTML::_('select.radiolist', JshopHelpersSelectOptions::getProductFieldTypes(), 'type', '', 'id', 'value', $productfield->type);
        $lists['group'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getProductFieldGroups('- - -'), 'group', 'class="inputbox"', 'id', 'name', $productfield->group);
        
        JFilterOutput::objectHTMLSafe($productfield, ENT_QUOTES);
        
        $view = $this->getView("product_fields", 'html');
        $view->setLayout("edit");
        $view->assign('row', $productfield);
        $view->assign('lists', $lists);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditProductFields', array(&$view));
        $view->displayEdit();
    }
    
    function addgroup(){
        $this->setRedirect("index.php?option=com_jshopping&controller=productfieldgroups");
    }
    
    function cancel(){
        $this->setRedirect("index.php?option=com_jshopping&controller=productfields");
    }
    
}
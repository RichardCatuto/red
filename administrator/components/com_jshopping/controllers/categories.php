<?php
/**
* @version      4.14.4 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerCategories extends JshoppingControllerBaseadmin{
    
    protected $modelSaveItemFileName = 'category_image';
    protected $urlEditParamId = 'category_id';
    protected $checkToken = array('save' => 1, 'remove' => 0);
    
    function __construct($config = array()){
        parent::__construct( $config );
        checkAccessController("categories");
        addSubmenu("categories");
    }
    
    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        
        $dispatcher = JDispatcher::getInstance();
        $_categories = JSFactory::getModel("categories");
        
        $context = "jshopping.list.admin.category";
        $limit = $mainframe->getUserStateFromRequest($context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int' );
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
		$text_search = $mainframe->getUserStateFromRequest($context.'text_search', 'text_search', '');
		
		$filter = array("text_search" => $text_search);
		
        $categories = $_categories->getTreeAllCategories($filter, $filter_order, $filter_order_Dir);
        $total = count($categories);

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);
        
        $countproducts = $_categories->getAllCatCountProducts();
        $categories = array_slice($categories, $pagination->limitstart, $pagination->limit);
        
        $view = $this->getView("category", 'html');
        $view->setLayout("list");
        $view->assign('categories', $categories);
        $view->assign('countproducts', $countproducts);
        $view->assign('pagination', $pagination);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
		$view->assign('text_search', $text_search);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher->trigger('onBeforeDisplayListCategoryView', array(&$view));
        $view->displayList();
    }
    
    function edit(){
        $cid = $this->input->getInt("category_id");
        $category = JSFactory::getTable("category","jshop");
        $category->load($cid);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;

        JFilterOutput::objectHTMLSafe($category, ENT_QUOTES);

        if ($cid){
            $parentid = $category->category_parent_id;
            $rows = JSFactory::getModel("categories")->_getAllCategoriesLevel($category->category_parent_id, $category->ordering);
        } else {
            $category->category_publish = 1;
            $parentid = $this->input->getInt("catid");
            $rows = JSFactory::getModel("categories")->_getAllCategoriesLevel($parentid);
        }
        
        $categories = JshopHelpersSelectOptions::getCategories(_JSHOP_TOP_LEVEL);
        $lists['templates'] = getTemplates('category', $category->category_template);
        $lists['onelevel'] = $rows;
        $lists['treecategories'] = JHTML::_('select.genericlist', $categories, 'category_parent_id','class="inputbox" onchange = "changeCategory()"','category_id','name', $parentid);
        $lists['parentid'] = $parentid;
        $lists['access'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getAccessGroups(), 'access','class = "inputbox"','id','title', $category->access);

        $view = $this->getView("category", 'html');
        $view->setLayout("edit");
        $view->assign('category', $category);
        $view->assign('lists', $lists);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditCategories', array(&$view));
        $view->displayEdit();
    }
    
    protected function getMessageSaveOk($post){
        return $post['category_id'] ? _JSHOP_CATEGORY_SUCC_UPDATE : _JSHOP_CATEGORY_SUCC_ADDED;
    }
    
    function sorting_cats_html(){
        $catid = $this->input->getVar('catid');
        print JSFactory::getModel("categories")->_getAllCategoriesLevel($catid);
    die();    
    }
    
    function delete_foto(){
        $catid = $this->input->getInt("catid");
        $this->getAdminModel()->deleteFoto($catid);
        die();
    }
    
    protected function getSaveOrderWhere(){
        $category_parent_id = $this->input->getInt("category_parent_id");
        return 'category_parent_id='.(int)$category_parent_id;
    }
}
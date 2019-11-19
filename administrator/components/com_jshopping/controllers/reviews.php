<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingControllerReviews extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("reviews");
        addSubmenu("other");
    }

    public function getUrlEditItem($id){
        return "index.php?option=com_jshopping&controller=".$this->getNameController()."&task=edit&cid[]=".$id;
    }
    
    function display($cachable = false, $urlparams = false){
        $jshopConfig = JSFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $id_vendor_cuser = getIdVendorForCUser();
        $reviews_model = JSFactory::getModel("reviews");
        $context = "jshoping.list.admin.reviews";
        $limit = $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0, 'int' );
        $category_id = $mainframe->getUserStateFromRequest( $context.'category_id', 'category_id', 0, 'int' );
        $text_search = $mainframe->getUserStateFromRequest( $context.'text_search', 'text_search', '');
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "pr_rew.review_id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "desc", 'cmd');

        if ($category_id){
            $product_id = $mainframe->getUserStateFromRequest( $context.'product_id', 'product_id', 0, 'int' );
        } else {
            $product_id = null;
        }

        $products_select = "";

        if ($category_id){
            $prod_filter = array("category_id"=>$category_id);
            if ($id_vendor_cuser){
                $prod_filter['vendor_id'] = $id_vendor_cuser;
            }
            $products = JshopHelpersSelectOptions::getProducts(1, 0, array('filter'=>$prod_filter, 'limitstart'=>0, 'limit'=>100));
            $products_select = JHTML::_('select.genericlist', $products, 'product_id', 'class="chosen-select" onchange="document.adminForm.submit();" ', 'product_id', 'name', $product_id);
        }

        $total = $reviews_model->getAllReviews($category_id, $product_id, NULL, NULL, $text_search, "count", $id_vendor_cuser, $filter_order, $filter_order_Dir);

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);

        $reviews = $reviews_model->getAllReviews($category_id, $product_id, $pagination->limitstart, $pagination->limit, $text_search, "list", $id_vendor_cuser, $filter_order, $filter_order_Dir);

        $categories = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCategories(_JSHOP_SELECT_CATEGORY), 'category_id', 'class="chosen-select" onchange="document.adminForm.submit();"', 'category_id', 'name', $category_id);

        $view = $this->getView("comments", 'html');
        $view->setLayout("list");
        $view->assign('categories', $categories);
        $view->assign('reviews', $reviews);
        $view->assign('limit', $limit);
        $view->assign('limitstart', $limitstart);
        $view->assign('text_search', $text_search);
        $view->assign('pagination', $pagination);
        $view->assign('products_select', $products_select);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->assign('config', $jshopConfig);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayReviews', array(&$view));
        $view->displayList();
     }

    function edit(){
        $jshopConfig = JSFactory::getConfig();
        $reviews_model = JSFactory::getModel("reviews");
        $cid = $this->input->getVar('cid');
        $review = $reviews_model->getReview($cid[0]);
        $mark = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getReviewMarks(), 'mark', 'class = "inputbox"', 'value', 'text', $review->mark);
        JFilterOutput::objectHTMLSafe($review, ENT_QUOTES);

        $view = $this->getView("comments", 'html');
        $view->setLayout("edit");
        if ($this->getTask()=='edit'){
            $view->assign('edit', 1);
        }
        $view->assign('review', $review);
        $view->assign('mark', $mark);
        $view->assign('config', $jshopConfig);
        $view->assign('etemplatevar', '');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditReviews', array(&$view));
        $view->displayEdit();
    }

}
<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelReviews extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'review';

     function getAllReviews($category_id = null, $product_id = null, $limitstart = null, $limit = null, $text_search = null, $result = "list", $vendor_id = 0, $order = null, $orderDir = null) {

        $lang = JSFactory::getLang();
        $db = JFactory::getDBO();
        $where = "";
        if ($product_id) $where .= " AND pr_rew.product_id='".$db->escape($product_id)."' ";
        if ($vendor_id) $where .= " AND pr.vendor_id='".$db->escape($vendor_id)."' ";

        if($limit > 0) {
            $limit = " LIMIT " . $limitstart . " , " . $limit;
        }
        $where .= ($text_search) ? ( " AND CONCAT_WS('|',pr.`".$lang->get('name')."`,pr.`".$lang->get('short_description')."`,pr.`".$lang->get('description')."`,pr_rew.review, pr_rew.user_name, pr_rew.user_email ) LIKE '%".$db->escape($text_search)."%' " ) : ('');
        $ordering = 'pr_rew.review_id desc';

        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }

        if($category_id) {
            $query = "select pr.`".$lang->get('name')."` as name,pr_rew.* , DATE_FORMAT(pr_rew.`time`,'%d.%m.%Y') as dateadd
            from  #__jshopping_products_reviews as pr_rew
            LEFT JOIN #__jshopping_products  as pr USING (product_id)
            LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)
            WHERE pr_cat.category_id = '" . $db->escape($category_id) . "' ".$where." ORDER BY ". $ordering ." ". $limit;
        }else {
            $query = "select pr.`".$lang->get('name')."` as name,pr_rew.*, DATE_FORMAT(pr_rew.`time`,'%d.%m.%Y') as dateadd
            from  #__jshopping_products_reviews as pr_rew
            LEFT JOIN #__jshopping_products  as pr USING (product_id)
            WHERE 1 ".$where." ORDER BY ". $ordering ." ". $limit;
        }
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        if ($result=="list"){
            return $db->loadObjectList();
        }else{
            $db->query();
            return $db->getNumRows();
        }
    }

    function getReview($id){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "select pr_rew.*, pr.`".$lang->get('name')."` as name from #__jshopping_products_reviews as pr_rew LEFT JOIN #__jshopping_products  as pr USING (product_id)  where pr_rew.review_id = '$id'";
        $db->setQuery($query);
        return $db->loadObject();
    }

    function getProdNameById($id){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "select pr.`".$lang->get('name')."` as name from #__jshopping_products  as pr where pr.product_id = '$id' LIMIT 1";
        $db->setQuery($query);
        return $db->loadResult();
    }

    function deleteReview($id){
        $db = JFactory::getDBO();
        $query = "delete from #__jshopping_products_reviews where review_id = '$id'";
        $db->setQuery($query);
        return $db->query();
    }

    public function save(array $post){
        $review = JSFactory::getTable('review', 'jshop');
        if( intval($post['review_id']) == 0 ) {
            $post['time'] = getJsDate();
        }
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveReview', array(&$post));
        if( !$post['product_id'] ) {
            $this->setError(_JSHOP_ERROR_DATA);
            return 0;
        }
        $review->bind($post);
        if( !$review->store() ) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $product = JSFactory::getTable('product', 'jshop');
        $product->load($review->product_id);
        $product->loadAverageRating();
        $product->loadReviewsCount();
        $product->store();
        $dispatcher->trigger('onAfterSaveReview', array(&$review));
        return $review;
    }

    public function deleteList(array $cid, $msg = 1){
        $model = JSFactory::getModel("reviews");
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveReview', array(&$cid));
        foreach($cid as $value) {
            $review = JSFactory::getTable('review', 'jshop');
            $review->load($value);
            $model->deleteReview($value);
            $product = JSFactory::getTable('product', 'jshop');
            $product->load($review->product_id);
            $product->loadAverageRating();
            $product->loadReviewsCount();
            $product->store();
            unset($product);
            unset($review);
        }
        $dispatcher->trigger('onAfterRemoveReview', array(&$cid));
    }
    
    public function publish(array $cid, $flag){
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishReview', array(&$cid, &$flag));
        foreach($cid as $value){
            $query = "UPDATE `#__jshopping_products_reviews` SET `publish` = '".$db->escape($flag)."' "
                . "WHERE `review_id` = '".$db->escape($value)."'";
            $db->setQuery($query);
            $db->query();

            $review = JSFactory::getTable('review', 'jshop');
            $review->load($value);
            $product = JSFactory::getTable('product', 'jshop');
            $product->load($review->product_id);
            $product->loadAverageRating();
            $product->loadReviewsCount();
            $product->store();
            unset($product);
            unset($review);
        }
        $dispatcher->trigger('onAfterPublishReview', array(&$cid, &$flag));
    }

}
<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopReview extends JTable {

    function __construct( &$_db ){
        parent::__construct('#__jshopping_products_reviews', 'review_id', $_db );
    }
    
    function getAllowReview(){
        $jshopConfig = JSFactory::getConfig();
        $user = JFactory::getUser();
		$res = 1;		
        if (!$jshopConfig->allow_reviews_prod){
            $res = 0;            
        }
        if ($res==1 && $jshopConfig->allow_reviews_only_registered && !$user->id){
            $res = -1;
        }		
		extract(js_add_trigger(get_defined_vars(), "after"));
        return $res;
    }

    function getText(){
		$allow_review = $this->getAllowReview();		
        if ($allow_review == -1){
            $res = _JSHOP_REVIEW_NOT_LOGGED;
        } else {
            $res = '';
        }
		extract(js_add_trigger(get_defined_vars(), "after"));
		return $res;
    }
	
	function check(){
        $db = JFactory::getDBO();
		$res = 1;
        if (!$this->product_id){
            $res = 0;
        }
        if ($this->user_name==''){
            $res = 0;
        }
        if ($this->user_email==''){
            $res = 0;
        }
        if ($this->review==''){
            $res = 0;
        }        
        $query = "SELECT product_id FROM #__jshopping_products WHERE product_id=".intval($this->product_id);
        $db->setQuery($query);
        $pid = intval($db->loadResult());
        if (!$pid){
            $res = 0;
        }
		extract(js_add_trigger(get_defined_vars(), "after"));
        return $res;
    }

}
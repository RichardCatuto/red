<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopProductReview extends jshopBase{
    
	protected $review;
	protected $data = array();
	
	public function __construct(){
		$this->review = JSFactory::getTable('review', 'jshop');
	}
	
	public function checkAllow(){
		if ($this->review->getAllowReview() <= 0){
			$this->setError($this->review->getText());
			return 0;
		}else{
			return 1;
		}
	}
	
	public function setData($data, $load_data_config = 1){
		$jshopConfig = JSFactory::getConfig();
		$this->data = $data;
		$review = $this->review;
		$review->bind($data);
		if ($load_data_config){
			$review->user_id = JFactory::getUser()->id;
			$review->time = getJsDate();
			$review->ip = $_SERVER['REMOTE_ADDR'];
			if ($jshopConfig->display_reviews_without_confirm){
				$review->publish = 1;    
			}
		}
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function setProductId($pid){
		$this->data['product_id'] = $pid;
	}
	
	public function getProductId(){
		return $this->data['product_id'];
	}
	
	public function check(){
		JDispatcher::getInstance()->trigger('onBeforeSaveReview', array(&$this->review));
		if (!$this->review->check()){
            $this->setError(_JSHOP_ENTER_CORRECT_INFO_REVIEW);
			return 0;
		}else{
			return 1;
		}
	}
	
	public function save(){
		$this->review->store();
		$product_id = $this->getProductId();
		
        JDispatcher::getInstance()->trigger('onAfterSaveReview', array(&$this->review));

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
        $product->loadAverageRating();
        $product->loadReviewsCount();
        $product->store();
	}
	
	public function mailSend(){
		$data = array();
		$data['product_id'] = $this->getProductId();
		$data['review'] = $this->review;
		
		$mail = JSFactory::getModel('reviewMail', 'jshop');
		$mail->setData($data);
		return $mail->send();
	}

}
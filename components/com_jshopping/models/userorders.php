<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('');

class jshopUserOrders{

	private $user_id = 0 ;
	private $list = array();
	
	public function setUserId($id){
		$this->user_id = $id;
	}
	
	public function getUserId(){
		return $this->user_id;
	}
	
	public function getListOrders(){
		$order = JSFactory::getTable('order', 'jshop');
		$this->list = $order->getOrdersForUser($this->user_id);
		$this->loadOrderLink();
		return $this->list;
	}
	
	public function getTotal(){
		$total = 0;
        foreach($this->list as $key=>$value){
            $total += $value->order_total / $value->currency_exchange;
        }
		return $total;
	}
	
	private function loadOrderLink(){
		$jshopConfig = JSFactory::getConfig();
		foreach($this->list as $key=>$value){
            $this->list[$key]->order_href = SEFLink('index.php?option=com_jshopping&controller=user&task=order&order_id='.$value->order_id,0,0,$jshopConfig->use_ssl);
        }
	}
	
	
}
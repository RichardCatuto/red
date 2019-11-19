<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelPayments extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'paymentMethod';
    protected $tableFieldOrdering = 'payment_ordering';
    
    function getAllPaymentMethods($publish = 1, $order = null, $orderDir = null) {
        $database = JFactory::getDBO(); 
        $query_where = ($publish)?("WHERE payment_publish = '1'"):("");
        $lang = JSFactory::getLang();
        $ordering = 'payment_ordering';
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $query = "SELECT payment_id, `".$lang->get("name")."` as name, `".$lang->get("description")."` as description , payment_code, payment_class, scriptname, payment_publish, payment_ordering, payment_params, payment_type FROM `#__jshopping_payment_method`
                  $query_where
                  ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $database->setQuery($query);
        return $database->loadObjectList();
    }
    
    function getTypes(){
    	return array('1' => _JSHOP_TYPE_DEFAULT,'2' => _JSHOP_PAYPAL_RELATED);
    }
    
    function getMaxOrdering(){
        $db = JFactory::getDBO();
        $query = "select max(payment_ordering) from `#__jshopping_payment_method`";
        $db->setQuery($query);
        return $db->loadResult();
    }
    
	function getListNamePaymens($publish = 1){
        $_list = $this->getAllPaymentMethods($publish);
        $list = array();
        foreach($_list as $v){
            $list[$v->payment_id] = $v->name;
        }
        return $list;
    }
    
    public function getPrepareDataSave($input){
        $post = $input->post->getArray();
        if (!isset($post['payment_publish'])){
            $post['payment_publish'] = 0;
        }
        if (!isset($post['show_descr_in_email'])){
            $post['show_descr_in_email'] = 0;
        }
        $post['price'] = saveAsPrice($post['price']);
        $post['payment_class'] = $input->getCmd("payment_class");
        if (!$post['payment_id'] && !$post['payment_type']){
            $post['payment_type'] = 1;
        }        
        $languages = JSFactory::getModel("languages")->getAllLanguages(1);
        foreach($languages as $lang){
            $post['description_'.$lang->language] = $input->get('description'.$lang->id, '', 'RAW');
        }
        return $post;
    }
    
    public function save(array $post){
        $dispatcher = JDispatcher::getInstance();
        $payment = JSFactory::getTable('paymentMethod', 'jshop');
        $dispatcher->trigger('onBeforeSavePayment', array(&$post));
		$payment->bind($post);
        if (!$payment->payment_id){
            $payment->payment_ordering = $this->getMaxOrdering() + 1;
        }
		if (isset($post['pm_params'])) {
			$parseString = new parseString($post['pm_params']);
			$payment->payment_params = $parseString->splitParamsToString();
		}
        if (!$payment->check()){
            $this->setError($payment->getError());            
            return 0;
        }
		$payment->store();
        $dispatcher->trigger('onAfterSavePayment', array(&$payment));        
        return $payment;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemovePayment', array(&$cid));
		foreach($cid as $id){
            $this->delete($id);
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
		}
        $dispatcher->trigger('onAfterRemovePayment', array(&$cid));
    }
    
    public function delete($id){
        $db = JFactory::getDBO();
        $query = "DELETE FROM `#__jshopping_payment_method` WHERE `payment_id`=".(int)$id;
        $db->setQuery($query);
        return $db->query();
    }
    
    public function publish(array $cid, $flag){
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishPayment', array(&$cid, &$flag));
		foreach($cid as $value){
			$query = "UPDATE `#__jshopping_payment_method`
					   SET `payment_publish` = '" . $db->escape($flag) . "'
					   WHERE `payment_id` = '" . $db->escape($value) . "'";
			$db->setQuery($query);
			$db->query();
		}        
        $dispatcher->trigger('onAfterPublishPayment', array(&$cid, &$flag));
    }
    
}
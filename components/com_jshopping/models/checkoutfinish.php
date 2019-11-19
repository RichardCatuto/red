<?php
/**
* @version      4.11.0 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(dirname(__FILE__)."/checkout.php");

class jshopCheckoutFinish extends jshopCheckout{

	public function getFinishStaticText(){
		$statictext = JSFactory::getTable("statictext","jshop");
        $rowstatictext = $statictext->loadData("order_finish_descr");
        $text = $rowstatictext->text;
		if (trim(strip_tags($text))==""){
            $text = '';
        }
		return $text;
	}
	
	public function paymentComplete($order_id, $text = ''){
		$order = JSFactory::getTable('order', 'jshop');
		$order->load($order_id);
		$pm_method = $order->getPayment();
		$paymentsysdata = $pm_method->getPaymentSystemData();
		$payment_system = $paymentsysdata->paymentSystem;
		if ($payment_system){
			$pmconfigs = $pm_method->getConfigs();
			$payment_system->complete($pmconfigs, $order, $pm_method);
		}
		JDispatcher::getInstance()->trigger('onAfterDisplayCheckoutFinish', array(&$text, &$order, &$pm_method));
	}
	
	public function clearAllDataCheckout(){
		extract(js_add_trigger(get_defined_vars(), "before"));
		$cart = JSFactory::getModel('cart', 'jshop');
        $cart->load();
        $cart->getSum();
        $cart->clear();
        $this->deleteSession();
		extract(js_add_trigger(get_defined_vars(), "after"));
	}
	
}
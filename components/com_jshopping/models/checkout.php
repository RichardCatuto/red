<?php
/**
* @version      4.17.2 26.06.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopCheckout extends jshopBase{
    
	protected $cart = null;
	
    function __construct(){
        JPluginHelper::importPlugin('jshoppingorder');
        JDispatcher::getInstance()->trigger('onConstructJshopCheckout', array(&$this));
    }
	
	function setCart($cart){
		$this->cart = $cart;
	}
	
	function getCart(){		
		return $this->cart;
	}
    
    function sendOrderEmail($order_id, $manuallysend = 0){
		$model = JSFactory::getModel('orderMail', 'jshop');
		$model->setData($order_id, $manuallysend);
		return $model->send();
    }
    
    function changeStatusOrder($order_id, $status, $sendmessage = 1){
		$model = JSFactory::getModel('orderChangeStatus', 'jshop');
		$model->setData($order_id, $status, $sendmessage);
		return $model->store();
    }
    
    function cancelPayOrder($order_id){
        $order = JSFactory::getTable('order', 'jshop');
        $order->load($order_id);
        $pm_method = JSFactory::getTable('paymentMethod', 'jshop');
        $pm_method->load($order->payment_method_id);
        $pmconfigs = $pm_method->getConfigs();
        $status = $pmconfigs['transaction_cancel_status'];
        if (!$status){
			$status = $pmconfigs['transaction_failed_status'];
		}
        if ($order->order_created) 
			$sendmessage = 1; 
		else 
			$sendmessage = 0;
        $this->changeStatusOrder($order_id, $status, $sendmessage);
        JDispatcher::getInstance()->trigger('onAfterCancelPayOrderJshopCheckout', array(&$order_id, $status, $sendmessage));
    }
    
    function setMaxStep($step){
        $session = JFactory::getSession();
        $jhop_max_step = $session->get('jhop_max_step');
        if (!isset($jhop_max_step)) $session->set('jhop_max_step', 2);
        $jhop_max_step = $session->get('jhop_max_step');
        $session->set('jhop_max_step', $step);
        JDispatcher::getInstance()->trigger('onAfterSetMaxStepJshopCheckout', array(&$step));
    }
    
    function checkStep($step){
        $mainframe = JFactory::getApplication();
        $jshopConfig = JSFactory::getConfig();
        $session = JFactory::getSession();
        
        if ($step<10){
            if (!$jshopConfig->shop_user_guest){
                checkUserLogin();
            }
            
            $cart = JSFactory::getModel('cart', 'jshop');
            $cart->load();

            if ($cart->getCountProduct() == 0){
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1,1));
                exit();
            }

            if ($jshopConfig->min_price_order && ($cart->getPriceProducts() < ($jshopConfig->min_price_order * $jshopConfig->currency_value) )){
                JError::raiseNotice("", sprintf(_JSHOP_ERROR_MIN_SUM_ORDER, formatprice($jshopConfig->min_price_order * $jshopConfig->currency_value)));
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1,1));
                exit();
            }
            
            if ($jshopConfig->max_price_order && ($cart->getPriceProducts() > ($jshopConfig->max_price_order * $jshopConfig->currency_value) )){
                JError::raiseNotice("", sprintf(_JSHOP_ERROR_MAX_SUM_ORDER, formatprice($jshopConfig->max_price_order * $jshopConfig->currency_value)));
                $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1,1));
                exit();
            }
        }

        if ($step>2){
            $jhop_max_step = $session->get("jhop_max_step");
            if (!$jhop_max_step){
                $session->set('jhop_max_step', 2);
                $jhop_max_step = 2;
            }
            if ($step > $jhop_max_step){
                if ($step==10){
                    $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1,1));
                }else{
                    JError::raiseWarning("", _JHOP_ERROR_STEP);
                    $mainframe->redirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step2',1,1, $jshopConfig->use_ssl));
                }
                exit();
            }
        }
    }
    
    function showCheckoutNavigation($step){
        $jshopConfig = JSFactory::getConfig();
        if (!$jshopConfig->ext_menu_checkout_step && in_array($step, array('0', '1'))){
            return '';
        }
        if ($jshopConfig->step_4_3){
            $array_navigation_steps = array('0'=>_JSHOP_CART, '1'=>_JSHOP_LOGIN, '2'=>_JSHOP_STEP_ORDER_2, '4'=>_JSHOP_STEP_ORDER_4, '3'=>_JSHOP_STEP_ORDER_3, '5'=>_JSHOP_STEP_ORDER_5);
        }else{
            $array_navigation_steps = array('0'=>_JSHOP_CART, '1'=>_JSHOP_LOGIN, '2' => _JSHOP_STEP_ORDER_2, '3' => _JSHOP_STEP_ORDER_3, '4' => _JSHOP_STEP_ORDER_4, '5' => _JSHOP_STEP_ORDER_5);
        }
        $output = array();
        $cssclass = array();
        if (!$jshopConfig->ext_menu_checkout_step){
            unset($array_navigation_steps['0']);
            unset($array_navigation_steps['1']);
        }
        if ($jshopConfig->shop_user_guest==2){
            unset($array_navigation_steps['1']);    
        }
        if ($jshopConfig->without_shipping || $jshopConfig->hide_shipping_step){
            unset($array_navigation_steps['4']);
        }
        if ($jshopConfig->without_payment || $jshopConfig->hide_payment_step){
            unset($array_navigation_steps['3']);
        }

        foreach($array_navigation_steps as $key=>$value){
            if ($key=='0'){
                $url = SEFLink('index.php?option=com_jshopping&controller=cart', 1, 0);
            }elseif($key=='1'){
                $url = SEFLink('index.php?option=com_jshopping&controller=user&task=login', 1, 0, $jshopConfig->use_ssl);
            }else{
                $url = SEFLink('index.php?option=com_jshopping&controller=checkout&task=step'.$key,0,0,$jshopConfig->use_ssl);
            }
            if ($key < $step && !($jshopConfig->step_4_3 && $key==3 && $step==4) || ($jshopConfig->step_4_3 && $key==4 && $step==3)){
                $output[$key] = '<span class="not_active_step"><a href="'.$url.'">'.$value.'</a></span>';
                $cssclass[$key] = "prev";
            }else{
                if ($key == $step){
                    $output[$key] = '<span id="active_step"  class="active_step">'.$value.'</span>';
                    $cssclass[$key] = "active";
                }else{
                    $output[$key] = '<span class="not_active_step">'.$value.'</span>';
                    $cssclass[$key] = "next";
                }
            }
        }

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayCheckoutNavigator', array(&$output, &$array_navigation_steps, &$step));
        
        $view = $this->getView('checkout');
        $view->setLayout("menu");
        $view->assign('steps', $output);
        $view->assign('step', $step);
        $view->assign('cssclass', $cssclass);
        $view->assign('array_navigation_steps', $array_navigation_steps);
        $dispatcher->trigger('onAfterDisplayCheckoutNavigator', array(&$view));
    return $view->loadTemplate();
    }
    
	function loadSmallCart($step = 0){
		$jshopConfig = JSFactory::getConfig();
		if ($jshopConfig->show_cart_all_step_checkout || $step==5){
            $small_cart = $this->showSmallCart($step);
        }else{
            $small_cart = '';
        }
		return $small_cart;
	}
	
    function showSmallCart($step = 0){
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
		
        $cart = JSFactory::getModel('cart', 'jshop')->init('cart', 0);
		
		$cartpreview = JSFactory::getModel('cartPreview', 'jshop');
		$cartpreview->setCart($cart);
		$cartpreview->setCheckoutStep($step);
		$price_items_show = $cartpreview->getPriceItemsShow();
		$deliverytimes = JSFactory::getAllDeliveryTime();

		$payment_name = $cartpreview->getCartPaymentName();
		$tax_list = $cartpreview->getTaxExt();
		$fullsumm = $cartpreview->getFullSum();
		$show_percent_tax = $cartpreview->getShowPercentTax();
        $hide_subtotal = $cartpreview->getHideSubtotal();		
		$text_total = $cartpreview->getTextTotalPrice();		
                
        $view = $this->getView('cart');
        $view->setLayout("checkout");
        $view->assign('step', $step);
        $view->assign('config', $jshopConfig);
        $view->assign('products', $cart->products);
        $view->assign('summ', $cartpreview->getSubTotal());
        $view->assign('image_product_path', $jshopConfig->image_product_live_path);
        $view->assign('no_image', $jshopConfig->noimage);
        $view->assign('discount', $cartpreview->getDiscount());
        $view->assign('free_discount', $cartpreview->getFreeDiscount(1));
        $view->assign('deliverytimes', $deliverytimes);
        $view->assign('payment_name', $payment_name);
		if ($price_items_show['payment_price']){
			$view->assign('summ_payment', $cart->getPaymentPrice());
		}
		if ($price_items_show['shipping_price']){
			$view->assign('summ_delivery', $cart->getShippingPrice());
		}
		if ($price_items_show['shipping_package_price']){
			$view->assign('summ_package', $cart->getPackagePrice());
		}
        $view->assign('tax_list', $tax_list);
        $view->assign('fullsumm', $fullsumm);
        $view->assign('show_percent_tax', $show_percent_tax);
        $view->assign('hide_subtotal', $hide_subtotal);
        $view->assign('text_total', $text_total);
        $view->assign('weight', $cartpreview->getWeight());
        $dispatcher->trigger('onBeforeDisplayCheckoutCartView', array(&$view));
    return $view->loadTemplate();
    }
    
	function removeWishlistItemToCart($number_id){
		$dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeLoadWishlistRemoveToCart', array(&$number_id));
        
        $cart = JSFactory::getModel('cart', 'jshop');
        $cart->load("wishlist");
        $prod = $cart->products[$number_id];
        $attr = unserialize($prod['attributes']);
        $freeattribut = unserialize($prod['freeattributes']);
        $cart->delete($number_id);
                        
        $cart = JSFactory::getModel('cart', 'jshop');
        $cart->load("cart");        
        $cart->add($prod['product_id'], $prod['quantity'], $attr, $freeattribut);
        $dispatcher->trigger('onAfterWishlistRemoveToCart', array(&$cart));
		return $cart;
	}
	
    function deleteSession(){
        $session = JFactory::getSession();
        $session->set('check_params', null);
        $session->set('cart', null);
        $session->set('jhop_max_step', null);        
        $session->set('jshop_price_shipping_tax_percent', null);
        $session->set('jshop_price_shipping', null);
        $session->set('jshop_price_shipping_tax', null);
        $session->set('pm_params', null);
        $session->set('payment_method_id', null);
        $session->set('jshop_payment_price', null);
        $session->set('shipping_method_id', null);
        $session->set('sh_pr_method_id', null);
        $session->set('jshop_price_shipping_tax_percent', null);                
        $session->set('jshop_end_order_id', null);
        $session->set('jshop_send_end_form', null);
        $session->set('show_pay_without_reg', 0);
        $session->set('checkcoupon', 0);
        JDispatcher::getInstance()->trigger('onAfterDeleteDataOrder', array(&$this));
    }
    
	function setEmptyCheckoutPrices(){
		$cart = $this->getCart();
		$cart->setShippingId(0);
		$cart->setShippingPrId(0);
		$cart->setShippingPrice(0);
		$cart->setPaymentId(0);
		$cart->setPaymentParams("");
		$cart->setPaymentPrice(0);
	}
	
    function getNoReturn(){
		$jshopConfig = JSFactory::getConfig();
		$cart = $this->getCart();
        $no_return = 0;
        if ($jshopConfig->return_policy_for_product){
            $cart_products = array();
            foreach($cart->products as $products){
                $cart_products[] = $products['product_id'];
            }
            $cart_products = array_unique($cart_products);
            $_product_option = JSFactory::getTable('productOption', 'jshop');
            $list_no_return = $_product_option->getProductOptionList($cart_products, 'no_return');
            $no_return = intval(in_array('1', $list_no_return));
        }
        if ($jshopConfig->no_return_all){
            $no_return = 1;
        }
        return $no_return;
    }
	
	function getInvoiceInfo($adv_user){
		$lang = JSFactory::getLang();
		$field_name = $lang->get("name");
		$info = array();
        $country = JSFactory::getTable('country', 'jshop');
        $country->load($adv_user->country);
        $info['f_name'] = $adv_user->f_name;
        $info['l_name'] = $adv_user->l_name;
        $info['firma_name'] = $adv_user->firma_name;
        $info['street'] = $adv_user->street;
        $info['street_nr'] = $adv_user->street_nr;
        $info['zip'] = $adv_user->zip;
        $info['state'] = $adv_user->state;
        $info['city'] = $adv_user->city;
        $info['country'] = $country->$field_name;
        $info['home'] = $adv_user->home;
        $info['apartment'] = $adv_user->apartment;
	return $info;
	}
	
	function getDeliveryInfo($adv_user, $invoice_info){
		$lang = JSFactory::getLang();
		$field_name = $lang->get("name");
		if ($adv_user->delivery_adress){
			$info = array();
            $country = JSFactory::getTable('country', 'jshop');
            $country->load($adv_user->d_country);
			$info['f_name'] = $adv_user->d_f_name;
            $info['l_name'] = $adv_user->d_l_name;
			$info['firma_name'] = $adv_user->d_firma_name;
			$info['street'] = $adv_user->d_street;
            $info['street_nr'] = $adv_user->d_street_nr;
			$info['zip'] = $adv_user->d_zip;
			$info['state'] = $adv_user->d_state;
            $info['city'] = $adv_user->d_city;
			$info['country'] = $country->$field_name;
            $info['home'] = $adv_user->d_home;
            $info['apartment'] = $adv_user->d_apartment;
		} else {
            $info = $invoice_info;
		}
	return $info;
	}
	
	function getDeliveryDateShow(){
		$cart = $this->getCart();
		$jshopConfig = JSFactory::getConfig();
		if ($jshopConfig->show_delivery_date){
            $date = $cart->getDeliveryDate();
            if ($date){
                $date = formatdate($date);
            }
        }else{
            $date = '';
        }
	return $date;
	}
	
	function getDeliveryTime(){
		$cart = $this->getCart();
		$jshopConfig = JSFactory::getConfig();
		$sh_mt_pr = $this->getShippingMethodPrice();
        if ($jshopConfig->show_delivery_time_checkout){
            $deliverytimes = JSFactory::getAllDeliveryTime();
            $deliverytimes[0] = '';
            $delivery_time = $deliverytimes[$sh_mt_pr->delivery_times_id];
            if (!$delivery_time && $jshopConfig->delivery_order_depends_delivery_product){
                $delivery_time = $cart->getDelivery();
            }
        }else{
            $delivery_time = '';
        }
	return $delivery_time;
	}
	
	function getShippingMethod(){
		$cart = $this->getCart();
		$sh_method = JSFactory::getTable('shippingMethod', 'jshop');
        $id = $cart->getShippingId();
        $sh_method->load($id);
	return $sh_method;
	}
	
	function getShippingMethodPrice(){
		$cart = $this->getCart();
		$sh_mt_pr = JSFactory::getTable('shippingMethodPrice', 'jshop');
        $sh_mt_pr->load($cart->getShippingPrId());
	return $sh_mt_pr;
	}
	
	function getPaymentMethod(){
		$cart = $this->getCart();
		$pm_method = JSFactory::getTable('paymentMethod', 'jshop');
        $id = $cart->getPaymentId();
		$pm_method->load($id);
	return $pm_method;
	}
	
	function setEndOrderId($id){
		JFactory::getSession()->set("jshop_end_order_id", $id);
	}
	
	function getEndOrderId(){
		return JFactory::getSession()->get("jshop_end_order_id");
	}
	
	function setSendEndForm($val){
		JFactory::getSession()->set("jshop_send_end_form", $val);
	}
	
	function getSendEndForm(){
		return JFactory::getSession()->get("jshop_send_end_form");
	}
	
}
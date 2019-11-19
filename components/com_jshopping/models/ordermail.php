<?php
/**
* @version      4.11.2 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopOrderMail extends jshopBase{
	
	private $order_id;
	private $manuallysend;
	private $order;
	private $show_percent_tax;
	private $hide_subtotal;
	private $order_email_descr;
	private $order_email_descr_end;
	private $text_total;	
	private $file_generete_pdf_order; 
	private $config_fields;
	private $count_filed_delivery;
	private $listVendors;
	private $vendors_send_message;
	private $vendor_send_order_admin;
	private $vendor_send_order;
	private $admin_send_order;
	
	public function setData($order_id, $manuallysend = 0){
		$this->order_id = $order_id;
		$this->manuallysend = $manuallysend;
		$this->loadOrderData();
	}
	
	public function getOrderId(){
		return $this->order_id;
	}
	
	public function getManuallysend(){
		return $this->manuallysend;
	}
	
	public function getMessage($type, $products = null, $show_customer_info = 1, $show_weight_order = 1, $show_total_info = 1, $show_payment_shipping_info = 1){
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        $liveurlhost = JURI::getInstance()->toString(array("scheme",'host', 'port'));
		
		if ($type=='client'){
			$client = 1;
		}else{
			$client = 0;
		}
		if (is_null($products)){
			$products = $this->order->products;
		}
		
		$view = $this->getView('checkout');

        $view->setLayout("orderemail");
        $view->assign('client', $client);
        $view->assign('show_customer_info', $show_customer_info);
        $view->assign('show_weight_order', $show_weight_order);
        $view->assign('show_total_info', $show_total_info);
        $view->assign('show_payment_shipping_info', $show_payment_shipping_info);
        $view->assign('config_fields', $this->config_fields);
        $view->assign('count_filed_delivery', $this->count_filed_delivery);
        $view->assign('order_email_descr', $this->order_email_descr);
        $view->assign('order_email_descr_end', $this->order_email_descr_end);
        $view->assign('config', $jshopConfig);
        $view->assign('order', $this->order);
        $view->assign('products', $products);
        $view->assign('show_percent_tax', $this->show_percent_tax);
        $view->assign('hide_subtotal', $this->hide_subtotal);
        $view->assign('noimage', $jshopConfig->noimage);
        $view->assign('text_total',$this->text_total);
        $view->assign('liveurlhost',$liveurlhost);
		if ($type=='vendor'){
			$dispatcher->trigger('onBeforeCreateTemplateOrderPartMail', array(&$view));
		}else{
			$dispatcher->trigger('onBeforeCreateTemplateOrderMail', array(&$view));
		}
        return $view->loadTemplate();
	}
	
	public function send(){
        $jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
        
        $dispatcher->trigger('onBeforeSendEmailsOrder', 
			array(&$this->order, &$this->listVendors, &$this->file_generete_pdf_order, &$this->admin_send_order, &$this));
        
		$message_client = $this->getMessage('client');
		$message_admin = $this->getMessage('admin');
		$this->loadMessageForListVendors();

        if ($this->getGeneretePdf()){
            $this->order->generatePdf($this->file_generete_pdf_order);
        }
		
		$send = array();
        
        //send mail client
		if ($this->order->email){
			$send['client'] = $this->sendMail('client', $this->order->email, $message_client);
        }
		
        //send mail admin
        if ($this->admin_send_order){
			$send['admin'] = $this->sendMail('admin', $jshopConfig->getAdminContactEmails(), $message_admin);
        }

        //send message mail vendors
        if ($this->vendors_send_message || $this->vendor_send_order){
            foreach($this->listVendors as $k=>$vendor){				
				$send['vendormessage:'.$vendor->id] = $this->sendMail('vendormessage', $vendor->email, $vendor->message, $vendor);
            }
        }

        //send order vendors
        if ($this->vendor_send_order_admin){
            foreach($this->listVendors as $k=>$vendor){				
				$send['vendor:'.$vendor->id] = $this->sendMail('vendor', $vendor->email, $message_admin, $vendor);
            }
        }

        $dispatcher->trigger('onAfterSendEmailsOrder', array(&$this->order, &$this, &$send));
		return $send;
	}
	
	public function getSubjectMail($type, $order){
		if ($type=='vendormessage'){
			$subject = sprintf(_JSHOP_NEW_ORDER_V, $order->order_number, "");
		}else{
			$subject = sprintf(_JSHOP_NEW_ORDER, $order->order_number, $order->f_name." ".$order->l_name);
		}
		extract(js_add_trigger(get_defined_vars(), "after"));
		return $subject;
	}	
	
	public function sendMail($type, $recipient, $message, $vendor = null){
		$app = JFactory::getApplication();
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
		
		$mailfrom = $app->getCfg('mailfrom');
        $fromname = $app->getCfg('fromname');
		$pdfsend = $this->getPdfSend();
		$manuallysend = $this->getManuallysend();
		$subject = $this->getSubjectMail($type, $this->order);
		$pdfsendtype = $this->getPdfSendType($type);		
		
		$mailer = JFactory::getMailer();
		$mailer->setSender(array($mailfrom, $fromname));
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($message);
		if ($pdfsendtype){
			$mailer->addAttachment($jshopConfig->pdf_orders_path."/".$this->order->pdf_file);
		}
		$mailer->isHTML(true);
		$dispatcher->trigger($this->getSendMailTriggerType($type), 
			array(&$mailer, &$this->order, &$manuallysend, &$pdfsend, &$vendor, &$this->vendors_send_message, &$this->vendor_send_order));
		return $mailer->Send();
	}
	
	protected function loadOrderData(){
		$jshopConfig = JSFactory::getConfig();
		
		$this->order = JSFactory::getTable('order', 'jshop');
        $this->order->load($this->getOrderId());
        $this->order->prepareOrderPrint('', 1);
        $this->show_percent_tax = $this->order->getShowPercentTax();
		$this->hide_subtotal = $this->order->getHideSubtotal();
		$this->order->fixConfigShowWeightOrder();
		$this->order_email_descr = $this->order->getStaticText('order_email_descr');
        if ($this->getManuallysend()){
            $order_email_descr_manually = $this->order->getStaticText('order_email_descr_manually');
            if (trim($order_email_descr_manually)!=''){
                $this->order_email_descr = $order_email_descr_manually;
            }
        }
		$this->order_email_descr_end = $this->order->getStaticText('order_email_descr_end');
		$this->text_total = $this->order->getTextTotal();
		
		$this->file_generete_pdf_order = $jshopConfig->file_generete_pdf_order;        
		$this->config_fields = $jshopConfig->getListFieldsRegisterType('address');
        $this->count_filed_delivery = $jshopConfig->getEnableDeliveryFiledRegistration('address');
		
		$this->listVendors = $this->getListVendors($this->order);
        $this->vendors_send_message = $this->getVendorsSendMessage();
		$this->vendor_send_order_admin = $this->getVendorSendOrderAdmin($this->order);
        $this->vendor_send_order = $this->getVendorSendOrder($this->order);
        $this->admin_send_order = $this->getAdminSendOrder($this->order, $this->listVendors);
	}
	
	protected function loadMessageForListVendors(){
        if ($this->vendors_send_message || $this->vendor_send_order){
            foreach($this->listVendors as $k=>$vendor){
                if ($this->vendors_send_message){
                    $show_customer_info = 0;
                    $show_weight_order = 0;
                    $show_total_info = 0;
                    $show_payment_shipping_info = 0;
                }
                if ($this->vendor_send_order){
                    $show_customer_info = 1;
                    $show_weight_order = 0;
                    $show_total_info = 0;
                    $show_payment_shipping_info = 1;
                }
                $vendor_order_items = $this->order->getVendorItems($vendor->id);
				
                $this->listVendors[$k]->message = $this->getMessage(
					'vendor', $vendor_order_items, $show_customer_info, $show_weight_order, $show_total_info, $show_payment_shipping_info
				);
            }
        }
	}
	
	protected function getPdfSend(){
		$pdfsend = 1;
        if (JSFactory::getConfig()->send_invoice_manually && !$this->getManuallysend()){
			$pdfsend = 0;
		}
		return $pdfsend;
	}
	
	protected function getGeneretePdf(){
		return $this->getPdfSend() && JSFactory::getConfig()->generate_pdf;
	}
	
	protected function getListVendors($order){
		if (JSFactory::getConfig()->admin_show_vendors){
            $listVendors = $order->getVendors();
        }else{
            $listVendors = array();
        }
		return $listVendors;
	}
	
	protected function getVendorsSendMessage(){
		return  JSFactory::getConfig()->vendor_order_message_type==1;
	}
	
	protected function getVendorSendOrderAdmin($order){
		$jshopConfig = JSFactory::getConfig();
		return (($jshopConfig->vendor_order_message_type==2 && $order->vendor_type == 0 && $order->vendor_id) || $jshopConfig->vendor_order_message_type==3);
	}
	
	protected function getVendorSendOrder($order){
		$jshopConfig = JSFactory::getConfig();
		$vendor_send_order = $jshopConfig->vendor_order_message_type==2;        
        if ($this->getVendorSendOrderAdmin($order)){
			$vendor_send_order = 0;
		}
		return $vendor_send_order;
	}
	
	protected function getAdminSendOrder($order, $listVendors){		
		$admin_send_order = 1;		
        if (JSFactory::getConfig()->admin_not_send_email_order_vendor_order && $this->getVendorSendOrderAdmin($order) && count($listVendors)){
			$admin_send_order = 0;
		}
		return $admin_send_order;
	}
	
	protected function getPdfSendType($type){
		$jshopConfig = JSFactory::getConfig();
		$pdfsend = $this->getPdfSend();
		if ($type=='client'){
			$pdfsendtype = $pdfsend && $jshopConfig->order_send_pdf_client;			
		}
		if ($type=='admin'){
			$pdfsendtype = $pdfsend && $jshopConfig->order_send_pdf_admin;			
		}		
		if ($type=='vendormessage'){
			$pdfsendtype = 0;			
		}
		if ($type=='vendor'){
			$pdfsendtype = $pdfsend && $jshopConfig->order_send_pdf_admin;			
		}
		return $pdfsendtype;
	}
	
	protected function getSendMailTriggerType($type){
		if ($type=='client'){			
			$trigger = 'onBeforeSendOrderEmailClient';
		}
		if ($type=='admin'){			
			$trigger = 'onBeforeSendOrderEmailAdmin';
		}		
		if ($type=='vendormessage'){
			$trigger = 'onBeforeSendOrderEmailVendor';
		}
		if ($type=='vendor'){			
			$trigger = 'onBeforeSendOrderEmailVendorOrder';
		}
		return $trigger;
	}
	
}
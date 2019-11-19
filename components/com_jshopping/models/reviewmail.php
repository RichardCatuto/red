<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(dirname(__FILE__)."/mail.php");

class jshopReviewMail extends jshopMail{
	
    public function getSubjectMail(){
		$subject = _JSHOP_NEW_COMMENT;
		extract(js_add_trigger(get_defined_vars(), "before"));
        return $subject;
    }
    
    public function getMessageMail(){
		$product = JSFactory::getTable('product', 'jshop');
        $product->load($this->getProductId());
		$review = $this->getReview();
		
        $view = $this->getView("product");
        $view->setLayout("commentemail");
        $view->assign('product_name', $product->getName());
        $view->assign('user_name', $review->user_name);
        $view->assign('user_email', $review->user_email);
        $view->assign('mark', $review->mark);
        $view->assign('review', $review->review);
		extract(js_add_trigger(get_defined_vars(), "before"));
        return $view->loadTemplate();
    }
    
    public function send(){
		$mainframe =JFactory::getApplication();
        $jshopConfig = JSFactory::getConfig();
		
		$mailfrom = $mainframe->getCfg('mailfrom');
        $fromname = $mainframe->getCfg('fromname');
		
        $mailer = JFactory::getMailer();
        $mailer->setSender(array($mailfrom, $fromname));
        $mailer->addRecipient($jshopConfig->getAdminContactEmails());
        $mailer->setSubject($this->getSubjectMail());
        $mailer->setBody($this->getMessageMail());
        $mailer->isHTML(true);
		extract(js_add_trigger(get_defined_vars(), "before"));
        return $mailer->Send();
    }
	
	protected function getReview(){
		return $this->data['review'];
	}
    
    protected function getProductId(){
		return (int)$this->data['product_id'];
	}
    
}
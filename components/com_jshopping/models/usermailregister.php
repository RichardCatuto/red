<?php
/**
* @version      4.18.0 10.03.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(dirname(__FILE__)."/mail.php");

class jshopUserMailRegister extends jshopMail{
	
    private $registration_request_data;
    
    public function setRegistrationRequestData(&$post){
        $this->registration_request_data = &$post;
    }
    
    public function getSubjectMail(){
        $params = $this->getParams();
        $data = $this->getData();
        $useractivation = $params->get('useractivation');
        if ($useractivation == 2){
            $subject = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );
        }else if ($useractivation == 1){
            $subject = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );
        }else{
            $subject = JText::sprintf(
                'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                $data['name'],
                $data['sitename']
            );
        }		
        return $subject;
    }
    
    public function getMessageMail(){
        $params = $this->getParams();
        $data = $this->getData();
        $useractivation = $params->get('useractivation');
        $sendpassword = $params->get('sendpassword', 1);
        if ($useractivation == 2){
            if ($sendpassword) {
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['linkactivate'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			} else {
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['linkactivate'],
					$data['siteurl'],
					$data['username']
				);
			}
        }else if ($useractivation == 1){
			if ($sendpassword) {
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['linkactivate'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			} else {
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['linkactivate'],
					$data['siteurl'],
					$data['username']
				);
			}
        } else {
            if ($sendpassword){
                $emailBody = JText::sprintf(
                    'COM_USERS_EMAIL_REGISTERED_BODY',
                    $data['name'],
                    $data['sitename'],
                    $data['siteurl'],
                    $data['username'],
                    $data['password_clear']
                );
            }else{
                $emailBody = JText::sprintf(
                    'COM_USERS_EMAIL_REGISTERED_BODY_NOPW',
                    $data['name'],
                    $data['sitename'],
                    $data['siteurl']
                );
            }
        }

        $view = $this->getView('user');
        $view->setLayout("registermail");
        $view->assign('data', $data);
        $view->assign('useractivation', $useractivation);
        $view->assign('sendpassword', $sendpassword);
        $view->assign('emailBody', $emailBody);
        JDispatcher::getInstance()->trigger('onBeforeRegistermailView', array(&$view));
        return $view->loadTemplate();
    }
    
    public function send(){
        $dispatcher = JDispatcher::getInstance();        
        $emailSubject = $this->getSubjectMail();
        $emailBody = $this->getMessageMail();
        $data = $this->getData();
        $dispatcher->trigger('onBeforeRegisterSendMailClient', array(&$this->registration_request_data, &$data, &$emailSubject, &$emailBody));
        
        $mailer = JFactory::getMailer();
        $mailer->setSender(array($data['mailfrom'], $data['fromname']));
        $mailer->addRecipient($data['email']);
        $mailer->setSubject($emailSubject);
        $mailer->setBody($emailBody);
        $mailer->isHTML(JSFactory::getConfig()->register_mail_html_format);
        $dispatcher->trigger('onBeforeRegisterMailerSendMailClient', array(&$mailer, &$this->registration_request_data, &$data, &$emailSubject, &$emailBody));
        $mailer->Send();
    }
    
    public function getSubjectMailAdmin(){
        $data = $this->getData();
        $subject = JText::sprintf(
            'COM_USERS_EMAIL_ACCOUNT_DETAILS',
            $data['name'],
            $data['sitename']
        );
        return $subject;
    }
    
    public function getMessageMailAdmin(){
        $data = $this->getData();
        $emailBody = JText::sprintf(
            'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
            $data['name'],
            $data['username'],
            $data['siteurl']
        );
        $view = $this->getView('user');
        $view->setLayout("registermailadmin");
        $view->assign('data', $data);
        $view->assign('emailBody', $emailBody);
        JDispatcher::getInstance()->trigger('onBeforeRegistermailAdminView', array(&$view));
        return $view->loadTemplate();
    }
    
    public function sendToAdmin(){
        $dispatcher = JDispatcher::getInstance();
        $data = $this->getData();
        $emailSubject = $this->getSubjectMailAdmin();
        $emailBodyAdmin = $this->getMessageMailAdmin();
        $rows = $this->getListAdminUserSendEmail();        
        $mode = JSFactory::getConfig()->register_mail_admin_html_format;
        foreach($rows as $row){
            $dispatcher->trigger('onBeforeRegisterSendMailAdmin', array(&$this->registration_request_data, &$data, &$emailSubject, &$emailBodyAdmin, &$row, &$mode));
            JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin, $mode);
        }
    }
    
}
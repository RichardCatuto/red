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

class jshopUserMailActivation extends jshopMail{
    
    public function getSubjectMail(){
        $data = $this->getData();
        $subject = JText::sprintf(
			'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT',
			$data['name'],
			$data['sitename']
		);
        return $subject;
    }
    
    public function getMessageMail(){
        $data = $this->getData();
        $emailBody = JText::sprintf(
			'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY',
			$data['name'],
			$data['siteurl'],
			$data['username']
		);
        $view = $this->getView('user');
        $view->setLayout("activationmail");
        $view->assign('data', $data);
        $view->assign('emailBody', $emailBody);
        JDispatcher::getInstance()->trigger('onBeforeActivationmailView', array(&$view));
        return $view->loadTemplate();
    }
    
    public function send(){
        $dispatcher = JDispatcher::getInstance();        
        $emailSubject = $this->getSubjectMail();
        $emailBody = $this->getMessageMail();
        $data = $this->getData();
        $mode = JSFactory::getConfig()->activation_mail_html_format;
		$dispatcher->trigger('onBeforeActivationSend', array(&$data, &$emailSubject, &$emailBody, &$mode));
        $return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody, $mode);
		if ($return !== true){
			$this->setError(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));
			return false;
		}
		return true;
    }
    
    public function getSubjectMailAdmin(){
        $data = $this->getData();
        $subject = JText::sprintf(
			'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT',
			$data['name'],
			$data['sitename']
        );
        return $subject;
    }
    
    public function getMessageMailAdmin(){
        $data = $this->getData();
        $emailBody = JText::sprintf(
			'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY',
			$data['sitename'],
			$data['name'],
			$data['email'],
			$data['username'],
			$data['siteurl'].'index.php?option=com_jshopping&controller=user&task=activate&token='.$data['activation']
		);
        $view = $this->getView('user');
        $view->setLayout("activationmailadmin");
        $view->assign('data', $data);
        $view->assign('emailBody', $emailBody);
        JDispatcher::getInstance()->trigger('onBeforeActivationmailAdminView', array(&$view));
        return $view->loadTemplate();
    }
    
    public function getListAdminUserSendEmail(){
        $db = JFactory::getDBO();
        $query = 'SELECT name, email, sendEmail FROM #__users WHERE sendEmail=1';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }
    
    public function sendToAdmin(){
        $dispatcher = JDispatcher::getInstance();
        $data = $this->getData();
        $emailSubject = $this->getSubjectMailAdmin();
        $emailBody = $this->getMessageMailAdmin();
        $rows = $this->getListAdminUserSendEmail();        
        $mode = JSFactory::getConfig()->activation_mail_admin_html_format;
        foreach($rows as $row){
			$dispatcher->trigger('onBeforeActivationSendMailAdmin', array(&$data, &$emailSubject, &$emailBody, &$row, &$mode));
			$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBody, $mode);
			if ($return !== true){
				$this->setError(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));
				return false;
			}
        }
		return true;
    }
    
}
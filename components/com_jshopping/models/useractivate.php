<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
jimport('joomla.user.helper');
include_once(dirname(__FILE__)."/userbase.php");

class jshopUseractivate extends jshopUserBase{
    
    public function __construct(){
		$this->loadUserParams();
        JDispatcher::getInstance()->trigger('onConstructJshopUseractivate', array(&$this));
    }
	
	public function check($token){
		$params = $this->getUserParams();
		if ($params->get('useractivation') == 0 || $params->get('allowUserRegistration') == 0) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
            return 0;
        }
        if ($token === null || strlen($token) !== 32) {
            $this->setError(JText::_('JINVALID_TOKEN'));
            return 0;
        }
		return 1;
	}
	    
	public function activate($token){
        $config = JFactory::getConfig();
        $userParams = $this->getUserParams();
		JPluginHelper::importPlugin('user');

        $userId = $this->getUserId($token);
        
        if (!$userId){
            $this->setError(JText::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));
            return false;
        }

        $user = JFactory::getUser($userId);
		$usermail = JSFactory::getModel('usermailactivation', 'jshop');
		$uri = JURI::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$data = $user->getProperties();
		$data['fromname'] = $config->get('fromname');
		$data['mailfrom'] = $config->get('mailfrom');
		$data['sitename'] = $config->get('sitename');
		$data['siteurl'] = JUri::base();

        // Admin activation is on and user is verifying their email
        if (($userParams->get('useractivation') == 2) && !$user->getParam('activate', 0)){
            $data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
            $data['activate'] = $base.JRoute::_('index.php?option=com_jshopping&controller=user&task=activate&token='.$data['activation'], false);

			$user->set('activation', $data['activation']);
            $user->setParam('activate', 1);
			
			$usermail->setData($data);
			if (!$usermail->sendToAdmin()){
				$this->setError($usermail->getError());
				return false;
			}
        }
		//Admin activation is on and admin is activating the account
		elseif (($userParams->get('useractivation') == 2) && $user->getParam('activate', 0)){
            $user->set('activation', '');
            $user->set('block', '0');
			$user->setParam('activate', 0);

			$usermail->setData($data);
			if (!$usermail->send()){
				$this->setError($usermail->getError());
				return false;
			}
        }else{
            $user->set('activation', '');
            $user->set('block', '0');
        }
        if (!$user->save()) {
            $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
            $user = false;
        }
		JDispatcher::getInstance()->trigger('onAfterUserActivate', array(&$this, &$token, &$user));
        return $user;
    }
	
	public function getMessageUserActivation($user){
		$useractivation = $this->getUserParams()->get('useractivation');

        if ($useractivation == 0){
            $msg = JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS');            
        }elseif ($useractivation == 1){
            $msg = JText::_('COM_USERS_REGISTRATION_ACTIVATE_SUCCESS');            
        }elseif ($user->getParam('activate')){
            $msg = JText::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS');            
        }else{
            $msg = JText::_('COM_USERS_REGISTRATION_ADMINACTIVATE_SUCCESS');            
        }
		return $msg;
	}
	
	private function getUserId($token){
		$db = JFactory::getDBO();
		$db->setQuery(
            'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__users') .
            ' WHERE '.$db->quoteName('activation').' = '.$db->Quote($token) .
            ' AND '.$db->quoteName('block').' = 1' .
            ' AND '.$db->quoteName('lastvisitDate').' = '.$db->Quote($db->getNullDate())
        );
        return (int)$db->loadResult();
	}

}
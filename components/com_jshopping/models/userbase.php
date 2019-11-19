<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

abstract class jshopUserBase extends jshopBase{
	
	protected $userparams = null;
	
	public function getUserParams(){
        return $this->userparams;
    }
	
	protected function loadUserParams(){	
		$this->userparams = JComponentHelper::getParams('com_users');
	}
	
}
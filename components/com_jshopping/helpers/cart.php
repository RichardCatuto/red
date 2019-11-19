<?php
/**
* @version      4.15.1 01.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshopHelpersCart{
		
	public static function checkAdd(){
		$jshopConfig = JSFactory::getConfig();
        $to = JshopHelpersRequest::getCartTo();
		return ((!$jshopConfig->user_as_catalog && getDisplayPriceShop()) || $to=='wishlist');
	}
	
	public static function checkView(){
		return !JSFactory::getConfig()->user_as_catalog;
	}
	
}
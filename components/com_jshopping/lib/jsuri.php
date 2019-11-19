<?php
/**
* @version      4.11.6 24.12.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JSUri extends JUri{

	public static function isInternal($url){
		$uri = static::getInstance($url);
		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));

		if (stripos($base, static::base()) !== 0 && !empty($host)){
			return false;
		}
		return true;
	}

}
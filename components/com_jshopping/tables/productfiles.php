<?php
/**
* @version      4.17.0 10.10.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopProductFiles extends JTable{
    
    function __construct( &$_db ){
        parent::__construct('#__jshopping_products_files', 'id', $_db);
    }
    
    function fileDemoIsVideo(){
        $video = 0;
        $info = pathinfo($this->demo);
        if (in_array($info['extension'], JSFactory::getConfig()->file_extension_video)){
            $video = 1;
        }        
        return $video;
    }
	
	function fileDemoIsAudio(){
        $audio = 0;
        $info = pathinfo($this->demo);
        if (in_array($info['extension'], JSFactory::getConfig()->file_extension_audio)){
            $audio = 1;
        }        
        return $audio;
    }
}
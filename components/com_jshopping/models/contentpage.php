<?php
/**
* @version      4.11.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('');

class jshopContentPage extends jshopBase{
	
	private $seodata;
	
	public function setSeodata($seodata){
		$this->seodata = $seodata;
	}
	
	public function load($page, $order_id = 0, $cartp = 0){
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();		
		$statictext = JSFactory::getTable("statictext","jshop");
        
        if ($jshopConfig->return_policy_for_product && $page=='return_policy' && ($cartp || $order_id)){
            if ($cartp){
                $cart = JSFactory::getModel('cart', 'jshop');
                $cart->load();
                $list = $cart->getReturnPolicy();
            }else{
                $order = JSFactory::getTable('order', 'jshop');
                $order->load($order_id);
                $list = $order->getReturnPolicy();
            }
            $listtext = array();
            foreach($list as $v){
                $listtext[] = $v->text;
            }
            $row = new stdClass();
            $row->id = -1;
            $row->text = implode('<div class="return_policy_space"></div>', $listtext);
        }else{
            $row = $statictext->loadData($page);
        }
                
        if (!$row->id){
			$this->setError(_JSHOP_PAGE_NOT_FOUND);           
            return false;
        }		
		if ($jshopConfig->use_plugin_content){
            $obj = new stdClass();
            $params = JFactory::getApplication()->getParams('com_content');
            $obj->text = $row->text;
            $obj->title = $this->seodata->title;
            $dispatcher->trigger('onContentPrepare', array('com_content.article', &$obj, &$params, 0));
            $row->text = $obj->text;
        }
        $text = $row->text;
        $dispatcher->trigger('onBeforeDisplayContent', array($page, &$text));
		return $text;
	}
	
}
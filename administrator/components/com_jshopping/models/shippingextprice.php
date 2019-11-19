<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelShippingExtPrice extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'shippingExt';
    
    function getList($active = 0){
        $db = JFactory::getDBO();
        $adv_query = "";
        if ($active==1){
            $adv_query = "where `published`='1'";
        }
        $query = "select * from `#__jshopping_shipping_ext_calc` ".$adv_query." order by `ordering`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function save(array $post){
        $shippingext = JSFactory::getTable('shippingExt', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveShippingExtCalc', array(&$post));
        $shippingext->bind($post);
        $shippingext->setShippingMethod($post['shipping']);
        $shippingext->setParams($post['params']);
        if (!$shippingext->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveShippingExtCalc', array(&$shippingext));
        return $shippingext;
    }

    public function delete(&$id) {
        $dispatcher = JDispatcher::getInstance();
        $shippingext = JSFactory::getTable('shippingExt', 'jshop');
        $dispatcher->trigger('onBeforeRemoveShippingExtPrice', array(&$id));
        $shippingext->delete($id);
        $dispatcher->trigger('onAfterRemoveShippingExtPrice', array(&$id));
    }
    
    public function publish(array $cid, $flag){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishShippingExtPrice', array(&$cid, &$flag));
        $obj = JSFactory::getTable('shippingExt', 'jshop');
        $obj->publish($cid, $flag);
        $dispatcher->trigger('onAfterPublishShippingExtPrice', array(&$cid, &$flag));
    }

}
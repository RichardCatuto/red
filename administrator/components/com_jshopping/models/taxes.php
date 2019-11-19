<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelTaxes extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'tax';

    function getAllTaxes($order = null, $orderDir = null) {
        $db = JFactory::getDBO();
        $ordering = 'tax_name';
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $query = "SELECT * FROM `#__jshopping_taxes` ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function getExtTaxes($tax_id = 0, $order = null, $orderDir = null) {
        $db = JFactory::getDBO();
        $where = "";
        if ($tax_id){
            $where = " where ET.tax_id=".(int)$tax_id;
        }
        $ordering = 'ET.id';
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $query = "SELECT ET.*, T.tax_name FROM `#__jshopping_taxes_ext` as ET left join #__jshopping_taxes as T on T.tax_id=ET.tax_id ".$where." ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function getValue($id){
        $db = JFactory::getDBO();
        $query = "select tax_value from #__jshopping_taxes where tax_id=".(int)$id;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }

    function save(array $post){
        $tax = JSFactory::getTable('tax', 'jshop');
        $post['tax_value'] = saveAsPrice($post['tax_value']);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveTax', array(&$tax));
        $tax->bind($post);
        if( !$tax->store() ) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveTax', array(&$tax));
        return $tax;
    }

    function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveTax', array(&$cid));
        foreach($cid as $value) {
            $tax = JSFactory::getTable('tax', 'jshop');
            $tax->load($value);
            $query = "SELECT pr.product_id
                       FROM `#__jshopping_products` AS pr
                       WHERE pr.product_tax_id = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $res = $db->query();
            if ($db->getNumRows($res)){
                continue;
            }
            $query = "DELETE FROM `#__jshopping_taxes` WHERE `tax_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            $query = "DELETE FROM `#__jshopping_taxes_ext` WHERE `tax_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
        }
        $dispatcher->trigger('onAfterRemoveTax', array(&$cid));
    }

}
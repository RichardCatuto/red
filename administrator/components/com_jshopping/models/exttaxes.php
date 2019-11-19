<?php
/**
* @version      4.14.0 26.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelExtTaxes extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'taxExt';
    
    public function getPrepareDataSave($input){
        $post = $input->post->getArray();
        $post['tax'] = saveAsPrice($post['tax']);
        $post['firma_tax'] = saveAsPrice($post['firma_tax']);
        return $post;
    }
    
    public function save(array $post){
        $tax = JSFactory::getTable('taxExt', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveExtTax', array(&$post));        
        $tax->bind($post);
        $tax->setZones($post['countries_id']);
        if (!$tax->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0; 
        }
        updateCountExtTaxRule();        
        $dispatcher->trigger('onAfterSaveExtTax', array(&$tax));
        return $tax;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveExtTax', array(&$cid));
        $res = array();
        foreach($cid as $id){
            $query = "DELETE FROM `#__jshopping_taxes_ext` WHERE `id` = ".(int)$id;
            $db->setQuery($query);
            if ($db->query()){
                $res[$id] = true;
                if ($msg){
                    $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
                }
            }else{
                $res[$id] = false;
            }
        }
        updateCountExtTaxRule();
        $dispatcher->trigger('onAfterRemoveExtTax', array(&$cid));
        return $res;
    }

}
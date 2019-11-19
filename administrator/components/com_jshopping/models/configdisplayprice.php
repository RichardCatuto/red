<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelConfigDisplayPrice extends JshoppingModelBaseadmin{

    public function getList($loadCountiesInfo = 0){
        $db = JFactory::getDBO(); 
        $query = "SELECT * FROM `#__jshopping_config_display_prices`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($loadCountiesInfo){
            $rows = $this->addCountryToListConfigPrices($rows);
        }
        return $rows;
    }
    
    protected function addCountryToListConfigPrices($rows){
        $countries = JSFactory::getModel("countries");
        $list = $countries->getAllCountries(0);    
        $countries_name = array();
        foreach($list as $v){
            $countries_name[$v->country_id] = $v->name;
        }
        foreach($rows as $k=>$v){
            $list = unserialize($v->zones);
            foreach($list as $k2=>$v2){
                $list[$k2] = $countries_name[$v2];
            }
            if (count($list) > 10){
                $tmp = array_slice($list, 0, 10);
                $rows[$k]->countries = implode(", ", $tmp)."...";
            }else{
                $rows[$k]->countries = implode(", ", $list);
            }
        }
        return $rows;
    }
    
    public function getPriceType(){
        return array(
            0 => _JSHOP_PRODUCT_BRUTTO_PRICE, 
            1 => _JSHOP_PRODUCT_NETTO_PRICE
        );
    }
    
    public function save(array $post){
        $configdisplayprice = JSFactory::getTable('configDisplayPrice', 'jshop');        
        $dispatcher = JDispatcher::getInstance();        
        $dispatcher->trigger('onBeforeSaveConfigDisplayPrice', array(&$post));                
        if (!$post['countries_id']){
            $this->setError(_JSHOP_ERROR_BIND);
            return 0;
        }
        $configdisplayprice->bind($post);
        $configdisplayprice->setZones($post['countries_id']);
        if (!$configdisplayprice->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0; 
        }
        updateCountConfigDisplayPrice();
        $dispatcher->trigger('onAftetSaveConfigDisplayPrice', array(&$configdisplayprice));
        return $configdisplayprice;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDeleteConfigDisplayPrice', array(&$cid));
        $res = array();
        foreach($cid as $id){
            $query = "DELETE FROM `#__jshopping_config_display_prices` WHERE `id`=".(int)$id;
            $db->setQuery($query);
            $db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
            $res[$id] = true;
        }
        updateCountConfigDisplayPrice();
        $dispatcher->trigger('onAfterDeleteConfigDisplayPrice', array(&$cid));
        return $res;
    }
}

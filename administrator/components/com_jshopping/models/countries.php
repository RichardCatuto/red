<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelCountries extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'country';
    protected $tableFieldPublish = 'country_publish';
    
    /**
    * get list country
    * 
    * @param int $publish (0-all, 1-publish, 2-unpublish)
    * @param int $limitstart
    * @param int $limit
    * @param int $orderConfig use order config
    * @return array
    */
    function getAllCountries($publish = 1, $limitstart = null, $limit = null, $orderConfig = 1, $order = null, $orderDir = null){
        $db = JFactory::getDBO();
        $jshopConfig = JSFactory::getConfig();
                
        if ($publish == 0) {
            $where = " ";
        } else {
            if ($publish == 1) {
                $where = (" WHERE country_publish = '1' ");
            } else {
                if ($publish == 2) {
                    $where = (" WHERE country_publish = '0' ");
                }
            }
        }
        $ordering = "ordering";
        if ($orderConfig && $jshopConfig->sorting_country_in_alphabet) $ordering = "name";
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        $lang = JSFactory::getLang();
        $query = "SELECT country_id, country_publish, ordering, country_code, country_code_2, `".$lang->get("name")."` as name FROM `#__jshopping_countries` ".$where." ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query, $limitstart, $limit);
        return $db->loadObjectList();
    }

    /**
    * get count country
    * @return int
    */
    function getCountAllCountries() {
        $db = JFactory::getDBO(); 
        $query = "SELECT COUNT(country_id) FROM `#__jshopping_countries`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    /**
    * get count county
    * @param int $publish
    * @return int
    */
    function getCountPublishCountries($publish = 1) {
        $db = JFactory::getDBO(); 
        $query = "SELECT COUNT(country_id) FROM `#__jshopping_countries` WHERE country_publish = '".intval($publish)."'";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    public function save(array $post){
        $dispatcher = JDispatcher::getInstance();		
        $dispatcher->trigger('onBeforeSaveCountry', array(&$post));
		$country = JSFactory::getTable('country', 'jshop');
		$country->bind($post);	
		if (!$country->country_publish){
			$country->country_publish = 0;
	    }    
		$this->_reorderCountry($country);
		if (!$country->store()) {
			$this->setError(_JSHOP_ERROR_SAVE_DATABASE);			
			return 0;
		}
        $dispatcher->trigger('onAfterSaveCountry', array(&$country));
        return $country;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
		$app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveCountry', array(&$cid));
        $res = array();
		foreach($cid as $id){
			$query = "DELETE FROM `#__jshopping_countries` WHERE `country_id`=".(int)$id;
			$db->setQuery($query);
			$db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_COUNTRY_DELETED, 'message');
            }
            $res[$id] = true;
		}
        $dispatcher->trigger('onAfterRemoveCountry', array(&$cid));
        return $res;
    }
    
    protected function _reorderCountry(&$country) {
		$db = JFactory::getDBO();
		$query = "UPDATE `#__jshopping_countries` SET `ordering` = ordering + 1 WHERE `ordering` > '".$country->ordering."'";		
		$db->setQuery($query);
		$db->query();
		$country->ordering++;
	}
    
    public function publish(array $cid, $flag){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishCountry', array(&$cid, &$flag));
        parent::publish($cid, $flag);
        $dispatcher->trigger('onAfterPublishCountry', array(&$cid, &$flag));
	}
      
}
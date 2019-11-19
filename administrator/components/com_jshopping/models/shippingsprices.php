<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelShippingsPrices extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'shippingMethodPrice';

    function save(array $post){
        $dispatcher = JDispatcher::getInstance();
        $shippings = JSFactory::getModel('shippings');
		$shipping_pr = JSFactory::getTable('shippingMethodPrice', 'jshop');
        $post['shipping_stand_price'] = saveAsPrice($post['shipping_stand_price']);
        $dispatcher->trigger('onBeforeSaveShippingPrice', array(&$post));
        $countries = $post['shipping_countries_id'];
		$shipping_pr->bind($post);
        if( isset($post['sm_params']) ) {
            $shipping_pr->setParams($post['sm_params']);
        } else {
            $shipping_pr->setParams(array());
		}
		if( !$shipping_pr->store() ) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
			return 0;
		}
		$shippings->savePrices($shipping_pr->sh_pr_method_id, $post);
		$shippings->saveCountries($shipping_pr->sh_pr_method_id, $countries);
        $dispatcher->trigger('onAfterSaveShippingPrice', array(&$shipping_pr));
        return $shipping_pr;
    }

    function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
		$db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveShippingPrice', array(&$cid));
		foreach($cid as $value) {
			$query = "DELETE FROM `#__jshopping_shipping_method_price`
					  WHERE `sh_pr_method_id` = '" . $db->escape($value) . "'";
			$db->setQuery($query);
			if( $db->query() ) {
				$query = "DELETE FROM `#__jshopping_shipping_method_price_weight`
						  WHERE `sh_pr_method_id` = '" . $db->escape($value) . "'";
				$db->setQuery($query);
				$db->query();
				$query = "DELETE FROM `#__jshopping_shipping_method_price_countries`
						  WHERE `sh_pr_method_id` = '" . $db->escape($value) . "'";
				$db->setQuery($query);
				$db->query();
                
                if ($msg){
                    $app->enqueueMessage(_JSHOP_SHIPPING_DELETED, 'message');
                }
			}
		}
        $dispatcher->trigger('onAfterRemoveShippingPrice', array(&$cid));
    }

}
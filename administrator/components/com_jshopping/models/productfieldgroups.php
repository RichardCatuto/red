<?php
/**
* @version      4.14.0 23.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die;

class JshoppingModelProductFieldGroups extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'productFieldGroup';

    function getList() {
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $query = "SELECT id, `".$lang->get("name")."` as name, ordering FROM `#__jshopping_products_extra_field_groups` order by ordering";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function save(array $post) {
        $productfieldgroup = JSFactory::getTable('productFieldGroup', 'jshop');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveProductFieldGroup', array(&$post));
        $productfieldgroup->bind($post);
        if( !$post['id'] ) {
            $productfieldgroup->ordering = null;
            $productfieldgroup->ordering = $productfieldgroup->getNextOrder();
        }
        if( !$productfieldgroup->store() ) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveProductFieldGroup', array(&$productfieldgroup));
        return $productfieldgroup;
    }

    function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        foreach($cid as $id){
            $query = "DELETE FROM `#__jshopping_products_extra_field_groups` WHERE `id` = ".(int)$id;
            $db->setQuery($query);
            $db->query();
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
        }
        JDispatcher::getInstance()->trigger('onAfterRemoveProductFieldGroup', array(&$cid));
    }

}
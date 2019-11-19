<?php
/**
* @version      4.14.0 24.07.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelProductLabels extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'productLabel';

    function getList($order = null, $orderDir = null){
        $db = JFactory::getDBO();
        $ordering = "name";
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
		$lang = JSFactory::getLang();
        $query = "SELECT id, image, `".$lang->get("name")."` as name FROM `#__jshopping_product_labels` ORDER BY ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    function save(array $post, $image = null){
        $jshopConfig = JSFactory::getConfig();
        require_once($jshopConfig->path.'lib/uploadfile.class.php');
		$productLabel = JSFactory::getTable('productLabel', 'jshop');
		$lang = JSFactory::getLang();
        $post['name'] = $post[$lang->get("name")];
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveProductLabel', array(&$post));
        if ($image){
            $upload = new UploadFile($image);
            $upload->setAllowFile(array('jpeg','jpg','gif','png'));
            $upload->setDir($jshopConfig->image_labels_path);
            $upload->setFileNameMd5(0);
            $upload->setFilterName(1);
            if ($upload->upload()){
                if ($post['old_image']){
                    @unlink($jshopConfig->image_labels_path."/".$post['old_image']);
                }
                $post['image'] = $upload->getName();
                @chmod($jshopConfig->image_labels_path."/".$post['image'], 0777);
            }else{
                if ($upload->getError() != 4){
                    JError::raiseWarning("", _JSHOP_ERROR_UPLOADING_IMAGE);
                    saveToLog("error.log", "Label - Error upload image. code: ".$upload->getError());
                }
            }
        }
		$productLabel->bind($post);
		if (!$productLabel->store()){
			$this->setError(_JSHOP_ERROR_SAVE_DATABASE);
			return 0;
		}        
        $dispatcher->trigger('onAfterSaveProductLabel', array(&$productLabel));        
        return $productLabel;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $jshopConfig = JSFactory::getConfig();
        $res = array();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveProductLabel', array(&$cid));
		foreach($cid as $id){
            $table = $this->getDefaultTable();
            $table->load($id);
            @unlink($jshopConfig->image_labels_path."/".$productLabel->image);
            $table->delete();			
            if ($msg){
                $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
            }
            $res[$id] = true;
		}
        $dispatcher->trigger('onAfterRemoveProductLabel', array(&$cid));
        return $res;
    }
    
}
<?php
/**
* @version      4.14.0 10.11.2012
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelManufacturers extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'manufacturer';
    protected $tableFieldPublish = 'manufacturer_publish';

    function getAllManufacturers($publish=0, $order=null, $orderDir=null){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang(); 
        $query_where = ($publish)?(" WHERE manufacturer_publish = '1'"):("");  
        $queryorder = '';        
        if ($order && $orderDir){
            $queryorder = "order by ".$order." ".$orderDir;
        }
        $query = "SELECT manufacturer_id, manufacturer_url, manufacturer_logo, manufacturer_publish, ordering, `".$lang->get('name')."` as name FROM `#__jshopping_manufacturers` $query_where ".$queryorder;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    function getList(){
        $jshopConfig = JSFactory::getConfig();
        if ($jshopConfig->manufacturer_sorting==2){
            $morder = 'name';
        }else{
            $morder = 'ordering';
        }
    return $this->getAllManufacturers(0, $morder, 'asc');
    }
    
    public function getPrepareDataSave($input){
        $jshopConfig = JSFactory::getConfig();
        $post = $input->post->getArray();
        $_alias = JSFactory::getModel("alias");
        $man_id = $post["manufacturer_id"];
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($post['name_'.$lang->language]);
            if ($jshopConfig->create_alias_product_category_auto && $post['alias_'.$lang->language]==""){
                $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            }
            $post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
            if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias1Group($post['alias_'.$lang->language], $lang->language, 0, $man_id)){
                $post['alias_'.$lang->language] = "";
                JError::raiseWarning("",_JSHOP_ERROR_ALIAS_ALREADY_EXIST);
            }
            $post['description_'.$lang->language] = $input->get('description'.$lang->id, '', 'RAW');
            $post['short_description_'.$lang->language] = $input->get('short_description_'.$lang->language, '', 'RAW');
        }
        return $post;
    }
    
    public function imageUpload(array $post, $image){
        $jshopConfig = JSFactory::getConfig();
        require_once($jshopConfig->path.'lib/image.lib.php');
        require_once($jshopConfig->path.'lib/uploadfile.class.php');
        $upload = new UploadFile($image);
        $upload->setAllowFile(array('jpeg','jpg','gif','png'));
        $upload->setDir($jshopConfig->image_manufs_path);
        $upload->setFileNameMd5(0);
        $upload->setFilterName(1);
        if ($upload->upload()){            
            if ($post['old_image']){
                @unlink($jshopConfig->image_manufs_path."/".$post['old_image']);
            }
            $name = $upload->getName();
            @chmod($jshopConfig->image_manufs_path."/".$name, 0777);
            
            if ($post['size_im_category'] < 3){
                if ($post['size_im_category'] == 1){
                    $category_width_image = $jshopConfig->image_category_width; 
                    $category_height_image = $jshopConfig->image_category_height;
                }else{
                    $category_width_image = $post['category_width_image'];
                    $category_height_image = $post['category_height_image'];
                }

                $path_full = $jshopConfig->image_manufs_path."/".$name;
                $path_thumb = $jshopConfig->image_manufs_path."/".$name;

                if (!ImageLib::resizeImageMagic($path_full, $category_width_image, $category_height_image, $jshopConfig->image_cut, $jshopConfig->image_fill, $path_thumb, $jshopConfig->image_quality, $jshopConfig->image_fill_color, $jshopConfig->image_interlace)) {
                    JError::raiseWarning("",_JSHOP_ERROR_CREATE_THUMBAIL);
                    saveToLog("error.log", "SaveManufacturer - Error create thumbail");
                }
                @chmod($jshopConfig->image_manufs_path."/".$name, 0777);
            }
            return $name;
        }else{
            if ($upload->getError() != 4){
                JError::raiseWarning("", _JSHOP_ERROR_UPLOADING_IMAGE);
                saveToLog("error.log", "SaveManufacturer - Error upload image. code: ".$upload->getError());
            }
            return '';
        }
    }
    
    public function save(array $post, $image = null){
        $dispatcher = JDispatcher::getInstance();
        $man = JSFactory::getTable('manufacturer', 'jshop');
        if (!$post['manufacturer_publish']){
            $post['manufacturer_publish'] = 0;
        }
        $dispatcher->trigger('onBeforeSaveManufacturer', array(&$post));
        $man->bind($post);
        if (!$post["manufacturer_id"]){
            $man->ordering = null;
            $man->ordering = $man->getNextOrder();            
        }
        if ($image){
            $image_name = $this->imageUpload($post, $image);
            if ($image_name){
                $man->manufacturer_logo = $image_name;
            }
        }
        if (!$man->store()) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }        
        $dispatcher->trigger('onAfterSaveManufacturer', array(&$man));
        return $man;
    }
    
    function deleteFoto($id){
        $jshopConfig = JSFactory::getConfig();
        $manuf = JSFactory::getTable('manufacturer', 'jshop');
        $manuf->load($id);
        @unlink($jshopConfig->image_manufs_path.'/'.$manuf->manufacturer_logo);
        $manuf->manufacturer_logo = "";
        $manuf->store();
    }
    
    public function deleteList(array $cid, $msg = 1){
        $app = JFactory::getApplication();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveManufacturer', array(&$cid));
        $res = array();
        foreach($cid as $value){
            $manuf = JSFactory::getTable('manufacturer', 'jshop');
            $manuf->load($value);
            if($manuf->delete()){
                if($manuf->manufacturer_logo){
                    @unlink(JSFactory::getConfig()->image_manufs_path.'/'.$manuf->manufacturer_logo);
                }
                if($msg){
                    $app->enqueueMessage(sprintf(_JSHOP_MANUFACTURER_DELETED, $value), 'message');
                }
                $res[$value] = true;
            }else if($msg){
                $app->enqueueMessage(implode("<br/>", $manuf->getErrors()), 'warning');
            }
        }
        $dispatcher->trigger('onAfterRemoveManufacturer', array(&$cid));
        return $res;
    }
    
    public function publish(array $cid, $flag){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishManufacturer', array(&$cid, &$flag));
        parent::publish($cid, $flag);
        $dispatcher->trigger('onAfterPublishManufacturer', array(&$cid, &$flag));
    }
      
}
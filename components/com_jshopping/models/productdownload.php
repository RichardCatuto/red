<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');

class jshopProductDownload{
    
	protected $id;
	protected $oid;
	protected $hash;
	protected $order;
	protected $stat_download;
	
	public function setId($id){
		$this->id = $id;
	}
	
	public function setOid($oid){
		$this->oid = $oid;
		$this->order = JSFactory::getTable('order', 'jshop');
        $this->order->load($oid);
		$this->stat_download = $this->order->getFilesStatDownloads();
	}
	
	public function setHash($hash){
		$this->hash = $hash;
	}
	
	public function getOrder(){
		return $this->order;
	}
	
	public function getStatDownload(){
		return $this->stat_download;
	}
	
	public function checkHash(){
		return $this->order->file_hash==$this->hash;
	}
    
	public function checkOrderStatusPaid(){
		$jshopConfig = JSFactory::getConfig();
		return in_array($this->order->order_status, $jshopConfig->payment_status_enable_download_sale_file);
	}
	
	public function checkUser(){
		$jshopConfig = JSFactory::getConfig();
		$user = JFactory::getUser();
		return !($jshopConfig->user_registered_download_sale_file && $this->order->user_id>0 && $this->order->user_id!=$user->id);
	}
	
	public function checkTimeDownload(){
		$jshopConfig = JSFactory::getConfig();
		return !($jshopConfig->max_day_download_sale_file && (time() > ($this->order->getStatusTime()+(86400*$jshopConfig->max_day_download_sale_file))));
	}
	
	public function checkNumberDownload(){
		$jshopConfig = JSFactory::getConfig();
		return !($jshopConfig->max_number_download_sale_file>0 && $this->stat_download[$this->id]['download'] >= $jshopConfig->max_number_download_sale_file);
	}
	
	public function checkFileId(){
		$jshopConfig = JSFactory::getConfig();
		$items = $this->order->getAllItems();
		$filesid = array();
        if ($jshopConfig->order_display_new_digital_products){
            $product = JSFactory::getTable('product', 'jshop');
            foreach($items as $item){
                $product->product_id = $item->product_id;
				$product->setAttributeActive(unserialize($item->attributes));
                $files = $product->getSaleFiles();
                foreach($files as $_file){
                    $filesid[] = $_file->id;
                }
            }
        }else{
            foreach($items as $item){
                $arrayfiles = unserialize($item->files);
                foreach($arrayfiles as $_file){
                    $filesid[] = $_file->id;
                }
            }
        }
        
        return in_array($this->id, $filesid);
	}
	
	public function getFileName(){
		$file = JSFactory::getTable('productFiles', 'jshop');
        $file->load($this->id);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterLoadProductFile', array(&$file, &$this->order));
        return $file->file;
	}
	
	public function getFile($name=''){
		if ($name==''){
			$name = $this->getFileName();
		}
		return JSFactory::getConfig()->files_product_path."/".$name;
	}
	
	public function storeStatDownloads(){
		$stat_download = $this->stat_download;
		$id = $this->id;
		$stat_download[$id]['download'] = intval($stat_download[$id]['download']) + 1;
        $stat_download[$id]['time'] = getJsDate();
        $this->order->setFilesStatDownloads($stat_download);
        $this->order->store();
	}
	
	public function downloadFile($file_name){
		if (!file_exists($file_name)){
            throw new Exception('Error. File not exist');
        }
		$fp = fopen($file_name, "rb");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-Type: application/octet-stream");
        header("Content-Length: " . (string)(filesize($file_name)));
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header("Content-Transfer-Encoding: binary");

        while( (!feof($fp)) && (connection_status()==0) ){
            print(fread($fp, 1024*8));
            flush();
        }
        fclose($fp);
	}
	
	public function getUrlDownload(){
		return JURI::root()."index.php?option=com_jshopping&controller=product&task=getfile&oid=".$this->oid."&id=".$this->id."&hash=".$this->hash;
	}
	
}
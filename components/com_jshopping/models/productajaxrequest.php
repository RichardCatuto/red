<?php
/**
* @version      4.16.1 22.03.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopProductAjaxRequest extends jshopBase{
	
	public $product;
	public $product_id;
	public $change_attr;
	public $qty;
	public $attribs;
	public $freeattr;
	public $request;
	
	public function __construct(){
		$this->product = JSFactory::getTable('product', 'jshop');
	}	
	
	public function setData(&$product_id, &$change_attr, &$qty, &$attribs, &$freeattr, &$request){		
        JDispatcher::getInstance()->trigger('onBeforeLoadDisplayAjaxAttrib', array(&$product_id, &$change_attr, &$qty, &$attribs, &$freeattr, &$request));
		$this->product_id = $product_id;
		$this->change_attr = $change_attr;
		$this->qty = $qty;
		$this->attribs = $attribs;
		$this->freeattr = $freeattr;
		$this->request = $request;
	}
	
	public function getProduct(){
		return $this->product;
	}
	
	public function getProductId(){
		return $this->product_id;
	}
	
	public function getChangeAttr(){
		return $this->change_attr;
	}
	
	public function getQty(){
		return $this->qty;
	}
	
	public function getAttribs(){
		return $this->attribs;
	}
	
	public function getFreeattr(){
		return $this->freeattr;
	}
	
	public function getRequest(){
		return $this->request;
	}
	
	public function getLoadProductData(){
		$jshopConfig = JSFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
		
		$product = $this->product;
		$product->load($this->product_id);
		$dispatcher->trigger('onBeforeLoadDisplayAjaxAttrib2', array(&$product));
		
		$attributes = $product->getInitLoadAttribute($this->attribs);
        $product->setFreeAttributeActive($this->freeattr);

        $rows = array();
        foreach($attributes as $k=>$v){          
            $rows['id_'.$k] = $v->selects;
        }

        $pricefloat = $product->getPrice($this->qty, 1, 1, 1);
        $price = formatprice($pricefloat);
        $available = intval($product->getQty() > 0);
		$displaybuttons = intval(intval($product->getQty() > 0) || $jshopConfig->hide_buy_not_avaible_stock==0);
        $ean = $product->getEan();
        $manufacturer_code = $product->getManufacturerCode();
        $weight = formatweight($product->getWeight());
        $basicprice = formatprice($product->getBasicPrice());
        
        $rows['price'] = $price;
        $rows['pricefloat'] = $pricefloat;
        $rows['available'] = $available;
        $rows['ean'] = $ean;
        $rows['manufacturer_code'] = $manufacturer_code;
        if ($jshopConfig->admin_show_product_basic_price){
            $rows['basicprice'] = $basicprice;
        }
        if ($jshopConfig->product_show_weight){
            $rows['weight'] = $weight;
        }
        if ($jshopConfig->product_list_show_price_default && $product->product_price_default>0){
            $rows['pricedefault'] = formatprice($product->product_price_default);
        }
        if ($jshopConfig->product_show_qty_stock){
            $qty_in_stock = getDataProductQtyInStock($product);
            $rows['qty'] = sprintQtyInStock($qty_in_stock);
        }
		
        $product->updateOtherPricesIncludeAllFactors();

        if (is_array($product->product_add_prices)){
            foreach($product->product_add_prices as $k=>$v){
                $rows['pq_'.$v->product_quantity_start] = formatprice($v->price).$v->ext_price;
            }
        }
        if ($product->product_old_price){
            $old_price = formatprice($product->product_old_price);
            $rows['oldprice'] = $old_price;
        }
		$rows['displaybuttons'] = $displaybuttons;
        if ($jshopConfig->hide_delivery_time_out_of_stock){
            $rows['showdeliverytime'] = $product->getDeliveryTimeId();
        }
        
        if ($jshopConfig->use_extend_attribute_data){
            $template_path = $jshopConfig->template_path.$jshopConfig->template."/product";
            $images = $product->getImages();
            $videos = $product->getVideos();
			$demofiles = $product->getDemoFiles();
			if (!file_exists($template_path."/block_image_thumb.php")){
				$tmp = array();
				foreach($images as $img){
					$tmp[] = $img->image_name;
				}
				$displayimgthumb = intval((count($images)>1) || (count($videos) && count($images)));
				$rows['images'] = $tmp;
				$rows['displayimgthumb'] = $displayimgthumb;
            }

			$view = $this->getView("product");
			$view->setLayout("demofiles");
			$view->assign('config', $jshopConfig);
			$view->assign('demofiles', $demofiles);
            $demofiles = $view->loadTemplate();            
            $rows['demofiles'] = $demofiles;

			if (file_exists($template_path."/block_image_thumb.php")){
                $product->getDescription();
                
                $view = $this->getView("product");
                $view->setLayout("block_image_thumb");
                $view->assign('config', $jshopConfig);            
                $view->assign('images', $images);            
                $view->assign('videos', $videos);            
                $view->assign('image_product_path', $jshopConfig->image_product_live_path);            
                $dispatcher->trigger('onBeforeDisplayProductViewBlockImageThumb', array(&$view));
                $block_image_thumb = $view->loadTemplate();
                
                $view = $this->getView("product");
                $view->setLayout("block_image_middle");
                $view->assign('config', $jshopConfig);            
                $view->assign('images', $images);            
                $view->assign('videos', $videos);            
                $view->assign('product', $product);            
                $view->assign('noimage', $jshopConfig->noimage);            
                $view->assign('image_product_path', $jshopConfig->image_product_live_path);
                $view->assign('path_to_image', $jshopConfig->live_path.'images/');
                $dispatcher->trigger('onBeforeDisplayProductViewBlockImageMiddle', array(&$view));
                $block_image_middle = $view->loadTemplate();

                $rows['block_image_thumb'] = $block_image_thumb;

                $rows['block_image_middle'] = $block_image_middle;
            }
        }
		$dispatcher->trigger('onBeforeDisplayAjaxAttribRows', array(&$rows, &$this));
        return $rows;
	}
	
	public function getProductDataJson(){
		$prod_data = $this->getLoadProductData();
		$rows = $this->json_encode_rows($prod_data);
		JDispatcher::getInstance()->trigger('onBeforeDisplayAjaxAttrib', array(&$rows, &$this->product) );
		$json = $this->json_encode_build($rows);
		return $json;
	}
	
	public function json_encode_rows($data){
		$rows = array();
		foreach($data as $k=>$v){
			if (is_array($v)){
				$tmp = array();
				foreach($v as $val){
					$tmp[] = '"'.json_value_encode($val, 1).'"';
				}
				$rows[] = '"'.$k.'"'.':'.'['.implode(',', $tmp).']';
			}else{
				$rows[] = '"'.$k.'"'.':'.'"'.json_value_encode($v, 1).'"';
			}
		}
		return $rows;
	}
	
	public function json_encode_build($rows){
		return '{'.implode(",", $rows).'}';
	}
	
}
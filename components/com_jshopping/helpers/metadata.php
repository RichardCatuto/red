<?php
/**
* @version      4.13.1 02.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshopHelpersMetadata{
	
	public static function metaData($alias, $loadParams = 1, $default_title = '', $path_way = '', $external_params = null){
		if ($path_way!=''){
			appendPathWay($path_way);
		}
		if ($loadParams && is_null($external_params)){
			$params = JFactory::getApplication()->getParams();
		}else{
			$params = null;
		}
		if ($external_params){
			$params = $external_params;
		}
		$seo = JSFactory::getTable("seo", "jshop");
        $seodata = $seo->loadData($alias);
		if ($seodata->title==""){
            $seodata->title = $default_title;
        }
        setMetaData($seodata->title, $seodata->keyword, $seodata->description, $params);
		return $seodata;
	}
	
	public static function metaDataDependenMainPageShop($alias, $loadParams = 1, $default_title = '', $path_way = ''){
		if (getThisURLMainPageShop()){
			$params = 0;
			$title = $default_title;
			$path = $path_way ;
        }else{
			$params = $loadParams;
			$title = '';
			$path = '';
        }
		return self::metaData($alias, $params, $title, $path);
	}
	
	public static function mainCategory($category, $params){
		setMetaData($category->meta_title, $category->meta_keyword, $category->meta_description, $params);
	}
	
	public static function category($category){
		if (getShopMainPageItemid()==JFactory::getApplication()->input->getInt('Itemid')){
            appendExtendPathWay($category->getTreeChild(), 'category');
        }
        if ($category->meta_title=="") $category->meta_title = $category->name;
        setMetaData($category->meta_title, $category->meta_keyword, $category->meta_description);
	}
	
	public static function cart(){
		self::metaDataDependenMainPageShop('cart', 1, _JSHOP_CART, _JSHOP_CART);
	}
	
	public static function checkoutAddress(){
		self::metaData("checkout-address", 0, _JSHOP_CHECKOUT_ADDRESS, _JSHOP_CHECKOUT_ADDRESS);
	}
	
	public static function checkoutPayment(){		
		self::metaData("checkout-payment", 0, _JSHOP_CHECKOUT_PAYMENT, _JSHOP_CHECKOUT_PAYMENT);
	}
	
	public static function checkoutShipping(){		
		self::metaData("checkout-shipping", 0, _JSHOP_CHECKOUT_SHIPPING, _JSHOP_CHECKOUT_SHIPPING);
	}
	
	public static function checkoutPreview(){		
		self::metaData("checkout-preview", 0, _JSHOP_CHECKOUT_PREVIEW, _JSHOP_CHECKOUT_PREVIEW);
	}
	
	public static function checkoutFinish(){
		$document = JFactory::getDocument();
        $document->setTitle(_JSHOP_CHECKOUT_FINISH);
        appendPathWay(_JSHOP_CHECKOUT_FINISH);
	}
	
	public static function content($page){		
		switch($page){
            case 'agb':
                $title = _JSHOP_AGB;
            break;
            case 'return_policy':
                $title = _JSHOP_RETURN_POLICY;
            break;
            case 'shipping':
                $title = _JSHOP_SHIPPING;
            break;
            case 'privacy_statement':
                $title = _JSHOP_PRIVACY_STATEMENT;
            break;
        }		
		if (getThisURLMainPageShop()){
			$pathway = $title;
			$loadParams = 0;
		}else{
			$pathway = '';
			$loadParams = 1;
		}
		return self::metaData("content-".$page, $loadParams, $title, $pathway);
	}
	
	public static function listManufacturers($params){
		self::metaData("manufacturers", 0, '', '', $params);
	}
	
	public static function manufacturer($manufacturer){
		if (getShopManufacturerPageItemid()==JFactory::getApplication()->input->getInt('Itemid')){
            appendPathWay($manufacturer->name);
        }
        if ($manufacturer->meta_title=="") $manufacturer->meta_title = $manufacturer->name;
        setMetaData($manufacturer->meta_title, $manufacturer->meta_keyword, $manufacturer->meta_description);
	}
	
	public static function search(){		
		self::metaDataDependenMainPageShop('search', 1, _JSHOP_SEARCH, _JSHOP_SEARCH);
	}
	
	public static function searchResult(){
		self::metaDataDependenMainPageShop('search-result', 1, _JSHOP_SEARCH, _JSHOP_SEARCH);
	}
	
	public static function userLogin(){
		self::metaDataDependenMainPageShop('login', 1, _JSHOP_LOGIN, _JSHOP_LOGIN);
	}
	
	public static function userRegister(){
		self::metaDataDependenMainPageShop('register', 1, _JSHOP_REGISTRATION, _JSHOP_REGISTRATION);
	}
	
	public static function userEditaccount(){
		if (shopItemMenu::getInstance()->getEditaccount() != JFactory::getApplication()->input->getInt('Itemid')){
			$pathway = _JSHOP_EDIT_DATA;
		}else{
			$pathway = '';
		}
		self::metaData("editaccount", 0, _JSHOP_EDIT_DATA, $pathway);
	}
	
	public static function userOrders(){
		if (shopItemMenu::getInstance()->getOrders() != JFactory::getApplication()->input->getInt('Itemid')){
			$path_way = _JSHOP_MY_ORDERS;
		}else{
			$path_way = '';
		}
		self::metaData("myorders", 0, _JSHOP_MY_ORDERS, $path_way);
	}
	
	public static function userOrder($order){
		$jshopConfig = JSFactory::getConfig();        
		self::metaData("myorder-detail", 0, _JSHOP_MY_ORDERS);
		$shim = shopItemMenu::getInstance();
		if ($shim->getOrders()!=JFactory::getApplication()->input->getInt('Itemid')){
			appendPathWay(_JSHOP_MY_ORDERS, SEFLink('index.php?option=com_jshopping&controller=user&task=orders', 0, 0, $jshopConfig->use_ssl));
		}
        appendPathWay(_JSHOP_ORDER_NUMBER.": ".$order->order_number);
	}
	
	public static function userMyaccount(){
		if (shopItemMenu::getInstance()->getUser() != JFactory::getApplication()->input->getInt('Itemid')){
			$pathway = _JSHOP_MY_ACCOUNT;
		}else{
			$pathway = '';
		}
		self::metaData("myaccount", 0, _JSHOP_MY_ACCOUNT, $pathway);
	}
	
	public static function userGroupsinfo(){
		setMetaData(_JSHOP_USER_GROUPS_INFO, "", "");
	}
	
	public static function listVendors(){
		self::metaData("vendors");
	}
	
	public static function vendorInfo($vendor){
		$title =  $vendor->shop_name;
		return self::metaData("vendor-info-".$vendor->id, 0, $title, $title);
	}
	
	public static function vendorProducts($vendor){
		$title =  $vendor->shop_name;
		return self::metaData("vendor-product-".$vendor->id, 0, $title, $title);
	}
	
	public static function wishlist(){
		self::metaDataDependenMainPageShop('wishlist', 1, _JSHOP_WISHLIST, _JSHOP_WISHLIST);
	}
	
	public static function product($category, $product){
		$app = JFactory::getApplication();
		$Itemid = $app->input->getInt('Itemid');
		if (getShopMainPageItemid()==$Itemid){
            appendExtendPathway($category->getTreeChild(), 'product');
        }
		$menu = $app->getMenu();
		$menuItem = $menu->getItem($Itemid);        
		if ($menuItem->query['view']!='product'){
			appendPathWay($product->name);
		}
        if ($product->meta_title=="") $product->meta_title = $product->name;
        setMetaData($product->meta_title, $product->meta_keyword, $product->meta_description);
	}
	
	public static function allProducts(){
		self::metaData("all-products");
	}
	
	public static function productsTophits(){		
		self::metaData("tophitsproducts");
	}
	
	public static function productsToprating(){		
		self::metaData("topratingproducts");
	}
	
	public static function productsLabel(){		
		self::metaData("labelproducts");
	}
	
	public static function productsBestseller(){		
		self::metaData("bestsellerproducts");
	}
	
	public static function productsRandom(){		
		self::metaData("randomproducts");
	}
	
	public static function productsLast(){		
		self::metaData("lastproducts");
	}

}
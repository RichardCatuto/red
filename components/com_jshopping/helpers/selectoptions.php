<?php
/**
* @version      4.14.0 07.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshopHelpersSelectOptions{
	
	public static function getCountrys($first = 1){
		$app = JFactory::getApplication();
		$option = array();
        $first_name = self::getFirstNameOption($first, _JSHOP_REG_SELECT);
		$country = JSFactory::getTable('country', 'jshop');
		if ($first!==0){
			$option[] = JHTML::_('select.option', '0', $first_name, 'country_id', 'name');
		}
		if ($app->getName() != 'site'){
			$list = $country->getAllCountries(0);
		}else{
			$list = $country->getAllCountries();
		}
	return array_merge($option, $list);
	}
	
	public static function getTitles(){
		$jshopConfig = JSFactory::getConfig();
		$option = array();
		foreach($jshopConfig->user_field_title as $key => $value) {
            $option[] = JHTML::_('select.option', $key, $value, 'id', 'name');
        }
		return $option;
	}
	
	public static function getClientTypes(){
		$jshopConfig = JSFactory::getConfig();
		$option = array();
        foreach($jshopConfig->user_field_client_type as $key => $value){
            $option[] = JHTML::_('select.option', $key, $value, 'id', 'name');
        }
		return $option;
	}
	
	public static function getProductsOrdering($typelist = 0){
		$jshopConfig = JSFactory::getConfig();
		$option = array();
		if ($typelist==1){
			$list = $jshopConfig->sorting_products_name_select;
		}else{
			$list = $jshopConfig->sorting_products_name_s_select;
		}
		foreach($list as $key=>$value){
            $option[] = JHTML::_('select.option', $key, $value, 'id', 'name' );
        }
		return $option;
	}
	
	public static function getProductsCount($extended_value = null){
		$jshopConfig = JSFactory::getConfig();
		$list = $jshopConfig->count_product_select;
		if (!is_null($extended_value)){
			insertValueInArray($extended_value, $list);
		}
		$option = array();
        foreach($list as $key => $value){
            $option[] = JHTML::_('select.option',$key, $value, 'id', 'name' );
        }
		return $option;
	}
    
    public static function getFirstNameOption($type, $value1 = ''){
        if ($type===1){
            $first_name = $value1;
        }elseif ($type===2){
            $first_name = _JSHOP_NONE;
        }elseif ($type===3){
            $first_name = _JSHOP_SELECT;
        }elseif ($type===4){
            $first_name = _JSHOP_ALL;
        }else{
            $first_name = $type;
        }
        return $first_name;
    }
    
    public static function getManufacturers($first = 1, $ext_first = 0){
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_NAME_MANUFACTURER." - ");
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'manufacturer_id', 'name');
        }
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'manufacturer_id', 'name');
        }
        $model = JSFactory::getModel('manufacturers');
        $manufs = $model->getList();
        return array_merge($first_option, $manufs);
    }
    
    public static function getLabels($first = 1, $ext_first = 0){
        $_labels = JSFactory::getModel("productLabels");
        $alllabels = $_labels->getList();
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'id','name');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_LABEL." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', '0', $first_name, 'id','name');
        }
        return array_merge($first_option, $alllabels);
    }
    
    public static function getAccessGroups($first = 0, $ext_first = 0){
        $accessgroups = getAccessGroups();        
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', '-1', "- - -", 'id', 'title');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_ACCESS." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', '0', $first_name, 'id','name');
        }
        return array_merge($first_option, $accessgroups);
    }
    
    public static function getPublish($first = 1){
        $f_option = array();
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_SHOW." - ");
        if ($first!==0){
            $f_option[] = JHTML::_('select.option', '0', $first_name, 'id','name');
        }
        $f_option[] = JHTML::_('select.option', 1, _JSHOP_PUBLISH, 'id', 'name');
        $f_option[] = JHTML::_('select.option', 2, _JSHOP_UNPUBLISH, 'id', 'name');
        return $f_option;
    }
    
    public static function getPublishGroup(){
        $published = array();
        $published[] = JHTML::_('select.option', '-1', "- - -", 'value', 'name');
        $published[] = JHTML::_('select.option', 0, _JSHOP_UNPUBLISH, 'value', 'name');
        $published[] = JHTML::_('select.option', 1, _JSHOP_PUBLISH, 'value', 'name');
        return $published;
    }
    
    public static function getVendors($first = 1){
        $vendors = JSFactory::getModel('vendors')->getAllVendorsNames(1);
        $first_option = array();
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_VENDOR." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', -1, $first_name, 'id', 'name');
        }        
        return array_merge($first_option, $vendors);
    }
    
    public static function getCategories($first = 1, $ext_first = 0){
        $categories = buildTreeCategory(0, 1, 0);
        $first_option = array();
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_CATEGORY." - ");
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'category_id', 'name');
        }
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'category_id', 'name');
        }
        return array_merge($first_option, $categories);
    }
    
    public static function getTaxs($first = 0, $ext_first = 0, $options = array()){
        $all_taxes = JSFactory::getModel("taxes")->getAllTaxes();
        $list_tax = array();
        if ($ext_first){
            $list_tax[] = JHTML::_('select.option', -1, "- - -", 'tax_id', 'tax_name');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_TAX." - ");
        if ($first!==0){
            $list_tax[] = JHTML::_('select.option', 0, $first_name, 'tax_id', 'tax_name');
        }
        foreach($all_taxes as $tax){
            $list_tax[] = JHTML::_('select.option', $tax->tax_id, $tax->tax_name.' (' . $tax->tax_value . '%)', 'tax_id', 'tax_name');
        }
        if (isset($options['product_tax_rate'])){
            $list_tax[] = JHTML::_('select.option', -1, _JSHOP_PRODUCT_TAX_RATE, 'tax_id', 'tax_name');
        }
        return $list_tax;
    }
    
    public static function getProductAttributPriceModify(){
        $price_modification = array();
        $price_modification[] = JHTML::_('select.option', '+','+', 'id','name');
        $price_modification[] = JHTML::_('select.option', '-','-', 'id','name');
        $price_modification[] = JHTML::_('select.option', '*','*', 'id','name');
        $price_modification[] = JHTML::_('select.option', '/','/', 'id','name');
        $price_modification[] = JHTML::_('select.option', '=','=', 'id','name');
        $price_modification[] = JHTML::_('select.option', '%','%', 'id','name');
        return $price_modification;
    }
    
    public static function getDeliveryTimes($first = 2, $ext_first = 0){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'id', 'name');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_DELIVERY_TIME." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        $list = JSFactory::getModel("deliveryTimes")->getDeliveryTimes();
        return array_merge($first_option, $list);
    }
    
    public static function getUnits($first = 0, $ext_first = 0){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'id', 'name');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_UNIT_MEASURE." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        $units = JSFactory::getModel("units")->getUnits();
        return array_merge($first_option, $units);
    }
    
    public static function getCurrencies($first = 0, $ext_first = 0){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'currency_id', 'currency_code');
        }
        $first_name = self::getFirstNameOption($first, " - "._JSHOP_CURRENCIES." - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'currency_id', 'currency_code');
        }
        $currencies = JSFactory::getModel("currencies")->getAllCurrencies();
        return array_merge($first_option, $currencies);
    }
	
    public static function getOrderStatus($first = 0, $ext_first = 0){
        $first_option = array();
        $first_name = self::getFirstNameOption($first, _JSHOP_ALL_ORDERS);
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'status_id', 'name');
        }
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'status_id', 'name');
        }
        $list = JSFactory::getModel("orders")->getAllOrderStatus();
        return array_merge($first_option, $list);
    }
    
    public static function getNotFinshed(){
        $option = array();
        $option[] = JHTML::_('select.option', 0, _JSHOP_HIDE, 'id', 'name');
        $option[] = JHTML::_('select.option', 1, _JSHOP_SHOW, 'id', 'name');
        return $option;
    }
    
    public static function getOrdersYears($first = 1){
        $firstYear = JSFactory::getModel("orders")->getMinYear();
        $y_option = array();
        $first_name = self::getFirstNameOption($first, " - - - ");
        if ($first!==0){
            $y_option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        for($y=$firstYear;$y<=date("Y");$y++){
            $y_option[] = JHTML::_('select.option', $y, $y, 'id', 'name');
        }
        return $y_option;
    }
    
    public static function getMonths($first = 1){
        $option = array();
        $first_name = self::getFirstNameOption($first, " - - - ");
        if ($first!==0){
            $option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        for($y=1; $y<=12; $y++){
            if ($y<10){
                $y_month = "0".$y;
            }else{
                $y_month = $y;
            }
            $option[] = JHTML::_('select.option', $y_month, $y_month, 'id', 'name');
        }
        return $option;
    }
    
    public static function getDays($first = 1){
        $option = array();
        $first_name = self::getFirstNameOption($first, " - - - ");
        if ($first!==0){
            $option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        for($y=1; $y<=31; $y++){
            if ($y<10){
                $name = "0".$y;
            }else{
                $name = $y;
            }
            $option[] = JHTML::_('select.option', $name, $name, 'id', 'name');
        }
        return $option;
    }
    
    public static function getLanguages(){
        $languages = JSFactory::getModel("languages")->getAllLanguages(1);
        return $languages;
    }
    
    public static function getPriceType(){
        $display_price_list = array();
        $display_price_list[] = JHTML::_('select.option', 0, _JSHOP_PRODUCT_BRUTTO_PRICE, 'id', 'name');
        $display_price_list[] = JHTML::_('select.option', 1, _JSHOP_PRODUCT_NETTO_PRICE, 'id', 'name');
        return $display_price_list;
    }
    
    public static function getShippings($first = 1){
        $list = JSFactory::getModel("shippings")->getAllShippings(0);
        $first_option = array();
        $first_name = self::getFirstNameOption($first, " - - - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'shipping_id', 'name');
        }
        return array_merge($first_option, $list);
    }
    
    public static function getPayments($first = 1){
        $list = JSFactory::getModel("payments")->getAllPaymentMethods(0);
        $first_option = array();
        $first_name = self::getFirstNameOption($first, " - - - ");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'payment_id', 'name');
        }
        return array_merge($first_option, $list);
    }
    
    public static function getUsers($first = 0, $ext_first = 0){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'user_id', 'name');
        }
        $first_name = self::getFirstNameOption($first, _JSHOP_USERS);
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'user_id', 'name');
        }
        $list = JSFactory::getModel('users')->getUsers();
        return array_merge($first_option, $list);
    }
    
    public static function getUserGroups($first = 0, $ext_first = 0){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'usergroup_id', 'usergroup_name');
        }
        $first_name = self::getFirstNameOption($first, _JSHOP_USERGROUPS);
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'usergroup_id', 'usergroup_name');
        }
        $list = JSFactory::getModel("userGroups")->getAllUsergroups();
        return array_merge($first_option, $list);
    }
    
    public static function getPaymentPriceTypes(){
        $currencyCode = getMainCurrencyCode();
        $list_price_type = array();
        $list_price_type[] = JHTML::_('select.option', "1", $currencyCode, 'id','name');
        $list_price_type[] = JHTML::_('select.option', "2", "%", 'id','name');
        return $list_price_type;
    }
    
    public static function getPaymentType(){
        $payment_type = JSFactory::getModel("payments")->getTypes();
        $opt = array();
        foreach($payment_type as $key => $value) {
            $opt[] = JHTML::_('select.option', $key, $value, 'id', 'name');
        }
        return $opt;
    }
    
    public static function getCountryOrdering($first = 1){
        $first_option = array();
        $first_name = self::getFirstNameOption($first, _JSHOP_ORDERING_FIRST);
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'ordering', 'name');
        }
        $list = JSFactory::getModel("countries")->getAllCountries(0);
        return array_merge($first_option, $list);
    }
    
    public static function getAttributeType(){
        $types = array();
        $types[] = JHTML::_('select.option', '1','Select','attr_type_id','attr_type');
        $types[] = JHTML::_('select.option', '2','Radio','attr_type_id','attr_type');
        return $types;
    }
    
    public static function getAttributeDependent(){
        $dependent = array();
        $dependent[] = JHTML::_('select.option', '0',_JSHOP_YES,'id','name');
        $dependent[] = JHTML::_('select.option', '1',_JSHOP_NO,'id','name');
        return $dependent;
    }
    
    public static function getAttributeShowCategory(){
        $all = array();
        $all[] = JHTML::_('select.option', 1, _JSHOP_ALL, 'id','value');
        $all[] = JHTML::_('select.option', 0, _JSHOP_SELECTED, 'id','value');
        return $all;
    }
    
    public static function getProductFieldShowCategory(){
        return self::getAttributeShowCategory();
    }
    
    public static function getAttributeGroups($first = 1){
        $first_option = array();
        $first_name = self::getFirstNameOption($first, "- - -");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        $list = JSFactory::getModel("attributesgroups")->getList();
        return array_merge($first_option, $list);
    }
    
    public static function getProducts($first = 1, $ext_first = 0, $options = array()){
        $first_option = array();
        if ($ext_first){
            $first_option[] = JHTML::_('select.option', -1, "- - -", 'product_id', 'name');
        }
        $first_name = self::getFirstNameOption($first, _JSHOP_SELECT_PRODUCT);
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'product_id', 'name');
        }
        if (isset($options['filter'])){
            $filter = $options['filter'];
        }else{
            $filter = array();
        }
        $limitstart = (int)$options['limitstart'];
        $limit = (int)$options['limit'];
        $list = JSFactory::getModel("products")->getAllProducts($filter, $limitstart, $limit);
        return array_merge($first_option, $list);
    }
    
    public static function getReviewMarks($first = 1){
        $jshopConfig = JSFactory::getConfig();
        $first_option = array();
        $first_name = self::getFirstNameOption($first, "none");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'value', 'text');
        }
        
        $options = array();
        for($i=1; $i<=$jshopConfig->max_mark; $i++){
            $options[] = JHTML::_('select.option', $i, $i, 'value', 'text');
        }
        return array_merge($first_option, $options);
    }
    
    public static function getProductFieldGroups($first = 1){
        $first_option = array();
        $first_name = self::getFirstNameOption($first, "- "._JSHOP_GROUP." -");
        if ($first!==0){
            $first_option[] = JHTML::_('select.option', 0, $first_name, 'id', 'name');
        }
        $list = JSFactory::getModel("productFieldGroups")->getList();
        return array_merge($first_option, $list);
    }
    
    public static function getProductFieldTypes(){
        $type = array();
        $type[] = JHTML::_('select.option', 0, _JSHOP_LIST, 'id', 'value');
        $type[] = JHTML::_('select.option', -1, _JSHOP_MULTI_LIST, 'id', 'value');
        $type[] = JHTML::_('select.option', 1, _JSHOP_TEXT, 'id', 'value');
        return $type;
    }
    
    public static function getCouponType(){
        $type = array();
        $type[] = JHTML::_('select.option', 0, _JSHOP_COUPON_PERCENT, 'id', 'name');
        $type[] = JHTML::_('select.option', 1, _JSHOP_COUPON_ABS_VALUE, 'id', 'name');
        return $type;
    }
    
}
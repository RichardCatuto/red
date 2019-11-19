<?php
/**
* @version      4.11.0 01.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
include_once(dirname(__FILE__)."/selectoptions.php");

class JshopHelpersSelects{
	
	public static function getCountry($selected = null, $attribs = null, $name = 'country'){
		$options = JshopHelpersSelectOptions::getCountrys();
		if (is_null($attribs)){
			$attribs = self::getDataultAttribs();
		}
    return JHTML::_('select.genericlist', $options, $name, $attribs, 'country_id', 'name', $selected);
	}
	
	public static function getTitle($selected = null, $attribs = null, $name = 'title'){
		$options = JshopHelpersSelectOptions::getTitles();
		if (is_null($attribs)){
			$attribs = self::getDataultAttribs();
		}
    return JHTML::_('select.genericlist', $options, $name, $attribs, 'id', 'name', $selected);
	}
	
	public static function getClientType($selected = null, $attribs = null, $name = 'client_type'){
		$options = JshopHelpersSelectOptions::getClientTypes();
		if (is_null($attribs)){
			$attribs = self::getDataultClientTypeAttribs();
		}
    return JHTML::_('select.genericlist', $options, $name, $attribs, 'id', 'name', $selected);
	}
	
	public static function getProductsOrdering($typelist, $selected = null, $attribs = null, $name = 'order'){
		$options = JshopHelpersSelectOptions::getProductsOrdering($typelist);
		if (is_null($attribs)){
			$attribs = self::getDataultProductsOrderingAttribs();
		}
    return JHTML::_('select.genericlist', $options, $name, $attribs, 'id', 'name', $selected);
	}
	
	public static function getProductsCount($extended_value = null, $selected = null, $attribs = null, $name = 'limit'){
		$options = JshopHelpersSelectOptions::getProductsCount($extended_value);
		if (is_null($attribs)){
			$attribs = self::getDataultProductsCountAttribs();
		}
    return JHTML::_('select.genericlist', $options, $name, $attribs, 'id', 'name', $selected);
	}
	
	public static function getManufacturer($selected = null, $attribs = null, $name = 'manufacturer_id'){
		$first = JHTML::_('select.option', 0, _JSHOP_SEARCH_ALL_MANUFACTURERS, 'manufacturer_id', 'name');
        $_manufacturers = JSFactory::getTable('manufacturer', 'jshop');
        $options = $_manufacturers->getList();
		array_unshift($options, $first);
		if (is_null($attribs)){
			$attribs = self::getDataultAttribs('');
		}
        return JHTML::_('select.genericlist', $options, $name, $attribs, 'manufacturer_id', 'name', $selected);
	}
	
	public static function getCategory($selected = null, $attribs = null, $name = 'category_id'){
		$options = buildTreeCategory(1);
        $first = JHTML::_('select.option', 0, _JSHOP_SEARCH_ALL_CATEGORIES, 'category_id', 'name' );
		array_unshift($options, $first);
		
		if (is_null($attribs)){
			$attribs = self::getDataultAttribs('');
		}
        return JHTML::_('select.genericlist', $options, $name, $attribs, 'category_id', 'name', $selected);
	}
	
	public static function getSearchCategory($selected = null){
		$jshopConfig = JSFactory::getConfig();
		$attribs = self::getDataultAttribs('');
		if ($jshopConfig->admin_show_product_extra_field){
            $urlsearchcaracters = SEFLink("index.php?option=com_jshopping&controller=search&task=get_html_characteristics&ajax=1", 0, 1);
            $attribs .= " onchange='updateSearchCharacteristic(\"".$urlsearchcaracters."\",this.value);'";
        }		
		return self::getCategory($selected, $attribs);
	}
	
	public static function getFilterManufacturer($manufacturers, $selected = null, $attribs = null, $name = 'manufacturers[]'){
		$key = 'id';
		if (isset($manufacturers[0]) && isset($manufacturers[0]->manufacturer_id)){
			$key = 'manufacturer_id';
		}
		$first = JHTML::_('select.option', 0, _JSHOP_ALL, $key, 'name');        
        $options = $manufacturers;
		array_unshift($options, $first);
		if (is_null($attribs)){
			$attribs = self::getDataultFilterManufacturerAttribs();
		}
        return JHTML::_('select.genericlist', $options, $name, $attribs, $key, 'name', $selected);
	}
	
	public static function getFilterCategory($categories, $selected = null, $attribs = null, $name = 'categorys[]'){
		$key = 'id';
		if (isset($categories[0]) && isset($categories[0]->category_id)){
			$key = 'category_id';
		}
		$first = JHTML::_('select.option', 0, _JSHOP_ALL, $key, 'name');        
        $options = $categories;
		array_unshift($options, $first);
		if (is_null($attribs)){
			$attribs = self::getDataultFilterCategoryAttribs();
		}
        return JHTML::_('select.genericlist', $options, $name, $attribs, $key, 'name', $selected);
	}
	
	public static function getDataultAttribs($type = 'register'){
		$jshopConfig = JSFactory::getConfig();
		if ($type=='register'){
			return 'class = "'.$jshopConfig->registration_select_class_css.'"';
		}else{
			return 'class = "'.$jshopConfig->frontend_select_class_css.'"';
		}
	}
	
	public static function getDataultClientTypeAttribs(){
		$jshopConfig = JSFactory::getConfig();
		return 'class = "'.$jshopConfig->registration_select_class_css.'" onchange="showHideFieldFirm(this.value)"';
	}
	
	public static function getDataultProductsOrderingAttribs(){
		$jshopConfig = JSFactory::getConfig();
		return 'class = "'.$jshopConfig->frontend_select_class_css.'" onchange="submitListProductFilters()"';
	}
	
	public static function getDataultProductsCountAttribs(){
		$jshopConfig = JSFactory::getConfig();
		return 'class = "'.$jshopConfig->frontend_select_class_css.'" onchange="submitListProductFilters()"';
	}
	
	public static function getDataultFilterManufacturerAttribs(){
		$jshopConfig = JSFactory::getConfig();
		return 'class = "'.$jshopConfig->frontend_select_class_css.'" onchange="submitListProductFilters()"';
	}
	
	public static function getDataultFilterCategoryAttribs(){
		$jshopConfig = JSFactory::getConfig();
		return 'class = "'.$jshopConfig->frontend_select_class_css.'" onchange="submitListProductFilters()"';
	}
	
}
<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewVendors extends JViewLegacy
{
    function displayList($tpl=null){
        JToolBarHelper::title( _JSHOP_VENDORS, 'generic.png' );
        JToolBarHelper::addNew();
        JToolBarHelper::deleteList(_JSHOP_DELETE_ITEM_CAN_BE_USED);
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title( $this->vendor->id ? _JSHOP_VENDORS.' / '.$this->vendor->shop_name : _JSHOP_VENDORS, 'generic.png' );
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
}
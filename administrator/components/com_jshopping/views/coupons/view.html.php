<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewCoupons extends JViewLegacy{
    function displayList($tpl=null){        
        JToolBarHelper::title( _JSHOP_LIST_COUPONS, 'generic.png' ); 
        JToolBarHelper::addNew();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList(_JSHOP_DELETE."?");
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title( $temp=($this->edit) ? (_JSHOP_EDIT_COUPON) : (_JSHOP_NEW_COUPON), 'generic.png' ); 
        JToolBarHelper::save();
        JToolBarHelper::spacer();
        JToolBarHelper::apply();
        JToolBarHelper::spacer();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
}
<?php
/**
* @version      4.17.1 06.04.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewOrders extends JViewLegacy
{
    function displayList($tpl=null){        
        JToolBarHelper::title(_JSHOP_ORDER_LIST, 'generic.png');
        JToolBarHelper::addNew();
        JToolBarHelper::deleteList(_JSHOP_DELETE."?");
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title($this->order->order_number, 'generic.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
    function displayShow($tpl=null){
        JToolBarHelper::title($this->order->order_number, 'generic.png');
        JToolBarHelper::back();
		JToolBarHelper::custom('send', 'mail', 'mail', _JSHOP_SEND_MAIL, false);
		JToolBarHelper::custom('edit', 'edit', 'edit', _JSHOP_EDIT, false);
        parent::display($tpl);
    }
    function displayTrx($tpl = null){
        JToolBarHelper::title($this->order->order_number."/ "._JSHOP_TRANSACTION, 'generic.png');
        JToolBarHelper::back();
        parent::display($tpl);
    }
}
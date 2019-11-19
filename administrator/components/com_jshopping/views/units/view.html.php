<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewUnits extends JViewLegacy
{
    function displayList($tpl=null){        
        JToolBarHelper::title( _JSHOP_LIST_UNITS_MEASURE, 'generic.png' ); 
        JToolBarHelper::addNew();
        JToolBarHelper::deleteList(_JSHOP_DELETE_ITEM_CAN_BE_USED);
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title( $temp = ($this->edit) ? (_JSHOP_UNITS_MEASURE_EDIT.' / '.$this->units->{JSFactory::getLang()->get('name')}) : (_JSHOP_UNITS_MEASURE_NEW), 'generic.png' ); 
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
}
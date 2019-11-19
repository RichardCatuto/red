<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewUsergroups extends JViewLegacy
{
    function displayList($tpl=null){        
        JToolBarHelper::title( _JSHOP_USERGROUPS, 'generic.png' ); 
        JToolBarHelper::addNew();
        JToolBarHelper::deleteList(_JSHOP_DELETE_ITEM_CAN_BE_USED);
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title($this->usergroup->usergroup_id ? (_JSHOP_EDIT_USERGROUP.' / '.$this->usergroup->usergroup_name) : (_JSHOP_EDIT_USERGROUP), 'generic.png' );  
        JToolBarHelper::save();
        JToolBarHelper::spacer();
        JToolBarHelper::apply();
        JToolBarHelper::spacer();
        JToolBarHelper::cancel();        
        parent::display($tpl);
    }
}
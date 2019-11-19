<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewTaxes_ext extends JViewLegacy
{
    function displayList($tpl=null){        
        JToolBarHelper::title( _JSHOP_LIST_TAXES_EXT, 'generic.png' );
        JToolBarHelper::custom( "back", 'folder', 'folder', _JSHOP_LIST_TAXES, false);
        JToolBarHelper::addNew();
        JToolBarHelper::deleteList(_JSHOP_DELETE."?");
        parent::display($tpl);
	}
    
    function displayEdit($tpl=null){
        JToolBarHelper::title( $temp=($this->tax->id) ? (_JSHOP_EDIT_TAX_EXT) : (_JSHOP_NEW_TAX_EXT), 'generic.png' ); 
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
}
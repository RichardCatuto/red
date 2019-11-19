<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewComments extends JViewLegacy{
    function displayList($tpl=null){        
        JToolBarHelper::title( _JSHOP_LIST_PRODUCT_REVIEWS, 'generic.png' ); 
        JToolBarHelper::addNew();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList(_JSHOP_DELETE."?");
        parent::display($tpl);
	}
    function displayEdit($tpl=null){
        JToolBarHelper::title( ($this->edit) ? (_JSHOP_EDIT_REVIEW) : (_JSHOP_NEW_REVIEW), 'generic.png' ); 
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
}
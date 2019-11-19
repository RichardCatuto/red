<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewProduct_list extends JViewLegacy{
    function display($tpl=null){
        JToolBarHelper::title( _JSHOP_LIST_PRODUCT, 'generic.png' ); 
        JToolBarHelper::addNew();
        JToolBarHelper::custom('copy', 'copy', 'copy_f2.png', JText::_('JLIB_HTML_BATCH_COPY'));
        JToolBarHelper::editList('editlist');
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList(_JSHOP_DELETE."?");
        parent::display($tpl);
	}
    function displaySelectable($tpl=null){
        parent::display($tpl);
    }
}
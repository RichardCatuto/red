<?php
/**
* @version      4.16.3 28.07.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingViewAddons extends JViewLegacy
{
    function displayList($tpl=null){
        JToolBarHelper::title( _JSHOP_ADDONS, 'generic.png');
        JToolBarHelper::addNew('help');
        parent::display($tpl);
	}
    
    function displayEdit($tpl = null){        
        JToolBarHelper::title(_JSHOP_ADDONS." / "._JSHOP_CONFIG.' / '.$this->row->name, 'generic.png' );
        JToolBarHelper::save();
        JToolBarHelper::spacer();
        JToolBarHelper::apply();
        JToolBarHelper::spacer();
        JToolBarHelper::cancel();        
        parent::display($tpl);
    }
    
    function displayInfo($tpl = null){        
        JToolBarHelper::title(_JSHOP_ADDONS." / "._JSHOP_DESCRIPTION.' / '.$this->row->name, 'generic.png' );
        JToolBarHelper::cancel();        
        parent::display($tpl);
    }
    
    function displayVersion($tpl = null){        
        JToolBarHelper::title(_JSHOP_ADDONS." / "._JSHOP_VERSION.' / '.$this->row->name, 'generic.png' );
        JToolBarHelper::cancel();        
        parent::display($tpl);
    }

    function displayHelp($tpl = null){
        JToolBarHelper::title(_JSHOP_ADDONS, 'generic.png');
        JToolBarHelper::cancel();
        parent::display($tpl);
    }
    
}
<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined( '_JEXEC' ) or die();

class JshoppingControllerLanguages extends JshoppingControllerBaseadmin{
    
    function __construct($config = array()){
        parent::__construct( $config );
        checkAccessController("languages");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $languages = JSFactory::getModel("languages");
        $rows = $languages->getAllLanguages(0);
        $jshopConfig = JSFactory::getConfig();        
                
		$view = $this->getView("languages_list", 'html');
        $view->assign('rows', $rows);
        $view->assign('default_front', $jshopConfig->getFrontLang());
        $view->assign('defaultLanguage', $jshopConfig->defaultLanguage);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayLanguage', array(&$view));
		$view->display();
    }
        
}
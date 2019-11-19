<?php
/**
* @version      4.14.0 20.01.2011
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerLicenseKeyAddon extends JControllerLegacy{
    
    function __construct($config = array()){
        parent::__construct( $config );
        $this->registerTask( 'add',   'edit' );
        $this->registerTask( 'apply', 'save' );
        checkAccessController("licensekeyaddon");
        addSubmenu("other");        
    }

	function display($cachable = false, $urlparams = false){
        $alias = $this->input->getVar("alias");
		$back = $this->input->getVar("back");
		$addon = JSFactory::getTable('addon', 'jshop');
		$addon->loadAlias($alias);		

		$view = $this->getView("addonkey", 'html');
        $view->assign('row', $addon);
        $view->assign('back', $back);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayLicenseKeyAddons', array(&$view));
		$view->display();
	}
	
	function save(){
        $addon = JSFactory::getTable('addon', 'jshop');
        $post = $this->input->post->getArray();
		$addon->bind($post);
		if (!$addon->store()) {
			JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE);
			$this->setRedirect("index.php?option=com_jshopping");
			return 0;
		}
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterSaveLicenseKeyAddons', array(&$addon));
        $this->setRedirect(base64_decode($post['back']));
	}
    
    function cancel(){
        $post = $this->input->post->getArray();
        $this->setRedirect(base64_decode($post['back']));
    }
}
<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerShippingExtPrice extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("shippingextprice");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
		$shippings = JSFactory::getModel("shippingExtPrice");
		$rows = $shippings->getList();

		$view = $this->getView("shippingext", 'html');
        $view->setLayout("list");
		$view->assign('rows', $rows);
        $view->sidebar = JHtmlSidebar::render();

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayShippingExtPrices', array(&$view));
		$view->displayList();
	}

	function edit() {
		$id = $this->input->getInt("id");
        $row = JSFactory::getTable('shippingExt', 'jshop');
        $row->load($id);

        if (!$row->exec) {
            JError::raiseError( 404, "Error load ShippingExt");
        }

        $shippings_conects = $row->getShippingMethod();

        $shippings = JSFactory::getModel("shippings");
        $list_shippings = $shippings->getAllShippings(0);

        $nofilter = array("params", "shipping_method");
        JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, $nofilter);

        $view = $this->getView("shippingext", 'html');
        $view->setLayout("edit");
        $view->assign('row', $row);
        $view->assign('list_shippings', $list_shippings);
        $view->assign('shippings_conects', $shippings_conects);

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditShippingExtPrice', array(&$view));
        $view->displayEdit();
	}

    function remove(){
        $id = $this->input->getInt("id");
        JSFactory::getModel("shippingextprice")->delete($id);
        $this->setRedirect("index.php?option=com_jshopping&controller=shippingextprice", _JSHOP_ITEM_DELETED);
    }

    function back(){
        $this->setRedirect("index.php?option=com_jshopping&controller=shippings");
    }

}
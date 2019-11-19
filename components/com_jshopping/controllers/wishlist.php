<?php
/**
* @version      4.13.0 10.10.2012
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class JshoppingControllerWishlist extends JshoppingControllerBase{
    
    function __construct($config = array()){
        parent::__construct($config);
        JPluginHelper::importPlugin('jshoppingcheckout');
        JDispatcher::getInstance()->trigger('onConstructJshoppingControllerWishlist', array(&$this));
    }

    function display($cachable = false, $urlparams = false){
        $this->view();
    }

    function view(){		
	    $jshopConfig = JSFactory::getConfig();        
        $ajax = $this->input->getInt('ajax');
		$dispatcher = JDispatcher::getInstance();
		$cartpreview = JSFactory::getModel('cartPreview', 'jshop');

		$cart = JSFactory::getModel('cart', 'jshop')->init("wishlist", 1);		

		JshopHelpersMetadata::wishlist();
		
		$cartpreview->setCart($cart);
		$cartpreview->setCheckoutStep(0);
        $shopurl = $cartpreview->getBackUrlShop();

        $view = $this->getView('cart');
        $view->setLayout("wishlist");
        $view->assign('config', $jshopConfig);
		$view->assign('products', $cartpreview->getProducts());
		$view->assign('image_product_path', $jshopConfig->image_product_live_path);
		$view->assign('image_path', $jshopConfig->live_path);
		$view->assign('no_image', $jshopConfig->noimage);
		$view->assign('href_shop', $shopurl);
		$view->assign('href_checkout', SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1));
        $dispatcher->trigger('onBeforeDisplayWishlistView', array(&$view));
		$view->display();
        if ($ajax) die();
    }

    function delete(){
        header("Cache-Control: no-cache, must-revalidate");
        $ajax = $this->input->getInt('ajax');
        $cart = JSFactory::getModel('cart', 'jshop');
        $cart->load('wishlist');    
        $cart->delete($this->input->getInt('number_id'));
        if ($ajax){
            print getOkMessageJson($cart);
            die();
        }
        $this->setRedirect( SEFLink($cart->getUrlList(),0,1) );
    }

    function remove_to_cart(){
        header("Cache-Control: no-cache, must-revalidate");
        $ajax = $this->input->getInt('ajax');
        $number_id = $this->input->getInt('number_id');
		
        $cart = JSFactory::getModel('checkout', 'jshop')->removeWishlistItemToCart($number_id);
		
        if ($ajax){
            print getOkMessageJson($cart);
            die();
        }
        $this->setRedirect( SEFLink('index.php?option=com_jshopping&controller=cart&task=view',1,1) );
    }
}
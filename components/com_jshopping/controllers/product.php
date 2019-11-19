<?php
/**
* @version      4.17.0 24.11.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
jimport('joomla.html.pagination');

class JshoppingControllerProduct extends JshoppingControllerBase{
    
    function __construct($config = array()){
        parent::__construct($config);
        JPluginHelper::importPlugin('jshoppingproducts');
        JDispatcher::getInstance()->trigger('onConstructJshoppingControllerProduct', array(&$this));
    }

    function display($cachable = false, $urlparams = false){
        $jshopConfig = JSFactory::getConfig();
        $user = JFactory::getUser();
		$dispatcher = JDispatcher::getInstance();
		$model = JSFactory::getModel('productShop', 'jshop');
		
		$ajax = $this->input->getInt('ajax');
        $tmpl = $this->input->getVar("tmpl");
		$product_id = (int)$this->input->getInt('product_id');
        $category_id = (int)$this->input->getInt('category_id');
        $attr = $this->input->getVar("attr");
		
		JSFactory::loadJsFilesLightBox();
		
        if ($tmpl!="component"){
			$model->storeEndPageBuy();
        }
		
        $back_value = $model->getBackValue($product_id, $attr);

        $dispatcher->trigger('onBeforeLoadProduct', array(&$product_id, &$category_id, &$back_value));
        $dispatcher->trigger('onBeforeLoadProductList', array());

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
		
		$category = JSFactory::getTable('category', 'jshop');
        $category->load($category_id);
        $category->name = $category->getName();
		
		$model->setProduct($product);
		
        $listcategory = $model->getCategories(1);
		
		$model->prepareView($back_value);
		$model->clearBackValue();
		
		$attributes = $model->getAttributes();
        $all_attr_values = $model->getAllAttrValues();

		if (!$product->checkView($category, $user, $category_id, $listcategory)){
            JError::raiseError(404, _JSHOP_PAGE_NOT_FOUND);
            return;
        }
        
        JshopHelpersMetadata::product($category, $product);
        
        $product->hit();
        		
		$allow_review = $model->getAllowReview();
		$text_review = $model->getTextReview();
		$select_review = $model->getSelectReview();

        $hide_buy = $model->getHideBuy();        
        $available = $model->getTextAvailable();
		$default_count_product = $model->getDefaultCountProduct($back_value);
        $displaybuttons = $model->getDisplayButtonsStyle();
        $product_images = $product->getImages();
        $product_videos = $product->getVideos();
        $product_demofiles = $product->getDemoFiles();
		
		$dispatcher->trigger('onBeforeDisplayProductList', array(&$product->product_related));
        
        $view = $this->getView("product");
        $view->setLayout("product_".$product->product_template);
        $dispatcher->trigger('onBeforeDisplayProduct', array(&$product, &$view, &$product_images, &$product_videos, &$product_demofiles) );
        $view->assign('config', $jshopConfig);
        $view->assign('image_path', $jshopConfig->live_path.'/images');
        $view->assign('noimage', $jshopConfig->noimage);
        $view->assign('image_product_path', $jshopConfig->image_product_live_path);
        $view->assign('video_product_path', $jshopConfig->video_product_live_path);
        $view->assign('video_image_preview_path', $jshopConfig->video_product_live_path);
        $view->assign('product', $product);
        $view->assign('category_id', $category_id);
        $view->assign('images', $product_images);
        $view->assign('videos', $product_videos);
        $view->assign('demofiles', $product_demofiles);
        $view->assign('attributes', $attributes);
        $view->assign('all_attr_values', $all_attr_values);
        $view->assign('related_prod', $product->product_related);
        $view->assign('path_to_image', $jshopConfig->live_path . 'images/');
        $view->assign('live_path', JURI::root());
        $view->assign('enable_wishlist', $jshopConfig->enable_wishlist);
        $view->assign('action', SEFLink('index.php?option=com_jshopping&controller=cart&task=add',1));
        $view->assign('urlupdateprice', SEFLink('index.php?option=com_jshopping&controller=product&task=ajax_attrib_select_and_price&product_id='.$product_id.'&ajax=1',1,1));
        if ($allow_review){
			JSFactory::loadJsFilesRating();
			$modelreviewlist = JSFactory::getModel('productReviewList', 'jshop');
			$modelreviewlist->setModel($product);
			$modelreviewlist->load();
			$review_list = $modelreviewlist->getList();
			$pagination = $modelreviewlist->getPagination();
			$pagenav = $pagination->getPagesLinks();
            $view->assign('reviews', $review_list);
            $view->assign('pagination', $pagenav);
			$view->assign('pagination_obj', $pagination);
            $view->assign('display_pagination', $pagenav!="");
        }
        $view->assign('allow_review', $allow_review);
        $view->assign('select_review', $select_review);
        $view->assign('text_review', $text_review);
        $view->assign('stars_count', floor($jshopConfig->max_mark / $jshopConfig->rating_starparts));
        $view->assign('parts_count', $jshopConfig->rating_starparts);
        $view->assign('user', $user);
        $view->assign('shippinginfo', SEFLink($jshopConfig->shippinginfourl,1));
        $view->assign('hide_buy', $hide_buy);
        $view->assign('available', $available);
        $view->assign('default_count_product', $default_count_product);
        $view->assign('folder_list_products', "list_products");
        $view->assign('back_value', $back_value);
		$view->assign('displaybuttons', $displaybuttons);
        $dispatcher->trigger('onBeforeDisplayProductView', array(&$view));
        $view->display();
        $dispatcher->trigger('onAfterDisplayProduct', array(&$product));
        if ($ajax) die();
    }
    
    function getfile(){
        $id = $this->input->getInt('id'); 
        $oid = $this->input->getInt('oid');
        $hash = $this->input->getVar('hash');
        $rl = $this->input->getInt('rl');
		
		$model = JSFactory::getModel('productDownload', 'jshop');
		$model->setId($id);
		$model->setOid($oid);
		$model->setHash($hash);
		
		if ($rl==1){
            //fix for IE
            print "<script type='text/javascript'>location.href='".$model->getUrlDownload()."';</script>";
            die();
        }
		
		if (!$model->checkHash()){
			JError::raiseError(500, "Error download file");
            return 0;
		}
		if (!$model->checkOrderStatusPaid()){
            JError::raiseWarning(500, _JSHOP_FOR_DOWNLOAD_ORDER_MUST_BE_PAID);
            return 0;
        }
		if (!$model->checkUser()){
            checkUserLogin();
        }
		if (!$model->checkTimeDownload()){
            JError::raiseWarning(500, _JSHOP_TIME_DOWNLOADS_FILE_RESTRICTED);
            return 0; 
        }
		if (!$model->checkFileId()){
			JError::raiseError(500, "Error download file");
            return 0;
		}
		if (!$model->checkNumberDownload()){
			JError::raiseWarning(500, _JSHOP_NUMBER_DOWNLOADS_FILE_RESTRICTED);
            return 0;
		}

        $name = $model->getFileName();
        if ($name==""){
            JError::raiseWarning('', "Error download file");
            return 0;
        }
        $file_name = $model->getFile($name);

		$model->storeStatDownloads();
        
        ob_end_clean();
        @set_time_limit(0);		
		$model->downloadFile($file_name);
        JDispatcher::getInstance()->trigger('onAfterDownloadProductFile', array($file_name, $id, $oid, $hash, $model));
        die();
    }
    
    function reviewsave(){
        JSession::checkToken() or die('Invalid Token');

        $post = $this->input->post->getArray();
        $backlink = $this->input->getVar('back_link');
		
		$model = JSFactory::getModel('productReview', 'jshop');
		$model->setData($post);
		if (!$model->checkAllow()){
			JError::raiseWarning('', $model->getError());
            $this->setRedirect($backlink);
            return 0;
		}
		if (!$model->check()){
			JError::raiseWarning('', $model->getError());
            $this->setRedirect($backlink);
            return 0;
		}
		$model->save();
		
		$model->mailSend();
		
		if (JSFactory::getConfig()->display_reviews_without_confirm){
            $this->setRedirect($backlink, _JSHOP_YOUR_REVIEW_SAVE_DISPLAY);
        }else{
            $this->setRedirect($backlink, _JSHOP_YOUR_REVIEW_SAVE);
        }
    }

	function ajax_attrib_select_and_price(){
		$request = $this->input->getArray();
        $product_id = $this->input->getInt('product_id');
        $change_attr = $this->input->getInt('change_attr');
		$qty = JshopHelpersRequest::getQuantity('qty', 1);
		$attribs = JshopHelpersRequest::getAttribute('attr');
        $freeattr = JshopHelpersRequest::getFreeAttribute('freeattr');
		
		$model = JSFactory::getModel('productAjaxRequest', 'jshop');
		$model->setData($product_id, $change_attr, $qty, $attribs, $freeattr, $request);
		print $model->getProductDataJson();
		die();
	}

    function showmedia(){
        $jshopConfig = JSFactory::getConfig();
        $media_id = $this->input->getInt('media_id');
        $file = JSFactory::getTable('productfiles', 'jshop');
        $file->load($media_id);

        $scripts_load = '<script type="text/javascript" src="'.JURI::root().'media/jui/js/jquery.min.js"></script>';
        $scripts_load .= '<script type="text/javascript" src="'.JURI::root().'components/com_jshopping/js/jquery/jquery.media.js"></script>';

        $view = $this->getView("product");
        $view->setLayout("playmedia");
        $view->assign('config', $jshopConfig);
        $view->assign('filename', $file->demo);
        $view->assign('description', $file->demo_descr);
        $view->assign('scripts_load', $scripts_load);
        $view->assign('file_is_video', $file->fileDemoIsVideo());
		$view->assign('file_is_audio', $file->fileDemoIsAudio());
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayProductShowMediaView', array(&$view) );
        $view->display(); 
        die();
    }
}
<?php
/**
* @version      4.14.0 10.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerConfig extends JControllerLegacy{
    
    function __construct($config = array()){
        parent::__construct($config);
        $this->registerTask('apply', 'save');
        $this->registerTask('applyseo', 'saveseo');
        $this->registerTask('applystatictext', 'savestatictext');
        checkAccessController("config");
        addSubmenu("config");        
    }

    function display($cachable = false, $urlparams = false){
        $jshopConfig = JSFactory::getConfig();        
        $current_currency = JSFactory::getTable('currency', 'jshop');
        $current_currency->load($jshopConfig->mainCurrency);
        if ($current_currency->currency_value!=1){
            JError::raiseWarning("", _JSHOP_ERROR_MAIN_CURRENCY_VALUE);    
        }
        $view=$this->getView("panel", 'html');
        $view->setLayout("config");
        $view->sidebar = JHtmlSidebar::render();
        $view->displayConfig();
    }
    
    function general(){
	    $jshopConfig = JSFactory::getConfig();

        $lists['languages'] = JHTML::_('select.genericlist', getAllLanguages(), 'defaultLanguage', '', 'language', 'name', $jshopConfig->defaultLanguage);
        $display_price_list = JshopHelpersSelectOptions::getPriceType();
        $lists['display_price_admin'] = JHTML::_('select.genericlist', $display_price_list, 'display_price_admin', '', 'id', 'name', $jshopConfig->display_price_admin);
        $lists['display_price_front'] = JHTML::_('select.genericlist', $display_price_list, 'display_price_front', '', 'id', 'name', $jshopConfig->display_price_front);
        $lists['template'] = getShopTemplatesSelect($jshopConfig->template);

    	$view = $this->getView("config", 'html');
        $view->setLayout("general");
        $view->assign('etemplatevar', '');
		$view->assign("lists", $lists);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigGeneral', array(&$view));
        $view->display();
    }
    
    function catprod(){
        $jshopConfig = JSFactory::getConfig();
        
        $displayprice = array();
        $displayprice[] = JHTML::_('select.option', 0, _JSHOP_YES, 'id', 'value');
        $displayprice[] = JHTML::_('select.option', 1, _JSHOP_NO, 'id', 'value');
        $displayprice[] = JHTML::_('select.option', 2, _JSHOP_ONLY_REGISTER_USER, 'id', 'value');
        $lists['displayprice'] = JHTML::_('select.genericlist', $displayprice, 'displayprice','','id','value', $jshopConfig->displayprice);
        
        $catsort = array();
        $catsort[] = JHTML::_('select.option', 1, _JSHOP_SORT_MANUAL, 'id','value');
        $catsort[] = JHTML::_('select.option', 2, _JSHOP_SORT_ALPH, 'id','value');
        $lists['category_sorting'] = JHTML::_('select.genericlist', $catsort, 'category_sorting','','id','value', $jshopConfig->category_sorting);
        $lists['manufacturer_sorting'] = JHTML::_('select.genericlist', $catsort, 'manufacturer_sorting','','id','value', $jshopConfig->manufacturer_sorting);
        
        $sortd = array();
        $sortd[] = JHTML::_('select.option', 0, _JSHOP_A_Z, 'id','value');
        $sortd[] = JHTML::_('select.option', 1, _JSHOP_Z_A, 'id','value');
        $lists['product_sorting_direction'] = JHTML::_('select.genericlist', $sortd, 'product_sorting_direction','','id','value', $jshopConfig->product_sorting_direction);
        
        $opt = array();
        $opt[] = JHTML::_('select.option', 'V.value_ordering', _JSHOP_SORT_MANUAL, 'id','value');
        $opt[] = JHTML::_('select.option', 'value_name', _JSHOP_SORT_ALPH, 'id','value');
        $opt[] = JHTML::_('select.option', 'PA.price', _JSHOP_SORT_PRICE, 'id','value');
        $opt[] = JHTML::_('select.option', 'PA.ean', _JSHOP_EAN_PRODUCT, 'id','value');
        $opt[] = JHTML::_('select.option', 'PA.count', _JSHOP_QUANTITY_PRODUCT, 'id','value');
        $opt[] = JHTML::_('select.option', 'PA.product_attr_id', _JSHOP_SPECIFIED_IN_PRODUCT, 'id','value');
        $lists['attribut_dep_sorting_in_product'] = JHTML::_('select.genericlist', $opt, 'attribut_dep_sorting_in_product','','id','value', $jshopConfig->attribut_dep_sorting_in_product);
        
        $opt = array();
        $opt[] = JHTML::_('select.option', 'V.value_ordering', _JSHOP_SORT_MANUAL, 'id','value');
        $opt[] = JHTML::_('select.option', 'value_name', _JSHOP_SORT_ALPH, 'id','value');
        $opt[] = JHTML::_('select.option', 'addprice', _JSHOP_SORT_PRICE, 'id','value');
        $opt[] = JHTML::_('select.option', 'PA.id', _JSHOP_SPECIFIED_IN_PRODUCT, 'id','value');
        $lists['attribut_nodep_sorting_in_product'] = JHTML::_('select.genericlist', $opt, 'attribut_nodep_sorting_in_product','','id','value', $jshopConfig->attribut_nodep_sorting_in_product);        
        
        $select = array();        
        foreach($jshopConfig->sorting_products_name_select as $key => $value){
            $select[] = JHTML::_('select.option', $key, $value, 'id', 'value');            
        }
        $lists['product_sorting'] = JHTML::_('select.genericlist',$select, "product_sorting", '', 'id','value', $jshopConfig->product_sorting);
        
        if ($jshopConfig->admin_show_product_extra_field){
            $_productfields = JSFactory::getModel("productFields");
            $rows = $_productfields->getList();
            $lists['product_list_display_extra_fields'] = JHTML::_('select.genericlist', $rows, "product_list_display_extra_fields[]", ' size="10" multiple = "multiple" ', 'id','name', $jshopConfig->getProductListDisplayExtraFields() );
            $lists['filter_display_extra_fields'] = JHTML::_('select.genericlist', $rows, "filter_display_extra_fields[]", ' size="10" multiple = "multiple" ', 'id','name', $jshopConfig->getFilterDisplayExtraFields() );
            $lists['product_hide_extra_fields'] = JHTML::_('select.genericlist', $rows, "product_hide_extra_fields[]", ' size="10" multiple = "multiple" ', 'id','name', $jshopConfig->getProductHideExtraFields() );
            $lists['cart_display_extra_fields'] = JHTML::_('select.genericlist', $rows, "cart_display_extra_fields[]", ' size="10" multiple = "multiple" ', 'id','name', $jshopConfig->getCartDisplayExtraFields() );
        }

        $lists['units'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getUnits(), "main_unit_weight", '', 'id','name', $jshopConfig->main_unit_weight);        
            
        $view = $this->getView("config", 'html');
        $view->setLayout("categoryproduct");
        $view->assign("lists", $lists);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigCatProd', array(&$view));
        $view->display();
    }
    
    function checkout(){
        $jshopConfig = JSFactory::getConfig();

        $lists['status'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getOrderStatus(), 'default_status_order', 'class = "inputbox"', 'status_id', 'name', $jshopConfig->default_status_order);
        $currency_code = getMainCurrencyCode();        
        $lists['default_country'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(3), 'default_country','class = "inputbox"','country_id','name', $jshopConfig->default_country);
        
        $vendor_order_message_type = array();
        $vendor_order_message_type[] = JHTML::_('select.option', 0, _JSHOP_NOT_SEND_MESSAGE, 'id', 'name' );
        $vendor_order_message_type[] = JHTML::_('select.option', 1, _JSHOP_WE_SEND_MESSAGE, 'id', 'name' );
        $vendor_order_message_type[] = JHTML::_('select.option', 2, _JSHOP_WE_SEND_ORDER, 'id', 'name' );
        $vendor_order_message_type[] = JHTML::_('select.option', 3, _JSHOP_WE_ALWAYS_SEND_ORDER, 'id', 'name' );
        $lists['vendor_order_message_type'] = JHTML::_('select.genericlist', $vendor_order_message_type, 'vendor_order_message_type','class = "inputbox" size = "1"','id','name', $jshopConfig->vendor_order_message_type);
        
		$option = array();
        $option[] = JHTML::_('select.option', 0, _JSHOP_STEP_3_4, 'id', 'name');
        $option[] = JHTML::_('select.option', 1, _JSHOP_STEP_4_3, 'id', 'name');
        $lists['step_4_3'] = JHTML::_('select.genericlist', $option, 'step_4_3','class = "inputbox"','id','name', $jshopConfig->step_4_3);

        $view = $this->getView("config", 'html');
        $view->setLayout("checkout");
        $view->assign("lists", $lists); 
        $view->assign("currency_code", $currency_code);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigCheckout', array(&$view));
        $view->display();
    }

    function fieldregister(){
        $jshopConfig = JSFactory::getConfig();
        $view = $this->getView("config", 'html');
        $view->setLayout("fieldregister");
        $config = new stdClass();
        include($jshopConfig->path.'lib/default_config.php');

        $current_fields = $jshopConfig->getListFieldsRegister();
        $view->assign("fields", $fields_client);
        $view->assign("current_fields", $current_fields);
        $view->assign("fields_sys", $fields_client_sys);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigFieldRegister', array(&$view));
        $view->display();
    }

    function adminfunction(){
        $jshopConfig = JSFactory::getConfig();
        $shop_register_type = array();
        $shop_register_type[] = JHTML::_('select.option', 0, "-", 'id', 'name' );
        $shop_register_type[] = JHTML::_('select.option', 1, _JSHOP_MEYBY_SKIP_REGISTRATION, 'id', 'name' );
        $shop_register_type[] = JHTML::_('select.option', 2, _JSHOP_WITHOUT_REGISTRATION, 'id', 'name' );
        $lists['shop_register_type'] = JHTML::_('select.genericlist', $shop_register_type, 'shop_user_guest','class = "inputbox" size = "1"','id','name', $jshopConfig->shop_user_guest);
        
        $opt = array();
        $opt[] = JHTML::_('select.option', 0, _JSHOP_NORMAL, 'id', 'name');
        $opt[] = JHTML::_('select.option', 1, _JSHOP_DEVELOPER, 'id', 'name');
        $lists['shop_mode'] = JHTML::_('select.genericlist', $opt, 'shop_mode','class = "inputbox"','id','name', $jshopConfig->shop_mode);

        $view = $this->getView("config", 'html');
        $view->setLayout("adminfunction");
        $view->assign("lists", $lists);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigAdminFunction', array(&$view));
        $view->display();
    }

    function currency(){
    	$jshopConfig = JSFactory::getConfig();
		
		$lists['currencies'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCurrencies(), 'mainCurrency','class = "inputbox"','currency_id','currency_code',$jshopConfig->mainCurrency);
		
		$i = 0;
		foreach($jshopConfig->format_currency as $key => $value){
            $currenc[$i] = new stdClass();
			$currenc[$i]->id_cur = $key;
			$currenc[$i]->format = $value;
			$i++;
		}
		$lists['format_currency'] = JHTML::_('select.genericlist', $currenc, 'currency_format','class = "inputbox"', 'id_cur', 'format', $jshopConfig->currency_format);
				
        $view = $this->getView("config", 'html');
        $view->setLayout("currency");
		$view->assign("lists", $lists);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigCurrency', array(&$view));
        $view->display();
    }
    
    function image(){
        $jshopConfig = JSFactory::getConfig();
        
        $resize_type = array();
        $resize_type[] = JHTML::_('select.option', 0, _JSHOP_CUT, 'id', 'name' );
        $resize_type[] = JHTML::_('select.option', 1, _JSHOP_FILL, 'id', 'name' );
        $resize_type[] = JHTML::_('select.option', 2, _JSHOP_STRETCH, 'id', 'name' );
        $select_resize_type = JHTML::_('select.genericlist', $resize_type, 'image_resize_type','class = "inputbox"','id','name', $jshopConfig->image_resize_type);
    	
    	$view = $this->getView("config", 'html');
        $view->setLayout("image");
        $view->assign("select_resize_type", $select_resize_type);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigImage', array(&$view));
        $view->display();
    }
    
    function storeinfo(){
        $vendor = JSFactory::getTable('vendor', 'jshop');
        $vendor->loadMain();
		$lists['countries'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCountrys(3), 'country', 'class = "inputbox"', 'country_id', 'name', $vendor->country);
        
        JFilterOutput::objectHTMLSafe($vendor, ENT_QUOTES);
        
    	$view = $this->getView("config", 'html');
        $view->setLayout("storeinfo");
        $view->assign("lists", $lists); 
		$view->assign("vendor", $vendor);
        $view->assign('etemplatevar', '');
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeEditConfigStoreInfo', array(&$view));
        $view->display();
    }
    
    function save(){
	    $jshopConfig = JSFactory::getConfig();
		$tab = $this->input->getVar('tab');
		$db = JFactory::getDBO();
        
        $dispatcher = JDispatcher::getInstance();
		$extconf = array('imageheader'=>'header.jpg', 'imagefooter'=>'footer.jpg');	
		$post = $this->input->post->getArray();
        $dispatcher->trigger('onBeforeSaveConfig', array(&$post, &$extconf));
	
        //general
		$array = array('display_price_admin', 'display_price_front','use_ssl','savelog','savelogpaymentdata');
		if ($tab == 1){
			foreach ($array as $key => $value) {
				if (!isset($post[$value])) $post[$value] = 0;
			}
		}
        
        if ($tab==3){
            $result = array();
            if ($jshopConfig->other_config!=''){
                $result = unserialize($jshopConfig->other_config);
            }
            $config = new stdClass();
            include($jshopConfig->path.'lib/default_config.php');
            foreach($image_other_config as $k){
                $result[$k] = $post[$k];
            }
            $post['other_config'] = serialize($result);
        }
        
        if ($tab == 5){
            $vendor = JSFactory::getTable('vendor', 'jshop');
            $post = $this->input->post->getArray();
            $vendor->id = $post['vendor_id'];
            $vendor->main = 1;
            $vendor->bind($post);
            $vendor->store();
        }
        
        //category/product
        $array = array('show_buy_in_category','show_tax_in_product','show_tax_product_in_cart','show_plus_shipping_in_product','hide_product_not_avaible_stock','hide_buy_not_avaible_stock','show_sort_product','show_count_select_products','show_delivery_time','demo_type','product_show_manufacturer_logo','product_show_weight',
                       'product_attribut_first_value_empty', 'show_hits', 'allow_reviews_prod', 'allow_reviews_only_registered','hide_text_product_not_available','use_plugin_content', 'product_list_show_weight', 'product_list_show_manufacturer','show_product_code','product_list_show_min_price', 'show_product_list_filters',
                       'product_list_show_vendor','product_show_vendor','product_show_vendor_detail','product_show_button_back','product_list_show_product_code','radio_attr_value_vertical','attr_display_addprice','product_list_show_price_description','display_button_print','product_list_show_price_default');
        if ($tab == 6){
            foreach ($array as $key => $value) {
                if (!isset($post[$value])) $post[$value] = 0;
            }
            $result = array();
            if ($jshopConfig->other_config!=''){
                $result = unserialize($jshopConfig->other_config);
            }
            $config = new stdClass();
            include($jshopConfig->path.'lib/default_config.php');
            foreach($catprod_other_config as $k){
                $result[$k] = $post[$k];
            }
            $post['other_config'] = serialize($result);
        }
        
        //case
        $array = array('hide_shipping_step', 'hide_payment_step', 'order_send_pdf_client','order_send_pdf_admin','hide_tax', 'show_registerform_in_logintemplate','sorting_country_in_alphabet','show_weight_order', 'discount_use_full_sum','show_cart_all_step_checkout',"show_product_code_in_cart",'show_return_policy_in_email_order',
                        'client_allow_cancel_order', 'admin_not_send_email_order_vendor_order','not_redirect_in_cart_after_buy','calcule_tax_after_discount');
        if ($tab == 7){
            if (!$post['next_order_number']){
                unset($post['next_order_number']);
            }
            foreach($array as $key=>$value){
                if (!isset($post[$value])) $post[$value] = 0;
            }
            $result = array();
            if ($jshopConfig->other_config!=''){
                $result = unserialize($jshopConfig->other_config);
            }
            $config = new stdClass();
            include($jshopConfig->path.'lib/default_config.php');
            foreach($checkout_other_config as $k){
                $result[$k] = $post[$k];
            }
            $post['other_config'] = serialize($result);
        }
        
        //shop function
        $array = array('without_shipping', 'without_payment', 'enable_wishlist', 'shop_user_guest','user_as_catalog', 'use_rabatt_code', 'admin_show_product_basic_price','admin_show_attributes','admin_show_delivery_time','admin_show_languages','use_different_templates_cat_prod','admin_show_product_video','admin_show_product_related','admin_show_product_files','admin_show_product_bay_price','admin_show_product_basic_price', 'admin_show_product_labels', 'admin_show_product_extra_field','admin_show_vendors','admin_show_freeattributes','use_extend_attribute_data');
        if ($tab == 8){
            foreach($array as $key => $value){
                if (!isset($post[$value])) $post[$value] = 0;
            }
            
            $post['without_shipping'] = intval(!$post['without_shipping']);
            $post['without_payment'] = intval(!$post['without_payment']);
            
            $result = array();
            if ($jshopConfig->other_config!=''){
                $result = unserialize($jshopConfig->other_config);
            }
            $config = new stdClass();
            include($jshopConfig->path.'lib/default_config.php');
            foreach($adminfunction_other_config as $k){
                $result[$k] = $post[$k];
            }
            $post['other_config'] = serialize($result);
        }
        
        if ($tab == 9){
            $config = new stdClass();
            include($jshopConfig->path.'lib/default_config.php');
                        
            foreach($fields_client_sys as $k=>$v){
                foreach($v as $v2){
                    $post['field'][$k][$v2]['require'] = 1;
                    $post['field'][$k][$v2]['display'] = 1;
                }
            }
            foreach($post['field'] as $k=>$v){
                foreach($v as $k2=>$v2){
                    if (!$post['field'][$k][$k2]['display']){
                        $post['field'][$k][$k2]['require'] = 0;
                    }
                }
            }

            $post['fields_register'] = serialize($post['field']);
        }
		
		if ($tab == 10){
			$result = array();
            $config = new stdClass();
			include($jshopConfig->path.'lib/default_config.php');
			if ($jshopConfig->other_config!=''){
                $result = unserialize($jshopConfig->other_config);
            }
			foreach($other_config as $k){
				$result[$k] = $post[$k];
			}
			$post['other_config'] = serialize($result);
		}

        if ($tab != 4){
		    $config = new jshopConfig($db);
		    $config->id = $jshopConfig->load_id;
		    if (!$config->bind($post)) {
			    JError::raiseWarning("",_JSHOP_ERROR_BIND);
			    $this->setRedirect('index.php?option=com_jshopping&controller=config');
			    return 0;
		    }
            
            if ($tab==6 && $jshopConfig->admin_show_product_extra_field){
                if (!isset($post['product_list_display_extra_fields'])) $post['product_list_display_extra_fields'] = array();
                if (!isset($post['filter_display_extra_fields'])) $post['filter_display_extra_fields'] = array();
                if (!isset($post['product_hide_extra_fields'])) $post['product_hide_extra_fields'] = array();
                if (!isset($post['cart_display_extra_fields'])) $post['cart_display_extra_fields'] = array();
                $config->setProductListDisplayExtraFields($post['product_list_display_extra_fields']);
                $config->setFilterDisplayExtraFields($post['filter_display_extra_fields']);
                $config->setProductHideExtraFields($post['product_hide_extra_fields']);
                $config->setCartDisplayExtraFields($post['cart_display_extra_fields']);
            }
		    
		    $config->transformPdfParameters();				
        	    
		    if (!$config->store()) {
			    JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE." ".$config->_error);
			    $this->setRedirect('index.php?option=com_jshopping&controller=config');
			    return 0;
		    }            
        }

		if (isset($_FILES['header'])){
			if ($_FILES['header']['size']){
				@unlink($jshopConfig->path."images/".$extconf['imageheader']);
				move_uploaded_file($_FILES['header']['tmp_name'], $jshopConfig->path."images/".$extconf['imageheader']);
			}
		}
	
		if (isset($_FILES['footer'])){
			if ($_FILES['footer']['size']){
				@unlink($jshopConfig->path."images/".$extconf['imagefooter']);
				move_uploaded_file($_FILES['footer']['tmp_name'], $jshopConfig->path."images/".$extconf['imagefooter']);
			}
		}
        
        if (isset($post['update_count_prod_rows_all_cats']) && $tab==6 && $post['update_count_prod_rows_all_cats']){
            $count_products_to_page = intval($post['count_products_to_page']);
            $count_products_to_row = intval($post['count_products_to_row']);
            $query = "update `#__jshopping_categories` set `products_page`='".$count_products_to_page."', `products_row`='".$count_products_to_row."'";
            $db->setQuery($query);
            $db->query();
            $query = "update `#__jshopping_manufacturers` set `products_page`='".$count_products_to_page."', `products_row`='".$count_products_to_row."'";
            $db->setQuery($query);
            $db->query();
        }

        $dispatcher->trigger('onAfterSaveConfig', array());
        
        if ($this->getTask()=='apply'){
            switch ($tab){
                case 1: $task = "general"; break;
                case 2: $task = "currency"; break;
                case 3: $task = "image"; break;
                case 5: $task = "storeinfo"; break;
                case 6: $task = "catprod"; break;
                case 7: $task = "checkout"; break;
                case 8: $task = "adminfunction"; break;
                case 9: $task = "fieldregister"; break;
				case 10: $task = "otherconfig"; break;
            }
            $this->setRedirect('index.php?option=com_jshopping&controller=config&task='.$task, _JSHOP_CONFIG_SUCCESS);
        }else{
		    $this->setRedirect('index.php?option=com_jshopping&controller=config', _JSHOP_CONFIG_SUCCESS);
        }
    }
    
    function seo(){        
        $rows = JSFactory::getModel("seo")->getList();
        
        $view = $this->getView("config", 'html');
        $view->setLayout("listseo");
        $view->assign('etemplatevar', '');
        $view->assign("rows", $rows);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplaySeo', array(&$view));
        $view->displayListSeo();    
    }
    
    function seoedit(){
        $id = $this->input->getInt("id");
        $seo = JSFactory::getTable("seo","jshop");
        $seo->load($id);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;        
        JFilterOutput::objectHTMLSafe($seo, ENT_QUOTES);
        
        $view=$this->getView("config", 'html');
        $view->setLayout("editseo");        
        $view->assign('row', $seo);
        $view->assign('etemplatevar', '');
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplaySeoEdit', array(&$view));
        $view->displayEditSeo();
    }
    
    function saveseo(){
        $post = $this->input->post->getArray();
        $model = JSFactory::getModel("seo");
        $seo = $model->save($post);
        if ($this->getTask()=='applyseo'){
            $this->setRedirect('index.php?option=com_jshopping&controller=config&task=seoedit&id='.$seo->id, _JSHOP_CONFIG_SUCCESS);
        }else{
            $this->setRedirect('index.php?option=com_jshopping&controller=config&task=seo', _JSHOP_CONFIG_SUCCESS);
        }
    }
    
    function statictext(){
        $rows = JSFactory::getModel("statictext")->getList();

        $view = $this->getView("config", 'html');
        $view->setLayout("liststatictext");
        $view->assign('etemplatevar', '');
        $view->assign("rows", $rows);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayStatisticText', array(&$view)); 
        $view->displayListStatictext();    
    }
    
    function statictextedit(){
        $id = $this->input->getInt("id");
        
        $statictext = JSFactory::getTable("statictext","jshop");
        $statictext->load($id);
        
        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        
        JFilterOutput::objectHTMLSafe( $statictext, ENT_QUOTES);
        
        $view = $this->getView("config", 'html');
        $view->setLayout("editstatictext");        
        $view->assign('row', $statictext);
        $view->assign('languages', $languages);
        $view->assign('etemplatevar', '');
        $view->assign('multilang', $multilang);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayStatisticTextEdit', array(&$view));
        $view->displayEditStatictext();
    }
    
    function savestatictext(){
        $model = JSFactory::getModel("statictext");
        $post = $model->getPrepareDataSave($this->input);
        $statictext = $model->save($post);
        if ($this->getTask()=='applystatictext'){
            $this->setRedirect('index.php?option=com_jshopping&controller=config&task=statictextedit&id='.$statictext->id, _JSHOP_CONFIG_SUCCESS);
        }else{            
            $this->setRedirect('index.php?option=com_jshopping&controller=config&task=statictext', _JSHOP_CONFIG_SUCCESS);
        }
    }
    
    function deletestatictext(){
        $id = $this->input->getInt("id");
        JSFactory::getModel("statictext")->delete($id);
        $this->setRedirect('index.php?option=com_jshopping&controller=config&task=statictext');
    }
    
    function publish(){
        $cid = $this->input->getVar('cid');
        JSFactory::getModel("statictext")->useReturnPolicy($cid, 1);
        $this->setRedirect('index.php?option=com_jshopping&controller=config&task=statictext');
    }
    
    function unpublish(){
        $cid = $this->input->getVar('cid');
        JSFactory::getModel("statictext")->useReturnPolicy($cid, 0);
        $this->setRedirect('index.php?option=com_jshopping&controller=config&task=statictext');
    }
    
    function preview_pdf(){
        $dispatcher = JDispatcher::getInstance();
		$jshopConfig = JSFactory::getConfig();
        $jshopConfig->currency_code = "USD";
        $file_generete_pdf_order = $jshopConfig->file_generete_pdf_order;		
        $order = JSFactory::getTable('order', 'jshop');
        $order->prepareOrderPrint = 1;
        $order->firma_name = "Firma";
        $order->f_name = "Fname";
        $order->l_name = 'Lname';
        $order->street = 'Street';
        $order->zip = "Zip"; 
        $order->city = "City";
        $order->country = "Country";
        $order->order_number = outputDigit(1, 8);
        $order->order_date = strftime($jshopConfig->store_date_format, time());
        $order->products = array();
        $prod = new stdClass();
        $prod->product_name = "Product name";
        $prod->product_ean = "12345678";
        $prod->product_quantity = 1;
        $prod->product_item_price = 125;
        $prod->product_tax = 19;
        $order->products[] = $prod;
        $order->order_subtotal = 125;
        $order->order_shipping = 20;        
        $display_price = $jshopConfig->display_price_front;
        if ($display_price==0){
            $order->display_price = 0;
            $order->order_tax_list = array(19 => 23.15);
            $order->order_total = 145;
        }else{
            $order->display_price = 1;
            $order->order_tax_list = array(19 => 27.55);
            $order->order_total = 172.55;
        }
        $dispatcher->trigger('onBeforeCreateDemoPreviewPdf', array(&$order, &$file_generete_pdf_order));
        require_once($file_generete_pdf_order);
		$order->pdf_file = generatePdf($order, $jshopConfig);
		header("Location: ".$jshopConfig->pdf_orders_live_path."/".$order->pdf_file);
		die();
	}
    
	function otherconfig(){
		$jshopConfig = JSFactory::getConfig();
        $config = new stdClass();
		include($jshopConfig->path.'lib/default_config.php');
        $tax_rule_for = array();
        $tax_rule_for[] = JHTML::_('select.option', 0, _JSHOP_FIRMA_CLIENT, 'id', 'name' );
        $tax_rule_for[] = JHTML::_('select.option', 1, _JSHOP_VAT_NUMBER, 'id', 'name' );
        $lists['tax_rule_for'] = JHTML::_('select.genericlist', $tax_rule_for, 'ext_tax_rule_for','class = "inputbox"','id','name', $jshopConfig->ext_tax_rule_for);

		$view = $this->getView("config", 'html');
		$view->setLayout("otherconfig");
        $view->assign("other_config", $other_config);
        $view->assign("other_config_checkbox", $other_config_checkbox);
        $view->assign("other_config_select", $other_config_select);
        $view->assign("config", $jshopConfig);
        $view->assign('etemplatevar', '');
		$view->assign("lists", $lists);
        $view->sidebar = JHtmlSidebar::render();
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeEditConfigOtherConfig', array(&$view));
		$view->display();
	}
    
}
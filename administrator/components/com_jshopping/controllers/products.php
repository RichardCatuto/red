<?php
/**
* @version      4.16.3 02.09.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();
jimport('joomla.html.pagination');

class JshoppingControllerProducts extends JshoppingControllerBaseadmin{

    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("products");
        addSubmenu("products");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $jshopConfig = JSFactory::getConfig();
        $products = JSFactory::getModel("products");
        $id_vendor_cuser = getIdVendorForCUser();

        $context = "jshoping.list.admin.product";
        $limit = $mainframe->getUserStateFromRequest($context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int' );
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', $jshopConfig->adm_prod_list_default_sorting, 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', $jshopConfig->adm_prod_list_default_sorting_dir, 'cmd');

        if (isset($_GET['category_id']) && $_GET['category_id']==="0"){
            $mainframe->setUserState($context.'category_id', 0);
            $mainframe->setUserState($context.'manufacturer_id', 0);
			$mainframe->setUserState($context.'vendor_id', -1);
            $mainframe->setUserState($context.'label_id', 0);
            $mainframe->setUserState($context.'publish', 0);
            $mainframe->setUserState($context.'text_search', '');
        }

        $category_id = $mainframe->getUserStateFromRequest($context.'category_id', 'category_id', 0, 'int');
        $manufacturer_id = $mainframe->getUserStateFromRequest($context.'manufacturer_id', 'manufacturer_id', 0, 'int');
		$vendor_id = $mainframe->getUserStateFromRequest($context.'vendor_id', 'vendor_id', -1, 'int');
        $label_id = $mainframe->getUserStateFromRequest($context.'label_id', 'label_id', 0, 'int');
        $publish = $mainframe->getUserStateFromRequest($context.'publish', 'publish', 0, 'int');
        $text_search = $mainframe->getUserStateFromRequest($context.'text_search', 'text_search', '');
        if ($category_id && $filter_order=='category'){
            $filter_order = 'product_id';
        }

        $filter = array("category_id"=>$category_id, "manufacturer_id"=>$manufacturer_id, "vendor_id"=>$vendor_id, "label_id"=>$label_id, "publish"=>$publish, "text_search"=>$text_search);
        if ($id_vendor_cuser){
            $filter["vendor_id"] = $id_vendor_cuser;
        }

        $show_vendor = $jshopConfig->admin_show_vendors;
        if ($id_vendor_cuser){
            $show_vendor = 0;
        }

        $total = $products->getCountAllProducts($filter);
        $pagination = new JPagination($total, $limitstart, $limit);
        $rows = $products->getAllProducts(
            $filter,
            $pagination->limitstart,
            $pagination->limit,
            $filter_order,
            $filter_order_Dir,
            array(
                'label_image' => 1,
                'vendor_name' => $show_vendor
            )
        );

        if ($show_vendor){
            $lists['vendors'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getVendors(), 'vendor_id','class="chosen-select" onchange="document.adminForm.submit();"', 'id', 'name', $vendor_id);
        }
        $lists['treecategories'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCategories(), 'category_id', 'class="chosen-select" onchange="document.adminForm.submit();"', 'category_id', 'name', $category_id );
        $lists['manufacturers'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getManufacturers(), 'manufacturer_id','class="chosen-select" onchange="document.adminForm.submit();"', 'manufacturer_id', 'name', $manufacturer_id);
        if ($jshopConfig->admin_show_product_labels) {
            $lists['labels'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getLabels(), 'label_id','style="width: 100px;" class="chosen-select" onchange="document.adminForm.submit();"','id','name', $label_id);
        }
        $lists['publish'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPublish(), 'publish', 'style="width: 100px;" class="chosen-select" onchange="document.adminForm.submit();"', 'id', 'name', $publish);

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayListProducts', array(&$rows));

        $view = $this->getView("product_list", 'html');
        $view->assign('rows', $rows);
        $view->assign('lists', $lists);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);
        $view->assign('category_id', $category_id);
        $view->assign('manufacturer_id', $manufacturer_id);
        $view->assign('pagination', $pagination);
        $view->assign('text_search', $text_search);
        $view->assign('config', $jshopConfig);
        $view->assign('show_vendor', $show_vendor);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher->trigger('onBeforeDisplayListProductsView', array(&$view));
        $view->display();
    }

    function edit(){
        $jshopConfig = JSFactory::getConfig();
        $lang = JSFactory::getLang();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onLoadEditProduct', array());
        $id_vendor_cuser = getIdVendorForCUser();
        $category_id = $this->input->getInt('category_id');
        $tmpl_extra_fields = null;
        $product_id = $this->input->getInt('product_id');
        $product_attr_id = $this->input->getInt('product_attr_id');

        //parent product
        if ($product_attr_id){
            $product_attr = JSFactory::getTable('productAttribut', 'jshop');
            $product_attr->load($product_attr_id);
			if ($product_attr->ext_attribute_product_id){
                $product_id = $product_attr->ext_attribute_product_id;
            }else{
                $product = JSFactory::getTable('product', 'jshop');
                $product->parent_id = $product_attr->product_id;
                $product->store();
                $product_id = $product->product_id;
                $product_attr->ext_attribute_product_id = $product_id;
                $product_attr->store();
            }
        }

        if ($id_vendor_cuser && $product_id){
            checkAccessVendorToProduct($id_vendor_cuser, $product_id);
        }

        $products = JSFactory::getModel("products");

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
        $_productprice = JSFactory::getTable('productPrice', 'jshop');
        $product->product_add_prices = $_productprice->getAddPrices($product_id);
        $product->product_add_prices = array_reverse($product->product_add_prices);
        $product->name = $product->getName();

        $_lang = JSFactory::getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
        $edit = intval($product_id);

        JFilterOutput::objectHTMLSafe($product, ENT_QUOTES);

        if (!$product_id){
            $product->product_quantity = 1;
            $product->product_publish = 1;
        }

		$product->product_quantity = floatval($product->product_quantity);

        if ($edit){
            $images = $product->getImages();
            $videos = $product->getVideos();
            $files  = $product->getFiles();
            $categories_select = $product->getCategories();
            $categories_select_list = array();
            foreach($categories_select as $v){
                $categories_select_list[] = $v->category_id;
            }
            $related_products = $products->getRelatedProducts($product_id);
        } else {
            $images = array();
            $videos = array();
            $files = array();
            $categories_select = null;
            if ($category_id) {
                $categories_select = $category_id;
            }
            $related_products = array();
            $categories_select_list = array();
        }
        if ($jshopConfig->tax){
            $list_tax = JshopHelpersSelectOptions::getTaxs();
            $withouttax = 0;
        }else{
            $withouttax = 1;
        }

        $categories = buildTreeCategory(0,1,0);
        if (count($categories)==0){
            JError::raiseNotice(0, _JSHOP_PLEASE_ADD_CATEGORY);
        }
        $lists['images'] = $images;
        $lists['videos'] = $videos;
        $lists['files'] = $files;

        $manufs = JshopHelpersSelectOptions::getManufacturers(2);

        //Attributes
        $_attribut = JSFactory::getModel('attribut');
        $list_all_attributes = $_attribut->getAllAttributes(2, $categories_select_list);
        $_attribut_value =JSFactory::getModel('attributValue');
        $lists['attribs'] = $product->getAttributes();
        $lists['ind_attribs'] = $product->getAttributes2();
        $lists['attribs_values'] = $_attribut_value->getAllAttributeValues(2);
        $all_attributes = $list_all_attributes['dependent'];

        $lists['ind_attribs_gr'] = array();
        foreach($lists['ind_attribs'] as $v){
            $lists['ind_attribs_gr'][$v->attr_id][] = $v;
        }

		foreach ($lists['attribs'] as $key => $attribs){
            $lists['attribs'][$key]->count = floatval($attribs->count);
        }

        $first = array();
        $first[] = JHTML::_('select.option', '0',_JSHOP_SELECT, 'value_id','name');

        foreach ($all_attributes as $key => $value){
            $values_for_attribut = $_attribut_value->getAllValues($value->attr_id);
            $all_attributes[$key]->values_select = JHTML::_('select.genericlist', array_merge($first, $values_for_attribut),'value_id['.$value->attr_id.']','class = "inputbox" size = "5" multiple="multiple" id = "value_id_'.$value->attr_id.'"','value_id','name');
            $all_attributes[$key]->values = $values_for_attribut;
        }
        $lists['all_attributes'] = $all_attributes;
        $product_with_attribute = (count($lists['attribs']) > 0);

        //independent attribute
        $all_independent_attributes = $list_all_attributes['independent'];

        $price_modification = JshopHelpersSelectOptions::getProductAttributPriceModify();

        foreach ($all_independent_attributes as $key => $value){
            $values_for_attribut = $_attribut_value->getAllValues($value->attr_id);
            $all_independent_attributes[$key]->values_select = JHTML::_('select.genericlist', array_merge($first, $values_for_attribut),'attr_ind_id_tmp_'.$value->attr_id.'','class = "inputbox middle2" ','value_id','name');
            $all_independent_attributes[$key]->values = $values_for_attribut;
            $all_independent_attributes[$key]->price_modification_select = JHTML::_('select.genericlist', $price_modification,'attr_price_mod_tmp_'.$value->attr_id.'','class = "inputbox small3" ','id','name');
            $all_independent_attributes[$key]->submit_button = '<input type = "button" class="btn" onclick = "addAttributValue2('.$value->attr_id.');" value = "'._JSHOP_ADD_ATTRIBUT.'" />';
        }
        $lists['all_independent_attributes'] = $all_independent_attributes;
		$lists['dep_attr_button_add'] = '<input type="button" class="btn" onclick="addAttributValue()" value="'._JSHOP_ADD.'" />';
        // End work with attributes and values

        if ($jshopConfig->admin_show_delivery_time){
            $lists['deliverytimes'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getDeliveryTimes(),'delivery_times_id','class = "inputbox"','id','name',$product->delivery_times_id);
        }

        // units
        $allunits = JshopHelpersSelectOptions::getUnits();
        if ($jshopConfig->admin_show_product_basic_price){
            $lists['basic_price_units'] = JHTML::_('select.genericlist', $allunits, 'basic_price_unit_id','class = "inputbox"','id','name',$product->basic_price_unit_id);
        }
        if (!$product->add_price_unit_id) $product->add_price_unit_id = $jshopConfig->product_add_price_default_unit;
        $lists['add_price_units'] = JHTML::_('select.genericlist', $allunits, 'add_price_unit_id','class = "inputbox middle"','id','name', $product->add_price_unit_id);
        //

        if ($jshopConfig->admin_show_product_labels){
            $lists['labels'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getLabels(2), 'label_id','class = "inputbox"','id','name',$product->label_id);
        }

        $lists['access'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getAccessGroups(), 'access','class = "inputbox"','id','title', $product->access);

        //currency
        $current_currency = $product->currency_id;
        if (!$current_currency) $current_currency = $jshopConfig->mainCurrency;
        $lists['currency'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCurrencies(), 'currency_id','class = "inputbox small2"','currency_id','currency_code', $current_currency);

        // vendors
        $display_vendor_select = 0;
        if ($jshopConfig->admin_show_vendors){
            $lists['vendors'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getVendors(0), 'vendor_id','class = "inputbox"', 'id', 'name', $product->vendor_id);
            $display_vendor_select = 1;
            if ($id_vendor_cuser > 0) $display_vendor_select = 0;
        }
        //

        //product extra field
        if ($jshopConfig->admin_show_product_extra_field) {
            $categorys_id = array();
            if (is_array($categories_select)){
                foreach($categories_select as $tmp){
                    $categorys_id[] = $tmp->category_id;
                }
            }
            $tmpl_extra_fields = $this->_getHtmlProductExtraFields($categorys_id, $product);
        }
        //

        //free attribute
        if ($jshopConfig->admin_show_freeattributes){
            $_freeattributes = JSFactory::getModel("freeattribut");
            $listfreeattributes = $_freeattributes->getAll();
            $activeFreeAttribute = $product->getListFreeAttributes();
            $listIdActiveFreeAttribute = array();
            foreach($activeFreeAttribute as $_obj){
                $listIdActiveFreeAttribute[] = $_obj->id;
            }
            foreach($listfreeattributes as $k=>$v){
                if (in_array($v->id, $listIdActiveFreeAttribute)){
                    $listfreeattributes[$k]->pactive = 1;
                }
            }
        }

        $lists['manufacturers'] = JHTML::_('select.genericlist', $manufs,'product_manufacturer_id','class = "inputbox"','manufacturer_id','name',$product->product_manufacturer_id);

        if ($jshopConfig->tax){
            $tax_value = JSFactory::getModel("taxes")->getValue($product->product_tax_id);
        }else{
            $tax_value = 0;
        }

        if ($product_id){
            $product->product_price = formatEPrice($product->product_price);
            if ($jshopConfig->display_price_admin==0){
                $product->product_price2 = formatEPrice($product->product_price / (1 + $tax_value / 100));
            }else{
                $product->product_price2 = formatEPrice($product->product_price * (1 + $tax_value / 100));
            }
        }else{
            $product->product_price2 = '';
        }

        $category_select_onclick = "";
        if ($jshopConfig->admin_show_product_extra_field){
            $category_select_onclick = 'onclick="reloadProductExtraField(\''.$product_id.'\')"';
        }

        if ($jshopConfig->tax){
            $lists['tax'] = JHTML::_('select.genericlist', $list_tax,'product_tax_id','class = "inputbox" onchange = "updatePrice2('.$jshopConfig->display_price_admin.');"','tax_id','tax_name',$product->product_tax_id);
        }
        $lists['categories'] = JHTML::_('select.genericlist', $categories, 'category_id[]', 'class="inputbox" size="10" multiple = "multiple" '.$category_select_onclick, 'category_id', 'name', $categories_select);
        $lists['templates'] = getTemplates('product', $product->product_template);

        $_product_option = JSFactory::getTable('productOption', 'jshop');
        $product_options = $_product_option->getProductOptions($product_id);
        $product->product_options = $product_options;

        if ($jshopConfig->return_policy_for_product){
            $_statictext = JSFactory::getModel("statictext");
            $first = array();
            $first[] = JHTML::_('select.option', '0', _JSHP_STPAGE_return_policy, 'id', 'alias');
            $statictext_list = $_statictext->getList(1);
            $lists['return_policy'] = JHTML::_('select.genericlist', array_merge($first, $statictext_list), 'options[return_policy]','class = "inputbox"','id','alias', $product_options['return_policy']);
        }

        $dispatcher->trigger('onBeforeDisplayEditProduct', array(&$product, &$related_products, &$lists, &$listfreeattributes, &$tax_value));

        $view=$this->getView("product_edit", 'html');
        $view->setLayout("default");
        $view->assign('product', $product);
        $view->assign('lists', $lists);
        $view->assign('related_products', $related_products);
        $view->assign('edit', $edit);
        $view->assign('product_with_attribute', $product_with_attribute);
        $view->assign('tax_value', $tax_value);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('tmpl_extra_fields', $tmpl_extra_fields);
        $view->assign('withouttax', $withouttax);
        $view->assign('display_vendor_select', $display_vendor_select);
        $view->assign('listfreeattributes', $listfreeattributes);
        $view->assign('product_attr_id', $product_attr_id);
        foreach($languages as $lang){
            $view->assign('plugin_template_description_'.$lang->language, '');
        }
        $view->assign('plugin_template_info', '');
        $view->assign('plugin_template_attribute', '');
        $view->assign('plugin_template_freeattribute', '');
        $view->assign('plugin_template_images', '');
        $view->assign('plugin_template_related', '');
        $view->assign('plugin_template_files', '');
        $view->assign('plugin_template_extrafields', '');
        $dispatcher->trigger('onBeforeDisplayEditProductView', array(&$view) );
		$view->display();
    }

    function save(){
        JSession::checkToken() or die('Invalid Token');
        $model = JSFactory::getModel("products");
        $post = $model->getPrepareDataSave($this->input);
        if (!$product = $model->save($post)){
            JError::raiseWarning("100", $model->getError());
            $this->setRedirect("index.php?option=com_jshopping&controller=products&task=edit&product_id=".$post['product_id']);
            return;
        }
        if ($product->parent_id!=0){
            print "<script type='text/javascript'>window.close();</script>";
            die();
        }
        if ($this->getTask()=='apply'){
            $this->setRedirect("index.php?option=com_jshopping&controller=products&task=edit&product_id=".$product->product_id, _JSHOP_PRODUCT_SAVED);
        }else{
            $this->setRedirect("index.php?option=com_jshopping&controller=products", _JSHOP_PRODUCT_SAVED);
        }
    }

    function editlist(){
        $cid = $this->input->getVar('cid');
        if (count($cid)==1){
            $this->setRedirect("index.php?option=com_jshopping&controller=products&task=edit&product_id=".$cid[0]);
            return 0;
        }
        $id_vendor_cuser = getIdVendorForCUser();
        $jshopConfig = JSFactory::getConfig();

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onLoadEditListProduct', array());

        $product = JSFactory::getTable('product', 'jshop');

        $all_taxes = JSFactory::getModel("taxes")->getAllTaxes();

        $list_tax = JshopHelpersSelectOptions::getTaxs(0, 1);
        if (count($all_taxes)==0) $withouttax = 1; else $withouttax = 0;

        $categories = buildTreeCategory(0,1,0);

        $manufs = JshopHelpersSelectOptions::getManufacturers(2, 1);

        $price_modification = JshopHelpersSelectOptions::getProductAttributPriceModify();
        $lists['price_mod_price'] = JHTML::_('select.genericlist', $price_modification,'mod_price','','id','name');
        $lists['price_mod_old_price'] = JHTML::_('select.genericlist', $price_modification,'mod_old_price','','id','name');
        if ($jshopConfig->admin_show_delivery_time) {
            $lists['deliverytimes'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getDeliveryTimes(2, 1),'delivery_times_id','class = "inputbox"','id','name');
        }
        if ($jshopConfig->admin_show_product_basic_price){
            $lists['basic_price_units'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getUnits(), 'basic_price_unit_id','class = "inputbox"','id','name');
        }
        if ($jshopConfig->admin_show_product_labels) {
            $lists['labels'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getLabels(2, 1), 'label_id','class = "inputbox"','id','name');
        }
        $lists['access'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getAccessGroups(0, 1), 'access','class = "inputbox"','id','title');

        //currency
        $current_currency = $product->currency_id;
        if (!$current_currency) $current_currency = $jshopConfig->mainCurrency;
        $lists['currency'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getCurrencies(), 'currency_id','class = "inputbox"','currency_id','currency_code', $current_currency);

        // vendors
        $display_vendor_select = 0;
        if ($jshopConfig->admin_show_vendors){
            $lists['vendors'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getVendors('- - -'), 'vendor_id','class = "inputbox"', 'id', 'name');
            $display_vendor_select = 1;
            if ($id_vendor_cuser > 0) $display_vendor_select = 0;
        }

		//extra field
		$category_select_onclick = "";
        if ($jshopConfig->admin_show_product_extra_field) {
			$category_select_onclick = 'onclick="reloadProductExtraField(\'0\')" ';
            $tmpl_extra_fields = $this->_getHtmlProductExtraFields();
        }
        //

        $lists['product_publish'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getPublishGroup(), 'product_publish', '', 'value', 'name');
        $lists['manufacturers'] = JHTML::_('select.genericlist', $manufs,'product_manufacturer_id','class = "inputbox"','manufacturer_id','name');
        $lists['tax'] = JHTML::_('select.genericlist', $list_tax,'product_tax_id','class = "inputbox"','tax_id','tax_name');
        $lists['categories'] = JHTML::_('select.genericlist', $categories, 'category_id[]', 'class="inputbox" size="10" multiple = "multiple" '.$category_select_onclick, 'category_id', 'name');
        $lists['templates'] = getTemplates('product', "", 1);

        $view = $this->getView("product_edit", 'html');
        $view->setLayout("editlist");
        $view->assign('lists', $lists);
        $view->assign('cid', $cid);
        $view->assign('config', $jshopConfig);
        $view->assign('withouttax', $withouttax);
        $view->assign('display_vendor_select', $display_vendor_select);
		$view->assign('tmpl_extra_fields', $tmpl_extra_fields);
        $view->assign('etemplatevar', '');
        $dispatcher->trigger('onBeforeDisplayEditListProductView', array(&$view) );
        $view->editGroup();
    }

    function savegroup(){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforSaveListProduct', array());
        $cid = $this->input->getVar('cid');
        $post = $this->input->post->getArray();
        $model = JSFactory::getModel("products");
        foreach($cid as $id){
			$model->productGroupUpdate($id, $post);
        }
        $dispatcher->trigger('onAfterSaveListProductEnd', array($cid, $post) );
        $this->setRedirect("index.php?option=com_jshopping&controller=products", _JSHOP_PRODUCT_SAVED);
    }

    function copy(){
        $cid = $this->input->getVar('cid');
        $text = JSFactory::getModel("products")->copyProducts($cid);
        $this->setRedirect("index.php?option=com_jshopping&controller=products", implode("</li><li>",$text));
    }

    function order(){
        $order = $this->input->getVar("order");
        $product_id = $this->input->getInt("product_id");
        $number = $this->input->getInt("number");
        $category_id = $this->input->getInt("category_id");
        JSFactory::getModel("products")->orderProductInCategory($product_id, $category_id, $number, $order);
        $this->setRedirect("index.php?option=com_jshopping&controller=products&category_id=".$category_id);
    }

    function saveorder(){
        $category_id = $this->input->getInt("category_id");
        $cid = $this->input->get('cid', array(), 'array');
        $order = $this->input->get('order', array(), 'array');
        JSFactory::getModel("products")->saveOrderProductInCategory($cid, $order, $category_id);
        $this->setRedirect("index.php?option=com_jshopping&controller=products&category_id=".$category_id);
    }

    function cancel(){
        $this->setRedirect("index.php?option=com_jshopping&controller=products");
    }

    function delete_foto(){
        $image_id = $this->input->getInt("id");
        JSFactory::getModel("products")->deleteImage($image_id);
        die();
    }

    function delete_video(){
        $video_id = $this->input->getInt("id");
        JSFactory::getModel("products")->deleteVideo($video_id);
        die();
    }

    function delete_file(){
        $id = $this->input->getInt("id");
        $type = $this->input->getVar("type");
        print JSFactory::getModel("products")->deleteFile($id, $type);
        die();
    }

    function search_related(){
        $jshopConfig = JSFactory::getConfig();
        $products = JSFactory::getModel("products");

        $text_search = $this->input->getVar("text");
        $limitstart = $this->input->getInt("start");
        $no_id = $this->input->getInt("no_id");
        $limit = $this->input->getInt("limit", 20);

        $filter = array("without_product_id"=>$no_id, "text_search"=>$text_search);
        $total = $products->getCountAllProducts($filter);
        $rows = $products->getAllProducts($filter, $limitstart, $limit);
        $page = ceil($total/$limit);

        $view = $this->getView("product_list", 'html');
        $view->setLayout("search_related");
        $view->assign('rows', $rows);
        $view->assign('config', $jshopConfig);
        $view->assign('limit', $limit);
        $view->assign('pages', $page);
        $view->assign('no_id', $no_id);
        $view->display();
        die();
    }

    function product_extra_fields(){
        $product_id = $this->input->getInt("product_id");
        $cat_id = $this->input->getVar("cat_id");
        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);

        $categorys = array();
        if (is_array($cat_id)){
            foreach($cat_id as $cid){
                $categorys[] = intval($cid);
            }
        }

        print $this->_getHtmlProductExtraFields($categorys, $product);
        die();
    }

    function _getHtmlProductExtraFields($categorys=array(), $product = null){
		if($product === null) $product = new stdClass;
		$_productfields = JSFactory::getModel("productFields");
        $list = $_productfields->getList(1);

        $_productfieldvalues = JSFactory::getModel("productFieldValues");
        $listvalue = $_productfieldvalues->getAllList();

        $f_option = array();
        $f_option[] = JHTML::_('select.option', 0, " - - - ", 'id', 'name');

        $fields = array();
        foreach($list as $v){
            $insert = 0;
            if ($v->allcats==1){
                $insert = 1;
            }else{
                $cats = unserialize($v->cats);
                foreach($categorys as $catid){
                    if (in_array($catid, $cats)) $insert = 1;
                }
            }
            if ($insert){
                $obj = new stdClass();
                $obj->id = $v->id;
                $obj->name = $v->name;
                $obj->groupname = $v->groupname;
                $tmp = array();
                foreach($listvalue as $lv){
                    if ($lv->field_id==$v->id) $tmp[] = $lv;
                }
                $name = 'extra_field_'.$v->id;
                if ($v->type==0){
                    if ($v->multilist==1){
                        $attr = 'multiple="multiple" size="10"';
                    }else{
                        $attr = "";
                    }
                    $obj->values = JHTML::_('select.genericlist', array_merge($f_option, $tmp), 'productfields['.$name.'][]', $attr, 'id', 'name', explode(',',$product->$name));
                }else{
                    $obj->values = "<input type='text' name='".$name."' value='".$product->$name."' />";
                }
                $fields[] = $obj;
            }
        }
        $view=$this->getView("product_edit", 'html');
        $view->setLayout("extrafields_inner");
        $view->assign('fields', $fields);
		$view->assign('product', $product);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeLoadTemplateHtmlProductExtraFields', array(&$view));
        return $view->loadTemplate();
    }

    function getfilesale(){
        $id = $this->input->getVar('id');

        $model = JSFactory::getModel('productDownload', 'jshop');
        $model->setId($id);
        $file_name = $model->getFile();

        ob_end_clean();
        @set_time_limit(0);
        $model->downloadFile($file_name);
        die();
    }

    function loadproductinfo(){
        $jshopConfig = JSFactory::getConfig();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onLoadInfoProduct', array());
        $id_vendor_cuser = getIdVendorForCUser();
        $product_id = $this->input->getInt('product_id');
        $layout = $this->input->getVar('layout','productinfo_json');
        $display_price = $this->input->getVar('display_price');
        $jshopConfig->setDisplayPriceFront($display_price);

        if ($id_vendor_cuser && $product_id){
            checkAccessVendorToProduct($id_vendor_cuser, $product_id);
        }

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
        $product->getDescription();
		$count_attributes = count($product->getRequireAttribute());
        $product_price = $product->getPrice();

        $res = array();
        $res['product_id'] = $product->product_id;
		$res['category_id'] = $product->getCategory();
        $res['product_ean'] = $product->product_ean;
        $res['manufacturer_code'] = $product->getManufacturerCode();
        $res['product_price'] = $product_price;
        $res['delivery_times_id'] = $product->delivery_times_id;
        $res['vendor_id'] = fixRealVendorId($product->vendor_id);
        $res['product_weight'] = $product->product_weight;
        $res['product_tax'] = $product->getTax();
        $res['product_name'] = $product->name;
        $res['count_attributes'] = $count_attributes;
		$res['thumb_image'] = getPatchProductImage($product->image, 'thumb');

        $view = $this->getView("product_edit", 'html');
        $view->setLayout($layout);
        $view->assign('res', $res);
        $view->assign('edit', null);
        $view->assign('product', $product);
        $dispatcher->trigger('onBeforeDisplayLoadInfoProduct', array(&$view) );
        $view->display();
    die();
    }

	function getvideocode() {
		$video_id = $this->input->getInt('video_id');
		$productvideo = JSFactory::getTable('productvideo', 'jshop');
		$productvideo->load($video_id);

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onAfterLoadVideoCodeForProduct', array(&$productvideo));

		$view=$this->getView('product_edit', 'html');
        $view->setLayout('product_video_code');
        $view->assign('code', $productvideo->video_code);

		$dispatcher->trigger('onBeforeDisplayVideoCodeForProduct', array(&$view) );
        $view->display();
		die();
	}

    function getattributes(){
        $jshopConfig = JSFactory::getConfig();
        $product_id = $this->input->getInt('product_id');
        $num = $this->input->getInt('num');
        $admin_load_user_id = $this->input->getInt('admin_load_user_id');
        $id_currency = $this->input->getInt('id_currency');
        $display_price = $this->input->getVar('display_price');
        $jshopConfig->setDisplayPriceFront($display_price);

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
        $attributesDatas = $product->getAttributesDatas();
        $product->setAttributeActive($attributesDatas['attributeActive']);
        $attributeValues = $attributesDatas['attributeValues'];

        $attributes = $product->getBuildSelectAttributes($attributeValues, $attributesDatas['attributeSelected'], 1);

        $_attributevalue = JSFactory::getTable('AttributValue', 'jshop');
        $all_attr_values = $_attributevalue->getAllAttributeValues();

        $product->getExtendsData();

        $urlupdateprice = 'index.php?option=com_jshopping&controller=products&task=ajax_attrib_select_and_price&product_id='.$product_id.'&ajax=1&admin_load_user_id='.$admin_load_user_id.'&id_currency='.$id_currency.'&display_price='.$display_price;

        $view = $this->getView("product_edit", 'html');
        $view->setLayout('product_attribute_select');
        $view->assign('attributes', $attributes);
        $view->assign('product', $product);
        $view->assign('num', $num);
        $view->assign('config', $jshopConfig);
        $view->assign('image_path', $jshopConfig->live_path.'/images');
        $view->assign('noimage', $jshopConfig->noimage);
        $view->assign('all_attr_values', $all_attr_values);
        $view->assign('urlupdateprice', $urlupdateprice);
        JDispatcher::getInstance()->trigger('onBeforeDisplayGetAttributes', array(&$view) );
        $view->display();
    }

    function ajax_attrib_select_and_price(){
        $jshopConfig = JSFactory::getConfig();
        $display_price = $this->input->getVar('display_price');
        $jshopConfig->setDisplayPriceFront($display_price);
        $product_id = $this->input->getInt('product_id');
        $change_attr = $this->input->getInt('change_attr');
        if ($jshopConfig->use_decimal_qty){
            $qty = floatval(str_replace(",", ".", $this->input->getVar('qty',1)));
        }else{
            $qty = $this->input->getInt('qty', 1);
        }
        if ($qty < 0) $qty = 1;
        $attribs = $this->input->getVar('attr');
        if (!is_array($attribs)) $attribs = array();
        $freeattr = array();

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeLoadDisplayAjaxAttrib', array(&$product_id, &$change_attr, &$qty, &$attribs, &$freeattr));

        $product = JSFactory::getTable('product', 'jshop');
        $product->load($product_id);
        $dispatcher->trigger('onBeforeLoadDisplayAjaxAttrib2', array(&$product));

        $attributesDatas = $product->getAttributesDatas($attribs);
        $product->setAttributeActive($attributesDatas['attributeActive']);
        $attributeValues = $attributesDatas['attributeValues'];
        $product->setFreeAttributeActive($freeattr);

        $attributes = $product->getBuildSelectAttributes($attributeValues, $attributesDatas['attributeSelected'], 1);

        $rows = array();
        foreach($attributes as $k=>$v){
            $rows[] = '"id_'.$k.'":"'.json_value_encode($v->selects, 1).'"';
        }

        $pricefloat = $product->getPrice($qty, 1, 1, 1);
        $price = formatprice($pricefloat);
        $available = intval($product->getQty() > 0);
        $ean = $product->getEan();
        $manufacturer_code = $product->getManufacturerCode();
        $weight = $product->getWeight();

        $rows[] = '"price":"'.json_value_encode($price).'"';
        $rows[] = '"pricefloat":"'.$pricefloat.'"';
        $rows[] = '"available":"'.$available.'"';
        $rows[] = '"ean":"'.json_value_encode($ean).'"';
        $rows[] = '"manufacturer_code":"'.json_value_encode($manufacturer_code).'"';
        $rows[] = '"weight":"'.json_value_encode($weight).'"';

        $qty_in_stock = getDataProductQtyInStock($product);
        $rows[] = '"qty":"'.json_value_encode(sprintQtyInStock($qty_in_stock)).'"';

        $product->updateOtherPricesIncludeAllFactors();

        $dispatcher->trigger('onBeforeDisplayAjaxAttrib', array(&$rows, &$product) );
        print '{'.implode(",",$rows).'}';
        die();
    }

}
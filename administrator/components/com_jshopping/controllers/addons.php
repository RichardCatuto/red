<?php
/**
* @version      4.16.1 04.06.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerAddons extends JControllerLegacy{
    
    function __construct($config = array()){
        parent::__construct($config);        
        checkAccessController("addons");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $addons = JSFactory::getModel("addons");
        $rows = $addons->getList(1);
        $back64 = base64_encode("index.php?option=com_jshopping&controller=addons");

        $view = $this->getView("addons", 'html');
        $view->setLayout("list");
        $view->assign('rows', $rows); 
        $view->assign('back64', $back64);
        $view->sidebar = JHtmlSidebar::render();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeDisplayAddons', array(&$view));
        $view->displayList();
    }
    
    function edit(){
        $id = $this->input->getVar("id");
        $dispatcher = JDispatcher::getInstance();
        $row = JSFactory::getTable('addon', 'jshop');
        $row->load($id);
        $config_file_patch = JPATH_COMPONENT_SITE."/addons/".$row->alias."/config.tmpl.php";
        $config_file_exist = file_exists($config_file_patch);

        $view = $this->getView("addons", 'html');
        $view->setLayout("edit");
        $view->assign('row', $row);
        $view->assign('params', $row->getParams());
        $view->assign('config_file_patch', $config_file_patch);
        $view->assign('config_file_exist', $config_file_exist);
        $dispatcher->trigger('onBeforeEditAddons', array(&$view));
        $view->displayEdit();
    }
    
    function save(){
        $this->saveConfig('save');
    }
    
    function apply(){
        $this->saveConfig();
    }
    
    private function saveConfig($task = 'apply'){
		$post = $this->input->post->getArray(array(), null, 'RAW');
        JSFactory::getModel("addons")->save($post);
        if ($task == 'save'){
            $this->setRedirect("index.php?option=com_jshopping&controller=addons");
        } else {
            $this->setRedirect("index.php?option=com_jshopping&controller=addons&task=edit&id=".$post['id']);
        }
    }

    function remove(){
        $id = $this->input->getVar("id");
        JSFactory::getModel("addons")->delete($id);
        $this->setRedirect("index.php?option=com_jshopping&controller=addons");
    }
    
    function info(){
        $id = $this->input->getVar("id");
        
        $dispatcher = JDispatcher::getInstance();
        $row = JSFactory::getTable('addon', 'jshop');
        $row->load($id);
        $file_patch = JPATH_COMPONENT_SITE."/addons/".$row->alias."/info.tmpl.php";
        $file_exist = file_exists($file_patch);

        $view = $this->getView("addons", 'html');
        $view->setLayout("info");
        $view->assign('row', $row);
        $view->assign('params', $row->getParams());
        $view->assign('file_patch', $file_patch);
        $view->assign('file_exist', $file_exist);
        $dispatcher->trigger('onBeforeInfoAddons', array(&$view));
        $view->displayInfo();
    }
    
    function version(){
        $id = $this->input->getVar("id");
        
        $dispatcher = JDispatcher::getInstance();
        $row = JSFactory::getTable('addon', 'jshop');
        $row->load($id);
        $file_patch = JPATH_COMPONENT_SITE."/addons/".$row->alias."/version.tmpl.php";
        $file_exist = file_exists($file_patch);

        $view = $this->getView("addons", 'html');
        $view->setLayout("info");
        $view->assign('row', $row);
        $view->assign('params', $row->getParams());
        $view->assign('file_patch', $file_patch);
        $view->assign('file_exist', $file_exist);
        $dispatcher->trigger('onBeforeVersionAddons', array(&$view));
        $view->displayVersion();
    }

    function help(){
        $view = $this->getView("addons", 'html');
        $view->setLayout("help");
        $view->displayHelp();
    }

}
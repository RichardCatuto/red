<?php
/**
* @version      4.13.0 11.09.2015
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('');

class jshopVendorList extends jshopBase{
	
	protected $model = null;
	protected $list = null;
	protected $total = null;
	protected $limit = null;
	protected $limitstart = null;
	protected $pagination = null;
	
	public function __construct(){
		$model = JSFactory::getTable('vendor', 'jshop');
		$this->setModel($model);
	}
	
	public function setModel($model){
		$this->model = $model;
		extract(js_add_trigger(get_defined_vars(), "after"));
	}
	
	public function getModel(){
		return $this->model;
	}
	
	public function getContext(){
		$context = "jshoping.list.front.vendor";
		return $context;
	}
	
	public function getCountPerPage(){
		return $this->getModel()->getCountPerPage();
	}
	
	public function getCountToRow(){
		return $this->getModel()->getCountToRow();
	}
	
	public function getTotal(){
		return $this->total;
	}
	
	public function getList(){
		return $this->list;
	}
	
	public function getPagination(){
        return $this->pagination;
    }
	
	public function getLimit(){
        return $this->limit;
    }
	
	public function getLimitStart(){
        return $this->limitstart;
    }
	
	protected function loadRequestData(){
		$mainframe = JFactory::getApplication();
		$model = $this->getModel();
		$context = $this->getContext();
		
		$this->limitstart = JFactory::getApplication()->input->getInt('limitstart');
        $this->limit = $mainframe->getUserStateFromRequest($context.'limit', 'limit', $this->getCountPerPage(), 'int');
	}
	
	public function load(){
		$this->loadRequestData();		
		$vendor = $this->getModel();
		
        $this->total = $vendor->getCountAllVendors();        
        if ($this->limitstart>=$this->total){
			$this->limitstart = 0;
		}
		
        $this->list = $vendor->getAllVendors(1, $this->limitstart, $this->limit);
        
        JDispatcher::getInstance()->trigger('onBeforeDisplayListVendors', array(&$this->list, &$this));
        
        $this->pagination = new JPagination($this->total, $this->limitstart, $this->limit);
        
        $this->list = $vendor->prepareViewListVendor($this->list);
		
		return 1;
	}
	
}
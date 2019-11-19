<?php
/**
* @version      4.11.0 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class jshopShippingMethodPriceWeight extends JTable {
    
    function __construct( &$_db ){
        parent::__construct( '#__jshopping_shipping_method_price_weight', 'sh_pr_weight_id', $_db );
    }
}
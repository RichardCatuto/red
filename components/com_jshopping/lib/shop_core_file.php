<?php
/**
* @version      4.14.0 25.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

define('JPATH_JOOMSHOPPING', JPATH_ROOT.'/components/com_jshopping');
define('JPATH_JOOMSHOPPING_ADMIN', JPATH_ADMINISTRATOR.'/components/com_jshopping');

JTable::addIncludePath(JPATH_JOOMSHOPPING.'/tables');
JLoader::discover('JshopHelpers', JPATH_JOOMSHOPPING."/helpers");
include_once(JPATH_JOOMSHOPPING."/tables/shopbase.php");
include_once(JPATH_JOOMSHOPPING."/tables/multilang.php");
include_once(JPATH_JOOMSHOPPING."/lib/jtableauto.php");
include_once(JPATH_JOOMSHOPPING."/lib/multilangfield.php");
include_once(JPATH_JOOMSHOPPING."/tables/config.php");
require_once(JPATH_JOOMSHOPPING."/lib/functions.php");
require_once(JPATH_JOOMSHOPPING."/lib/shop_item_menu.php");
require_once(JPATH_JOOMSHOPPING."/lib/jsuri.php");
require_once(JPATH_JOOMSHOPPING."/models/base.php");
require_once(JPATH_JOOMSHOPPING_ADMIN."/models/baseadmin.php");
include_once(JPATH_JOOMSHOPPING."/payments/payment.php");
include_once(JPATH_JOOMSHOPPING."/shippingform/shippingform.php");
<?php
/**
* @version      4.13.0 25.03.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_COMPONENT.'/models');
require_once(JPATH_COMPONENT_SITE."/lib/factory.php");
require_once(JPATH_COMPONENT.'/controllers/base.php');
$controller = getJsFrontRequestController();
require("loadparams.php");

if (file_exists(JPATH_COMPONENT.'/controllers/'.$controller.'.php'))
    require_once(JPATH_COMPONENT.'/controllers/'.$controller.'.php');
else
    JError::raiseError(403, JText::_('Access Forbidden'));

$classname = 'JshoppingController'.$controller;
$controller = new $classname();
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
displayTextJSC();
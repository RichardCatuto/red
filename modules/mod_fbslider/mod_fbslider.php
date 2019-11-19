<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once __DIR__ . '/helper.php';
$fblikebox = modFBLikebox::getFBLikebox($params);
require JModuleHelper::getLayoutPath('mod_fbslider', $params->get('layout', 'default'));
?>
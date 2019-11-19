<?php 
/**
* @version      4.17.1 30.03.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
var urlcheckpassword = '<?php print $this->urlcheckpassword?>';
var register_field_require = {};
<?php
foreach($config_fields as $key=>$val){
    if ($val['require']){
        print "register_field_require['".$key."']=1;";
    }
}
?>
</script>
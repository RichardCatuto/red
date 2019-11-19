<?php 
/**
* @version      4.9.1 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="jshop list_product" id="comjshop_list_product">
<?php print $this->_tmp_list_products_html_start?>
<?php foreach ($this->rows as $k=>$product) : ?>
    <?php if ($k % $this->count_product_to_row == 0) : ?>
        <div class = "row-fluid">
    <?php endif; ?>
    
    <div class = "sblock<?php echo $this->count_product_to_row;?>">
        <div class = "block_product">
            <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
        </div>
    </div>
            
    <?php if ($k % $this->count_product_to_row == $this->count_product_to_row - 1) : ?>
        <div class = "clearfix"></div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($k % $this->count_product_to_row != $this->count_product_to_row - 1) : ?>
    <div class = "clearfix"></div>
    </div>
<?php endif; ?>
<?php print $this->_tmp_list_products_html_end;?>
</div>
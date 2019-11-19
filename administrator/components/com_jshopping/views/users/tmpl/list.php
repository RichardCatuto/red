<?php
/**
* @version      4.17.0 28.04.2017
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

$rows = $this->rows;
$pageNav = $this->pageNav;
$select_user = $this->select_user;
JHtml::_('formbehavior.chosen', '.chosen-select');
?>
<?php if(!$select_user) {?>
<div id="j-sidebar-container" class="span2">
    <?php  echo $this->sidebar; ?>
</div>
<?php }?>

<div  <?php if(!$select_user) {?> id="j-main-container" class="span10"<?php }else{?>class="span12" <?php }?>>
  

<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_jshopping&controller=users<?php if($select_user) {?>&tmpl=component&select_user=1<?php }?>">
<?php echo JHtml::_('form.token');?>
<?php print $this->tmp_html_start?>

<div class="js-stools clearfix jshop_block_filter">
    <div class="js-stools-container-bar">
        <?php print $this->tmp_html_filter?>

        <div class="filter-search btn-group pull-left">
            <input type="text" id="text_search" name="text_search" placeholder="<?php print _JSHOP_SEARCH?>" value="<?php echo htmlspecialchars($this->text_search);?>" />
        </div>

        <div class="btn-group pull-left hidden-phone">
            <button class="btn hasTooltip" type="submit" title="<?php print _JSHOP_SEARCH?>">
                <i class="icon-search"></i>
            </button>
            <button class="btn hasTooltip" onclick="document.id('text_search').value='';this.form.submit();" type="button" title="<?php print _JSHOP_CLEAR?>">
                <i class="icon-remove"></i>
            </button>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="js-stools-container-filters">
        <div class="js-stools-field-filter">
            <?php print $this->select_group?>
        </div>
    </div>

    <?php print $this->tmp_html_filter_end?>
</div>
 
<table class="table table-striped" width="100%">
<thead>
<tr>
 <th width="20">
   #
 </th>
 <?php if(!$select_user) {?> 
 <th width="20">
   <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
 </th>
 <?php }?>
 <th align="left">
   <?php echo JHTML::_('grid.sort', _JSHOP_NUMBER, 'number', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <th align="left">
   <?php echo JHTML::_('grid.sort', _JSHOP_USERNAME, 'u_name', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <th width="150" align="left">
   <?php echo JHTML::_('grid.sort', _JSHOP_USER_FIRSTNAME, 'f_name', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <th width="150" align="left">
   <?php echo JHTML::_('grid.sort', _JSHOP_USER_LASTNAME, 'l_name', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <th align="left">
   <?php echo JHTML::_('grid.sort', _JSHOP_FIRMA, 'firma_name', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <th>
    <?php echo JHTML::_('grid.sort', _JSHOP_EMAIL, 'U.email', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <?php print $this->tmp_html_col_after_email?>
 <th>
    <?php echo JHTML::_('grid.sort', _JSHOP_USERGROUP_NAME, 'usergroup_name', $this->filter_order_Dir, $this->filter_order)?>
 </th>
 <?php if(!$select_user) {?> 
 <th class="center">
    <?php print _JSHOP_ORDERS;?>
 </th>
 <th class="center">
    <?php print _JSHOP_ENABLED;?>
 </th>
 <th width="50" class="center">
    <?php echo _JSHOP_EDIT;?>
</th>
 <?php }?>
<th width="40" class="center">
    <?php echo JHTML::_('grid.sort', _JSHOP_ID, 'user_id', $this->filter_order_Dir, $this->filter_order)?>
</th>
<?php print $this->tmp_html_col_after_id?>
</tr>
</thead> 
<?php $i=0; foreach($rows as $row){?>

<tr class="row<?php echo ($i  %2);?>" >
    
 <td>
   <?php echo $pageNav->getRowOffset($i);?>
 </td>
 <?php if(!$select_user) {?> 
 <td>
   <?php echo JHtml::_('grid.id', $i, $row->user_id);?>
 </td>
 <?php }?>
 <td>
     <?php if($select_user) {?> <a onclick="window.parent.selectUserBehaviour('<?php echo $row->user_id?>','<?php echo $this->e_name?>')" href="#"> <?php }?>
   <?php echo $row->number;?>
    <?php if($select_user) {?> </a> <?php }?>
 </td>
 <td>
  <?php if(!$select_user) {?>    
   <a href="index.php?option=com_jshopping&controller=users&task=edit&user_id=<?php echo $row->user_id?>">
     <?php echo $row->u_name?>
   </a>
  <?php }else{?>
   <a onclick="window.parent.selectUserBehaviour('<?php echo $row->user_id?>','<?php echo $this->e_name?>')" href="#">  
     <?php echo $row->u_name?>
   </a>
  <?php }?>
 </td>
 <td>
   <?php echo $row->f_name;?>
 </td>
 <td>
   <?php echo $row->l_name;?>
 </td>
 <td>
   <?php echo $row->firma_name;?>
 </td>
 <td>
   <?php echo $row->email;?>
 </td>
 <?php print $row->tmp_html_col_after_email?>
 <td>
   <?php echo $row->usergroup_name;?>
 </td>
 <?php if(!$select_user) {?> 
 <td class="center">
   <a class="btn btn-mini" href='index.php?option=com_jshopping&controller=orders&client_id=<?php print $row->user_id?>' target='_blank'>
    <?php print _JSHOP_ORDERS?>
   </a>
 </td>
 <td class="center">
   <?php echo JHtml::_('jgrid.published', !$row->block, $i);?>
 </td>
 <td class="center">
    <a class="btn btn-micro" href='index.php?option=com_jshopping&controller=users&task=edit&user_id=<?php print $row->user_id?>'>
        <i class="icon-edit"></i>
    </a>
 </td>
 <?php }?>
 <td class="center">
    <?php print $row->user_id?>
 </td>
<?php print $row->tmp_html_col_after_id?>
</tr>

<?php 
$i++;
}?>
<tfoot>
<tr>
    <?php print $this->tmp_html_col_before_td_foot?>
	<td colspan="12">
		<div class = "jshop_list_footer"><?php echo $pageNav->getListFooter(); ?></div>
        <div class = "jshop_limit_box"><?php echo $pageNav->getLimitBox(); ?></div>
	</td>
    <?php print $this->tmp_html_col_after_td_foot?>
</tr>
</tfoot>  
</table>
<input type="hidden" name="filter_order" value="<?php echo $this->filter_order?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter_order_Dir?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php print $this->tmp_html_end?>
</form>
</div>
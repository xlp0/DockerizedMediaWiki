<?php
/* Smarty version 3.1.39, created on 2021-04-28 03:55:50
  from 'wiki:Google Spreadsheet' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_6088dcc62bc9b2_04354495',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a27e13d66cdea162188301b25fc351fdd832bdb1' => 
    array (
      0 => 'wiki:Google Spreadsheet',
      1 => 20210428031001,
      2 => 'wiki',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6088dcc62bc9b2_04354495 (Smarty_Internal_Template $_smarty_tpl) {
?><iframe width="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['width']->value, ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 600 : $tmp);?>
" height="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['height']->value, ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 400 : $tmp);?>
" style="border: none" src="//docs.google.com/spreadsheets/d/<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['key']->value));?>
/pubhtml?widget=true&amp;headers=false"></iframe><?php }
}

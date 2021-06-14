<?php
/* Smarty version 3.1.39, created on 2021-04-28 03:55:50
  from 'wiki:Google Document' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_6088dcc62f4405_66143050',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd2f83e4e01b31172ffa1d816a0f26db1c0db5e69' => 
    array (
      0 => 'wiki:Google Document',
      1 => 20210428033036,
      2 => 'wiki',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6088dcc62f4405_66143050 (Smarty_Internal_Template $_smarty_tpl) {
?><iframe width="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['width']->value, ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 500 : $tmp);?>
" height="<?php echo (($tmp = @htmlspecialchars($_smarty_tpl->tpl_vars['height']->value, ENT_QUOTES, 'UTF-8', true))===null||$tmp==='' ? 300 : $tmp);?>
" src="//docs.google.com/<?php if ((isset($_smarty_tpl->tpl_vars['id']->value))) {?>document/pub?id=<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['id']->value));?>
&amp;embedded=1<?php } elseif ((isset($_smarty_tpl->tpl_vars['key']->value))) {?>View?docID=<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['key']->value));?>
&hgd=1<?php }?>" style="border: none"></iframe><?php }
}

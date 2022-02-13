<?php
/* Smarty version 3.1.39, created on 2021-04-28 03:55:50
  from 'wiki:Google Presentation' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.39',
  'unifunc' => 'content_6088dcc627ae73_88270224',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7451f0d2c5542e1282e409114d5d5971e9e96316' => 
    array (
      0 => 'wiki:Google Presentation',
      1 => 20210428034717,
      2 => 'wiki',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6088dcc627ae73_88270224 (Smarty_Internal_Template $_smarty_tpl) {
?>

<?php if ($_smarty_tpl->tpl_vars['size']->value == 'medium') {?><iframe src='https://docs.google.com/presentation/d/<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['docid']->value));?>
/preview' frameborder='0' width='555' height='451' ></iframe><?php } elseif ($_smarty_tpl->tpl_vars['size']->value == 'large') {?><iframe src='https://docs.google.com/presentation/d/<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['docid']->value));?>
/preview' frameborder='0' width='700' height='559'> </iframe><?php } else { ?><iframe src='https://docs.google.com/presentation/d/<?php echo str_replace("%2F", "/", rawurlencode($_smarty_tpl->tpl_vars['docid']->value));?>
/preview' frameborder='0' width='388' height='342'></iframe><?php }
}
}

<?php /* Smarty version 2.6.21, created on 2014-10-16 07:37:29
         compiled from file:/data/htdocs/ifadmin/gameengine/game/mvc/views//index/index.tpl */ ?>
<html><head>
<title>GM System</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
  <frameset cols="160,*" id="frame">
	<frame src="http://<?php echo $this->_tpl_vars['server_host']; ?>
/gm/?sid=<?php echo $this->_tpl_vars['sid']; ?>
" style="overflow-x: hidden;" id="leftFrame" name="leftFrame" noresize="noresize" marginwidth="0" marginheight="0" frameborder="0" target="main" scrolling="auto">
	<frame src="http://<?php echo $this->_tpl_vars['server_host']; ?>
/activity/facebook.tpl?sid=<?php echo $this->_tpl_vars['sid']; ?>
" name="main" marginwidth="0" marginheight="0" frameborder="0" target="_self" scrolling="auto">
  </frameset>
<noframes>
  <body></body>
    </noframes>
<div style="position: absolute; display: none; z-index: 9999;" id="livemargins_control"></div></frameset></html>
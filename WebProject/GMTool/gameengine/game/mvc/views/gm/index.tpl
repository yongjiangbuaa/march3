<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>管理页面</title>
<script language="javascript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/jquery-1.4.4.min.js"></script>
<style type="text/css">
body {
  font-family:"微软雅黑",Verdana,"宋体", Geneva, sans-serif;
  font-size: 14px;
}

p {
  line-height: 1.5em;
}

ul.menu, ul.menu ul {
  list-style-type:none;
  margin: 0;
  padding: 0;
  width: 100%;
}

ul.menu a {
  display: block;
  text-decoration: none;	
}

ul.menu li {
	border-bottom:1px #8ba7bf solid;
}

ul.menu li a {
  background: #00707B;
  color: #fff;	
  padding: 0.5em;
  font-weight:bold;
}

ul.menu li a:hover {
  background: #00404B;
  font-weight:bold;
}

ul.menu li ul li a {
  background: #c8dae8;
  color: #16456f;
  padding-left: 20px;
  font-weight:bold;
}

ul.menu li ul li a:hover {
  background: #f2f7f9;
  border-left:solid;
  padding-left: 15px;
  font-weight:bold;
}
.flag{
 background: #f2f7f9;
  padding-left: 15px;
  font-weight:bold;
}


.code { border: 1px solid #ccc; list-style-type: decimal-leading-zero; padding: 5px; margin: 0; }
.code code { display: block; padding: 3px; margin-bottom: 0; }
.code li { background: #ddd; border: 1px solid #ccc; margin: 0 0 2px 2.2em; }
.indent1 { padding-left: 1em; }
.indent2 { padding-left: 2em; }
.indent3 { padding-left: 3em; }
.indent4 { padding-left: 4em; }
.indent5 { padding-left: 5em; }
</style>
<script language="javascript">
function initMenus() {
	$('ul.menu ul').hide();
	$.each($('ul.menu'), function(){
		$('#' + this.id + '.expandfirst ul:first').show();
	});
	$('ul.menu li a').click(
		function() {
			var checkElement = $(this).next();
			var parent = this.parentNode.parentNode.id;

			if($('#' + parent).hasClass('noaccordion')) {
				$(this).next().slideToggle('normal');
				return false;
			}
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				if($('#' + parent).hasClass('collapsible')) {
					$('#' + parent + ' ul:visible').slideUp('normal');
				}
				return false;
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('#' + parent + ' ul:visible').slideUp('normal');
				checkElement.slideDown('normal');
				return false;
			}
		}
	);
}

function clickMenu(){  
    $("#menu4 li ul li").click(function(){
 		$("#menu4 li ul li").removeClass("flag");//首先移除全部的active
 		$(this).removeClass();//选中的添加acrive
 		$(this).addClass("flag");//选中的添加acrive
 });

}


$(document).ready(function() {initMenus();clickMenu();});
</script>

</head><body>
	<ul id="menu4" class="menu collapsible expandfirst">
   		<li>
			<a href="#"> 数据统计</a>
			<ul style="display: none;">
                 <li><a href="http://<{$server_host}>/user/list?sid=<{$sid}>" target="main">玩家数据</a></li>
            </ul>
		</li>
		<li>
			<a href="#"> GM后台</a>
			<ul style="display: block;">
            	<li class="flag"><a href="http://<{$server_host}>/user?sid=<{$sid}>" target="main">游戏用户管理</a></li>
            	<li><a href="http://<{$server_host}>/gm/mysql?sid=<{$sid}>" target="main">数据库工具</a></li>
		    </ul>
		</li>
		<li>
			<a href="#">数据修复</a>
			<ul style="display: none;">
            </ul>
		</li>
	</ul>

</body></html>
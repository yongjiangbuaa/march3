<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body {font-family: "Trebuchet MS",Verdana,sans-serif;}
a{color:#0098CB;text-decoration:none; } 
a:hover {color:#0098CB;text-decoration:underline;cursor:pointer;}
.truecolor {
border-collapse: collapse;
}
.truecolor td {
border:1px solid #ccc;
font-size:13px;
text-align:center;
padding-bottom:4px;
padding-left: 4px;
padding-right: 4px;
padding-top: 4px;
}

.content{
width:180px;
white-space:nowrap;
word-break:keep-all;
overflow:hidden;
text-overflow:ellipsis;
}

.truecolor th {color:#0098CB;background-color:#DDDDDD;border-color: #DDDDDD;padding:2px;font-size:13px;border:1px solid #DDDDDD;}
.truecolor tr:hover{background-color:#FFFFDD; color:#333;}
.truecolor tr {
border-bottom-color: #EEEEEE;
border-bottom-style: solid;
border-bottom-width: 1px;
height:30px;
}
.color1 {background-color:#fff; color:#333;}
.color2 {background-color:#E4E4E4; color:#333;}
.icon {
background-position: 0 50%;
background-repeat: no-repeat;
padding-bottom: 3px;
padding-left: 20px;
padding-top: 2px;
}
.s_icon {
background-image: url("http://<{$img}>/images/save.png"); 
font-size:13px;
}
.a_icon{
background-image:url('http://<{$img}>/images/add.png');
}
.f_icon{
background-image: url("http://<{$img}>/images/fav.png"); 
}

.e_icon{
background-image: url("http://<{$img}>/images/feed.png"); 
}

.c_icon {
margin:0 auto;
background-image:url('http://<{$img}>/images/true.png');
}
</style>

<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/json2.js"></script>
<script language="javascript" type="text/javascript">

function userList(page){
	var api = 'gm/gm/user';
	var publishID = '<{$publishID}>';
	var uid = '';
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":publishID,
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"",
			"changes":[],
			"params":{
				"uid":uid,
				"type":1,
				"page":page,
			}}]};

	
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			var pager = user.pager;
			if(code == 200 && user== null){
				$("#show").text('not found!');
				return false;
			}
			if(code == 200){
				var html = "<table class='truecolor' border=1 width=100%>";
				html += "<tr>  <th width='180'>游戏名</th><th>PlatformAddress</th> <th>等级</th> <th>坐标x</th> <th>坐标y</th> <th>用户金币</th> <th>系统金币</th> <th>建筑序列</th> <th>统帅经济</th> <th>统帅建设</th> <th>背包空间</th> <th>武将空间</th> </tr>";
				$.each(user.data,function(entryIndex,entry){
					var flag = entryIndex%2 + 1;
                    html += "<tr class='color" + flag + "' ><td>" + entry['name'] + "</td>";
                    html += "<td>" + entry['platformAddress'] + "</td>";
                    html += "<td>" + entry['level'] + "</td>";
                    html += "<td>" + entry['x'] + "</td>";
                    html += "<td>" + entry['y'] + "</td>";
                    html += "<td>" + entry['user_gold'] + "</td>";
                    html += "<td>" + entry['system_gold'] + "</td>";
                    html += "<td>" + entry['constructCount'] + "</td>";
                    html += "<td>" + entry['economy'] + "</td>";
                    html += "<td>" + entry['construct'] + "</td>";
                    html += "<td>" + entry['inventorySpace'] + "</td>";
					html += "<td>" + entry['generalSpace'] + "</td></tr>";
                });
                html += "</table></p>";
                if(pager != null){
                	html += "<div>" + pager + "</div>";
                }
                var limit = 1000;
                var out = '';
                if(user.total != null){
                	for(var i=0;i<user.total;i+=limit){
                		var res_start = i + 1;
                		var res_end = i + limit;
                		out += '<option value=' + i +'>' + res_start + '-' + res_end + '</option>';
                	}
                }
				$("#show").html(html);
				$("#out").html(out);
			}
	});
}

function check(key){
	var pattern = /[^0-9]/g;
	if(pattern.test(key.value)){
		alert('Please enter the number');
		$("#turn").val('');
	}
	return false;
}

function turnPage(){
	var page = $("#turn").val();
	userList(page);
}

function excel(){
	var page = $("select option:selected").val();
	 window.location.href= "http://<{$server_host}>/user/exportExcel?sid=<{$sid}>&type=2&offset=" + page;
}

$(document).ready(function(){userList(1);});
</script>
</head>
<body>
<div style="width:100%;text-align:center;">
	<div style='color:#0098CB;'>分批导出用户数据，请选择：<select id='out'></select> &nbsp;<a class='e_icon icon' href='#' onclick='excel();'>导出</a></div>
	<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
	<p>
	<div id='show'></div>
</div>
</body>
</html>


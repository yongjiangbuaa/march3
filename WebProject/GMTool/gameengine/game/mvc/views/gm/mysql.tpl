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

.c_icon {
margin:0 auto;
background-image:url('http://<{$img}>/images/true.png');
}
.e_icon{
background-image: url("http://<{$img}>/images/feed.png"); 
}
.b_icon{
background-image: url("http://<{$img}>/images/changeset.png");
}
</style>
<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/json2.js"></script>
<script language="JavaScript" type="text/javascript">
var tableIndex = new Array();
var tableAttr = new Array("name","scale","type","max_length","not_null","primary_key","auto_increment","binary","unsigned","zerofill","has_default","default_value");
function mysql(type){
	var api = 'gm/gm/Mysql';
	var platform_uid = '<{$publishID}>';
	var uid = '';
	var sql = '';
	var table = '';
	if(type == 5)
		sql = $("#sql").val();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":platform_uid,
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"",
			"changes":[],
			"params":{
				"type":type,
				"sql":sql,
			}}]};
	params = JSON.stringify(params);
	$("#showResult").text('执行中...');
	$("#show").html('');
	$.ajax({
		  url: 'http://' + '<{$server_host}>' + '/rest/' + api,
		  data: params,
		  type: 'POST',
		  success: function(data){
			  var data = JSON.parse(data);
			  var code = data.code;
				if(code == 200){
					$("#showResult").text('success!');
				}
		 }
	});
}
function getMysqlData(page){
	var api = 'gm/gm/Mysql';
	var platform_uid = '<{$publishID}>';
	var uid = '';
	var tablename = $("#tablename").val();
	var where1 = $("#where1").val();
	var condition1 = $("#condition1").val();
	var num1 = $("#num1").val();
	var where2 = $("#where2").val();
	var condition2 = $("#condition2").val();
	var num2 = $("#num2").val();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":platform_uid,
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"",
			"changes":[],
			"params":{
				"tablename":tablename,
				"type":3,
				"page":page,
				"where1":where1,
				"condition1":condition1,
				"num1":num1,
				"where2":where2,
				"condition2":condition2,
				"num2":num2,
			}}]};
	params = JSON.stringify(params);
	$("#showResult").text('执行中...');
	$.ajax({
		  url: 'http://' + '<{$server_host}>' + '/rest/' + api,
		  data: params,
		  type: 'POST',
		  success: function(data){
			  var data = JSON.parse(data);
			  var sqlData = data.data.data;
			  var page = data.data.page;
			  var code = data.code;
			  var titleName = data.data.show;
 				if(code == 200)
				{
					$("#showResult").text('no data!');
					$("#show").html('');
				}

				if(code == 200 && sqlData.length > 0){
					$("#showResult").text('success!');
					var html = "<table class='truecolor' border=1 width=100%>";
					var title = false;
					var flag = 1;
					for(var i=0;i<sqlData.length;i++){ 
						if(!title)
						{
							html += "<tr>";
							for(var j in sqlData[i]){
								html += "<th>" + j + "</th>";
							}
							html += "</tr>";
							title = true;
						}
						flag = flag%2 + 1;
						html += "<tr class='color" + flag + "' >";
						for(var j in sqlData[i]){
							html += "<td>" + sqlData[i][j] + "</td>";
						}
						html += "</tr>";
					}
					html += "</table></p>";
					if(page != null){
						html += "<div>" + page + "</div>";
					}
					$("#show").html(html);
				}
			}
	});
}
function getTabelStruct(){
	tableIndex = new Array();
	var api = 'gm/gm/Mysql';
	var platform_uid = '<{$publishID}>';
	var uid = '';
	var table = '';
	table = $("#table").val();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":platform_uid,
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"",
			"changes":[],
			"params":{
				"type":6,
				"table":table,
			}}]};
	params = JSON.stringify(params);
	$("#showResult").text('执行中...');
	$("#show").html('');
	$.ajax({
		url: 'http://' + '<{$server_host}>' + '/rest/' + api,
		data: params,
		type: 'POST',
		success: function(data){
			var data = JSON.parse(data);
			var code = data.code;
			var struct = data.data.struct;
			if(code == 200){
				$('#d_4').show();
				var html = "<table class='truecolor' border=1 width=100%>";
				var flag = 1;
				html += "<tr><th>index</th>";
				for(var i in tableAttr){
					html += "<th>" + tableAttr[i] + "</th>";
				}
				html += "</tr>";
				$.each(struct,function(entryIndex,entry){
					flag = flag%2 + 1;
					html += "<div class='div"+entryIndex+"'>";
					html += "<tr class='color" + flag + "' >";
					html += "<td>" + entryIndex + "</td>";
					tableIndex.push(entryIndex);
					html += "<td><input type='text' id='"+ entryIndex + "_name' value='" + entry['name'] + "' size = 5></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_scale' value='" + entry['scale'] + "' size = 5 disabled></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_type' value='" + entry['type'] + "' size = 10 disabled></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_max_length' value='" + entry['max_length'] + "' size = 5></td>";
					if(entry['has_default'])
						html += "<td><select id='"+ entryIndex + "_not_null'><Option value=0>true</Option><Option value=1>false</Option></select></td>";
					else
						html += "<td><select id='"+ entryIndex + "_not_null'><Option value=0>true</Option><Option value=1 selected>false</Option></select></td>";
					//html += "<td><input type='text' id='"+ entryIndex + "_not_null' value='" + entry['not_null'] + "' size = 5></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_primary_key' value='" + entry['primary_key'] + "' size = 5></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_auto_increment' value='" + entry['auto_increment'] + "' size = 5 disabled></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_binary' value='" + entry['binary'] + "' size = 5 disabled></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_unsigned' value='" + entry['unsigned'] + "' size = 5 disabled></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_zerofill' value='" + entry['zerofill'] + "' size = 5 disabled></td>";
					//html += "<td><input type='text' id='"+ entryIndex + "_has_default' value='" + entry['has_default'] + "' size = 5></td>";
					if(entry['has_default'])
						html += "<td><select id='"+ entryIndex + "_has_default'><Option value=0>true</Option><Option value=1>false</Option></select></td>";
					else
						html += "<td><select id='"+ entryIndex + "_has_default'><Option value=0>true</Option><Option value=1 selected>false</Option></select></td>";
					html += "<td><input type='text' id='"+ entryIndex + "_default_value' value='" + entry['default_value'] + "' size = 5></td>";
					html += "</tr></div>";
					//$("#"+ entryIndex + "_has_default").val(1).attr("selected", 1);
				});
				html += "</table></p>";
				$("#showResult").text('success!');
				$("#show").html(html);
			}
		}
	});
}
function modifyTabelStruct(){
	$('#d_4').hide();
	var api = 'gm/gm/Mysql';
	var platform_uid = '<{$publishID}>';
	var uid = '';
	var table = '';
	var struct = new Object();
	table = $("#table").val();
	$("#showResult").text('loading...');
	var html = "<table class='truecolor' border=1 width=100%>";
	var flag = 1;
	html += "<tr><th>index</th>";
	for(var i in tableAttr){
		html += "<th>" + tableAttr[i] + "</th>";
	}
	html += "</tr>";
	for(var i in tableIndex){
		flag = flag%2 + 1;
		html += "<tr class='color" + flag + "' >";
		html += "<td>" + tableIndex[i] + "</td>";
		struct[tableIndex[i]] = new Object();
		var temp = 0;
		for(var j in tableAttr){
			if(tableAttr[j] == 'has_default' || tableAttr[j] == 'not_null')
				temp = $("#"+tableIndex[i]+"_"+tableAttr[j]+" option:selected").text();
			else
				temp = $("#"+tableIndex[i]+"_"+tableAttr[j]).val();
			struct[tableIndex[i]][tableAttr[j]] = temp;			
			html += "<td>" + temp + "</td>";
		}
		html += "</tr>";
	}
	html += "</table></p>";
	$("#showResult").text('success!');
	$("#show").html(html);
		var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":platform_uid,
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"",
			"changes":[],
			"params":{
				"type":7,
				"table":table,
				"newStruct":struct,
				"tableIndex":tableIndex,
			}}]};
	params = JSON.stringify(params);
	$.ajax({
		url: 'http://' + '<{$server_host}>' + '/rest/' + api,
		data: params,
		type: 'POST',
	});
}
function turnPage(){
	var page = $("#turn").val();
	getMysqlData(page);
}
function clearAll(){
	$('#d_1').hide();
	$('#d_2').hide();
	$('#d_3').hide();
	$('#d_4').hide();
	$('#show').html('');
}
</script>
</head>
<body>
<div style="width:100%;text-align:center;">
	<div style="TEXT-ALIGN:center"><span id="showResult" style="font-size:2;color:red"></span></div></p>
	<div style="TEXT-ALIGN:center;color:#0098CB;">
		数据库初始化：
		<a class='c_icon icon' href='#' onclick="mysql(1);">生成所有表</a>&nbsp;&nbsp;
		<a class='c_icon icon' href='#' onclick="clearAll();mysql(2);">初始化世界</a>&nbsp;&nbsp;
		<a class='c_icon icon' href='#' onclick="clearAll();mysql(4);">MYSQL配置信息</a>&nbsp;&nbsp;
		<br /><br />数据库操作：
		<a class='c_icon icon' href='#' onclick="clearAll();$('#d_1').show();">获得数据库内容</a>&nbsp;&nbsp;
		<a class='b_icon icon' href='#' onclick="clearAll();$('#d_2').show();">执行数据库语句</a>&nbsp;&nbsp;
		<a class='b_icon icon' href='#' onclick="clearAll();$('#d_3').show();">修改数据库结构</a>&nbsp;&nbsp;
	</div>
	<div style='TEXT-ALIGN:center;color:#0098CB;display:none' id='d_1'>
		<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
		表名: <input type='text' id='tablename' value='world'>&nbsp; &nbsp; 
		<br /><p>条件查询：如level>10：条件1输入level，符号输入>，条件值输入10
		<br /><p>
		条件1 <input type='text' id='where1' value=''>&nbsp; &nbsp; 
		符号 <input type='text' id='condition1' value=''>&nbsp; &nbsp; 
		条件值 <input type='text' id='num1' value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<br /><p>
		条件2 <input type='text' id='where2' value=''>&nbsp; &nbsp; 
		符号 <input type='text' id='condition2' value=''>&nbsp; &nbsp; 
		条件值 <input type='text' id='num2' value=''>&nbsp; &nbsp; 
		<a class='c_icon icon' href='#' onclick="getMysqlData(1);">查询</a>
	</div>
	<div style='TEXT-ALIGN:center;color:#0098CB;display:none' id='d_2'>
		<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
		执行语句: <input type='text' id='sql' value='' size = 100>&nbsp; &nbsp; 
		<a class='c_icon icon' href='#' onclick="mysql(5);">执行</a>
	</div>
	<div style='TEXT-ALIGN:center;color:#0098CB;display:none' id='d_3'>
		<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
		输入需要修改的Table名: <input type='text' id='table' value='test' size = 20>&nbsp; &nbsp; 
		<a class='c_icon icon' href='#' onclick="getTabelStruct();">点击查询表结构</a>
	</div>
	<div style='TEXT-ALIGN:center;color:#0098CB;display:none' id='d_4'>
		<a class='b_icon icon' href='#' onclick="modifyTabelStruct();">点击保存修改</a>
	</div>
</div>
<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
<div style="width:100%;height:800px;text-align:center;">
	<div id='show' style="width:100%;text-align:center;margin:0 auto;"></div>
</div>
</body>
</html>

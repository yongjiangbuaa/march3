<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body {font-family: "Trebuchet MS",Verdana,sans-serif;}
a{color:#0098CB;text-decoration:none; }
a:hover {color:#0098CB;text-decoration:underline;}
.truecolor {border:1px solid #DDDDDD;text-align:center; background-color: #FFFFDD;border-collapse:collapse; color:#0098CB}
.truecolor td { border:1px solid #FFFFDD; padding:8px;}
.truecolor th {background-color:#FFFFDD; color:#FFFFDD;padding:10px;}
#backgroundPopup{	
	display:none;
	position:fixed;	
	_position:absolute;	
	height:100%;	
	width:100%;	
	top:0;	
	left:0;	
	background:#000000;
	border:1px solid #cecece;	
	z-index:1;	
}	
#popupContact{
	overflow:scroll;
	text-align:center;
	display:none;	
	position:fixed;	
	_position:absolute;	
	height:600px;	
	width:700px; 
	background:#FFFFFF;	
	border:2px solid #cecece;	
	z-index:2;	
	padding:12px;	
	font-size:13px; 
}
#popupContact h1{	
	text-align:left;	
	color:#0098CB;	
	font-size:14px;	
	font-weight:700;	
	border-bottom:1px dotted #D3D3D3;	
	padding-bottom:2px;	
	margin-bottom:20px;	
}
#popupContact th{
	font-size:14px;	
	color:#0098CB; 
}	
#popupContactClose{	
	font-size:14px;	
	line-height:14px;	
	right:6px;	
	top:4px;	
	position:absolute;	
	color:#6fa5fd;	
	font-weight:700;
	display:block;	
}
.general{
	display:none;
}

.good {
	display:none;	
}

.science{
	display:none;
}

.gem{
	display:none;
}
.city{
	display:none;
}
.lord{
	display:none;
}
.a_button{width:60px;height:24px;*padding-top:3px;border:#666666 1px solid;cursor:pointer}
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
	font-size:13px;
}
.s_icon {
	background-image: url("http://<{$img}>/images/save.png"); 
	font-size:13px;
}
.a_icon{
	background-image:url('http://<{$img}>/images/add.png');
	font-size:13px;
}
</style>

<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="http://<{$server_host}>/gameengine/test/js/json2.js"></script>
<script language="JavaScript" type="text/javascript">


function userInfo(){
	var user_name = $("#user_name").val();
	var user_uid = $("#user_id").val();
	var p_uid = $("#platform_uid").val();
	var pa_uid = $("#platform_address").val();
	var platform_uid = '<{$publishID}>';
	$(".general").hide();
	$(".good").hide();
	$(".science").hide();
	$(".city").hide();
	$(".lord").hide();
	$("#item").html('');
	$("#map").html('');
	
	if(user_name == '' && user_uid == '' && p_uid == ''){
		$("showResult").text('Please enter name or UID');
		return false;
	}
	if(user_name != ''){
		return false;
		var api = 'user/user/query';
		var params = {
			"sign":'<{$sign}>',
			"platform_user_uid":user_name,
			"platform_uid":platform_uid,
			"type":1,
		}
	}
	if(user_uid != ''){
		var api = 'user/user/get';
		var params = {
			"id":"",
			"info":{
				"platformAppId":"",
				"platformUserId":"",
				},
			"sign":'<{$sign}>',
			"data":{
				0:{gameUserId:user_uid},
			},
			"platform_uid":platform_uid,
		}
	}
	if(p_uid != ''){
		var api = 'user/user/get';
		var params = {
			"id":"",
			"info":{
				"platformAppId":"",
				"platformUserId":"",
				},
			"sign":'<{$sign}>',
			"data":
			{
				0:{
					platformUserId:p_uid,
					platformAppId:pa_uid
				},
			},
			"platform_uid":platform_uid,
			"type":3,
		}
	}
	params = JSON.stringify(params);
	$("#showResult").text('');
	$.ajax({
		url: 'http://' + '<{$server_host}>' + '/rest/' + api,
		data: params,
		type: 'POST',
		success: function(data){
			var data = JSON.parse(data);
				var user = data.data[0];
				if(data.code == 200 && user== null){
					$("#showResult").text('not found!');
					$("#query_body").hide();
					return false;
				}
				if(data.code == 200 && user!= null){
					$("#user_name").val('');
					$("#user_id").val('');
					$("#query_body").show();
					$("#user_uid").text(user.uid);
					$("#p_name").text(user.name);
					$("#level").val(user.level);
					$("#user_gold").val(user.user_gold);
					$("#system_gold").val(user.system_gold);
					$("#pic").val(user.pic);
					$("#vip").val(user.vip);
					$("#x").text(user.x);
					$("#y").text(user.y);
					$("#platformAddress").val(user.platformAddress);
					$("#w_info").text();
				}
		}
	});
}

function submit(){
	var api = 'user/user/modify';
	var uid = $("#user_uid").text();
	var system_gold = $("#system_gold").val();
	var pic = $("#pic").val();
	var vip = $("#vip").val();
	var level = $("#level").val();
	var user_gold = $("#user_gold").val();
	var platformAddress = $("#platformAddress").val();
	
	$("#w_info").text('');
	if(parseInt(system_gold) != system_gold){
		$("#w_info").text('Please enter a valid value of system_gold');
		$("#system_gold").focus();
		return false;
	}
	if(parseInt(pic) != pic){
		$("#w_info").text('Please enter a valid value of pic');
		$("#pic").focus();
		return false;
	}
	if(parseInt(vip) != vip){
		$("#w_info").text('Please enter a valid value of vip');
		$("#vip").focus();
		return false;
	}
	if(parseInt(level) != level){
		$("#w_info").text('Please enter a valid value of level');
		$("#level").focus();
		return false;
	}
	if(parseInt(user_gold) != user_gold){
		$("#w_info").text('Please enter a valid value');
		$("#user_gold").focus();
		return false;
	}

	if(platformAddress == ''){
		$("#w_info").text('Please enter a valid value');
		$("#platformAddress").focus();
		return false;
	}

	var params = {
		"sign":'<{$sign}>',
		"info":{
			"gameUserId":uid,
		},
		"platform_app":'<{$publishID}>',
		"data":{
			"system_gold":system_gold,
			"pic":pic,
			"vip":vip,
			"level":level,
			"user_gold":user_gold,
			"platformAddress":platformAddress,
		}};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//$("#result").text(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			if(data.code == 400){
				$("#w_info").text('not_found!');
				return false;
			}
			if(data.code == 200){
				$("#user_uid").text(user.uid);
				$("#user_id").val('');
				$("#query_body").show();
				$("#user_uid").text(user.uid);
				$("#p_name").text(user.name);
				$("#level").val(user.level);
				$("#user_gold").text(user.user_gold);
				$("#system_gold").val(user.system_gold);
				$("#pic").val(user.pic);
				$("#vip").val(user.vip);
				$("#x").text(user.x);
				$("#y").text(user.y);
				$("#platformAddress").val(user.platformAddress);
				alert('update success');
			}
	});
	$('#submit_btn').attr('disabled', false);
}

function userItem(){
	$("#item").html('');
	$("#map").html('');
	$(".general").hide();
	$(".science").hide();
	$(".good").hide();
	$(".city").hide();
	$(".lord").hide();
	var item_type = $("select option:selected").val();
	var api = 'gm/gm/GmService';
	var uid = $("#user_uid").text();
	var state = $("#is_used:checked").val();
	if(typeof(state) == 'undefined'){
		state = 0;
	}
	if(item_type == 2){
		//查询游戏所有将军
		$("#t").val(0);
		$(".general").show();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"general_type":0,
					"type":8,
				}}]};
		params = JSON.stringify(params);
		//alert(params);
		$.post(
			'http://' + '<{$server_host}>' + '/rest/' + api,
			params,
			function(data){
				//alert(data);return;
				var data = JSON.parse(data);
				var user = data.data;
				var code = data.code;
				if(code == 200 && user== null){
					alert('not found!');
					return false;
				}
				if(code == 200){
					var html = '';
					$.each(user.general,function(entryIndex,entry){
						html += "<Option value=" + entry['id'] + ">" + entry['name'] + " &nbsp;&nbsp;统:" + entry['govern'] + " 勇:" + entry['brave'] + " 智:" + entry['wisdom'] + "</option>";
						});
						html += "<p>";
					$("#n").html(html);
				}
		});
		var content = '添加可招募武将';
		if(state == 1){
			content = '添加在野武将';	
		}
		$("#add_g").html(content);
	}
	if(item_type == 3){
		//查询游戏所有物品
		$("#g_t").val(0);
		$("#g_c").val(0);
		$(".good").show();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"good_type":0,
					"publishID":'<{$publishID}>',
					"color":0,
					"type":11,
				}}]};
		params = JSON.stringify(params);
		//alert(params);
		$.post(
			'http://' + '<{$server_host}>' + '/rest/' + api,
			params,
			function(data){
				//alert(data);return;
				var data = JSON.parse(data);
				var user = data.data;
				var code = data.code;
				if(code == 200 && user== null){
					alert('not found!');
					return false;
				}
				if(code == 200){
					var html = '';
					$.each(user.inventory,function(entryIndex,entry){
						html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
						});
						html += "<p>";
					$("#g_n").html(html);
				}
		});
	}
	if(item_type == 4){
		//查询游戏所有科技
		$("#s_t").val(0);
		$(".science").show();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"science_type":0,
					"type":14,
				}}]};
		params = JSON.stringify(params);
		//alert(params);
		$.post(
			'http://' + '<{$server_host}>' + '/rest/' + api,
			params,
			function(data){
				//alert(data);return;
				var data = JSON.parse(data);
				var user = data.data;
				var code = data.code;
				if(code == 200 && user== null){
					alert('not found!');
					return false;
				}
				if(code == 200){
					var html = '';
					$.each(user.science,function(entryIndex,entry){
						html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
						});
						html += "<p>";
					$("#s_n").html(html);
				}
		});
	}
	if(item_type == 5){
		if(!confirm('确定开启该用户所有势力地图吗？')){
			return false;
		}
		$("#map").html('please wait...');
	}
	if(item_type == 6){
		//查询游戏所有宝石
		$("#m_t").val(1);
		$(".gem").show();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"gem_type":1,
					"publishID":'<{$publishID}>',
					"type":17,
				}}]};
		params = JSON.stringify(params);
		//alert(params);
		$.post(
			'http://' + '<{$server_host}>' + '/rest/' + api,
			params,
			function(data){
				//alert(data);return;
				var data = JSON.parse(data);
				var user = data.data;
				var code = data.code;
				if(code == 200 && user== null){
					alert('not found!');
					return false;
				}
				if(code == 200){
					var html = '';
					$.each(user.inventory,function(entryIndex,entry){
						html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
						});
						html += "<p>";
					$("#m_n").html(html);
				}
		});
	}
	if(item_type == 19){
		//查询用户城市
		$(".city").show();
	}
	if(item_type == 21){
		//查询用户城市
		$(".lord").show();
	}
	//查看用户item
	var uid = $("#user_uid").text();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"user_uid":uid,
				"type":item_type,
				"status":state,
			}}]};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//alert(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200 && user== null){
				alert('not found!');
				return false			}
			if(code == 200){
				var html = '';
					//如果是武将
					if(item_type == 2){
							html += "<div><table><tr><th width='180px'>name</th><th>level</th><th width='45px'>corps</th><th width='80px'>govern</th><th width='80px'>brave</th><th width='80px'>wisdom</th><th width='60px'>延时</th><th width='50px'></th></tr>";
						$.each(user.training,function(entryIndex,entry){
								if(entry['type'] >= 7){
										var col = 'red';
										if(entry['status'] == 0){
												col = 'gray';
										}
									html += "<tr><td><font color='" + col + "'>" + entry['name'] + '<br>' + entry['end_time'] + "</font></td>";
								}else{
										html += "<tr><td>" + entry['name'] + "</td>";
								}
								html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
								html += "<td>" + entry['corps']; + "</td>"
								html += "<td>" + entry['govern_ori'] + "+<input type='text' id='govern_" + entryIndex + "' value='" + entry['govern_refresh'] + "' size=3></td>";
								html += "<td>" + entry['brave_ori'] + "+<input type='text' id='brave_" + entryIndex + "' value='" + entry['brave_refresh'] + "' size=3></td>";
								html += "<td>" + entry['wisdom_ori'] + "+<input type='text' id='wisdom_" + entryIndex + "' value='" + entry['wisdom_refresh'] + "' size=3></td>";
								if(entry['type'] >= 7 ){
										html += "<td><input type='text' id='yanshi_" + entryIndex + "' size=3 value='0'>天</td>";
								}else{
									html += "<td>---</td>"; 
								}
									html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
							});
							html += "</table></div>";
						}
						//如果是物品
						if(item_type == 3){
								html += "<p><div><table><tr><th width='200px'>name</th><th width='60px'>level</th><th width='50px'>itemId</th><th>count</th><th width='50px'></th></tr>";
									$.each(user,function(entryIndex,entry){
										html += "<tr><td>" + entry['name'] + "&nbsp; </td>";
												html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td><td>" + entry['itemId'] + "</td>";
											html += "<td><input type='text' id='count_" + entryIndex + "' value='" + entry['count'] + "' size=3></td><td>";
											html += "<td align=center><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td>";
									html += "<td align=center><a class='s_icon icon' href='#' onclick=\"removeItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">删除</a></td></tr>";
								});
								html += "</table></div>";
						}
						//如果是科技
						if(item_type == 4){
								html += "<p><div><table><tr><th width='200px'>name</th><th width='200px'>level</th><th></th></tr>";
							$.each(user.science,function(entryIndex,entry){
									html += "<tr><td>" + entry['name'] + "</td>";
								html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
								html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['science_id'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
							});
							html += "</table></div>";
						}
						if(item_type == 5){
							html += '<font size=4 color=red>success!</font>';
							$("#map").html(html);
							return false;
						}
						//如果是宝石
						if(item_type == 6){
									html += "<p><div style='text-align:center;'><table><tr><th width='360px'>name</th><th width='100px'>nums</th><th width='100px'>addition</th><th width='50px'>need_receive</th></tr>";
									$.each(user.gem_inventory,function(entryIndex,entry){
									html += "<tr><td align='left'>" + entry['name'] + "</td>";
											if(entry['receive'] == 1){
												html += "<td>" + entry['nums'] + "</td>";
											}else{
												html += "<td>" + entry['nums'] + "/10</td>";
										}
										html += "<td>" + entry['addition'] + "</td>";
										html += "<td>" + entry['receive'] + "</td></tr>";
								});
								html += "</table></div>";
						}
					//cityItem
					if(item_type == 19 || item_type == 21){
					html += "<p><div><table><tr><th width='200px'>type</th><th width='200px'>num</th><th></th></tr>";
					$.each(user,function(entryIndex,entry){
						if(entryIndex == "uid" || entryIndex == "itemId" || entryIndex == "className" || entryIndex == "instance")
						{
						}
						else
						{
							html += "<tr><td>" + entryIndex + "</td>";
							html += "<td><input type='text' id='type_" + entryIndex + "' value='" + entry + "' size=9 maxlength=9></td>";
							html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + uid + "','" + entryIndex + "'," + item_type +");\">保存</a></td></tr>";
						}
					});
					html += "</table></div>";
					}
				$("#item").html(html);
				//调用函数居中窗口
				centerPopup();	
				//调用函数加载窗口
				loadPopup();
			}
	});
}

function updateItem(uid, index_id, item_type){
	var api = 'gm/gm/GmService';
	if(item_type == 2){
		var state = $("#is_used:checked").val();
		if(typeof(state)  == 'undefined'){
			state = 0;
		}
		var level = $("#level_" + index_id).val();
		var govern = $("#govern_" + index_id).val();
		var brave = $("#brave_" + index_id).val();
		var wisdom = $("#wisdom_" + index_id).val();
		var yanshi = $("#yanshi_" + index_id).val();
		if(typeof(yanshi) == 'undefined'){
			yanshi = 0;
		}
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"general_id":uid,
					"type":7,
					"level":level,
					"govern":govern,
					"brave":brave,
					"wisdom":wisdom,
					"yanshi":yanshi,
				}}]};
	}
	if(item_type == 3){
		var level = $("#level_" + index_id).val();
		var count = $("#count_" + index_id).val();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"good_id":uid,
					"type":10,
					"count":count,
					"level":level,
				}}]};
	}
	if(item_type == 4){
		var level = $("#level_" + index_id).val();
		var user_uid = $("#user_uid").text();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"science_id":uid,
					"type":13,
					"level":level,
					"user_uid":user_uid,
				}}]};
	}
	if(item_type == 19){
		var level = $("#level_" + index_id).val();
		var user_uid = $("#user_uid").text();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"type":20,
					"user_uid":uid,
					"index":index_id,
					"num":$("#type_" + index_id).val(),
				}}]};
	}
	if(item_type == 21){
		var level = $("#level_" + index_id).val();
		var user_uid = $("#user_uid").text();
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"type":22,
					"user_uid":uid,
					"index":index_id,
					"num":$("#type_" + index_id).val(),
				}}]};
	}
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
						"id":1,
						"info":{
							"sign":'<{$sign}>',
							"publishID":'<{$publishID}>',
							"userID":uid,
							"XA_targname":null},
						"data":[{
							"name":"gm.UserAction",
							"changes":[],
							"params":{
								"user_uid":uid,
								"type":item_type,
								"status":state,
							}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
									//如果是武将
									if(item_type == 2){
											html += "<div><table><tr><th width='180px'>name</th><th>level</th><th width='45px'>corps</th><th width='80px'>govern</th><th width='80px'>brave</th><th width='80px'>wisdom</th><th width='60px'>延时</th><th width='50px'></th></tr>";
										$.each(user.training,function(entryIndex,entry){
												if(entry['type'] >= 7){
														var col = 'red';
														if(entry['status'] == 0){
																col = 'gray';
														}
													html += "<tr><td><font color='" + col + "'>" + entry['name'] + '<br>' + entry['end_time'] + "</font></td>";
												}else{
														html += "<tr><td>" + entry['name'] + "</td>";
												}
												html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
												html += "<td>" + entry['corps']; + "</td>"
												html += "<td>" + entry['govern_ori'] + "+<input type='text' id='govern_" + entryIndex + "' value='" + entry['govern_refresh'] + "' size=3></td>";
												html += "<td>" + entry['brave_ori'] + "+<input type='text' id='brave_" + entryIndex + "' value='" + entry['brave_refresh'] + "' size=3></td>";
												html += "<td>" + entry['wisdom_ori'] + "+<input type='text' id='wisdom_" + entryIndex + "' value='" + entry['wisdom_refresh'] + "' size=3></td>";
												if(entry['type'] >= 7){
														html += "<td><input type='text' id='yanshi_" + entryIndex + "' size=3 value='0'>天</td>";
												}else{
													html += "<td>---</td>"; 
												}
													html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
											});
											html += "</table></div>";
										}
										//如果是物品
										if(item_type == 3){
									html += "<p><div><table><tr><th width='200px'>name</th><th width='60px'>level</th><th width='50px'>itemId</th><th>count</th><th width='50px'></th></tr>";
									$.each(user,function(entryIndex,entry){
										html += "<tr><td>" + entry['name'] + "&nbsp; </td>";
										html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td><td>" + entry['itemId'] + "</td>";
										html += "<td><input type='text' id='count_" + entryIndex + "' value='" + entry['count'] + "' size=3></td><td>";
										html += "<td align=center><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td>";
										html += "<td align=center><a class='s_icon icon' href='#' onclick=\"removeItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">删除</a></td></tr>";
									});
									html += "</table></div>";
									}
										//如果是科技
										if(item_type == 4){
												html += "<p><div><table><tr><th width='200px'>name</th><th width='200px'>level</th><th></th></tr>";
											$.each(user.science,function(entryIndex,entry){
													html += "<tr><td>" + entry['name'] + "</td>";
												html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
												html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['science_id'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
											});
											html += "</table></div>";
										}
							if(item_type == 19 || item_type == 21){
							html += "<p><div><table><tr><th width='200px'>type</th><th width='200px'>num</th><th></th></tr>";
							$.each(user,function(entryIndex,entry){
								if(entryIndex == "uid" || entryIndex == "itemId" || entryIndex == "className" || entryIndex == "instance")
								{
								}
								else
								{
									html += "<tr><td>" + entryIndex + "</td>";
									html += "<td><input type='text' id='type_" + entryIndex + "' value='" + entry + "' size=9 maxlength=9></td>";
									html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + uid + "','" + entryIndex + "'," + item_type +");\">保存</a></td></tr>";
								}
							});
							html += "</table></div>";
							}
									$("#item").html(html);
									//调用函数居中窗口
									centerPopup();	
									//调用函数加载窗口
									loadPopup();
									alert('update success!');
							}
					});
			}
	});
	
}
function removeItem(uid, index_id, item_type){
	var api = 'gm/gm/GmService';
	if(item_type == 3){
		var params = {
			"id":1,
			"info":{
				"sign":'<{$sign}>',
				"publishID":'<{$publishID}>',
				"userID":uid,
				"XA_targname":null},
			"data":[{
				"name":"gm.UserAction",
				"changes":[],
				"params":{
					"good_id":uid,
					"type":23,
				}}]};
	}
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
						"id":1,
						"info":{
							"sign":'<{$sign}>',
							"publishID":'<{$publishID}>',
							"userID":uid,
							"XA_targname":null},
						"data":[{
							"name":"gm.UserAction",
							"changes":[],
							"params":{
								"user_uid":uid,
								"type":item_type,
							}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
								//如果是物品
								if(item_type == 3){
									html += "<p><div><table><tr><th width='200px'>name</th><th width='60px'>level</th><th width='50px'>itemId</th><th>count</th><th width='50px'></th></tr>";
									$.each(user,function(entryIndex,entry){
										html += "<tr><td>" + entry['name'] + "&nbsp; </td>";
										html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td><td>" + entry['itemId'] + "</td>";
										html += "<td><input type='text' id='count_" + entryIndex + "' value='" + entry['count'] + "' size=3></td><td>";
										html += "<td align=center><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td>";
										html += "<td align=center><a class='s_icon icon' href='#' onclick=\"removeItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">删除</a></td></tr>";
									});
									html += "</table></div>";
								}
								$("#item").html(html);
								//调用函数居中窗口
								centerPopup();	
								//调用函数加载窗口
								loadPopup();
								alert('update success!');
							}
					});
			}
	});
	
}

//查询所有hero
function allGeneral(){
	$("#n").html('');
	var general_type = $("#t option:selected").val();
	var uid = '';
	var api = 'gm/gm/GmService';
	var use_state = $("#is_used:checked").val();
	//如果是星座武将
	if(general_type == '7' && typeof(use_state) == 'undefined'){
		$("#is_used").attr("checked",true);
		$("#add_g").html('添加在野武将');
		userItem();
		$("#t").val(7).attr("selected", 1);
	}
	
	//查询游戏所有将军
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"general_type":general_type,
				"type":8,
			}}]};
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//alert(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200 && user== null){
				alert('not found!');
				return false;
			}
			if(code == 200){
				var html = '';
				$.each(user.general,function(entryIndex,entry){
					html += "<Option value=" + entry['id'] + ">" + entry['name'] + " &nbsp;统:" + entry['govern'] + " 勇:" + entry['brave'] + " 智:" + entry['wisdom'] + "</option>";
					});
					html += "<p>";
				$("#n").html(html);
			}
	});
	
}


//查询所有good
function allGood(){
	$("#g_n").html('');
	var good_type = $("#g_t option:selected").val();
	var good_color = $("#g_c option:selected").val();
	if(good_type >= 6){
		$("#col").hide();
		$("#num").show();
		good_color = 0;
	}else{
		$("#col").show();
		$("#num").hide();
	}
	var uid = '';
	var api = 'gm/gm/GmService';
	//查询游戏所有将军
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"good_type":good_type,
				"color":good_color,
				"type":11,
			}}]};
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//alert(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200 && user== null){
				alert('not found!');
				return false;
			}
			if(code == 200){
				var html = '';
				$.each(user.inventory,function(entryIndex,entry){
					html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
					});
					html += "<p>";
				$("#g_n").html(html);
			}
	});
	
}

//查询所有good
function allGem(){
	$("#m_n").html('');
	var good_type = $("#m_t option:selected").val();
	var uid = '';
	var api = 'gm/gm/GmService';
	//查询游戏所有宝石
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"gem_type":good_type,
				"type":17,
			}}]};
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//alert(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200 && user== null){
				alert('not found!');
				return false;
			}
			if(code == 200){
				var html = '';
				$.each(user.inventory,function(entryIndex,entry){
					html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
					});
					html += "<p>";
				$("#m_n").html(html);
			}
	});
	
}

//查询所有science
function allScience(){
	$("#s_n").html('');
	var science_type = $("#s_t option:selected").val();
	var uid = '';
	var api = 'gm/gm/GmService';
	//查询游戏所有将军
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"science_type":science_type,
				"type":14,
			}}]};
	params = JSON.stringify(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			//alert(data);return;
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			if(code == 200 && user== null){
				alert('not found!');
				return false;
			}
			if(code == 200){
				var html = '';
				$.each(user.science,function(entryIndex,entry){
					html += "<Option value=" + entry['id'] + ">" + entry['name'] + "</option>";
					});
					html += "<p>";
				$("#s_n").html(html);
			}
	});
	
}


//添加一个hero
function addGeneral(){
	var api = 'gm/gm/GmService';
	var general_id = $("#n option:selected").val();
	var uid = $("#user_uid").text();
	var state = $("#is_used:checked").val();
	if(typeof(state) == 'undefined'){
		state = 0;
	}
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"general_id":general_id,
				"user_uid":uid,
				"type":9,
				"status":state,
			}}]};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			var m = data.message;
			if(code == 400){
				alert(m);
				return false;
			}
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
					"id":1,
					"info":{
						"sign":'<{$sign}>',
						"publishID":'<{$publishID}>',
						"userID":uid,
						"XA_targname":null},
					"data":[{
						"name":"gm.UserAction",
						"changes":[],
						"params":{
							"user_uid":uid,
							"type":2,
							"status":state,
						}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
								html += "<div><table><tr><th width='180px'>name</th><th>level</th><th width='45px'>corps</th><th width='80px'>govern</th><th width='80px'>brave</th><th width='80px'>wisdom</th><th width='60px'>延时</th><th width='50px'></th></tr>";
								$.each(user.training,function(entryIndex,entry){
										if(entry['type'] >= 7){
												var col = 'red';
												if(entry['status'] == 0){
														col = 'gray';
												}
											html += "<tr><td><font color='" + col + "'>" + entry['name'] + '<br>' + entry['end_time'] + "</font></td>";
										}else{
												html += "<tr><td>" + entry['name'] + "</td>";
										}
										html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
										html += "<td>" + entry['corps']; + "</td>"
										html += "<td>" + entry['govern_ori'] + "+<input type='text' id='govern_" + entryIndex + "' value='" + entry['govern_refresh'] + "' size=3></td>";
										html += "<td>" + entry['brave_ori'] + "+<input type='text' id='brave_" + entryIndex + "' value='" + entry['brave_refresh'] + "' size=3></td>";
										html += "<td>" + entry['wisdom_ori'] + "+<input type='text' id='wisdom_" + entryIndex + "' value='" + entry['wisdom_refresh'] + "' size=3></td>";
										if(entry['type'] >= 7 ){
												html += "<td><input type='text' id='yanshi_" + entryIndex + "' size=3 value='0'>天</td>";
										}else{
											html += "<td>---</td>"; 
										}
											html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
									});
									html += "</table></div>";
									$("#item").html(html);
								alert('update success!');
							}
					});
			}
	});
	
}
//添加一个物品
function addGood(){
	var api = 'gm/gm/GmService';
	var good_id = $("#g_n option:selected").val();
	var uid = $("#user_uid").text();
	var num = $("#g_m option:selected").val();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"good_id":good_id,
				"user_uid":uid,
				"type":12,
				"count":num,
			}}]};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			var m = data.message;
			if(code == 400){
				alert(m);
				return false;
			}
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
						"id":1,
						"info":{
							"sign":'<{$sign}>',
							"publishID":'<{$publishID}>',
							"userID":uid,
							"XA_targname":null},
						"data":[{
							"name":"gm.UserAction",
							"changes":[],
							"params":{
								"user_uid":uid,
								"type":3,
							}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
								html += "<p><div><table><tr><th width='200px'>name</th><th width='60px'>level</th><th width='50px'>itemId</th><th>count</th><th width='50px'></th></tr>";
								$.each(user,function(entryIndex,entry){
									html += "<tr><td>" + entry['name'] + "&nbsp; </td>";
									html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td><td>" + entry['itemId'] + "</td>";
									html += "<td><input type='text' id='count_" + entryIndex + "' value='" + entry['count'] + "' size=3></td><td>";
									html += "<td align=center><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td>";
									html += "<td align=center><a class='s_icon icon' href='#' onclick=\"removeItem('" + entry['uid'] + "'," + entryIndex + "," + entry['item_type'] + ");\">删除</a></td></tr>";
								});
								html += "</table></div>";
								$("#item").html(html);
								alert('update success!');
							}
					});
			}
	});
}

//添加一个宝石
function addGem(){
	var api = 'gm/gm/GmService';
	var good_id = $("#m_n option:selected").val();
	var uid = $("#user_uid").text();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"gem_id":good_id,
				"user_uid":uid,
				"type":18,
			}}]};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			var m = data.message;
			if(code == 400){
				alert(m);
				return false;
			}
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
						"id":1,
						"info":{
							"sign":'<{$sign}>',
							"publishID":'<{$publishID}>',
							"userID":uid,
							"XA_targname":null},
						"data":[{
							"name":"gm.UserAction",
							"changes":[],
							"params":{
								"user_uid":uid,
								"type":6,
							}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
								html += "<p><div style='text-align:center;'><table><tr><th width='360px'>name</th><th width='100px'>nums</th><th width='100px'>addition</th><th width='50px'>need_receive</th></tr>";
										$.each(user.gem_inventory,function(entryIndex,entry){
											html += "<tr><td align='left'>" + entry['name'] + "</td>";
											if(entry['receive'] == 1){
												html += "<td>" + entry['nums'] + "</td>";
											}else{
												html += "<td>" + entry['nums'] + "/10</td>";
										}
										html += "<td>" + entry['addition'] + "</td>";
										html += "<td>" + entry['receive'] + "</td></tr>";
									});
										html += "</table></div>";
								$("#item").html(html);
								alert('update success!');
							}
					});
			}
	});
}

//添加一个科技
function addScience(){
	var api = 'gm/gm/GmService';
	var science_id = $("#s_n option:selected").val();
	var uid = $("#user_uid").text();
	var params = {
		"id":1,
		"info":{
			"sign":'<{$sign}>',
			"publishID":'<{$publishID}>',
			"userID":uid,
			"XA_targname":null},
		"data":[{
			"name":"gm.UserAction",
			"changes":[],
			"params":{
				"science_id":science_id,
				"user_uid":uid,
				"type":15,
			}}]};
	params = JSON.stringify(params);
	//alert(params);
	$.post(
		'http://' + '<{$server_host}>' + '/rest/' + api,
		params,
		function(data){
			var data = JSON.parse(data);
			var user = data.data;
			var code = data.code;
			var m = data.message;
			if(code == 400){
				alert(m);
				return false;
			}
			if(code == 200){
				var uid = $("#user_uid").text();
				var params = {
						"id":1,
						"info":{
							"sign":'<{$sign}>',
							"publishID":'<{$publishID}>',
							"userID":uid,
							"XA_targname":null},
						"data":[{
							"name":"gm.UserAction",
							"changes":[],
							"params":{
								"user_uid":uid,
								"type":4,
							}}]};
					params = JSON.stringify(params);
					//alert(params);
					$.post(
						'http://' + '<{$server_host}>' + '/rest/' + api,
						params,
						function(data){
							//alert(data);return;
							var data = JSON.parse(data);
							var user = data.data;
							var code = data.code;
							if(code == 200 && user== null){
								alert('not found!');
								return false;
							}
							if(code == 200){
								var html = '';
								html += "<p><div><table><tr><th width='200px'>name</th><th width='200px'>level</th><th></th></tr>";
									$.each(user.science,function(entryIndex,entry){
											html += "<tr><td>" + entry['name'] + "</td>";
										html += "<td><input type='text' id='level_" + entryIndex + "' value='" + entry['level'] + "' size=3></td>";
										html += "<td><a class='s_icon icon' href='#' onclick=\"updateItem('" + entry['science_id'] + "'," + entryIndex + "," + entry['item_type'] + ");\">保存</a></td></tr>";
									});
									html += "</table></div>";
								$("#item").html(html);
								alert('update success!');
							}
					});
			}
	});
}
var popupStatus = 0;
function loadPopup(){	
	if(popupStatus==0){	
	$("#backgroundPopup").css({	
	"opacity": "0.7"  
	});	
	$("#backgroundPopup").fadeIn("slow");	
	$("#popupContact").fadeIn("slow");	
	popupStatus = 1;	
	}	
}  

//使用Jquery去除弹窗效果 
function disablePopup(){	
	//仅在开启标志popupStatus为1的情况下去除
	if(popupStatus==1){	
	$("#backgroundPopup").fadeOut("slow");	
	$("#popupContact").fadeOut("slow");	
	popupStatus = 0;	
	}	
}

//将弹出窗口定位在屏幕的中央
function centerPopup(){	
	//获取系统变量
	var windowWidth = document.documentElement.clientWidth;	
	var windowHeight = document.documentElement.clientHeight;	
	var popupHeight = $("#popupContact").height();	
	var popupWidth = $("#popupContact").width();	
	//居中设置	
	$("#popupContact").css({	
	"position": "absolute",	
	"top": windowHeight/2-popupHeight/2,	
	"left": windowWidth/2-popupWidth/2	
	});	
	//以下代码仅在IE6下有效
		
	$("#backgroundPopup").css({	
	"height": windowHeight	
	});	
}

$(document).ready(function(){
	$("input[name='user']").change(function(){
		var flag = $("input[name='user']:checked").val();
		$.each( [1,2,3], function(i, n){
 			if(flag == n){
 				$("#d_" + n).show();
 			}else{
 				$("#d_" + n).hide();
 			}
		});
		$("#user_name").val('');
		$("#user_id").val('');
		$("#platform_uid").val('');
		$("#platform_address").val('ik2');
	});
	//关闭弹出窗口	
	//点击"X"所触发的事件
	$("#popupContactClose").click(function(){	
		disablePopup();	
	});	
	//点击窗口以外背景所触发的关闭窗口事件!
	$("#backgroundPopup").click(function(){	
		disablePopup();	
	});	
	//键盘事件监听
	$(document).keypress(function(e){
		//如果是esc	
		if(e.keyCode==27 && popupStatus==1){	
			disablePopup();	
		}
		//如果是enter
		if(e.keyCode == 13){
			userInfo();	
		}
	});
});
</script>
</head>
<body>
	<div style="width:100%;height:80px;text-align:center;"></p>
		<div style='color:#0098CB;'>查询方式:
			<Input type='radio' name='user' value=1 >游戏名<Input type='radio' name='user' value=2 checked>游戏UID<Input type='radio' name='user' value=3>平台UID
		</div>
		<div style="TEXT-ALIGN:center"><span id="showResult" style="font-size:2;color:red"></span></div></p>
		<div style='display:none;color:#0098CB;' id='d_1'>用户游戏 Name: <input type='text' id='user_name' value=''>&nbsp; &nbsp;<a class='c_icon icon' href='#' onclick="userInfo();">查询</a></div></p>
		<div id='d_2' style='color:#0098CB;'>用户游戏 Uid: <input type='text' id='user_id' value=''>&nbsp; &nbsp;<a class='c_icon icon' href='#' onclick="userInfo();">查询</a></div></p>
		<div style='display:none;color:#0098CB;' id='d_3'>用户Platform_address: <input type='text' id='platform_uid' value='' size=20>_<input type='text' id='platform_address' value='ik2' size=20>&nbsp; &nbsp;<a class='c_icon icon' href='#' onclick="userInfo();">查询</a></div></p>
	</div>
<hr style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="100%" color=#BBBBBB SIZE=1>
	<div id='query_body' style="width:100%;text-align:center;display:none;">
		<div><span id="w_info" style="font-size:2;color:red"></span></div></p>
		<table class='truecolor' border=0 cellPadding=15 sellSpacing=1 align=center width=100%>
			<tr><td colspan=3>用户基本信息<span style="position:relative;left:200px;"><a class='s_icon icon' href='#' onclick="submit();">保存</a></span></td></tr>
			<tr align=left>
				<td width=300>uid: <span id='user_uid'></span></td>
				<td width=300>name: <span id='p_name'></span></td>
				<td width=300>level: <Input type='text' id='level' value='' size=12></span></td>
			</tr>
			<tr align=left>
				<td width=300>x: <span id='x'></span></td>
				<td width=300>y: <span id='y'></span></td>
				<td width=300>vip: <Input type='text' id='vip' value='' size=12></td>
			</tr>
			<tr align=left>
				<td width=300>user_gold: <Input type='text' id='user_gold' value='' size=12></td>
				<td width=300>system_gold: <Input type='text' id='system_gold' value='' size=12></td>
				<td width=300>pic: <Input type='text' id='pic' value='' size=12></td>
			</tr>
			<tr align=left>
				<td width=300>platformAddress: <Input type='text' id='platformAddress' value='' size=12></td>
			</tr>
			<tr><td colspan=3></td></tr>
			<tr><td align=left>
			<div><font size=3>相关信息:</font>
				<select>
					<Option value=3>物品</Option>
					<Option value=19>城市</Option>
					<Option value=21>君主</Option>
				</select>
				<a class='c_icon icon' href='#' onclick="userItem();">查看</a></p>
			</div></td><td colspan=2></td></tr>
			</table></p>
<div id="popupContact">
  	<a id="popupContactClose">x</a>
  	<h1> 	<div class='general'>
  			武将状态:<Input type='checkbox' id='is_used' value=1 checked onclick='userItem();'>在野<p>
			添加武将&nbsp;&nbsp;
			类型:<select id='t' onchange='allGeneral();'>
				<Option value=0>0</Option>
				<Option value=1>1</Option>
				<Option value=2>2</Option>
				<Option value=3>3</Option>
				<Option value=4>4</Option>
				<Option value=5>5</Option>
				<Option value=6>6</Option>
				<Option value=7>星座</Option>
			</select>
			名字:<select id='n'></select>
			&nbsp;&nbsp;
			<a id='add_g' class='a_icon icon' href='#' onclick='addGeneral();'></a>
		</div>
		<div class='good'>
			添加物品&nbsp;&nbsp;&nbsp;&nbsp;
			类型:<select id='g_t' onchange='allGood();'>
				<Option value=0>武器</Option>
				<Option value=1>铠甲</Option>
				<Option value=2>战马</Option>
				<Option value=3>披风</Option>
				<Option value=4>卷轴</Option>
				<Option value=5>法杖</Option>
				<Option value=6>其它</Option>
				<Option value=8>宝箱</Option>
			</select>
			<span id='col'>颜色:<select id='g_c' onchange='allGood();'>
				<Option value=0>白色</Option>
				<Option value=1>蓝色</Option>
				<Option value=2>绿色</Option>
				<Option value=3>黄色</Option>
				<Option value=4>红色</Option>
				<Option value=5>紫色</Option>
			</select></span>
			名字:<select id='g_n'></select>
			<span id='num' style='display:none;'>数量:
				<select id= 'g_m'>
					<Option value=1>1</Option>
					<Option value=3>3</Option>
					<Option value=5>5</Option>
					<Option value=10>10</Option>
					<Option value=15>15</Option>
					<Option value=20>20</Option>
					<Option value=30>30</Option>
					<Option value=50>50</Option>
					<Option value=100>100</Option>
				</select>
			</span>
			&nbsp;&nbsp;&nbsp;
			<a class='a_icon icon' href='#' onclick='addGood();'>添加</a>
		</div>
		<div class='science'>
			添加科技&nbsp;&nbsp;&nbsp;&nbsp;
			类型:<select id='s_t' onchange='allScience();'>
				<Option value=0>科技</Option>
				<Option value=1>阵法</Option>
			</select>
			名字:<select id='s_n'></select>&nbsp;&nbsp;&nbsp;
			<a class='a_icon icon' href='#' onclick='addScience();'>添加</a>
		</div>
		<div class='gem'>
			添加宝石&nbsp;&nbsp;&nbsp;&nbsp;
			类型:<select id='m_t' onchange='allGem();'>
				<Option value=1>普通攻击</Option>
				<Option value=2>普通防御</Option>
				<Option value=3>技能攻击</Option>
				<Option value=4>技能防御</Option>
				<Option value=5>法术攻击</Option>
				<Option value=6>法术防御</Option>
				<Option value=7>带兵数量</Option>
			</select>
			名字:<select id='m_n'></select>
			&nbsp;&nbsp;&nbsp;
			<a class='a_icon icon' href='#' onclick='addGem();'>添加</a>
		</div>
		<div class='city'>
			城市状态
		</div>
		<div class='lord'>
			君主属性
		</div>
		</h1>
  	<div id='item'></div>
</div>
<div id='map'></div>

<div id="backgroundPopup"></div>
</body>
</html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script language="JavaScript" type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="../js/json2.js"></script>
</head>
<body scroll="no">
<script language="JavaScript" type="text/javascript">
function showAPI(){
	var addr = $('#address').val();
	$.ajax({
		  url: 'http://' + addr + '/discovery/json',
		  success: function(data) {
			  var results = $.parseJSON(data);
			  results = results.data;
			  var box = $('#apiname');
			  box.html('');
			  $("input[name='http_method']").filter('[value=2]').attr('checked', true);
			  $('#api_desc').html('');
			  for(var i=1;i<=7;i++){
				  $('#params_key_'+i).val('');
				  $('#params_value_'+i).val('');
				  $('#params_type_'+i).val('');
				  $('#params_desc_'+i).val('');
			  }
			  $('#submit_count').val(1);
			  $('#submit_index').val(1);
			  $('#queryShow').val('');
			  $('#postShow').val('');
			  $('#resultShow').val('');
			  $('#divresult').html('');
			  $('#subimt_btn').attr('disabled', true);
			  box.html('<option value="">--Select API--</option>');
			  for(var key in results){
				  for(var method in results[key]){
					  box.html(box.html() + '<option value="'+key+'.'+results[key][method]['serviceName']+'">'+key+'.'+results[key][method]['serviceName']+'</option>');
				  }
			  }
			  var api_source = $('#api_source').val();
			  if(api_source.length > 0){
				  box.attr('value' , api_source);
				  showService(box);
			  }
		  }
	});
}
function showService(obj){
	var addr = $('#address').val();
	var api = $(obj).val();
	$.ajax({
		  url: 'http://' + addr + '/discovery/json',
		  success: function(data) {
			  $('#submit_count').val(1);
			  $('#submit_index').val(1);
			  var results = $.parseJSON(data);
			  results = results.data;
			  for(var i=1;i<=7;i++){
				  $('#params_key_'+i).val('');
				  $('#params_value_'+i).val('');
				  $('#params_type_'+i).val('');							  
				  $('#params_desc_'+i).val('');
			  }
			  for(var key in results){
				  for(var method in results[key]){
					  if(api != key+'.'+results[key][method]['serviceName']){
					       continue;
					  }
					  if(results[key][method]['Protocol']['allow'] == "FILE"){
						  $('#protocol').val('file');
					  }else{
						  $('#protocol').val('rest');
					  }
					  $('#queryShow').val('');
					  $('#postShow').val('');
					  $('#resultShow').val('');
					  $('#divresult').html('');
					  $('#subimt_btn').attr('disabled', false);
					  //GET or POST
					  if(typeof(results[key][method]['Method']['allow']) != "undefined"
						  && results[key][method]['Method']['allow'].toUpperCase() == 'POST'){
						  $("input[name='http_method']").filter('[value=2]').attr('checked', true);
					  }else{
						  $("input[name='http_method']").filter('[value=1]').attr('checked', true);
					  }
					  var desc = results[key][method]['description'];
					  desc.replace(/\r\n/i, '<br>');
					  $('#api_desc').html(desc);
					  //simple params
					  if(typeof(results[key][method]['ServiceParam']) != "undefined"
						  && results[key][method]['ServiceParam'].length > 0){
						  var result;
						  var params = results[key][method]['ServiceParam'];
						   
						  var index = 1;
						  for(var pos in params){
							  $('#params_type_'+index).val(params[pos][1]);
							  $('#params_key_'+index).val(params[pos][2]);
							  $('#params_desc_'+index).val(params[pos][3]);
							  index++;
						  }
					  }
				  }
			  }
		  }
	});
}
$(document).ready(function(){
	showAPI();
});
function submit(){
	var min = $('#submit_index').val();
	var max = $('#submit_count').val();
	var i;
	for(i=min;i<=max;i++)
		submit2(i);
}
function submit2(count){
	$('#subimt_btn').attr('disabled', true);
	$('#queryShow').val('');
	$('#postShow').val('');
	$('#resultShow').val('');
	$('#divresult').html('');
	var addr = $('#address').val();
	var api = $('#apiname').val();
	api = api.replace(/\./g, '/');
	var format = $('#format').val();
	var method = parseInt($("input[name='http_method']:checked").val());
	var params = new Object();
	var id = $('#id_value').val();
	params['id'] = id;
	var info = new Object();
	for(var i=1;i<=3;i++){
        var key = $('#info_key_'+i).val();
        var value = $('#info_value_'+i).val();
        if(key.length > 0 && value.length > 0){
            if(api=='user/user/platformRegisterAndSetInfo' && key=='platformUserId')
            {
            	value = value + count;
            }
        	info[key] = value;
        }
    }
	params['info'] = info;
	var data = new Object();
    for(var i=1;i<=7;i++){
        var key = $('#params_key_'+i).val();
        var value = $('#params_value_'+i).val();
        if(key.length > 0 && value.length > 0){
            if(key == 'name'){
				var action_name = value;
            }
        	data[key] = value;
        }
    }
    if(action_name == null)
    {
    	params['data'] = data;
    }
    else
    {
    params['data'] = [{
		"name":action_name,
		"changes":[],
		"index":[],
		"params":data,
		}];
    }
    var protocol = $('#protocol').val();
    var query = 'http://' + addr + '/'+ protocol+ '/' + api + '?format=' + format;
    if(protocol == "file"){
    	query = 'http://' + addr + '/'+ protocol+ '/' + api;
    }
    if(method!=2){
        for(var key in params){
        	query += '&' + key + '=' + params[key];
        }
    }
    $('#queryShow').val(query);
	if(method == 2){
		params = JSON.stringify(params);
		var getData = JSON.parse(params);
		var postData = $('#postData').val();
		if(format=='json'){
			if(postData.length > 0){
				postData = JSON.parse(postData);
				for(var key in postData){
					getData[key] = postData[key];
		        }
			}
			params = JSON.stringify(getData);
		}else{
			params = postData;
		}
		$('#postShow').val(params);
	}
	$.ajax({
		  url: 'http://' + addr + '/' + protocol +'/' + api + '?format=' + format,
		  data: params,
		  type: method == 2 ? 'POST' : 'GET',
		  success: function(data){
			  $('#resultShow').val(data);
			  $('#divresult').html(data);
			  $('#subimt_btn').attr('disabled', false);
		  }
	});
	$('#subimt_btn').attr('disabled', false);
};
</script>
	<input id="protocol" type="hidden" value="rest"></input>
	<table>
		<tr>
        	<td valign="top">
        		<table border="0" cellpadding="0" cellspacing="0">
        			<tbody>
						<tr>
        				<td>
							<input type="hidden" name="api_source" id="api_source" value="<?php echo (isset($_GET['api']) ? $_GET['api'] : '')?>" />
							<table class="parameters" width="500" border="0" cellpadding="4" cellspacing="0">
        						<tbody>
								<tr>
        							<td width="160" align="right">Server：</td>
        							<td>http://<input type="text" id="address" name="address" value="<?php echo $_SERVER['HTTP_HOST']?><?php echo preg_replace('/\/gameengine/i', '', preg_replace('/\/test\/rest(\/index\.php)*$/i','',$_SERVER['PHP_SELF']))?>" style="width:190px;" onchange="showAPI();">
        							</td>
        						</tr>			
								<tr>
        							<td width="160" align="right">Format：</td>
        							<td width="340">
        								<select id="format" name="format" style="width:195px;">
        									<option value="json">JSON</option>
        									<option value="xml">XML</option>
        								</select>
        							</td>
        						</tr>
        						<tr>
        							<td>API：</td>
        							<td>
        								<span>
        									<select id="apiname" name="apiname" style="width:195px;" onchange="showService(this);">
        									</select>
        								</span>&nbsp;</td>
        						</tr>
        						<tr>
        							<td>Method：</td>
        							<td>
        								<input type="radio" name="http_method" value="1">GET
        								<input type="radio" name="http_method" value="2">POST
        							</td>
        						</tr>
        						<tr>
        							<td>Description：</td>
        							<td>
        								<span id='api_desc'></span>
        							</td>
        						</tr>
        						</tbody>
							</table>
        				</td>
						</tr>
        			</tbody>
				</table>
        	</td>
        	<td valign="top">
        		API Query：<br>
        		<textarea name="queryShow" id="queryShow" cols="60" rows="3" style="overflow-x:scroll" readonly='readonly'></textarea><br /><br />
	       	</td>
        </tr>
        <tr></tr>
    </table>
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
        	<td valign="top">
        		<table border="0" cellpadding="0" cellspacing="0">
        			<tbody>
						<tr>
        				<td>
							<table class="parameters" width="500" border="0">
        						<tbody>
        						<tr>
        							<td>Id：</td>
        						</tr>
        						<tr>
        							<td align="center">Param</td>
        							<td align="center">Value</td>
        							<td align="center">Type</td>
        							<td align="center">Desc</td>
        						</tr>
        						<tr>
        							<td><input type="text" id="id_key" name="id_key" value="id" size="20"></td>
        							<td><input type="text" id="id_value" name="id_value" value="1" size="30"></td>
        							<td><input type="text" id="id_type" name="id_type" value="int" size="10" readonly='readonly'></td>
        							<td><input type="text" id="id_desc" name="id_desc" value="请求ID序号" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td>Info：</td>
        						</tr>
        						<tr>
        							<td align="center">Param</td>
        							<td align="center">Value</td>
        							<td align="center">Type</td>
        							<td align="center">Desc</td>
        						</tr>
        						<tr>
        							<td><input type="text" id="info_key_1" name="info_key_1" value="platformAppId" size="20" ></td>
        							<td><input type="text" id="info_value_1" name="info_value_1" value="ik2@elex337_cn_1" size="30"></td>
        							<td><input type="text" id="info_type_1" name="info_type_1"" value="string" size="10" readonly='readonly'></td>
        							<td><input type="text" id="info_desc_1"" name="info_desc_1" value="平台app id" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="info_key_2" name="info_key_2" value="platformUserId" size="20"></td>
        							<td><input type="text" id="info_value_2" name="info_value_2" value="" size="30"></td>
        							<td><input type="text" id="info_type_2" name="info_type_2" value="string" size="10" readonly='readonly'></td>
        							<td><input type="text" id="info_desc_2" name="info_desc_2" value="平台用户id" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="info_key_3" name="info_key_3" value="gameUserId" size="20"></td>
        							<td><input type="text" id="info_value_3" name="info_value_3" value="" size="30"></td>
        							<td><input type="text" id="info_type_3" name="info_type_3" value="string" size="10" readonly='readonly'></td>
        							<td><input type="text" id="info_desc_3" name="info_desc_3" value="游戏用户id" size="30" readonly='readonly'></td>
        						</tr>
        						
        						
        						<tr>
        							<td>Data：</td>
        						</tr>
        						<tr>
        							<td align="center">Param</td>
        							<td align="center">Value</td>
        							<td align="center">Type</td>
        							<td align="center">Desc</td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_1" name="params_key_1" value="" size="20"></td>
        							<td><input type="text" id="params_value_1" name="params_value_1" value="" size="30"></td>
        							<td><input type="text" id="params_type_1" name="params_type_1" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_1" name="params_desc_1" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_2" name="params_key_2" value="" size="20"></td>
        							<td><input type="text" id="params_value_2" name="params_value_2" value="" size="30"></td>
        							<td><input type="text" id="params_type_2" name="params_type_2" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_2" name="params_desc_2" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_3" name="params_key_3" value="" size="20"></td>
        							<td><input type="text" id="params_value_3" name="params_value_3" value="" size="30"></td>
        							<td><input type="text" id="params_type_3" name="params_type_3" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_3" name="params_desc_3" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_4" name="params_key_4" value="" size="20"></td>
        							<td><input type="text" id="params_value_4" name="params_value_4" value="" size="30"></td>
        							<td><input type="text" id="params_type_4" name="params_type_4" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_4" name="params_desc_4" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_5" name="params_key_5" value="" size="20"></td>
        							<td><input type="text" id="params_value_5" name="params_value_5" value="" size="30"></td>
        							<td><input type="text" id="params_type_5" name="params_type_5" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_5" name="params_desc_5" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_6" name="params_key_6" value="" size="20"></td>
        							<td><input type="text" id="params_value_6" name="params_value_6" value="" size="30"></td>
        							<td><input type="text" id="params_type_6" name="params_type_6" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_6" name="params_desc_6" value="" size="30" readonly='readonly'></td>
        						</tr>
        						<tr>
        							<td><input type="text" id="params_key_7" name="params_key_7" value="" size="20"></td>
        							<td><input type="text" id="params_value_7" name="params_value_7" value="" size="30"></td>
        							<td><input type="text" id="params_type_7" name="params_type_7" value="" size="10" readonly='readonly'></td>
        							<td><input type="text" id="params_desc_7" name="params_desc_7" value="" size="30" readonly='readonly'></td>
        						</tr>
        						</tbody>
							</table>
        				</td>
        				<td>        
							<table class="parameters" width="500" border="0">
        					<tbody>        								
        					<tr>
        						<td align="left" >Post：</td>
        						<td></td>
        					</tr>
        					<tr>
        						<td>
        							<textarea name="postData" id="postData" cols="40" rows="5" style="overflow-x:scroll"></textarea>
        						</td>
        					</tr>
        					</tbody>
							</table>
        				</td>
						</tr>
        			</tbody>
				</table>
        	</td>
        </tr>
        <tr></tr>
    </table>    
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>	
        	<td valign="top">
        		<table border="0" cellpadding="0" cellspacing="0">
        			<tbody>
            			<tr>
            				<td><span id="ParamDiv"></span>
            				API Post：(调试代码错误直接显示在最下方)<br>
        					<textarea name="postShow" id="postShow" cols="40" rows="3" style="overflow-x:scroll" readonly='readonly'></textarea><br /><br />
            				</td>
            			</tr>
            			<tr>
            				<td>
            					<table width="500" border="0" cellpadding="4" cellspacing="0">
            						<tbody><tr>
            							<td width="20">&nbsp;</td>
            							<td width="340" align="left">
            								执行初始值<input id='submit_index' type="text" value="1" size="5">
	            							执行结束值<input id='submit_count' type="text" value="1" size="5">
            								<input id='subimt_btn' type="button" value="Submit" onclick="submit();" style="width:60px;height:24px;*padding-top:3px;border:#666666 1px solid;cursor:pointer">
            							</td>
            						</tr>
            					</tbody></table>
            				</td>
            			</tr>
        			</tbody>
				</table>
        	</td>
        	<td valign="top">
        		API Result：<br>
        		<textarea name="resultShow" id="resultShow" cols="60" rows="10" style="overflow-x:scroll" readonly='readonly'></textarea><br /><br />
        	</td>
        </tr>
        <tr></tr>
    </table>
     <div> API Result:</div>
    <div name="divresult" id="divresult"></div>
</body>
</html>
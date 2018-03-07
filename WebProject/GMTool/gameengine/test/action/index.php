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
		  url: 'http://' + addr + '/discovery/action',
		  success: function(data) {
			  var results = $.parseJSON(data);
			  results = results.data;
			  var box = $('#apiname');
			  box.html('');
			  $("input[name='http_method']").filter('[value=2]').attr('checked', true);
			  $('#api_desc').html('');
			  for(var i=1;i<=5;i++){
				  $('#params_key_'+i).val('');
				  $('#params_value_'+i).val('');
				  $('#params_type_'+i).val('');
				  $('#params_desc_'+i).val('');
			  }
			  $('#queryShow').val('');
			  $('#postShow').val('');
			  $('#resultShow').val('');
			  $('#subimt_btn').attr('disabled', true);
			  box.html('<option value="">--Select API--</option>');
			  for(var key in results){
					  box.html(box.html() + '<option value="'+results[key]+'">'+results[key]+'</option>');
			  }
		  }
	});
}
function selected(){
	$('#subimt_btn').attr('disabled', false);
}
$(document).ready(function(){
	showAPI();
});
function submit(){
	$('#subimt_btn').attr('disabled', true);
	$('#queryShow').val('');
	$('#postShow').val('');
	$('#resultShow').val('');
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
        	info[key] = value;
        }
    }
	params['info'] = info;
//   

	var data = new Object();
    var api = $('#apiname').val();
	data['name'] = api;
	data['params'] = new Object();
	data['index'] = $('#params_key_index').val();
	for(var i=2;i<=5;i++){
		var key = $('#params_key_'+i).val();
		var value = $('#params_value_'+i).val();
		if(key.length > 0 && value.length > 0){
			data['params'][key] = value;
		}
  	}
//    var paramPost = $('#paramPost').val();
//    if(paramPost.length > 0){
//    	data['params'] = new Object();
//    	paramPost = JSON.parse(paramPost);
//		for(var key in paramPost){
//			data['params'][key] = paramPost[key];
//        }
//	}
    
//    var changePost = $('#changePost').val();
//    if(changePost.length > 0){
//    	data['changes'] = new Object();
//        data['changes'][0] = new Object();
//    	changePost = JSON.parse(changePost);
//		for(var key in changePost){
//			data['changes'][0][key] = changePost[key];
//        }
//	}
    params['data'] = new Object();
    params['data'][0] = data;
    
    
    var query = 'http://' + addr + '/rest/action/action/execute?format=' + format;
    if(method!=2){
        alert('Only post alowed!');
    }
    $('#queryShow').val(query);
	if(method == 2){
		params = JSON.stringify(params);
		$('#postShow').val(params);
	}
	$.ajax({
		  url: query,
		  data: params,
		  type: method == 2 ? 'POST' : 'GET',
		  success: function(data){
			  $('#resultShow').val(data);
			  $('#subimt_btn').attr('disabled', false);
		  }
	});
	$('#subimt_btn').attr('disabled', false);
};
</script>
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
        							<td>http://<input type="text" id="address" name="address" value="<?php echo $_SERVER['HTTP_HOST']?>" style="width:190px;" onchange="showAPI();">
        							</td>
        						</tr>
								<tr>
        							<td width="160" align="right">Format：</td>
        							<td width="340">
        								<select id="format" name="format" style="width:195px;">
        									<option value="json">JSON</option>
        								</select>
        							</td>
        						</tr>
        						<tr>
        							<td>API：</td>
        							<td>
        								<span>
        									<select id="apiname" name="apiname" style="width:195px;" onchange="selected();">
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
        							<td><input type="text" id="info_key_1" name="info_key_1" value="platformAppId" size="20"></td>
        							<td><input type="text" id="info_value_1" name="info_value_1" value="" size="30"></td>
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
        							<td colspan=4>Data：</td>
        						</tr>
        						<tr>
        							<td align="center">Param</td>
        							<td align="center">Value</td>
        							<td align="center">Type</td>
        							<td align="center">Desc</td>
        							<input type="hidden" name="params_key_index" id="params_key_index" value="1" />
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
        						<!-- <tr>
        						<td colspan=3 align="left" >params：</td>
        						<td colspan=3 align="left" >changes：</td>
	        					</tr>
	        					<tr>
	        						<td colspan=3>
	        							<textarea name="params" id="paramPost" cols="40" rows="5" style="overflow-x:scroll"></textarea>
	        						</td>
	        						<td colspan=3>
	        							<textarea name="changes" id="changePost" cols="40" rows="5" style="overflow-x:scroll"></textarea>
	        						</td>
	        					</tr> -->
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
    <br/><br/>
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>	
        	<td valign="top">
        		<table border="0" cellpadding="0" cellspacing="0">
        			<tbody>
            			<tr>
            				<td><span id="ParamDiv"></span>
            				API Post：<br>
        					<textarea name="postShow" id="postShow" cols="40" rows="5" style="overflow-x:scroll" readonly='readonly'></textarea><br /><br />
            				</td>
            			</tr>
            			<tr>
            				<td>
            					<table width="500" border="0" cellpadding="4" cellspacing="0">
            						<tbody><tr>
            							<td width="160">&nbsp;</td>
            							<td width="340" align="left">
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
</body>
</html>
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
	$('#method_span').hide();
	$.ajax({
		  url: 'http://' + addr + '/discovery/jsonrpc',
		  success: function(data) {
			  var results = $.parseJSON(data);
			  results = results.data;
			  var box = $('#apiname');
			  box.html('');
			  $("input[name='http_method']").filter('[value=1]').attr('checked', true);
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
			  var span = $('#method_span');
			  span.html('');
			  for(var key in results){
				  box.html(box.html() + '<option value="'+key+'">'+key+'</option>');
				  suffix = key.replace(/\./g, '_');
				  var html = '<select class="method" id="method_name_'+suffix+'" name="method_name_'+suffix+'" style="width:195px;" display="none" onchange="showService(this)">';
				  for(var method in results[key]){
					  html += '<option value="'+results[key][method]['serviceName']+'">'+results[key][method]['serviceName']+'</option>';
				  }
				  html += '</select>';
				  span.html(span.html() + html);
			  }
			  var api_source = $('#api_source').val();
			  if(api_source.length > 0){
				  var array = api_source.split('.');
				  var method = array.pop();
				  var service = array.join('.');
				  var suffix = service.replace(/\./g, '_');
				  box.attr('value' , service);
				  var methodObj = $('#method_name_' + suffix);
				  methodObj.attr('value' , method);
				  $('#method_span').show();
				  $('.method').hide();
				  methodObj.show();
				  showService(methodObj);
			 }
		  }
	});
}
function showMethods(obj){
	$('#method_span').show();
	$('.method').hide();
	var suffix = $(obj).val();
	suffix = suffix.replace(/\./g, '_');
	var obj = $('#method_name_' + suffix);
	obj.show();
	showService(obj);
}
function showService(obj){
	var addr = $('#address').val();
	var api = $('#apiname').val();
	api = api + '.' + $(obj).val();
	$.ajax({
		  url: 'http://' + addr + '/discovery/jsonrpc',
		  success: function(data) {
			  var results = $.parseJSON(data);
			  results = results.data;
			  for(var key in results){
				  for(var method in results[key]){
					  if(api != key+'.'+results[key][method]['serviceName']){
					       continue;
					  }
					  $('#queryShow').val('');
					  $('#postShow').val('');
					  $('#resultShow').val('');
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
						  for(var i=1;i<=5;i++){
							  $('#params_key_'+i).val('');
							  $('#params_value_'+i).val('');
							  $('#params_type_'+i).val('');							  
							  $('#params_desc_'+i).val('');
						  } 
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
	$('#subimt_btn').attr('disabled', true);
	$('#queryShow').val('');
	$('#postShow').val('');
	$('#resultShow').val('');
	var addr = $('#address').val();
	var api = $('#apiname').val();
	var suffix = api.replace(/\./g, '_');
	var method = $('#method_name_' + suffix).val();
	api = api.replace(/\./g, '/');
	var params = new Object();
    for(var i=1;i<=5;i++){
        var key = $('#params_key_'+i).val();
        var value = $('#params_value_'+i).val();
        if(key.length > 0 && value.length > 0){
        	params[key] = value;
        }
    }
	params = JSON.stringify(params);
	var getData = JSON.parse(params);
	var postData = $('#postData').val();
	if(postData.length > 0){
	}
    var query = 'http://' + addr + '/xmlrpc/' + api;
    $('#queryShow').val(query);
	$('#postShow').val(postData);
	$.ajax({
		  url: query,
		  data: postData,
		  type: 'POST',
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
        							<td>http://<input type="text" id="address" name="address" value="<?php echo $_SERVER['HTTP_HOST']?><?php echo preg_replace('/\/gameengine/i', '', preg_replace('/\/test\/xmlrpc(\/index\.php)*$/i','',$_SERVER['PHP_SELF']))?>" style="width:190px;" onchange="showAPI();">
        							</td>
        						</tr>			
								<tr>
        							<td width="160" align="right">Format：</td>
        							<td width="340">
        								<select id="format" name="format" style="width:195px;">
        									<option value="xml">XML</option>
        								</select>
        							</td>
        						</tr>
        						<tr>
        							<td>API：</td>
        							<td>
        								<span>
        									<select id="apiname" name="apiname" style="width:195px;" onchange="showMethods(this);">
        									</select>
        								</span>&nbsp;</td>
        						</tr>
        						<tr>
        							<td align="right">Method：</td>
        							<td>
        								<span id='method_span'>
        									<select class='method' id="method_name" name="method_name" style="width:195px;">
        									</select>
        								</span>&nbsp;</td>
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
            				API Post：<br>
        					<textarea name="postShow" id="postShow" cols="40" rows="3" style="overflow-x:scroll" readonly='readonly'></textarea><br /><br />
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
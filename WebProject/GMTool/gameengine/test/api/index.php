<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script language="JavaScript" type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="../js/json2.js"></script>
<script language="JavaScript" type="text/javascript">
function showAPI(){
	var addr = $('#address').val();
	$.ajax({
		  url: 'http://' + addr + '/discovery/json',
		  success: function(data) {
			  var results = $.parseJSON(data);
			  results = results.data;
			  var html = '';
			  for(var key in results){
				  for(var method in results[key]){
				  	html += '<div style="width:800;height:80;overflow:auto;border:1 solid black;"><span><a href="/test/api/api.php?api=' + key+'.'+results[key][method]['serviceName'] + '">' + key+'.'+results[key][method]['serviceName'] + 
				  	'</a></span><div>'+ results[key][method]['description'] + 
				  	'</div></div>';
				  }
			  }
			  $('#apis').html(html);
		  }
	});
}
$(document).ready(function(){
	showAPI();
});
</script>
</head>
<body scroll="no">
<div>Server: http://<input type="text" id="address" name="address" value="<?php echo $_SERVER['HTTP_HOST']?><?php echo preg_replace('/\/test\/api(\/index\.php)*$/i','',$_SERVER['PHP_SELF'])?>" style="width:190px;" onchange="showAPI();">
</div>
<div id='apis'>
</div>
</body>
</html>
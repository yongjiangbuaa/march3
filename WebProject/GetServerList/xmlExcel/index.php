<?php 
require_once 'Config.php';
$svrPath  = $GLOBALS['svrPath'];
$req_base = "$svrPath/IF2ProcessRequest.php?act=view";

//$resource_dir = '/IF/trunk/src/server/smartfoxserver/SFS2X/resource';
//$resource_dir = '/IF/trunk/src/server/smartfoxserver/SFS2X/resource';
$resource_dir = '/usr/local/cok/SFS2X/resource';

$client_dir = $resource_dir.'/cn';

$abtestB_client_dir = $resource_dir.'/cn_abtestB';
$abtestB_resource_dir = $resource_dir.'/abtestB';

$clientend_list = array();
$backend_list = array();
foreach (glob($client_dir.'/*.xml') as $xmlfile) {
	$bn = basename($xmlfile);
	$clientend_list[$bn] = basename($bn, '.xml');
}
foreach (glob($resource_dir.'/*.xml') as $xmlfile) {
	$bn = basename($xmlfile);
	$backend_list[$bn] = basename($bn, '.xml');
}

$abtestb_clientend_list = array();
$abtestb_backend_list = array();
foreach (glob($abtestB_client_dir.'/*.xml') as $xmlfile) {
	$bn = basename($xmlfile);
	$abtestb_clientend_list[$bn] = basename($bn, '.xml');
}
foreach (glob($abtestB_resource_dir.'/*.xml') as $xmlfile) {
	$bn = basename($xmlfile);
	$abtestb_backend_list[$bn] = basename($bn, '.xml');
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>Xml文件列表</title>
  <meta name="Generator" content="Elex">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  
  <style>
a:link {
    text-decoration: none;
}

a:visited {
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
	color: #FF00FF;
}

a:active {
    text-decoration: underline;
}

</style>
 </head>

 <body>
 
<table border="0"><tr><td style="vertical-align: top;">

<table border="0">
<tr><td style="vertical-align: top;">
<table border="1">
	<tr> <td colspan="1" style="text-align: center;">---------前台表---------</td></tr>
	<tr> <td colspan="1" style="text-align: center;">resource/cn/</td></tr>
	<?php foreach ($clientend_list as $xmlname => $disname) {
		$requrl = $req_base."&filename=cn/".$xmlname;
		echo "<tr><td>";
		echo '<a href="'.$requrl.'">';
		echo "$xmlname</a></td></tr>";
	}?>
</table>
</td><td style="vertical-align: top;">
<table border="1">
	<tr> <td colspan="1" style="text-align: center;">---------后台表---------</td></tr>
	<tr> <td colspan="1" style="text-align: center;">resource/</td></tr>
	<?php foreach ($backend_list as $xmlname => $disname) {
		$requrl = $req_base."&filename=".$xmlname;
		echo "<tr><td>";
		echo '<a href="'.$requrl.'">';
		echo "$xmlname</a></td></tr>";
	}?>
</table>
</td></tr></table>

</td>
</tr></table>

 </body>
</html>

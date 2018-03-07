<?php
require 'xingcloud.php';
error_reporting(0);
//kv
try{
	$kvError = '';
	$service = KVStorageService::getService();
	$service->set('demo', '123', 'Hello World');
	$kvStatus = ($service->get('demo', '123') == 'Hello World');
}catch(Exception $e){
	$kvStatus = 0;
	$kvError = $e->getMessage();
}
//mysql
try{
	$mysql_host=xingcloud_get("mysql_host");
	$mysql_port=xingcloud_get("mysql_port");
	$mysql_user=xingcloud_get("mysql_user");
	$mysql_pass=xingcloud_get("mysql_passwd");
	$mysql_db=xingcloud_get("mysql_db");
	$mysqlStatus = 0;
	$conn=mysql_connect($mysql_host.':'.$mysql_port,$mysql_user,$mysql_pass);
	if(is_resource($conn)){
		mysql_select_db($mysql_db, $conn);
		if(($mysqlError = mysql_error())==''){
			$mysqlStatus = 1;
		}
		mysql_close($conn);
	}else{
		$mysqlError = mysql_error();
	}
}catch(Exception $e){
	$mysqlStatus = 0;
	$mysqlError = $e->getMessage();
}

//memcache
try{
	$memcacheError = '';
	$memcacheStatus = extension_loaded('memcache');
	if($memcacheStatus){
		$memcache_host = xingcloud_get('memcache_host');
		$memcache_port = xingcloud_get('memcache_port');
		$memcache = new Memcache();
		$memcacheStatus = $memcache->connect($memcache_host, $memcache_port);
		if($memcacheStatus){
			$memcache->close();
		}else{
			$memcacheError = "connection failed!";
		}
	}else{
		$memcacheError = "memcache extension not loaded!";
	}
}catch(Exception $e){
	$memcacheStatus = 0;
	$memcacheError = $e->getMessage();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>XingCloud - Welcome to XingCloud</title>
<link href="css/application.css" media="screen" rel="stylesheet" type="text/css">
</head>

<body>
<div class="content">
  <h2>Welcome to XingCloud</h2>
  <div class="flash_box">
  <div class="flash_box_top"></div>
  <div class="flash_box_bottom"></div>
</div>
<fieldset class="fieldset">
  <legend>XingCloud Service List</legend>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tbody>
  <table>
  <tr>
    <td rowspan="10" align="center" width="400">
      <h2>KV Storage</h2>
	  <?php
		if($kvStatus==0){
	  ?>
		<br>
		<div align="left"><?php echo $kvError ?></div>
	  <?php
		}
	  ?>      
    </td>
    <td rowspan="3" width="1"><img alt="Pixel_grey" src="images/pixel_grey.gif" height="50" width="1"></td>
    <td rowspan="2" align="center" width="80">
	  <?php
		if($kvStatus>0){
	  ?>
	  	<img alt="ready" src="images/ready.gif" height="40" border="0" width="53">
	  <?php
		}else{
	  ?>
			<img alt="error" src="images/error.jpg" height="40" border="0" width="53">
	  <?php
		}
	  ?>
    </td>
  </tr>
  </table>
  <table>
  <tr>
    <td rowspan="10" align="center" width="400">
      <h2>MySQL</h2>
	  <?php
		if($mysqlStatus==0){
	  ?>
		<br>
		<div align="left"><?php echo $mysqlError ?></div>
	  <?php
		}
	  ?>	  
    </td>
    <td rowspan="3" width="1"><img alt="Pixel_grey" src="images/pixel_grey.gif" height="50" width="1"></td>
    <td rowspan="2" align="center" width="80">
	  <?php
		if($mysqlStatus>0){
	  ?>
	  	<img alt="ready" src="images/ready.gif" height="40" border="0" width="53">
	  <?php
		}else{
	  ?>
			<img alt="error" src="images/error.jpg" height="40" border="0" width="53">
	  <?php
		}
	  ?>
    </td>
  </tr>
  </table>
  <table>
  <tr>
    <td rowspan="10" align="center" width="400">
      <h2>Memcache</h2>
	  <?php
		if($memcacheStatus==0){
	  ?>
		<br>
		<div align="left"><?php echo $memcacheError ?></div>
	  <?php
		}
	  ?>	  
    </td>
    <td rowspan="3" width="1"><img alt="Pixel_grey" src="images/pixel_grey.gif" height="50" width="1"></td>
    <td rowspan="2" align="center" width="80">
	  <?php
		if($memcacheStatus>0){
	  ?>
	  	<img alt="ready" src="images/ready.gif" height="40" border="0" width="53">
	  <?php
		}else{
	  ?>
			<img alt="error" src="images/error.jpg" height="40" border="0" width="53">
	  <?php
		}
	  ?>
    </td>
  </tr>
  </table>  
</tbody>
</table>
</fieldset>
	</div>
    <div class="clear"></div>
    </div>
  </div>
  <div id="app_bottom"></div>
</body>
</html>
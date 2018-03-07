<?php
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

//$host = '10.61.5.66:3310';
$host = '10.61.5.68';
$u = 'citylife';
$p = 'pd8Do7TrWnh0';

//$host = 'localhost';
//$u = 'root';
//$p = '';
$client = mysql_connect($host, $u, $p, true);
//"5:100000213714237","11124062894dea4d07895d1"
$sql = "select * from shop_9.shop_7 where uid='11124062894dea4d07895d1'";
//$sql = "select * from citylife.usercontext where uid='1:-1'";
$rt = mysql_query($sql, $client);
$data = mysql_fetch_assoc($rt);
echo 'from new db=';
print_r($data);	


//$host = '10.61.5.66:3310';
$host = '10.61.5.68';
$u = 'citylife';
$p = 'pd8Do7TrWnh0';

//$host = 'localhost';
//$u = 'root';
//$p = '';
$client2 = mysql_connect($host, $u, $p, true);
$sql2 = "select * from citylife.shop where uid='11124062894dea4d07895d1'";
//$sql = "select * from citylife.usercontext where uid='1:-1'";
$rt2 = mysql_query($sql2, $client2);
$data2= mysql_fetch_assoc($rt2);
echo 'from old db=';
print_r($data2);	
?>
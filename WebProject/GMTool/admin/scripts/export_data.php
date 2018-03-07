<?php
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

$t1 = microtime(true);
//$host = '10.61.5.66:3310';
$host = '10.61.5.68';
$u = 'citylife';
$p = 'pd8Do7TrWnh0';
//$host = 'localhost';
//$u = 'root';
//$p = '';
$src_table = $_REQUEST['table'];
//$src_table = 'farmland';
$process = $_REQUEST['process'];
//$process = 0;
$process_total = 10;//默认支持10进程

$src_db = 'citylife.'.$src_table;
$db_prefix = $src_table;
$target_u = 'citylife';
$target_p = 'pd8Do7TrWnh0';
//$target_u = 'root';
//$target_p = '';
$config = array('shop'=>              array('host'=>'10.61.5.68','db_num'=>'10', 'table_num'=>'20'),
				'sidewalk'=>          array('host'=>'10.61.5.68','db_num'=>'10', 'table_num'=>'100'),
				'road'=>              array('host'=>'10.61.5.70','db_num'=>'10', 'table_num'=>'100'),
				'farmland'=>          array('host'=>'10.61.5.70','db_num'=>'10', 'table_num'=>'100'),
				'decoration'=>        array('host'=>'10.61.5.80','db_num'=>'10', 'table_num'=>'100'),
				'house'=>             array('host'=>'10.61.5.80','db_num'=>'10', 'table_num'=>'20'),
				'crewmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'giftmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'messagecenter'=>     array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'giftrequestmessage'=>array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'warehouse'=>         array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'waterdefine'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'kfq'=>               array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'boatstation'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'airport'=>           array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'userprofile'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'usercontext'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'constructionsite'=>  array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'facility'=>          array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'headquarter'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'aircraft'=>          array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'waterfeature'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'lostmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'manufacturer'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'neighbornavigator'=> array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'permitmessage'=>     array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'pier'=>              array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'playernews'=>        array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'playground'=>        array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'specialdecoration'=> array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'stadium'=>           array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'trainmessage'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				);	
//$target_host = $config[$src_table]['host'];		
$db_num = $config[$src_table]['db_num'];
$table_num = $config[$src_table]['table_num'];	
$client = mysql_connect($host, $u, $p, true);
//$target = mysql_connect($target_host, $target_u, $target_p, true);
$sql_num = 'select count(uid) from '.$src_db;
$result = mysql_query($sql_num, $client);
$num = mysql_fetch_array($result);
if($process){
	$start = intval($num[0] / $process_total) * ($process - 1);
	$end = intval($num[0] / $process_total) * $process;
	if(($num[0] % $process_total) && $process == $process_total){
		$end = $end + ($num[0] % $process_total);
	}
	$dir = "/tmp/$src_table".'_'.$process.'/';
}else{
	$start = 0;
	$end = $num[0];
	$dir = "/tmp/$src_table/";
}

$total = 0;
$uid_arr = array();
$limit = 10000;
for($i = $start; $i < $end; $i+=$limit){
	$value_string = '';
	$sql = 'select uid from '.$src_db.' limit '.$i.','.$limit;
	$rt = mysql_query($sql,$client);
	$rows = array();
	while ($row = mysql_fetch_array($rt, MYSQL_ASSOC)) {
        $rows[] = $row;
    }

	foreach ($rows as $uidlist){
		$index = abs(crc32($uidlist['uid']));
		$db_index = intval($index / $table_num % $db_num);
		$db_name = sprintf("%s_%d",$db_prefix,$db_index);
		$table_name = sprintf("%s_%d",$db_prefix,intval($index % $table_num));
		$key = $db_name.'_'.$table_name;
		$uid_arr[$key] .= ",'".$uidlist['uid']."'";
		$total++;
	}
	
}

//mkdir($dir);
foreach ($uid_arr as $k_db_table=>$uid_str){
		$dir_file = $dir.$k_db_table.'.txt';
		$targetsql = 'select * from '.$src_db.' where uid in ('.ltrim($uid_str,",").') into outfile '."'".$dir_file."'"." fields terminated by ',' enclosed by '\"' lines terminated by '\\n';";
//		file_put_contents('/tmp/targetsql.log', $targetsql."\n", FILE_APPEND);
//		exit;
		mysql_query($targetsql, $client);
}

$t2 = microtime(true) - $t1;
echo 'export process:'.$process. ' table='.$src_table. ' t='.$t2.' deal num='.$total;
?>
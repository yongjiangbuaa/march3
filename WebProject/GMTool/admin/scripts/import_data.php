<?php
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

$t1 = microtime(true);

$src_table = $_REQUEST['table'];
//$src_table = 'road';
$process = $_REQUEST['process'];
//$process = 0;
$process_total = 10;//默认支持10进程

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
$target_host = $config[$src_table]['host'];		
$db_num = $config[$src_table]['db_num'];
$table_num = $config[$src_table]['table_num'];	
$target = mysql_connect($target_host, $target_u, $target_p, true);
$total = 0;
if($process){
	$dir = "/tmp/$src_table".'_'.$process.'/';
}else{
	$dir = "/tmp/$src_table/";	
}
for($j = 0; $j < $db_num; $j++){
	for($i = 0; $i < $table_num; $i++){
		$dir_file = $dir.$src_table.'_'.$j.'_'.$src_table.'_'.$i.'.txt';
		$sql = 'load data infile '."'".$dir_file."'".' ignore into table '.$src_table.'_'.$j.'.'.$src_table.'_'.$i." character set utf8 fields terminated by ',' enclosed by '\"' lines terminated by '\\n';";
		$rt_shop = mysql_query($sql,$target);
		$total++;
	}
}
echo 'import process:'.$process.' table='.$src_table. ' total num ='.$total;
$t2 = microtime(true) - $t1;
echo ' t='.$t2;
?>
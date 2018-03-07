<?php
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
$t1 = microtime(true);
//$host = 'localhost';
//$u = 'root';
//$p = '';
$src_table = $_REQUEST['table'];
//$src_db = 'citylife.'.$src_table;
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
$target_host = $config[$src_table]['host'];		
$db_num = $config[$src_table]['db_num'];
$table_num = $config[$src_table]['table_num'];	
$target = mysql_connect($target_host, $target_u, $target_p, true);
$total = 0;
for($j = 0; $j < $db_num; $j++){
	for($i = 0; $i < $table_num; $i++){
		$sql = 'select count(uid) from '.$db_prefix.'_'.$j.'.'.$db_prefix.'_'.$i;
//		$sql = 'delete from '.$db_prefix.'_'.$j.'.'.$db_prefix.'_'.$i;
		$rt_shop = mysql_query($sql,$target);
		$shop = mysql_fetch_array($rt_shop);
		$total += $shop[0];
	}
}
file_put_contents('/tmp/check_migrate_data.log', 'table='.$src_table. ' total num ='.$total."\n", FILE_APPEND);
$t2 = microtime(true) - $t1;
echo ' t='.$t2;
?>
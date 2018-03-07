<?php
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
$now = time();
$fileindex = intval( $now / 86400 ) - 1;
//$fileindex = $_REQUEST['index'];
$file = '/data/htdocs/log/login_info_'.$fileindex.'.log';
if(!file_exists($file)){
	file_put_contents('/data/htdocs/log/login_info_commit.log', $file.' not exists!'."\n", FILE_APPEND);
	exit;
}
//$file = '/data/htdocs/log/login_info_15162.log';
//$file = 'c:/test.log';

$host = '10.61.5.68';
$u = 'citylife';
$p = 'pd8Do7TrWnh0';

//$host = 'localhost';
//$u = 'root';
//$p = '';
$dbname = 'statistic';
//$dbname = 'citylife';
$table_total = 'login_total';
$table_detail = 'login_detail';

$client = mysql_connect($host, $u, $p, true);

$handle = @fopen ( $file, "r" );
if ($handle) {
	while ( ! feof ( $handle ) ) {
		$line = trim(fgets ( $handle, 4096 ));
		$info = explode(' ', $line);
		$f = str_replace('f=', '', $info[0]);
		$sns_uid = str_replace('uid=', '', $info[1]);
		$t = str_replace('t=', '', $info[2]);
		$index = date("n", $t);
		$pos = strpos($sns_uid, ':');
		$sns = substr($sns_uid, 0, $pos);
		$uid = substr($sns_uid, $pos+1);
		$sql_detail = 'insert into '.$dbname.'.'.$table_detail.'_'.$index.' values('.$f.','.$sns.','."'".$uid."',".$t.',"")';
		$sql_total_get = 'select * from '.$dbname.'.'.$table_total.' where sns='.$sns;
		$resouce_get = mysql_query($sql_total_get, $client);
		$re_sel = mysql_fetch_assoc($resouce_get);
		if($re_sel){
			if($f == 0){
				$sql_upd = 'update '.$dbname.'.'.$table_total.' set new_user_num= new_user_num+1, update_time='.$now. ' where sns='.$sns;	
			}
			if($f == 1){
				$sql_upd = 'update '.$dbname.'.'.$table_total.' set daily_user_num= daily_user_num+1, update_time='.$now. ' where sns='.$sns;
			}
			if($f == 2){
				$sql_upd = 'update '.$dbname.'.'.$table_total.' set choppy_user_num= choppy_user_num+1, update_time='.$now. ' where sns='.$sns;
			}
			mysql_query($sql_upd, $client);
		}else{
			if($f == 0){
				$sql_ins = 'insert into '.$dbname.'.'.$table_total.' values('.$sns.',1,0,0,'.$now.')';
			}
			if($f == 1){
				$sql_ins = 'insert into '.$dbname.'.'.$table_total.' values('.$sns.',0,1,0,'.$now.')';
			}
			if($f == 2){
				$sql_ins = 'insert into '.$dbname.'.'.$table_total.' values('.$sns.',0,0,1,'.$now.')';
			}
			mysql_query($sql_ins, $client);
		}
		mysql_query($sql_detail, $client);
	}
	fclose ( $handle );
	file_put_contents('/data/htdocs/log/login_info_commit.log', $fileindex.' complete!'."\n", FILE_APPEND);
	echo $fileindex.' complete!';
}else {
	echo "open $file fail";
}
?>

<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';
ini_set('memory_limit', '512M');
$type = 1;//1是master,2是stats
if($type==1) {
	$db = COK_DB_NAME;
	$dump_db = "master_db";
}
///usr/local/bin/php /data/htdocs/stats/infobright/mailSrcType2.php sid=2

//*/1 * * * *  /usr/local/bin/php /data/htdocs/stats/infobright/mailSrcType.php sid=1 >> /tmp/qin_mail.log 2>&1

define('MODULE',basename(__FILE__, '.php'));

if(!write_pid_file_tmp(MODULE)){
	return;
}
//insert into x_mail_tmp select uid,toUser,fromUser,fromName,title,contents,rewardId,itemIdFlag,status,type,rewardStatus,saveFlag,createTime,reply,translationId,0 from mail_old order by uid limit 100000;
//insert into mail select uid,toUser,fromUser,fromName,title,contents,rewardId,itemIdFlag,status,type,rewardStatus,saveFlag,createTime,reply,translationId,0 from x_mail_tmp;
//delete  from mail_old where uid in (select uid from x_mail_tmp) order by uid limit 100000 ;
//truncate x_mail_tmp;

for($i=0;$i<5;++$i) {
	$sql1 = "insert into $db.x_mail_tmp select uid,toUser,fromUser,fromName,title,contents,rewardId,itemIdFlag,status,type,rewardStatus,saveFlag,createTime,reply,translationId,0 from $db.mail_old order by uid limit 10000";

	executesql($sql1, 'qinbin0105');

	$sql2 = "insert into $db.mail select uid,toUser,fromUser,fromName,title,contents,rewardId,itemIdFlag,status,type,rewardStatus,saveFlag,createTime,reply,translationId,0 from $db.x_mail_tmp";

	executesql($sql2, 'qinbin0105');

	$sql3 = "delete  from $db.mail_old where uid in (select uid from $db.x_mail_tmp) order by uid limit 10000 ";

	executesql($sql3, 'qinbin0105');

	$sql4 = "truncate $db.x_mail_tmp";

	executesql($sql4, 'qinbin0105');

	sleep(2);
//	insert into x_gold_cost_record_tmp select uid,userId,goldType,type,param1,param2,originalGold,cost,remainGold,0,time from gold_cost_record_old order by uid limit 100000;
//insert into gold_cost_record select uid,userId,goldType,type,param1,param2,originalGold,cost,remainGold,0,time from x_gold_cost_record_tmp;
//delete  from gold_cost_record_old where uid in (select uid from x_gold_cost_record_tmp) order by uid limit 100000 ;
//truncate x_gold_cost_record_tmp;
	$sql5 = "insert into $db.x_gold_cost_record_tmp select uid,userId,goldType,type,param1,param2,originalGold,cost,remainGold,0,time from $db.gold_cost_record_old order by uid limit 20000";

	executesql($sql5, 'qinbin0105');

	$sql6 = "insert into $db.gold_cost_record select uid,userId,goldType,type,param1,param2,originalGold,cost,remainGold,0,time from $db.x_gold_cost_record_tmp";

	executesql($sql6, 'qinbin0105');

	$sql7 = "delete  from $db.gold_cost_record_old where uid in (select uid from $db.x_gold_cost_record_tmp) order by uid limit 20000 ";

	executesql($sql7, 'qinbin0105');

	$sql8 = "truncate $db.x_gold_cost_record_tmp";

	executesql($sql8, 'qinbin0105');
	sleep(2);

}


//sleep(10);
remove_pid_file_tmp(MODULE);

function executesql ($sql ,$file)
{
	$dump_file = "/home/qinbinbin/$file".SERVER_ID;
	if(file_exists($dump_file)){
		unlink($dump_file);
	}
	touch($dump_file);
	$dump_file = realpath($dump_file);
	$dump_file = str_replace('\\', '/', $dump_file);
	global $dump_db;
	$cmd = build_mysql_cmd(
		$dump_db,
		$sql,
		$dump_file
	);
//	echo $cmd.PHP_EOL;
	$re = system($cmd, $retval);
}
function write_pid_file_tmp($name, $server_id = null)
{
	if ($server_id === null) {
		$server_id = SERVER_ID;
	}
	$pidFile = '/tmp/' . $name . '_' . $server_id . '.pid';
	if (file_exists($pidFile)) {
		echo "================pid file exists '" . $pidFile . "' ===============".PHP_EOL;
		return false;
	}
	echo "================ create pid file '" . $pidFile . "' ===============".PHP_EOL;
	$s = file_put_contents($pidFile, $server_id);
	return $s > 0;
}
function remove_pid_file_tmp($name,$server_id = null)
{
	if ($server_id === null) {
		$server_id = SERVER_ID;
	}
	$pidFile = '/tmp/' . $name . '_' . $server_id . '.pid';
	if (file_exists($pidFile)) {
		unlink($pidFile);
		echo "================ unlink pid file '" . $pidFile . "' ===============".PHP_EOL;
	}
}

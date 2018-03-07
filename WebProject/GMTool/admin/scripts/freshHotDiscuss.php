<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
include_once ADMIN_ROOT.'/admins.php';

ini_set('mbstring.internal_encoding','UTF-8');
set_time_limit(0);
date_default_timezone_set('UTC');

$pidFile = '/tmp/freshhotdiscuss.pid';

if(file_exists($pidFile)){
    echo '====>script is running....'.PHP_EOL;
    exit();
}
file_put_contents($pidFile,'freshhotdiscuss');

$redis = new Redis();
$statTime= date("Y-m-d H:i:s",time());
echo "start HotInfoRefresh Time: $statTime \n ";

$redisConnErr = $redis->connect(GLOBAL_REDIS_SERVER_IP2,GLOBAL_REDIS_SERVER_IP2_PORT);
if(!$redisConnErr){
	echo "redis connect is error \n";
}
$discussTypes = array(0,1,2,3,101,201,202,203,204,205,206,207,4,208,209);
$dbIp=GLOBAL_DB_SERVER_IP;
$dbName='cokdb_global';
$fixInfo='+++++++++++++++++++++++++++++++++';
foreach($discussTypes as $key => $value){
//   $freshSql='select id,type,server_id serverId,user_uid uid,user_name userName,content,agree_count agreeCount,create_time createTime from discuss_info where type='.$value.' and agree_count > 0  ORDER BY agree_count desc limit 5';
   $freshSql='select id,type,server_id serverId,user_uid uid,user_name userName,content,agree_count agreeCount,create_time createTime,pic ,pic_ver picVer from discuss_info where type='.$value.' and agree_count > 0  ORDER BY agree_count desc limit 5';

   $hotResult =getRecordFromDB($dbIp, $dbName, $freshSql);
   $discussInfoKey='discuss:hot:info:type:'.$value;
   $discussAgreeKey='discuss:agree:type:'.$value;

   $curhotinfo=$redis->lrange($discussInfoKey,0,-1);
   if(!empty($curhotinfo)){
      foreach($curhotinfo as $index => $info){
        $tempHotInfo=json_decode($info,true);
        $showId=$tempHotInfo['id'];
        $redis->hdel($discussAgreeKey,$showId);
      }
   }
   $redis->del($discussInfoKey);

//   echo "$freshSql \n";
   foreach($hotResult as $key1 => $value1){
        $curAgreeCount=$value1['agreeCount'];
        $discussId=$value1['id'];
        $king_result = json_encode($value1);
        $redis->lpush($discussInfoKey,$king_result);
        $redis->hset($discussAgreeKey,$discussId,$curAgreeCount);
    echo " $king_result \n";
   }
   echo "$fixInfo \n";
}
$redis->close();
$endTime= date("Y-m-d H:i:s",time());

echo "end HotInfoRefresh Time: $endTime \n";
if(file_exists($pidFile)) {
    unlink($pidFile);
}

function getRecordFromDB($dbIp, $dbName, $sql){
    $mysqli = new mysqli($dbIp, GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD, $dbName, 3306);
	//$mysqli = new mysqli($dbIp, "cok","1234567", $dbName, 3306);
    $ret = array();
    if ($mysqli->connect_errno) {
        echo "ERROR, Connect failed $dbIp";
        return $ret;
    }
    $result = $mysqli->query($sql);
    if ($result && is_object($result)) {
        while ($row = $result->fetch_assoc()) {
            $ret [] = $row;
        }
        $result->free();
    }
    $mysqli->close();
    return $ret;
}

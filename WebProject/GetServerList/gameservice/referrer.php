<?php

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
date_default_timezone_set('UTC');


$input = $_REQUEST;
$logfile="/dev/null";
$logadjustback = "/data/log/referrer_callback";

$gameUid = $input["coquid"];
if(!$gameUid){
    $gameUid = $input["cokuid"];//客户端误写
}
if(!$gameUid) {
    file_put_contents($logfile,"empty coquid".PHP_EOL,FILE_APPEND);
    return;
}
$referrer = $input["network_name"];
$referrer = trim($referrer);
$app_referrer = $input["app"];
//file_put_contents($logfile,"$referrer".PHP_EOL,FILE_APPEND);
if(!$referrer) {
    file_put_contents($logfile,"$gameUid empty network_name".PHP_EOL,FILE_APPEND);
    return;
}
define('ROOT', dirname(__DIR__));

require_once ROOT.'/db/db.inc.php';

$currServerId = intval(substr($gameUid,-6));
if($currServerId<=0)
    return;


function get_sfs_server_info_list($sid){
        $xml_file = '/data/htdocs/resource/servers.xml';
        $xml = simplexml_load_file($xml_file);

        $path = 'Group/ItemSpec[@id="'.$sid.'"]';
        $list = $xml->xpath($path);

        $server=$list[0];//check ...
        $id = strval($server['id']);
        $ret = array(
                    'svr_id' => $id,
                    'db_ip' => strval($server["db_ip"]),
                    'db_name' => strval($server["db_name"]),
                    'db_port' => 3306
                );
        return $ret;
}


$dbInfo=get_sfs_server_info_list($currServerId);
//$dbInfo = get_db_info($currServerId);
if(!$dbInfo)
    return;
$gameUid=escape_mysql_special_char($gameUid);
$sql = "select * from stat_reg where uid='$gameUid'";

//$host = gethostbyname(gethostname());

$ini_array = parse_ini_file("config.ini");
$RUN_LEVEL=$ini_array['run_level'];
if ($RUN_LEVEL == '0') {
    //dev
    $mysqli = new mysqli($dbInfo['db_ip'], 'cok', '1234567', $dbInfo['db_name'],$dbInfo['db_port']);
}else if ($RUN_LEVEL == '9') {
    $mysqli = new mysqli($dbInfo['db_ip'], 'gow', 'ZPV48MZH6q9V8oVNtu', $dbInfo['db_name'], $dbInfo['db_port']);
}else{
        exit;
}

$result = $mysqli->query($sql);
$data = array();
if ($result && is_object($result)) {
    /* fetch associative array */
    while ($row = $result->fetch_assoc()) {
        $data [] = $row;
    }
    /* free result set */
    $result->free();
}
$data = $data[0];
$closeDb=false;
if(!$data){//没有玩家数据,返回
    file_put_contents($logfile,"===not exit $gameUid not in db $referrer".PHP_EOL,FILE_APPEND);
    $closeDb = true;
}elseif(!empty($data["referrer"]) ){//玩家数据中已有referrer,且不为Organic,退出
    file_put_contents($logfile,"$gameUid in db is not empty,$referrer db is ".$data["referrer"].PHP_EOL,FILE_APPEND);
    $closeDb = true;
}
//elseif($data['referrer']=='Organic' && $referrer == 'Organic'){//玩家referrer为Organic,但返回也为Organic,退出
//    file_put_contents($logfile,"$gameUid in db is Organic,referrer return too".PHP_EOL,FILE_APPEND);
//    $closeDb = true;
//}


if(!$closeDb) {
    // ==============================应用宝包下载的玩家   自定义应用宝渠道
    if(stripos($app_referrer,  'com.tencent.tmgp.coq') !==false) {
        $mysqli->query("update stat_reg set referrer='yyb Android' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db app_referrer is yyb Android" . PHP_EOL, FILE_APPEND);
    }

    if (stripos($referrer, 'Adwords') !==false) { //stripos不区分大小写,adwords必须在前边,google很多渠道
        $mysqli->query("update stat_reg set referrer='adwords' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is adwords" . PHP_EOL, FILE_APPEND);
    }
    elseif(stripos($referrer, 'Google') !==false && stripos($referrer, 'Campaigns') ==false) { ///google search
        $mysqli->query("update stat_reg set referrer='googlesearch' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is googlesearch" . PHP_EOL, FILE_APPEND);
    }elseif (stripos($referrer, "Facebook") !== false) {
        $mysqli->query("update stat_reg set referrer='facebook' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is facebook" . PHP_EOL, FILE_APPEND);
    }
    elseif(stripos($referrer, 'yeahmobi') !==false) {
        $mysqli->query("update stat_reg set referrer='yeahmobi' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is yeahmobi" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'ndb mobi') !==false) {
        $mysqli->query("update stat_reg set referrer='ndb mobi' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is ndb mobi" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'mobvista') !==false) {
        $mysqli->query("update stat_reg set referrer='mobvista' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is mobvista" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'everyads') !==false) {
        $mysqli->query("update stat_reg set referrer='everyads' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is everyads" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'Glispa') !==false) {
        $mysqli->query("update stat_reg set referrer='glispa' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is glispa" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'instagram') !==false) {
        $mysqli->query("update stat_reg set referrer='instagram' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is instagram" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'approud') !==false) {
        $mysqli->query("update stat_reg set referrer='approud' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is approud" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'adcolony') !==false) {
        $mysqli->query("update stat_reg set referrer='adcolony' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is adcolony" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'Everads') !==false) {
        $mysqli->query("update stat_reg set referrer='everads' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is Everads" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'Mobisharks') !==false) {
        $mysqli->query("update stat_reg set referrer='mobisharks' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is Mobisharks" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'Universal App Campaigns') !==false) {
        $mysqli->query("update stat_reg set referrer='uac' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is Universal App Campaigns" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  '猎豹') !==false) {
        $mysqli->query("update stat_reg set referrer='liebao' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is liebao" . PHP_EOL, FILE_APPEND);
    }elseif(stripos($referrer,  'Untrusted') !==false) {
        $mysqli->query("update stat_reg set referrer='untrusted' where uid='$gameUid'");
        file_put_contents($logfile, "$gameUid set db referrer is untrusted" . PHP_EOL, FILE_APPEND);
    }
    //所有其他渠道,默认用 传过来字段
    elseif(!empty($referrer) && count($referrer) <100){
        $mysqli->query("update stat_reg set referrer='$referrer' where uid='$gameUid'");
        file_put_contents($logfile, "----$gameUid set db referrer is $referrer" . PHP_EOL, FILE_APPEND);
    }
    else{
        file_put_contents("$logadjustback/unknow_referrer", "$gameUid is unknown $referrer" . PHP_EOL, FILE_APPEND);
    }
    $record = false;
//    $rewardReferrer = array('Appturbo-ios','Appturbo-Android','Huawei');
    $rewardReferrer = array();
    foreach($rewardReferrer as $item){
        if(stripos($referrer,  $item) !==false){
            $record = true;
            break;
        }
    }
    if($record) {
        $client = new Redis();
        $redis_key = 'referrer_mail';
        $r = $client->connect("127.0.0.1", 6379, 3);//conn 3 sec timeout.
        if ($r === false) {
            file_put_contents("$logadjustback/admail_reward.log", "connect redis false $gameUid  $referrer " . time() . PHP_EOL, FILE_APPEND);
        } else {
            $arr = array('sid' => $currServerId, 'gameuid' => $gameUid, 'referrer' => $referrer, 'createtime' => floor(microtime(true)*1000));
            $client->rPush($redis_key, json_encode($arr));
        }
        $client->close();
    }

    //adjust 回调log日志
    $file = date("Ymd");
    $msg = date("Y-m-d H:i:s") . '      ' . time() . '  ' . $referrer . '       ' . json_encode($input);
    file_put_contents("$logadjustback/login$file.log", $msg . "\n", FILE_APPEND);
}
$mysqli->close();
function escape_mysql_special_char($val){
    $val = preg_replace('/select|update|drop|truncate|insert|delete|show|desc|ALTER|create| and | or |sleep|union|order/i','',$val);
    $pattern = '/[\']/';
    $replacement = '\\\\${0}';
    $val = preg_replace($pattern,$replacement,$val);
    return $val;
}


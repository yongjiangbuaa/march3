<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/1/4
 * Time: 13:40
 */
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errros',true);
date_default_timezone_set('GMC');


$file = 'stat_login_error.log';
if(!file_exists($file)){
    echo "data file $file not exist\n";
    exit(1);
}

$handle = fopen($file, "rb");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        parse_line($buffer);
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
//$data = array();
//$data[] = '{"_id":{"$oid":"5685c18bd4e36b334f762723"},"msg":"[LOCAL_DB] execute \"insert into stat_login_2016_0 (time, uid, disconnect, ip, level, castlelevel, `payTotal`, `deviceId`, `regTime`, `pf`, `country`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\" with [1451606411653,15239970000001,0,112.198.75.111,13,14,0,357447043534678-8c771601ac2a,1449998799970,market_global,PH]"}';
//$data[] = '{"_id":{"$oid":"5685c1c4d4e36b334f762731"},"msg":"[LOCAL_DB] execute \"update stat_login_2016_0 set disconnect = ? where uid = ? and time = ?\" with [1451606468793,26985447000001,1451606443167]"}';
//$data[] = '{"_id":{"$oid":"5685c18fd4e36b334f762724"},"msg":"[LOCAL_DB] query \"select count(1) loginTimes from stat_login_2016_0 where uid = ? and time \u003e ?\" with [68123718000001,1451606400000]"}';

//foreach($data as $line ){
////    echo $line,PHP_EOL;
//    parse_line($line);
//}

function parse_line($line){
    $line = trim($line);
    if(empty($line)){
       return false;
    }
    $sql = substr($line,71);
//    echo $sql,PHP_EOL;
    if(strncasecmp($sql,'insert',6) === 0){
        // insert
        $params = substr($sql,179);
        $pos = strpos($params,']');
        $params = substr($params,0,$pos);
        process_insert($params);
//        echo $params,PHP_EOL;
    }elseif(strncasecmp($sql,'update',6) === 0){
        // update
        $params = substr($sql,79);
        $pos = strpos($params,']');
        $params = substr($params,0,$pos);
        process_update($params);
//        echo $params,PHP_EOL;
    }else{
//        echo "skip msg: ",$line,PHP_EOL;
    }

}

function process_insert($param_text){
    $insert_sql = "insert into stat_login_2016_0 (time, uid, disconnect, ip, level, castlelevel, `payTotal`, `deviceId`, `regTime`, `pf`, `country`) values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');\n";
    $param = explode(',',$param_text);
    $sql = vsprintf($insert_sql,$param);
    file_put_contents('stat_login_data.sql',$sql,FILE_APPEND);
}

function process_update($param_text){
    $update_sql = "update stat_login_2016_0 set disconnect = '%s' where uid = '%s' and time = '%s';\n";
    $param = explode(',',$param_text);
    $sql = vsprintf($update_sql,$param);
    file_put_contents('stat_login_data.sql',$sql,FILE_APPEND);
}
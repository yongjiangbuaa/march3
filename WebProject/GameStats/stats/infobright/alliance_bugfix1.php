<?php
//$minSid=5;
//$maxSid=5;
$dbInfoIp = '10.155.110.57';
//$dbInfoIp='10.1.16.211';
$dbInfo = get_server_db_info($dbInfoIp);
ini_set('memory_limit', '1024M');

if (empty($dbInfo)) {
    echo "select dbInfo error";
    exit();
}
$uidArr = array(
    array('s2',	 '570033131000002',	'200002',	50),
    array('s5',	 '1022743062000005',	'200002',	50),
    array('s1',	 '712523891000002',	'200002',	44),
    array('s2',	 '570033131000002',	'200002',	36),
    array('s8',	 '26562385000009',	'200002',	29),
    array('s1',	 '407106094000001',	'200002',	27),
    array('s9',	 '284505228000009',	'200002',	26),
    array('s9',	 '284505228000009',	'200002',	23),
    array('s1',	 '712523891000002',	'200002',	20),
    array('s4',	 '68852785000003',	'200002',	20),
    array('s8',	 '26562385000009',	'200002',	14),
    array('s9',	 '324592292000009',	'200002',	14),
    array('s1',	 '159206609000001',	'200002',	13),
    array('s2',	 '570033131000002',	'200002',	13),
    array('s1',	 '141398232000001',	'200002',	12),
    array('s1',	 '47157046000001',	'200002',	12),
    array('s9',	 '284505228000009',	'200002',	12),
    array('s9',	 '284505228000009',	'200002',	12),
    array('s1',	 '241229121000001',	'200002',	11),
    array('s8',	 '26562385000009',	'200002',	11),
    array('s1',	 '100346917000001',	'200002',	10),
    array('s1',	 '201345497000006',	'200002',	10),
    array('s1',	 '14327244000001',	'200002',	10),
    array('s1',	 '204982561000001',	'200002',	10),
    array('s1',	 '43511440000001',	'200002',	10),
    array('s1',	 '244814940000001',	'200002',	10),
    array('s1',	 '710647000001',	'200002',	10),
    array('s2',	 '320674852000004',	'200002',	10),
    array('s3',	 '473165074000003',	'200002',	10),
    array('s3',	 '473165074000003',	'200002',	10),
    array('s4',	 '692394195000003',	'200002',	10),
    array('s9',	 '324592292000009',	'200002',	10),
    array('s9',	 '245341160000009',	'200002',	10),
    array('s9',	 '245341160000009',	'200002',	10),
    array('s18',	 '1512699398000015',	 '200002',	20),
    array('s18',	 '397378236000023',	 '200002',	16),
    array('s16',	 '691956926000016',	 '200002',	15),
    array('s18',	 '397378236000023',	 '200002',	14),
    array('s18',	 '397378236000023',	 '200002',	14),
    array('s14',	 '1054891739000014',	 '200002',	10),
    array('s18',	 '1512699398000015',	 '200002',	10),
    array('s25',	 '480096532000025',	 '200002',	15),
    array('s27',	 '310525383000027',	 '200002',	11),
    array('s38',	 '10508104504000038',	 '200002',	60),
    array('s35',	 '10318748064000059',	 '200002',	20),
    array('s40',	 '10202119109000040',	 '200002',	18),
    array('s40',	 '10282130118000040',	 '200002',	16),
    array('s35',	 '10284501281000035',	 '200002',	15),
    array('s31',	 '10084340500000031',	 '200002',	12),
    array('s40',	 '10554405565000040',	 '200002',	12),
    array('s35',	 '11321418771000035',	 '200002',	10),
    array('s35',	 '10318748064000059',	 '200002',	10),
    array('s35',	 '10388919596000035',	 '200002',	10),
);
$data = array();
foreach($uidArr as $tmp){
    $cnt = intval($tmp[3]*0.9);
    echo '---'.$cnt.PHP_EOL;
    $data[$tmp[0]][] = array('uid'=>$tmp[1],'itemid'=>$tmp[2],'cnt'=>$cnt);
}
$sidArr = array_keys($data);

foreach ($sidArr as $serverid) {
    $sid = substr($serverid,1);
    $curDbInfo = $dbInfo[$sid];
    if (empty($curDbInfo)) {
        echo "select $sid ++++++++ is not exist \n";
        continue;
    }

    $db_ip = $curDbInfo['ip_inner'];
    $db_name = $curDbInfo['dbname'];
    if (empty($db_ip) || empty($db_name)) {
        echo "db $db_ip is not exist \n";
        continue;
    }
    $link = mysqli_connect($db_ip, "gow", "ZPV48MZH6q9V8oVNtu", $db_name, 3306);
    if (!$link) {
        echo 'connect db serve error ' . SERVER_ID . '--' . PHP_EOL;
        return;
    }
    foreach ($data[$serverid] as $item) {

        $uid = $item['uid'];
        $cnt = $item['cnt'];
        $itemid = $item['itemid'];

        $itemGetSql = "update user_item set count=count-$cnt where ownerId='{$uid}' and itemId='{$itemid}' and count>$cnt";
        echo $sid.'__'.$itemGetSql.PHP_EOL;

        $result = mysqli_query($link, $itemGetSql);
        print_r($result);
        echo "\n";
//
//        $itemInfos = array();
//        if ($result && is_object($result)) {
//            while ($row = $result->fetch_assoc()) {
//                $itemInfos [] = $row;
//            }
//            $result->free();
//        }
//
//        if (empty($itemInfos)) {
//            echo "there is no data in alliance_member $sid \n";
//            continue;
//        }
//        $decrNum = $cnt * 108000;
//        $num = $itemInfos[0]['accPoint'] - $decrNum;
//        if ($num < 0) {
//            $num = 0;
//        }
//        $sqldelete = "update alliance_member set accPoint=$num where uid='{$uid}' ";
//
//        echo $sid.'--'. $itemInfos[0]['accPoint'] .'__' . $sqldelete . PHP_EOL;
////        $qinret = mysqli_query($link, $sqldelete);
    }

}

function getRecordFromDB($dbIp, $dbName, $sql)
{
    $mysqli = new mysqli($dbIp, "gow", "ZPV48MZH6q9V8oVNtu", $dbName, 3306);
    //$mysqli = new mysqli($dbIp, "cok","1234567", $dbName, 3306);
    if ($mysqli->connect_errno) {
        echo "ERROR, Connect failed $dbIp";
        return;
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

function get_server_db_info($dbInfoIp)
{
    $dbArr = array();
    $sql = "select db_id, dbname, ip_inner from tbl_db where db_id > 0";
    $ret = getRecordFromDB($dbInfoIp, "cokdb_admin_deploy", $sql);
    foreach ($ret as $key => $value) {
        $dbArr[$value['db_id']] = $value;
    }
    return $dbArr;
    //ip,db_ip
}

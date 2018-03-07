<?php

$span = 1;
if (isset($_REQUEST['fixdate'])) {
    $req_date_end = $_REQUEST['fixdate'];
} else {
    $req_date_end = date('Y-m-d', time());
}
$req_date_start = date("Y-m-d", strtotime("-$span day", strtotime($req_date_end)));

$reg_date_end_ts = strtotime($req_date_end) * 1000;
$reg_date_start_ts = strtotime($req_date_start) * 1000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$tablename = 'goods_cost_record' . '_' . date('Ym', $reg_date_start_ts / 1000);


// sid date,itemid,cost是消耗个数     type 0是增加,1是消耗
$sql = "select from_unixtime(time/1000,'%Y%m%d') as date ,type,itemId, param1,sum(cost) cost from $tablename where time>=$reg_date_start_ts and time<=$reg_date_end_ts group by date,itemId,type,param1";

$dbInfo = get_game_db(SERVER_ID);
$ret = query_game_db($dbInfo, $sql);

//echo $sql.PHP_EOL;
$coun = array();
foreach ($ret as $users) {
    $date = $users['date'];
    $itemId = $users['itemId'];
    $cost = $users['cost'];
    $type = $users['type'];
    $param1 = $users['param1'];
    $coun[$date][$type][$itemId][$param1] = $cost > 0 ? $cost : 0;
}
$records = array();
foreach ($coun as $date => $itemkey) {
    foreach ($itemkey as $type => $itemkey1) {
        foreach ($itemkey1 as $itemid => $costvalueArr) {
            foreach ($costvalueArr as $param1 => $costvalue) {
                $one = array();
                $one['date'] = $date;
                $one['type'] = $type;
                $one['itemId'] = $itemid;
                $one['cost'] = isset($costvalue) ? $costvalue : 0;
                $one['param1'] = isset($param1) ? $param1 : 0;
                $records[] = $one;
            }
        }
    }
}
//echo json_encode($records);
$client = getInfobrightConnect('stat_log_rbi_dailyGoodsCost.php');
if (!$client) {
    echo 'mysql error stat_log_rbi_dailyGoodsCost.php' . PHP_EOL;
    return;
}
foreach ($records as $fieldvalue) {
    $keys = array_keys($fieldvalue);
    $updKv = buildUpdateSql($fieldvalue);
    $f = join(',', $keys);
    $str = join(',', $fieldvalue);
    $f = 'sid,' . $f;
    $str = SERVER_ID . ',' . $str;

    $insertSql = "INSERT into %s ($f) VALUES " . " ($str) ";
    $ondup = 'ON DUPLICATE KEY UPDATE ' . $updKv;
    $insertSql .= " $ondup;";


    $db_tbl = "$statdb_allserver.stat_log_rbi_dailygoodscost";
    $sql = sprintf($insertSql, $db_tbl);
//	echo $sql,"\n";
    query_infobright_new($client, $sql);
}


<?php
//日期	新增		次日留存	3日留存	4日留存	5日留存
//date	addCount	r1	r2	r3	r4	r5

$span = 5;
if (isset($_REQUEST['fixdate'])) {
    $req_date_end = $_REQUEST['fixdate'];
} else {
    $req_date_end = date('Ymd', time());
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day", strtotime($req_date_end)));

$reg_date_end_ts = strtotime($req_date_end) * 1000 + 86400 * 1000;//包含今天
$reg_date_start_ts = strtotime($req_date_start) * 1000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

//$whereSql= "l.date>=str_to_date('$req_date_start','%Y-%m-%d') and l.date<=str_to_date('$req_date_end','%Y-%m-%d')";
$monthArr = monthList($reg_date_start_ts / 1000, $reg_date_end_ts / 1000);
foreach ($monthArr as $i) {
    $db_start = 'coklog_function.function_log_' . $i;
    $sql = "select server_id,date,date_format(from_unixtime(var_data1/1000),'%Y%m%d') as regDate,count(distinct l.userid) users  from $db_start l
            where l.timestamp>=$reg_date_start_ts and l.timestamp< $reg_date_end_ts and category=3 and type=1 and l.var_data1>=$reg_date_start_ts and l.var_data1< $reg_date_end_ts group by server_id,date,date_format(from_unixtime(var_data1/1000),'%Y%m%d') ";
    if (isset($sql_sum)) {
        $sql_sum = $sql_sum . " union " . $sql;
    } else {
        $sql_sum = $sql;
    }
}
$retData = query_infobright($sql_sum);

//echo $sql;
$datas = array();
foreach ($retData as $value) {
    $datas[$value["server_id"]][$value["regDate"]][date('Ymd', strtotime($value["date"]))] += $value["users"];
}

$records = array();
ksort($datas);
foreach ($datas as $server_id => $logs) {
    foreach ($logs as $date => $data) {
        $date_ts = strtotime($date);
        $one = array();
        $one['sid'] = $server_id;
        $one['date'] = $date;
        $one['addCount'] = intval($data[$date]);

        $r = $data;
        if ($r) {
            ksort($r);
            foreach ($r as $date2 => $data2) {
                if ($date2 == $date) continue;
                $date2_ts = strtotime($date2);
                $days = round(($date2_ts - $date_ts) / 86400);
                if ($days < 0) continue;
                if ($days > 5) continue;
                $one["r$days"] = $data2;
            }
            $records[] = $one;
        }
    }
}
//print_r($records);


foreach ($records as $fieldvalue) {
    $keys = array_keys($fieldvalue);
    $updKv = buildUpdateSql($fieldvalue);
    $f = join(',', $keys);
    $str = join(',', $fieldvalue);
//	$f = 'sid,'.$f;
//	$str = SERVER_ID.','.$str;

    $insertSql = "INSERT into %s ($f) VALUES " . " ($str) ";
    $ondup = 'ON DUPLICATE KEY UPDATE ' . $updKv;
    $insertSql .= " $ondup;";

    $db_tbl = "$statdb_allserver.stat_retention_kill_titan";
    $sql = sprintf($insertSql, $db_tbl);
    echo $sql, "\n";
    query_infobright($sql);
}


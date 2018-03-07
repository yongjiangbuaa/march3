<?php

!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
    $start = date("Y-m-d 00:00",time()-86400*6);
if(!$_REQUEST['end'])
    $end = date("Y-m-d 23:59",time());
if($_REQUEST['userId'])
    $userId = $_REQUEST['userId'];
$type = $_REQUEST['action'];
//$userId = $_REQUEST['userId'];
if ($type == 'view') {
    $sqlSoul = "select uid,soul_count,soul_army_rank from user_soul where uid='{$userId}'";
    $ret = $page->execute($sqlSoul, 3);
    $ret = $ret['ret'];
    if (!empty($ret) && !empty($ret['data']['0'])) {
        $soul = $ret['data']['0'];
        $start = $_REQUEST['start'];
        $end = $_REQUEST['end'];
        $startDate = strtotime(substr($start, 0, 10));
        $endDate = strtotime(substr($end, 0, 10));
        $monthArr = monthList($startDate,$endDate);
        $sql;
        foreach ($monthArr as $i){
            $db_start = 'coklog_function.function_log_' . $i;
            $sqlSub = "select date,type, sum(int_data1) sumData from $db_start where userid='{$userId}' and category=59 and type < 3 and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') group by date, type";
            if (empty($sql)) {
                $sql = $sqlSub;
            }else{
                $sql = $sql . " union all ".$sqlSub;
            }
        }

        $sql .= " order by date desc";
        $result_pass = query_infobright($sql);

        $alldata = $datearr =  array();
        foreach ($result_pass['ret']['data'] as $currow){
            $date = $currow['date'];
            $datearr[$date] = $date;

            if($currow['type'] == 1){//英灵数
                $alldata[$date]['soul'] = $currow['sumData'];
            }else{//死侍数
                $alldata[$date]['soulSoldier'] = $currow['sumData'];
            }
        }

        $showData = true;
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
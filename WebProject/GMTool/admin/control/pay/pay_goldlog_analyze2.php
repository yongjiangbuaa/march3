<?php
!defined('IN_ADMIN') && exit('Access Denied');
if ($_REQUEST['user'])
    $itemid = $_REQUEST['user'];//道具id

if (!$_REQUEST['end'])
    $start = date("Y-m-d 00:00:00", time() - 86400 * 2);
$end = date("Y-m-d 23:59:59", time());
//金币消费统计类型
$eventNames = $goldLink;
$eventOptions = '<option></option>';
foreach ($eventNames as $eventType => $eventName)
    $eventOptions .= "<option id={$eventType}>{$eventName}</option>";

$eventNames['sum'] = '合计';

global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);

if ($_REQUEST['analyze'] == 'platform') {
    $erversAndSidsArr = getSelectServersAndSids($sttt);
    $selectServer = $erversAndSidsArr['withS'];
    $selectServerids = $erversAndSidsArr['onlyNum'];
    $start = $_REQUEST['start'] ? strtotime($_REQUEST['start']) * 1000 : 0;
    $end = $_REQUEST['end'] ? strtotime($_REQUEST['end']) * 1000 : strtotime($end) * 1000;

    $start = date('Ymd', $start / 1000);
    $end = date('Ymd', $end / 1000);

    //语言文件
    $lang = loadLanguage();
    $GoodsXml = loadXml('goods', 'goods');
    $BuildsXml = loadXml('building', 'building');

    $count = 0;
     $dateEvent = $eventAll =$alldata = array();
    //没有type=9的数据,=9去每个服查询.type=9是赠送金币

    $sids = implode(',', $selectServerids);
    $whereSql = " where sid in ($sids)";

    //选择消费类型type,不选时候,type!=9 9是增加金币
    $whereSql .= " and date >= $start and date < $end ";
    if ($_REQUEST['payuser']) {
        $whereSql .= " and paidFlag > 0 ";
    }
    if ($_REQUEST['event']) {
        $whereSql .= " and type={$_REQUEST['event'] }";
    }
    if(!$itemid){
        //没有选择消费类型
        //每种类型(type)物品购买总人数总次数
        $sql = "select date,type,sum(users) as user,sum(times) as times ,sum(sumc) money from stat_allserver.pay_goldStatistics_daily_groupByType g $whereSql GROUP BY date,type order by date desc ";
        $result1 = query_infobright($sql);
        if ($result1['ret']['data']) {
            foreach ($result1['ret']['data'] as $curRow) {
                $date = $curRow['date'];
                $dateEvent[$date] = $date;
                $eventType = $curRow['type'];
                $eventAll[$date][$eventType]['times'] += $curRow['times'];
                $eventAll[$date][$eventType]['user'] += $curRow['user'];
                $eventAll[$date][$eventType]['money'] += $curRow['money'];
                //不分日期 总计
                $alldata[$eventType]['times'] +=  $curRow['times'];
                $alldata[$eventType]['user'] +=  $curRow['user'];
                $alldata[$eventType]['money'] +=  $curRow['money'];

            }
        }
        if(in_array($_COOKIE['u'],$privilegeArr)){
            $html .= $sql.PHP_EOL;
        }
    }
    //另外特殊处理type12 的这些
    //type12是各种道具,此时需要看param1来确定每种商品 或者 CD具体队列   [[ 购买总人数 | 总次数 ]]
    if($itemid){
        $sql = "select date,type,param1,sum(users) as user,sum(times) as times ,sum(sumc) money from stat_allserver.pay_goldStatistics_daily_groupByGoodsAndResource $whereSql and type=12 and param1='$itemid' GROUP BY date,param1 order by date desc ";
    }else{
        $sql = "select date,type,param1,sum(users) as user,sum(times) as times ,sum(sumc) money from stat_allserver.pay_goldStatistics_daily_groupByGoodsAndResource $whereSql and type=12 and ( param1 > 200349 or param1 < 200300 ) GROUP BY date,param1 order by date desc ";
    }
    if(in_array($_COOKIE['u'],$privilegeArr)){
        $html .= '####'.$sql.PHP_EOL;
    }
    $result1 = query_infobright($sql);
    if ($result1['ret']['data']) {
        foreach ($result1['ret']['data'] as $curRow) {
            if((int)$curRow['param1'] > 0 ){
                $eventType = (int)$curRow['param1'];
                $type = $curRow['type'];
//                $eventAll[$date][$type]['times'] -= $curRow['times'];
//                $eventAll[$date][$type]['user'] -= $curRow['user'];
            }else{
                continue;
            }
//            $eventType = (int)$curRow['param1'] > 0 ? $curRow['param1'] : $curRow['type'];
            $date = $curRow['date'];
            $dateEvent[$date] = $date;
            $eventAll[$date][$eventType]['times'] += $curRow['times'];
            $eventAll[$date][$eventType]['user'] += $curRow['user'];
            $eventAll[$date][$eventType]['money'] += $curRow['money'];

            $alldata[$eventType]['times'] += $curRow['times'];
            $alldata[$eventType]['user'] += $curRow['user'];
            $alldata[$eventType]['money'] += $curRow['money'];
        }
    }
    $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>类型</th><th>名称</th><th colspan='3'>总计</th>";
    foreach ($dateEvent as $date) {
        $html .= "<th colspan='3'>$date</th>";
    }
    $html .= "</tr></thead>";

    $html .= "<tr><th>-</th><th>-</th>
<th><a href='#' onclick=\"sort_table(1, $(this).index(), asc1); asc1 *= -1;\">人数</a></th>
<th><a href='#' onclick=\"sort_table(1, $(this).index(), asc1); asc1 *= -1;\">次数</a></th>
<th><a href='#' onclick=\"sort_table(1, $(this).index(), asc1); asc1 *= -1;\">消耗</a></th>";
    foreach ($dateEvent as $date) {
        $html .= "<th>人数</th><th>次数</th><th>消耗</th>";
    }
    $html .= "</tr><tbody id='adDataTable'>";


    foreach ($alldata as $eventType=>$value) {
        if ($eventType < 200000) {
            $eventName = $eventNames[$eventType];
        } elseif ($eventType < 400000 || $eventType > 500000) {
            $eventName = $lang[(int)$GoodsXml[$eventType]['name']];
        } else {
            $level = $eventType % 100;
            $eventTypeTmp = intval($eventType / 100) * 100;
            $eventName = $level . '级 ' . $lang[(int)$GoodsXml[$eventTypeTmp]['name']];
        }
        if (!$eventName) {
            $eventName = $eventType;
        }
        $htmltmp='';
        $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>$eventType</font></td><td><font color='#0088CC'>$eventName</font></td><td>{$alldata[$eventType]['user']}</td><td>{$alldata[$eventType]['times']}</td><td>{$alldata[$eventType]['money']}</td>";
        foreach($dateEvent as $date){
            $htmltmp .= "<td>{$eventAll[$date][$eventType]['user']}</td><td>{$eventAll[$date][$eventType]['times']}</td><td>{$eventAll[$date][$eventType]['money']}</td>";
        }
        $htmltmp .= "</tr>";
        $html .= $htmltmp;
    }
    $html .= '</tbody><table></div>';
    echo $html;
    exit();
//        if ($eventType == 52 || $eventType == 31) {
//            $eventName = "<span style='color: red;cursor: pointer;font-size: 14px;' onclick='getInfo($eventType)'>$eventName</span> ";
//        }
//        $html .= "<tbody><tr class='listTr'><td style='width:115px;'><font color='#0088CC'>$eventName</font></td><td><font color='#0088CC'>{$eventAll[$eventType]['result']}</font></td>";
//        foreach ($dateEvent as $date => $dateCount) {
//            $temp = $event[$eventType][$date]['result'];
//            if ($temp == null)
//                $temp = '-';
//            $html .= "<td><span style='color: #0088CC;cursor: pointer;' onclick=\"getUserInfo('$eventType','$date')\">$temp</span></td>";
//        }
//        $html .= "</tr></tbody><tbody><tr class='listTr'><td>次数</td><td>{$eventAll[$eventType]['times']}</td>";
//        foreach ($dateEvent as $date => $dateCount) {
//            $temp = $event[$eventType][$date]['times'];
//            if ($temp == null)
//                $temp = '-';
//            $html .= "<td>$temp</td>";
//        }
//        $html .= "</tr></tbody><tbody><tr class='listTr'><td>人数</td><td>{$eventAll[$eventType]['user']}</td>";
//        foreach ($dateEvent as $date => $dateCount) {
//            $temp = $event[$eventType][$date]['user'];
//            if ($temp == null)
//                $temp = '-';
//            $html .= "<td>$temp</td>";
//        }

}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>
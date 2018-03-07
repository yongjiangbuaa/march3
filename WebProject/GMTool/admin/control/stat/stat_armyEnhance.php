<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d', time() - 86400 * 3);
$end = date('Y-m-d');
global $servers;
$allServerFlag = false;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

$statType_title = array('兵种强化');
foreach ($statType_title as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}
//FOOT(1),
//        RIDE(2),
//        BOW(3),
//        CAR(4),
//        DRAGON(5),
if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
    $end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);
    $monthArr = monthList(strtotime($start),strtotime($end));

    $sids = implode(',', $selectServerids);
    if (empty($_REQUEST['selectServer'])) {
        $whereSql = "";
    } else {
        $whereSql = " and server_id in ($sids) ";
    }
    if ($_REQUEST['userId']) {
        $user_id = $_REQUEST['userId'];
        $whereSql .= " and userid='$user_id' ";
    }
    $html = '';
    $wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    $statType = $_REQUEST['statType'];
    if ($statType == 0 ) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data2 as armyType,count(DISTINCT userid) users ,count(1) times ,sum(int_data1) cnt,max(int_data3) as maxlevel from $db_start where category=48 and type=1 $wheretime $whereSql group by date ,armyType ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc";
        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $indexArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $index =  $currow['armyType'];

            $indexArr[$index] = $index;
            $alldata[$date][$index]['times'] += $currow['times'];
            $alldata[$date][$index]['users'] += $currow['users'];
            $alldata[$date][$index]['cnt'] += $currow['cnt'];
            $alldata[$date][$index]['maxlevel'] += $currow['maxlevel'];

            $sum[$index]['times'] += $currow['times'];//总和
            $sum[$index]['users'] += $currow['users'];
            $sum[$index]['cnt'] += $currow['cnt'];//数量
            $sum[$index]['maxlevel'] += $currow['maxlevel'];//数量
        }
        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>--</th><th colspan='4'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='4'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>itemid</th><th>次数</th><th>人数</th><th>数量</th><th>等级</th>";
        foreach ($datearr as $date) {
            $html .= "<th>次数</th><th>人数</th><th>数量</th><th>等级</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        ksort($indexArr);
        foreach ($indexArr as $key) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['times']}</td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['cnt']}</td><td>{$sum[$key]['maxlevel']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['times']}</td><td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['cnt']}</td><td>{$alldata[$date][$key]['maxlevel']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }
    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>
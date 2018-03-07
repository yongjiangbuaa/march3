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
//王战打点: 1.王战结果统计 2.王座易主 3.投石车易主 4.王座战斗 5.投石车战斗
$statType_title = array('王战结果统计', '王座易主', '投石车易主','王座战斗','投石车战斗');
foreach ($statType_title as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}

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
    if ($statType == 0) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, count(1) times ,sum(var_data3) users from $db_start where category=34 and type=1 $wheretime $whereSql group by date  ";
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
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date]['times'] += $currow['times'];
            $alldata[$date]['users'] += $currow['users'];

            $sum['times'] += $currow['times'];//总和
            $sum['users'] += $currow['users'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>-</th><th>次数</th><th>人数(重复)</th></tr></thead>";

        $html .="<tr><td>合计</td><td>{$sum['times']}</td><td>{$sum['users']}</td></tr>";
        foreach ($datearr as $date) {
            $html .= "<tr><td>{$date}</td><td>{$alldata[$date]['times']}</td><td>{$alldata[$date]['users']}</td></tr>";
        }

        $html .= '</table></div>';
    }
    else if ($statType == 1 || $statType==2) {
        $type = $statType+1;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, userid ,count(1) times from $db_start where category=34 and type=$type $wheretime $whereSql group by date,userid  ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date ,userid desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $people = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $people[$currow['userid']] = $currow['userid'];
            $alldata[$date][$currow['userid']] += $currow['times'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr>";
        foreach ($datearr as $date) {
            $html .= "<th>uid</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($people as $key=>$item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">";
            foreach ($datearr as $date) {
                $showvalue = $alldata[$date][$key]?$alldata[$date][$key]:'--';
                $htmltmp .= "<td>{$key}</td><td>{$showvalue}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }
    else if($statType == 3 || $statType == 4){
        $type= $statType + 1;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, userid,sum(var_data1) attKillCount ,sum(var_data2) defKillCount from $db_start where category=34 and type=$type $wheretime $whereSql group by date ,userid ";
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
        $alldata = $datearr = $sum = $people = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $user = $currow['userid'];
            $people[$user] = $user;

            $alldata[$date][$user]['attKillCount'] += $currow['attKillCount'];
            $alldata[$date][$user]['defKillCount'] += $currow['defKillCount'];

        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='1'>uid</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>--</th>";
        foreach ($datearr as $date) {
            $html .= "<th>attKillCount</th><th>defKillCount</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($people as $key=>$item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td>{$key}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['attKillCount']}</td><td>{$alldata[$date][$key]['defKillCount']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';

    }
    if(empty($alldata)){

    }
    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>
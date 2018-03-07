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

//包含：每天参与迷宫的人数；玩家购买钥匙的数量；达到每层的人数统计；玩家购买不同档位步数的次数（例如50金币档买了50次，100金币档买了100次）；每一层打了BOSS进入下一层和没打BOSS进入下一层的人数；每一层选择进入下一层和选择离开的人数
$statType_title = array('购买礼包后抽奖', '分享抽奖');
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
    if ($statType == 1) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, count(DISTINCT userid) users ,count(1) times from $db_start where category=41 and type=1 $wheretime $whereSql group by date  ";
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
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>-</th><th>次数</th><th>人数</th></tr></thead>";

        $html .="<tr><td>合计</td><td>{$sum['times']}</td><td>{$sum['users']}</td></tr>";
        foreach ($datearr as $date) {
            $html .= "<tr><td>{$date}</td><td>{$alldata[$date]['times']}</td><td>{$alldata[$date]['users']}</td></tr>";
        }

        $html .= '</table></div>';
    }
    else if ($statType == 0) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data1 as times,sum(int_data2) as sumgold  from $db_start where category=41 and type=0 $wheretime $whereSql group by date,times ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc,times desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['times']] = $currow['sumgold'];

            $sum[$currow['times']] += $currow['sumgold'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='1'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>次数</th><th>金币数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>金币数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

//        ksort($sum);
        foreach ($sum as $key=>$item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$item}</td>";
            foreach ($datearr as $date) {
                $showvalue = $alldata[$date][$key]>0?$alldata[$date][$key]:0;
                $htmltmp .= "<td>{$showvalue}</td>";
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
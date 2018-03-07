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
// 0 招募 1 招募花费金币 2 训练 3 清除训练cd 4 解雇英雄 5 升星
$statType_title = array('免费招募', '招募花费金币', '训练', '清除训练cd', '解雇英雄', '升星');
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
            $sql_pass = "select date, int_data1 as heroid,int_data2 as quality ,count(DISTINCT userid) users,count(1) times from $db_start where category=39 and type=0 $wheretime $whereSql group by date,int_data1,int_data2  ";
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


            $alldata[$date][$currow['heroid']][$currow['quality']]['users'] += $currow['users'];
            $alldata[$date][$currow['heroid']][$currow['quality']]['times'] += $currow['times'];

            $sum[$currow['heroid']][$currow['quality']]['times'] += $currow['times'];//总和
            $sum[$currow['heroid']][$currow['quality']]['users'] += $currow['users'];//总和
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>--</th><th colspan='2'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>英雄ID</th><th>品质</th><th>次数</th><th>人数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>次数</th><th>人数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        ksort($sum);
        foreach ($sum as $heroid => $item) {
            foreach ($item as $quality => $value) {

                $htmltmp = '';
                $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td>{$heroid}</td><td>{$quality}</td><td>{$sum[$heroid][$quality]['times']}</td><td>{$sum[$heroid][$quality]['users']}</td>";
                foreach ($datearr as $date) {
                    $showvalue = $alldata[$date][$heroid][$quality]['times'] > 0 ? $alldata[$date][$heroid][$quality]['times'] : 0;
                    $showvalue1 = $alldata[$date][$heroid][$quality]['users'] > 0 ? $alldata[$date][$heroid][$quality]['users'] : 0;
                    $htmltmp .= "<td>{$showvalue}</td><td>{$showvalue1}</td>";
                }
                $htmltmp .= "</tr>";
                $html .= $htmltmp;
            }
        }
        $html .= '</tbody></table></div>';
    } else if ($statType == 1) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, sum(int_data1) cost,count(DISTINCT userid) users,count(1) times  from $db_start where category=39 and type=1 $wheretime $whereSql group by date  ";
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

            $alldata[$date]['cost'] += $currow['cost'];
            $alldata[$date]['users'] += $currow['users'];
            $alldata[$date]['times'] += $currow['times'];

            $sum['cost'] += $currow['cost'];
            $sum['users'] += $currow['users'];
            $sum['times'] += $currow['times'];

        }
        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><th>--</th><th>次数</th><th>人数</th><th>花费</th></thead>";

        $html .= "<tr><td>合计</td><td>{$sum['times']}</td><td>{$sum['users']}</td><td>{$sum['cost']}</td></tr>";
        foreach ($datearr as $date) {
            $html .= "<tr><td>{$date}</td><td>{$alldata[$date]['times']}</td><td>{$alldata[$date]['users']}</td><td>{$alldata[$date]['cost']}</td></tr>";
        }

    } else if ($statType == 2 || $statType == 4) {
        //购买钥匙数量
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data1,count(DISTINCT userid) users ,count(1) times from $db_start where category=39 and type=$statType $wheretime $whereSql group by date,int_data1  ";
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

            $alldata[$date][$currow['int_data1']]['times'] += $currow['times'];
            $alldata[$date][$currow['int_data1']]['users'] += $currow['users'];

            $sum[$currow['int_data1']]['times'] += $currow['times'];//总和
            $sum[$currow['int_data1']]['users'] += $currow['users'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>---</th><th colspan='2'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>英雄ID</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sum as $key => $item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['times']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';

    } else if ($statType == 3) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data1,sum(int_data2) cost,count(DISTINCT userid) users ,count(1) times from $db_start where category=39 and type=$statType $wheretime $whereSql group by date,int_data1   ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date ,int_data1 desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['int_data1']]['users'] = $currow['users'];
            $alldata[$date][$currow['int_data1']]['times'] = $currow['times'];
            $alldata[$date][$currow['int_data1']]['cost'] = $currow['cost'];

            $sum[$currow['int_data1']]['users'] += $currow['users'];
            $sum[$currow['int_data1']]['times'] += $currow['times'];
            $sum[$currow['int_data1']]['cost'] += $currow['cost'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>---</th><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>英雄ID</th><th>花费</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>花费</th><th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sum as $key => $item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['cost']}</td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['cost']}</td><td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['times']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    } else if ($statType == 5) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data1,sum(int_data3) cost,count(DISTINCT userid) users ,count(1) times from $db_start where category=39 and type=$statType $wheretime $whereSql group by date,int_data1   ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date ,int_data1 desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['int_data1']]['users'] = $currow['users'];
            $alldata[$date][$currow['int_data1']]['times'] = $currow['times'];
            $alldata[$date][$currow['int_data1']]['cost'] = $currow['cost'];

            $sum[$currow['int_data1']]['users'] += $currow['users'];
            $sum[$currow['int_data1']]['times'] += $currow['times'];
            $sum[$currow['int_data1']]['cost'] += $currow['cost'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>---</th><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>英雄ID</th><th>花费</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>花费</th><th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sum as $key => $item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['cost']}</td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['cost']}</td><td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['times']}</td>";
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
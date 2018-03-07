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
$statType_title = array('参与人数', '达到每层人数统计', '购买钥匙数量','不同档位购买次数','离开房间类型及剩余步数(6步以上统一算6)');
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
            $sql_pass = "select date, count(DISTINCT userid) users ,count(1) times from $db_start where category=19 and type=1 $wheretime $whereSql group by date  ";
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
    else if ($statType == 1) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, floor ,count(1) users  from (select date,userid ,max(int_data1) floor from $db_start where category=19 and type=2 $wheretime $whereSql group by date ,userid) AA group by date ,floor  ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date ,floor desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['floor']] += $currow['users'];

            $sum[$currow['floor']] += $currow['users'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>层</th><th>人数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>层</th><th>人数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        ksort($sum);
        foreach ($sum as $key=>$item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$item}</td>";
            foreach ($datearr as $date) {
                $showvalue = $alldata[$date][$key]>0?$alldata[$date][$key]:0;
                $htmltmp .= "<td>{$key}</td><td>{$showvalue}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }
    else if($statType == 2){
        //购买钥匙数量
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, count(DISTINCT userid) users ,count(1) times from $db_start where category=19 and type=3 $wheretime $whereSql group by date  ";
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
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>-</th><th>购买数量</th><th>购买人数</th></tr></thead>";

        $html .="<tr><td>合计</td><td>{$sum['times']}</td><td>{$sum['users']}</td></tr>";
        foreach ($datearr as $date) {
            $html .= "<tr><td>{$date}</td><td>{$alldata[$date]['times']}</td><td>{$alldata[$date]['users']}</td></tr>";
        }

        $html .= '</table></div>';
    }
    else if ($statType == 3) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data2,count(DISTINCT userid) users ,count(1) times from $db_start where category=19 and type=4 $wheretime $whereSql group by date,int_data2   ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date ,int_data2 desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['int_data2']]['users'] = $currow['users'];
            $alldata[$date][$currow['int_data2']]['times'] = $currow['times'];

            $sum[$currow['int_data2']]['users'] += $currow['users'];
            $sum[$currow['int_data2']]['times'] += $currow['times'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>档位(金币)</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>档位(金币)</th><th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sum as $key=>$item) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$key}</td><td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['times']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }
    else if ($statType == 4) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,int_data1 ,case when int_data2<=5 then int_data2 when int_data2>6 then 6 end as step,count(1) times from $db_start where category=19 and type=5 $wheretime $whereSql group by date ,int_data1 ,step  ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date,int_data1,step desc ";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }$alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $alldata[$date][$currow['int_data1']][$currow['step']] = $currow['times'];

            $sum[$currow['int_data1']][$currow['step']] += $currow['times'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>房间类型</th><th>剩余步数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>房间类型</th><th>剩余步数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sum as $key=>$item) {
            ksort($item);
            foreach($item as $key1=>$value1) {

                $htmltmp = '';
                $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$key1}</td><td>{$value1}</td>";
                foreach ($datearr as $date) {
                    $htmltmp .= "<td>{$key}</td><td>{$key1}</td><td>{$alldata[$date][$key][$key1]}</td>";
                }
                $htmltmp .= "</tr>";
                $html .= $htmltmp;
            }
        }
        $html .= '</tbody></table></div>';
    }
    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>
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

$statType_title = array('全部刷新数据', '解锁栏位', '提炼栏位', '收取', '好友解锁', '刷新某个栏位');
foreach ($statType_title as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}

if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
    $end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);
    $table_start = date('Ym', strtotime($start));
    $table_end = date('Ym', strtotime($end));
    $monthArr = monthList(strtotime($start), strtotime($end));

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
            $sql_pass = "select date,case when int_data2=0 then 0
when int_data2>0 then 1 end as freetype,sum(int_data2) costmoney,count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=1 $wheretime $whereSql group by date,freetype ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $type = $currow['freetype'];
            if ($type == 0) {
                $alldata[$date][0]['cnt'] += $currow['cnt'];//次数
                $alldata[$date][0]['usercnt'] += $currow['usercnt'];//人数

            } elseif ($type == 1) {//付费
                $alldata[$date][1]['cnt'] += $currow['cnt'];//次数
                $alldata[$date][1]['usercnt'] += $currow['usercnt'];//人数
                $alldata[$date][1]['costmoney'] += $currow['costmoney'];
            }
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        //副标题
        $html .= "<tr><th>DATE</th><th>免费人数</th><th>免费次数</th><th>花钱人数</th><th>花钱次数</th><th>金币花费</th>";

        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($datearr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">
			<td>{$value}</td>
			<td>{$alldata[$value][0]['usercnt']}</td>
			<td>{$alldata[$value][0]['cnt']}</td>
			<td>{$alldata[$value][1]['usercnt']}</td>
			<td>{$alldata[$value][1]['cnt']}</td>
			<td>{$alldata[$value][1]['costmoney']}</td>";
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    } elseif ($statType == 1) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,var_data1,sum(int_data1) costmoney,count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=2 $wheretime $whereSql group by date,var_data1 ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $itemArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $item = $currow['var_data1'];//解锁栏位类型
            $itemArr[$item] = $item;
            $datearr[$date] = $date;

            $alldata[$date][$item]['cnt'] += $currow['cnt'];//次数
            $alldata[$date][$item]['usercnt'] += $currow['usercnt'];//人数
            $alldata[$date][$item]['costmoney'] += $currow['costmoney'];//花的钱
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        $html .= "<tr><th>DATE</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr>";
        //副标题
        $html .= "<tr><th>栏位ID</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th><th>消耗金币</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($itemArr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">";
            $htmltmp .= "<td>{$value}</td>";

            foreach ($datearr as $date) {
                $htmltmp .= "
				<td>{$alldata[$date][$value]['usercnt']}</td>
				<td>{$alldata[$date][$value]['cnt']}</td>
				<td>{$alldata[$date][$value]['costmoney']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    } elseif ($statType == 2) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,var_data3, count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=3 $wheretime $whereSql group by date,var_data3 ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date,var_data3 desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $itemArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $item = $currow['var_data3'];
            $datearr[$date] = $date;
            $itemArr[$item] = $item;
            $alldata[$date][$item]['cnt'] += $currow['cnt'];//次数
            $alldata[$date][$item]['usercnt'] += $currow['usercnt'];//人数
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        $html .= "<tr><th>DATE</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr>";
        //副标题
        $html .= "<tr><th>栏位ID</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($itemArr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">";
            $htmltmp .= "<td>{$value}</td>";

            foreach ($datearr as $date) {
                $htmltmp .= "
				<td>{$alldata[$date][$value]['usercnt']}</td>
				<td>{$alldata[$date][$value]['cnt']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    } elseif ($statType == 3) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,var_data3, count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=4 $wheretime $whereSql group by date,var_data3 ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date,var_data3 desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $itemArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $item = $currow['var_data3'];
            $datearr[$date] = $date;
            $itemArr[$item] = $item;
            $alldata[$date][$item]['cnt'] += $currow['cnt'];//次数
            $alldata[$date][$item]['usercnt'] += $currow['usercnt'];//人数
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        $html .= "<tr><th>DATE</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr>";
        //副标题
        $html .= "<tr><th>栏位ID</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($itemArr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">";
            $htmltmp .= "<td>{$value}</td>";

            foreach ($datearr as $date) {
                $htmltmp .= "
				<td>{$alldata[$date][$value]['usercnt']}</td>
				<td>{$alldata[$date][$value]['cnt']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    } elseif ($statType == 4) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=5 $wheretime $whereSql group by date ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $alldata[$date]['cnt'] += $currow['cnt'];//次数
            $alldata[$date]['usercnt'] += $currow['usercnt'];//人数

        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        //副标题
        $html .= "<tr><th>DATE</th><th>人数</th><th>次数</th>";

        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($datearr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">
			<td>{$value}</td>
			<td>{$alldata[$value]['usercnt']}</td>
			<td>{$alldata[$value]['cnt']}</td>";
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }
    if ($statType == 5) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,case when int_data1=0 then 0
when int_data1>0 then 1 end as freetype,sum(int_data1) costmoney,count(DISTINCT  userid) usercnt,count(1) cnt from $db_start where category=33 and type=6 $wheretime $whereSql group by date,freetype ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'], $privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $timesArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $type = $currow['freetype'];
            if ($type == 0) {
                $alldata[$date][0]['cnt'] += $currow['cnt'];//次数
                $alldata[$date][0]['usercnt'] += $currow['usercnt'];//人数

            } elseif ($type == 1) {//付费
                $alldata[$date][1]['cnt'] += $currow['cnt'];//次数
                $alldata[$date][1]['usercnt'] += $currow['usercnt'];//人数
                $alldata[$date][1]['costmoney'] += $currow['costmoney'];
            }
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";

        //副标题
        $html .= "<tr><th>DATE</th><th>免费人数</th><th>免费次数</th><th>花钱人数</th><th>花钱次数</th><th>金币花费</th>";

        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($datearr as $value) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">
			<td>{$value}</td>
			<td>{$alldata[$value][0]['usercnt']}</td>
			<td>{$alldata[$value][0]['cnt']}</td>
			<td>{$alldata[$value][1]['usercnt']}</td>
			<td>{$alldata[$value][1]['cnt']}</td>
			<td>{$alldata[$value][1]['costmoney']}</td>";
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
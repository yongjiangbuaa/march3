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

$statType_title = array('参与人数');
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
    if ($statType == 0) {//分服展示
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select server_id,date, int_data1 , count(DISTINCT userid) users ,count(1) times from $db_start where category=44 and type=1 $wheretime $whereSql group by server_id,date ,int_data1 ";
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
        $alldata = $datearr = $sum = $sidArr = $indexArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $sid = $currow['server_id'];
            $index = $currow['int_data1'];

            $sidArr[$sid] = $sid;
            $indexArr[$index] = $index;//档位

            $alldata[$sid][$index][$date]['times'] += $currow['times'];
            $alldata[$sid][$index][$date]['users'] += $currow['users'];

            $sum[$sid][$index]['times'] += $currow['times'];//总和
            $sum[$sid][$index]['users'] += $currow['users'];
        }

        //--------------------------------

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>sid</th><th>档位</th><th colspan='2'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>--</th><th>---</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($sidArr as $server) {
            foreach($indexArr as $key) {
                $htmltmp = '';
                $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$server}</font></td><td>{$key}</td><td>{$sum[$server][$key]['users']}</td><td>{$sum[$server][$key]['times']}</td>";
                foreach ($datearr as $date) {
                    $showtimes = $alldata[$server][$key][$date]['times'] > 0 ? $alldata[$server][$key][$date]['times'] : 0;
                    $showvalue = $alldata[$server][$key][$date]['users'] > 0 ? $alldata[$server][$key][$date]['users'] : 0;
                    $htmltmp .= "<td>{$showvalue}</td><td>{$showtimes}</td>";
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
<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (!$_REQUEST['start'])
    $start = date("Y-m-d 00:00", time() - 86400 * 6);
if (!$_REQUEST['end'])
    $end = date("Y-m-d 23:59", time());
if ($_REQUEST['analyze'] == 'user') {
    if (empty($_REQUEST['user'])) {
        echo '<div><font color="red">请输入用户uid</font></div>';
        exit();
    } else {
        $user = $_REQUEST['user'];
        $sql = "select name from userprofile where uid='$user'";
        $result = $page->execute($sql, 3);
        $name = $result['ret']['data'][0]['name'];
    }

    $start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
    $end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);
    $monthArr = monthList(strtotime($start),strtotime($end));

    $sids = substr($currentServer,1);

    $whereSql = " and server_id in ($sids) and userid='$user' ";

    $wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "select timeStamp,userid,int_data1 as originalVipScore,int_data2 as modscore,int_data3 as remainVipScore from $db_start where category=55 and type=1 $wheretime $whereSql ";
        if (isset($sql_sum)) {
            $sql_sum = $sql_sum . " union all " . $sql_pass;
        } else {
            $sql_sum = $sql_pass;
        }
    }
    $sql_sum .= "order by timeStamp desc";

    $result_pass = query_infobright($sql_sum);
    if (in_array($_COOKIE['u'], $privilegeArr)) {
        $html .= $sql_sum;
    }
    $log = array();
	foreach ($result_pass['ret']['data'] as $curRow) {
        $data = $curRow;
        $logItem['时间'] = date('Y-m-d H:i:s', $data['timeStamp'] / 1000);
        $logItem['用户UID'] = $data['userid'];
        $logItem['用户'] = $name;
        $logItem['变化前'] = $data['originalVipScore'];
        $logItem['变化值'] = $data['modscore'];
        $logItem['变化后'] = $data['remainVipScore'];
        $log[] = $logItem;
    }
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	foreach ($log as $sqlData) {
        if (!$title) {
            $html .= "<tr class='listTr'><th>编号</th>";
            foreach ($sqlData as $key => $value)
                $html .= "<th>" . $key . "</th>";
            $html .= "</tr>";
            $title = true;
        }
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>$i</td>";
        $i++;
        foreach ($sqlData as $key => $value) {
            $html .= "<td>" . $value . "</td>";
        }
        $html .= "</tr>";
    }
	$html .= "</table></div><br/>";
	if ($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>
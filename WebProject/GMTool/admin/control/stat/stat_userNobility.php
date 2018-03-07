<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time()+86400);
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$battleType = array('功勋值获取与消耗玩家数量分布','每天拜师统计');
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}
$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

$monthArr = monthList(strtotime($start),strtotime($end));
$sids = implode(',', $selectServerids);
$whereSql=" and server_id in ($sids) ";

if ($_REQUEST ['analyze'] == 'platform'&&$_REQUEST['battleType']==0) {


    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";

    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "
                 select userid,date ,sum(int_data1) effort from $db_start l WHERE  $time and  category=4 and type=3 $whereSql group by date,userid
             ";
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_pass ;
        }else{
            $sql_sum = $sql_pass;
        }
    }
    $result_pass =query_infobright($sql_sum);
    $result_effort = $result_pass['ret']['data'];
    $res_array = array();
    foreach ($result_effort as $curRow) {
        $date = $curRow['date'];
        if ($date == null) {
            continue;
        }
        $effort = $curRow['effort'];
        if (!isset($effort_user[$date])) {
            for ($i = 0; $i <= 4; $i++) {
                $effort_user[$date][$i] = 0;
            }
        }
        if ($effort < 1000) {
            $effort_user[$date][0] += 1;
        }
        if ($effort >= 1000 && $effort <= 1999) {
            $effort_user[$date][1] += 1;
        }
        if ($effort >= 2000 && $effort <= 4999) {
            $effort_user[$date][2] += 1;
        }
        if ($effort >= 5000 && $effort <= 7499) {
            $effort_user[$date][3] += 1;
        }
        if ($effort >= 7500) {
            $effort_user[$date][4] += 1;
        }
    }
    $title = array(
        '时间',
        '获取<1000',
        '获取：[1000~1999]',
        '获取：[2000~4999]',
        '获取：[5000~7499]',
        '获取>=7500',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach ($effort_user as $_key => $_value) {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" . $_key . "</td>";
        foreach ($_value as $count_user) {
            $html .= "<td>" . ($count_user == null ? 0 : $count_user) . "人</td>";
        }
//        $html .= "<td>" .($_value[1]==null?0:$_value[1]) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";

    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "
                 select userid,date ,sum(int_data1) expend from $db_start l WHERE $time and   category=4 and type=4 $whereSql group by date,userid
              ";
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_pass ;
        }else{
            $sql_sum = $sql_pass;
        }
    }
    $result_expend =query_infobright($sql_sum);
    $result_expend = $result_expend['ret']['data'];
    $res_array = array();
    foreach ($result_expend as $curRow) {
        $date = $curRow['date'];
        if ($date == null) {
            continue;
        }
        $expend = $curRow['expend'];
        if (!isset($expend_user[$date])) {
            for ($i = 0; $i <= 4; $i++) {
                $expend_user[$date][$i] = 0;
            }
        }
        if ($expend < 1000) {
            $expend_user[$date][0] += 1;
        }
        if ($expend >= 1000 && $expend <= 4999) {
            $expend_user[$date][1] += 1;
        }
        if ($expend >= 5000 && $expend <= 9999) {
            $expend_user[$date][2] += 1;
        }
        if ($expend >= 10000 && $expend <= 14499) {
            $expend_user[$date][3] += 1;
        }
        if ($expend >= 15500) {
            $expend_user[$date][4] += 1;
        }
    }
    $_title = array(
        '时间',
        '消耗<1000',
        '消耗：[1000~4999]',
        '消耗：[5000~9999]',
        '消耗：[10000~14499]',
        '消耗>=15500',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($_title as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach ($expend_user as $_key => $_value) {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" . $_key . "</td>";
        foreach ($_value as $count_user) {
            $html .= "<td>" . ($count_user == null ? 0 : $count_user) . "人</td>";
        }
//        $html .= "<td>" .($_value[1]==null?0:$_value[1]) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    echo $html;
    exit();
}

if ($_REQUEST ['analyze'] == 'platform'&&$_REQUEST['battleType']==1) {
    $start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($start)*1000;
    $end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
    $time = "timeStamp>=$start and timeStamp<=$end ";

    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "
                 select date,  count(type)  teacher from $db_start  WHERE $time and
            category=4 and type=1 $whereSql group by date
              ";
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_pass ;
        }else{
            $sql_sum = $sql_pass;
        }
    }
    $result_user =query_infobright($sql_sum);
    $result_user = $result_user['ret']['data'];
    $res_array = array();
    foreach ($result_user as $curRow) {
        $date = $curRow['date'];
        if($date == null){
            continue;
        }
//        $type = $curRow['type'];
        $res_array[$date]=$curRow['teacher']!=null?$curRow['teacher']:0;
        }
    $title = array(
        '时间',
        '服务器每日拜师的玩家数',
    );
    $html  = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($res_array as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$_key . "</td>";
        $html .= "<td>" .$_value . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";

    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
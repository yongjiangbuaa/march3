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

$battleType = array('按资源统计','按点击祭坛次数统计','按天统计');
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}
if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $start_stamp = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($start)*1000;
    $end_stamp = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
    $monthArr = monthList($start_stamp/1000,$end_stamp/1000);
    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";

    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    if ($_REQUEST['battleType'] == 0) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select count(distinct l.userid) user from $db_start l WHERE $time and category=11 and type=1 $whereSql";

            if(isset($sql_sum)){
                $sql_sum = $sql_sum . " union " . $sql_pass ;
            }else{
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum =query_infobright($sql_sum);
        $sum_people = $sql_sum['ret']['data'][0]['user'];

        $sql_sum = false;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_user = "select l.date,l.int_data1 type,count(*) click,sum(int_data2) resource, count(distinct l.userid) user from $db_start l
                WHERE $time and category=11 and type=1 $whereSql group by date, l.int_data1 ";
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql_user ;
            }else{
                $sql_sum = $sql_user;
            }
        }
        $result_user =query_infobright($sql_sum);
        $result_user = $result_user['ret']['data'];
//        $html .= json_encode($result_user);
        $html = "<h3>祭坛使用人数：" . $sum_people . " </h3>";
        $title = array(
            '日期',
            '资源类型',
            '点击平均次数',
            '系统放出资源',
            '使用人数',
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach ($result_user as $_key => $_value) {
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td> " . $_value['date'] . "</td>";
            switch ($_value['type']){
                case '0':
                    $html .= "<td>木头</td>";
                    break;
                case '1':
                    $html .= "<td>秘银</td>";
                    break;
                case '2':
                    $html .= "<td>铁矿</td>";
                    break;
                case '3':
                    $html .= "<td>粮食</td>";
                    break;
            }
//            $html .= "<td>" . $_value['type'] . "</td>";
            $html .= "<td>" . $_value['click'] / $_value['user'] . " </td>";
            $html .= "<td>" . $_value['resource'] . " </td>";
            $html .= "<td>" . $_value['user'] . " </td>";
        }
        $html .= "</tr>";
        $html .= "</table></div><br/>";
    }

    if ($_REQUEST['battleType'] == 1) {
        $html = "<h3>若不单个服查询，0次=总人数-使用祭坛人数，则0次不准确</h3>";
        $sql ="select count(distinct ub.uid) as total from userprofile u  inner join user_building ub  on u.uid =ub.uid
        where  ub.itemid=400000 and ub.level>=8 and u.lastOnlineTime>=$start_stamp and u.lastOnlineTime<=$end_stamp;";
        $result =  $page->execute($sql,3);
        $result = $result['ret']['data'];

        $sql_sum = false;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_num = "select date, count(l.userid) num from $db_start l WHERE  $time and category=11 and type=1  $whereSql group by date,userid  ";
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql_num ;
            }else{
                $sql_sum = $sql_num;
            }
        }
        $result_num =query_infobright($sql_sum);
        $result_num = $result_num['ret']['data'];
        $res_array = array();
        foreach ($result_num as $curRow) {
            $date = $curRow['date'];
            if($date == null){
                continue;
            }
            $num = $curRow['num'];
            if(!isset($num_user[$date])){
                for($i=0;$i<=5;$i++){
                    $num_user[$date][$i] =0;
                }
            }
            if($num<=6){
                $num_user[$date][0] +=1;
            }
            if($num>=7&&$num<=15){
                $num_user[$date][1] +=1;
            }
            if($num>=16&&$num<=30){
                $num_user[$date][2] +=1;
            }
            if($num>=31&&$num<=50){
                $num_user[$date][3] +=1;
            }
            if($num>50){
                $num_user[$date][4] +=1;
            }
            $num_user[$date][5] +=1;
//            foreach($result as $total){
//                if($total['date']==$date){
//                    $num_user[$date][5]$total['dau']
//                }
//            }
        }
        $title = array(
            '时间',
            '1~6次',
            '7~15次',
            '16~30次',
            '31~50次',
            '50+次',
            '0次',
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title as $key=>$value){
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach($num_user as $_key=>$_value){
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td>" .$_key . "</td>";
            $html .= "<td>" .($_value[0]==null?0:$_value[0]) . "人</td>";
            $html .= "<td>" .($_value[1]==null?0:$_value[1]). "人</td>";
            $html .= "<td>" .($_value[2]==null?0:$_value[2]) . "人</td>";
            $html .= "<td>" .($_value[3]==null?0:$_value[3]) . "人</td>";
            $html .= "<td>" .($_value[4]==null?0:$_value[4]) . "人</td>";
            $html .= "<td>" .($result[0]['total']-$_value[5]) . "人</td>";
//            foreach($_value as $count_user){
//                $html .= "<td>" .($count_user==null?0:$count_user) . "人</td>";
//            }
            $html .= "</tr>";
        }
        $html .= "</table></div><br/>";
    }
    if ($_REQUEST['battleType'] == 2) {
        $sql_sum = false;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_num = "select date, type, count(*) num,count(distinct l.userid) user from $db_start l WHERE $time and category=11  $whereSql group by date,type ";

            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql_num ;
            }else{
                $sql_sum = $sql_num;
            }
        }
        $result_sum =query_infobright($sql_sum);
        $result_sum = $result_sum['ret']['data'];
//        $html .= json_encode($result_user);
        foreach($result_sum as $num){
            $date = $num['date'];
            $type = $num['type'];
            if(!isset($arr_day[$date])){
                for($i=1;$i<=2;$i++){
                    $arr_day[$date][$i] =0;
                }
            }
            if($type==1){
                $arr_day[$date][1] = $num['user'];
            }
            if($type==2){
                $arr_day[$date][2] = $num['num']/$num['user'];
            }
//else{
//                $arr_day[$date][$type]=0;
//            }
        }
//        $html .=json_encode($result_sum);
            $title = array(
            '日期',
            '祭坛使用人数',
            '使用祝福平均次数',
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach ($arr_day as $_key => $_value) {
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td> " .$_key . "</td>";
            $html .= "<td>" . $_value[1] . "</td>";
            $html .= "<td>" . $_value[2] . " </td>";
        }
        $html .= "</tr>";
        $html .= "</table></div><br/>";
    }
    if ($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
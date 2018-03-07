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

if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    $monthArr = monthList(strtotime($start),strtotime($end));
    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";

    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "select date, count(userid) sum_user from $db_start l where $time $whereSql and  category=8  ";

        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_pass ;
        }else{
            $sql_sum = $sql_pass;
        }
    }
    $result_sum =query_infobright($sql_sum);
    $result_sum = $result_sum['ret']['data'];

    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "select date ,count(userid) use_user from $db_start where $time $whereSql and category=7 ;";
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_pass ;
        }else{
            $sql_sum = $sql_pass;
        }
    }
    $result_use =query_infobright($sql_sum);
    $result_use = $result_use['ret']['data'];
    foreach($result_sum as $users){
        $date = $users['date'];
        foreach($result_use as $user){
            if($date==$user['date']){
                $_user = $user['use_user'];
            }
        }
        $sum_user = $users['sum_user'];
        $percent = round($_user/$sum_user,6);
        $change_percent[$date]=$percent*100;
    }
    $html .="<th>使用邀请码/新用户</th>";
    $html .="</br>";
    $html .= "<div style='float:left;width:90%;height:100px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($change_percent as $key => $value) {
            $html .= "<th>" . $key . ": " . $value . "%</th>";
        }
        $html .= "</tr>";
        $html .= "</table></div><br/>";

    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_user = "select int_data2 , count(var_data1) count_user from $db_start where  $time $whereSql and category=7 ;";

        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_user ;
        }else{
            $sql_sum = $sql_user;
        }
    }
    $result_user =query_infobright($sql_sum);
    $result_user = $result_user['ret']['data'];
    foreach($result_user as $post){
        $count_lv= $post['int_data2'];
        $count_user =$post['count_user'];
        if(!isset($post_distribute[$count_lv])){
//            $post_distribute[$count_lv]['zero'] =0;
            $post_distribute[$count_lv]['one'] =0;
            $post_distribute[$count_lv]['two'] =0;
            $post_distribute[$count_lv]['three'] =0;
            $post_distribute[$count_lv]['four'] =0;
            $post_distribute[$count_lv]['five'] =0;
        }
//        if ($count_user ==0) {
//            $post_distribute[$count_lv]['zero'] += 1;
//        }
        if ($count_user >= 1 && $count_user <= 10) {
            $post_distribute[$count_lv]['one'] += 1;
        }
        if ($count_user >= 11 && $count_user <= 20) {
            $post_distribute[$count_lv]['two'] += 1;
        }
        if ($count_user >= 21 && $count_user <= 30) {
            $post_distribute[$count_lv]['three'] += 1;
        }
        if ($count_user >= 31 && $count_user <= 40) {
            $post_distribute[$count_lv]['four'] += 1;
        }
        if ($count_user >= 41 && $count_user <= 50) {
            $post_distribute[$count_lv]['five'] += 1;
        }
    }
    $title = array(
//        'zero'=>'0次',
        'one'=>'1~10次',
        'two'=>'11~20次',
        'three'=>'21~30次',
        'four'=>'31~40次',
        'five'=>'41~50次',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    $html .="<th>大本等级</th>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($post_distribute as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $_key = $_key!=null?$_key:0;
        $html .= "<td>" .$_key . "级</td>";
        foreach($_value as $post_num=>$count_user) {
            foreach ($title as $num => $users) {
                if ($post_num==$num) {
                    $html .= "<td>".$count_user."人</td>";
                }
            }
        }
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

<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*5);
$end = date('Y-m-d',time());
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];
$battleType = array('按关卡统计','每天统计','第一次参活动的玩家城堡等级分布','不同金币领取次数的玩家分布','不同气力消耗的玩家次数分布','点杀泰坦的留存');
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}
//$where = "timeStamp<=".strtotime($start)*1000;
//$deleteSql="delete from log_rbi where category=3 and $where;";
//$deleteResult=$page->execute($deleteSql, 2);
if ($_REQUEST ['analyze'] == 'platform'){
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $start_stamp = $_REQUEST['start'] ? strtotime($_REQUEST['start']) * 1000 : strtotime($start) * 1000;
    $end_stamp = $_REQUEST['end'] ? strtotime($_REQUEST['end']) * 1000 : strtotime($end) * 1000;
    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    $monthArr = monthList($start_stamp/1000,$end_stamp/1000);
    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";


    if ($_REQUEST['battleType']==0) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select * from (
             select userid,max(int_data1) pass,max(int_data2) level,max(int_data3) friends from $db_start where $time and category=3 and type=1 $whereSql group by userid
             ) info ";
            $result=query_infobright($sql_pass);
            if($result['ret']['data'][0]['userid']==null){
                continue;
            }
            if(isset($sql_sum)){
                $sql_sum .= " union " . $sql_pass ;
            }else{
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .=" order by info.pass;";
        $result_pass =query_infobright($sql_sum);
        $result_pass = $result_pass['ret']['data'];
    foreach ($result_pass as $arr_count){
        $pass = $arr_count['pass'];
        $level = $arr_count['level'];
        $userid = $arr_count['userid'];
        $friends = $arr_count['friends'];
        $bulevel_pass[$pass][0] +=1;
        $bulevel_pass[$pass][1][$level] +=1;
        if($friends == null || $friends == 0){
            $friends = 'none';
        }
        $bulevel_pass[$pass][2][$friends] +=1;
    }
//    $html = json_encode($bulevel_pass);
    $title = array(
        '关卡数',
        '停留的玩家数量',
        '停留玩家的城堡等级分布',
        '停留玩家的好友数量分布'
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($bulevel_pass as $_key=>$_value){
        ksort($_value[1]);
        ksort($_value[2]);
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td> 第".$_key."关: </td>";
        $html .="<td>" .$_value[0]."人 </td>";
        $html .="<td>".($_value[1]==null? 0:json_encode($_value[1]))."</td>";
        $html .="<td>".($_value[2]==null? 0:str_replace('none','0',json_encode($_value[2])))."</td>";
    }
        $html .= "</tr>";
    $html .= "</table></div><br/>";
        $sql_sum=false;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_new = "select * from (select userid,max(int_data1)  pass,max(int_data2) level,max(int_data3) friends
	             from $db_start
	             where  category=3 and type=1 and $time and var_data1>=$start_stamp and var_data1<=$end_stamp $whereSql
                   group by userid
	             ) info  ";
            $result=query_infobright($sql_new);
            if($result['ret']['data'][0]['userid']==null){
                continue;
            }
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql_new ;
            }else{
                $sql_sum = $sql_new;
            }
        }

        $sql_sum .=" order by info.pass;";
        $result_new = query_infobright($sql_sum);
//        $html .=json_encode($result_new);
        $result_new = $result_new['ret']['data'];
        foreach ($result_new as $arr_new){
            $new_pass = $arr_new['pass'];
            $new_level = $arr_new['level'];
            $new_userid = $arr_new['userid'];
            $new_friends = $arr_new['friends'];
            $bulevel_new[$new_pass][0] +=1;
            $bulevel_new[$new_pass][1][$new_level] +=1;
            if($new_friends == null || $new_friends == 0){
                $new_friends = 'none';
            }
            $bulevel_new[$new_pass][2][$new_friends] +=1;
        }


        $title_new = array(
            '关卡数',
            '新用户停留的玩家数量',
            '新用户停留玩家的城堡等级分布',
            '新用户停留玩家的好友数量分布'
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title_new as $key=>$value){
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach($bulevel_new as $_key=>$_value){
            ksort($_value[1]);
            ksort($_value[2]);
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td> 第".$_key."关: </td>";
            $html .="<td>" .$_value[0]."人 </td>";
            $html .="<td>".($_value[1]==null? 0:json_encode($_value[1]))."</td>";
            $html .="<td>".($_value[2]==null? 0:str_replace('none','0',json_encode($_value[2])))."</td>";
        }
        $html .= "</tr>";
        $html .= "</table></div><br/>";
}

if ($_REQUEST['battleType']==1) {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_user = "select date,  count(distinct l.userid) throw_user from $db_start l WHERE $time and
            category=3 and type=1 $whereSql group by date; ";
        $result=query_infobright($sql_user);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_user ;
        }else{
            $sql_sum = $sql_user;
        }
    }
    $result_user = query_infobright($sql_sum);
    $result_user = $result_user['ret']['data'];
    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_gold = "select date , sum(int_data2) gold  from $db_start l where $time and category=3 and type=3 $whereSql group by date; ";
        $result=query_infobright($sql_gold);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_gold ;
        }else{
            $sql_sum = $sql_gold;
        }
    }
    $result_gold = query_infobright($sql_sum);
    $result_gold = $result_gold['ret']['data'];
    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $new_user = "select date,  count(distinct l.userid) throw_user from $db_start l WHERE
            category=3 and type=1 and $time and var_data1>=$start_stamp and var_data1<=$end_stamp
            $whereSql group by date; ";
        $result=query_infobright($new_user);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $new_user ;
        }else{
            $sql_sum = $new_user;
        }
    }
    $result_new_user = query_infobright($sql_sum);

    $result_new_user = $result_new_user['ret']['data'];
    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $new_gold = "select date , sum(int_data2) gold  from $db_start l where  category=3 and type=3 and $time and var_data1>=$start_stamp and var_data1<=$end_stamp $whereSql group by date; ";
        $result=query_infobright($new_gold);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $new_gold ;
        }else{
            $sql_sum = $new_gold;
        }
    }
    $result_new_gold = query_infobright($sql_sum);
    $result_new_gold = $result_new_gold['ret']['data'];
    $res_array = array();
    foreach ($result_user as $curRow) {
        $date = $curRow['date'];
        if($date == null){
            continue;
        }
//        $type = $curRow['type'];
        $res_array[$date][0]=$curRow['throw_user']!=null?$curRow['throw_user']:0;
        foreach($result_gold as $sum_gold){
            $date1 = $sum_gold['date'];
            if($date1==$date){
                $res_array[$date][1]=$sum_gold['gold']!=null?$sum_gold['gold']:0;
            }
        }
        foreach($result_new_user as $sum_new_user){
            $date2 = $sum_new_user['date'];
            if($date2==$date){
                $res_array[$date][2]=$sum_new_user['throw_user']!=null?$sum_new_user['throw_user']:0;
            }
        }
        foreach($result_new_gold as $sum_new_gold){
            $date3 = $sum_new_gold['date'];
            if($date3==$date){
                $res_array[$date][3]=$sum_new_gold['gold']!=null?$sum_new_gold['gold']:0;
            }
        }
    }

    $title = array(
        '时间',
        '服务器每日参与过此活动的玩家数',
        '服务器每日通过此系统放出的金币量',
        '新用户中参与过此活动的玩家数',
        '新用户每日通过此系统获得的金币量',
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
        $html .= "<td>" .$_value[0] . "</td>";
        $html .= "<td>" .($_value[1]==null?0:$_value[1]) . "</td>";
        $html .= "<td>" .($_value[2]==null?0:$_value[2]) . "</td>";
        $html .= "<td>" .($_value[3]==null?0:$_value[3]) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
}


if ($_REQUEST['battleType']==2) {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql = "select int_data1 ,date, userid from $db_start l WHERE $time and
              category=3 and type=2 $whereSql group by date,userid";
        $result=query_infobright($sql);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql ;
        }else{
            $sql_sum = $sql;
        }
    }
    $result_blv = query_infobright($sql_sum);

    $result_blv = $result_blv['ret']['data'];
    foreach ($result_blv as $blv) {
        $bulv = $blv['int_data1'];
        $build_level[$bulv] += 1;
    }
    ksort($build_level);
    $title = array(
        '城堡等级',
        '玩家数量',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($build_level as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$_key . "级</td>";
        $html .= "<td>" .$_value . "人</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
}

if ($_REQUEST['battleType']==3) {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_user = "select count(distinct l.userid) user from $db_start l WHERE  $time and category=3 $whereSql ; ";//参与过活动的玩家数
        $result=query_infobright($sql_user);
        if($result['ret']['data'][0]['user']==null){
            continue;
        }
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_user ;
        }else{
            $sql_sum = $sql_user;
        }
    }
    $result_user = query_infobright($sql_sum);

    $result_user = $result_user['ret']['data'][0]['user'];
    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_gold = "select date, count(l.userid) gold_count from $db_start l WHERE $time and  category=3 and type=3 $whereSql group by date,userid ; ";
        $result=query_infobright($sql_gold);
        if($result['ret']['data'][0]['gold_count']==null){
            continue;
        }
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_gold ;
        }else{
            $sql_sum = $sql_gold;
        }
    }
    $result_gold = query_infobright($sql_sum);

    $result_gold = $result_gold['ret']['data'];
    $res_array = array();
    foreach ($result_gold as $curRow) {
        $date = $curRow['date'];
        if($date == null){
            continue;
        }
        $get_gold = $curRow['gold_count'];
        if(!isset($get_sum_user[$date])){
            for($i=1;$i<=10;$i++){
                $get_sum_user[$date][$i] =0;
            }
        }
        if($get_gold>=10){
            $get_sum_user[$date][10] +=1;
        }else{
            $get_sum_user[$date][$get_gold] +=1;
        }
    }
    foreach($get_sum_user as $_date=>$sum_link){
        $sum_user=0;
        foreach($sum_link as $con_user){
            $sum_user +=$con_user;
        }
        $get_sum_user[$_date][11]=$result_user-$sum_user;
    }
    $title = array(
        '时间',
        '1次',
        '2次',
        '3次',
        '4次',
        '5次',
        '6次',
        '7次',
        '8次',
        '9次',
        '大于等于10次',
        '0次'
    );
    $html = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($get_sum_user as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$_key . "</td>";
        foreach($_value as $count_gold=>$count_user){
            $html .= "<td>" .$count_user . "人</td>";
        }
//        $html .= "<td>" .($_value[1]==null?0:$_value[1]) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
}

if ($_REQUEST ['analyze'] == 'platform'&&$_REQUEST['battleType']==4) {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_effort = "select date ,sum(int_data1) effort from $db_start l WHERE $time and  category=3 and type=4 $whereSql group by date,userid ; ";
        $result=query_infobright($sql_effort);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_effort ;
        }else{
            $sql_sum = $sql_effort;
        }
    }
    $result_effort = query_infobright($sql_sum);

    $result_effort = $result_effort['ret']['data'];
    $res_array = array();
    foreach ($result_effort as $curRow) {
        $date = $curRow['date'];
        if($date == null){
            continue;
        }
       $effort = $curRow['effort'];
        if(!isset($effort_user[$date])){
            for($i=0;$i<=6;$i++){
                $effort_user[$date][$i] =0;
            }
        }
        if($effort<3600){
            $effort_user[$date][0] +=1;
        }
        if($effort>=3600&&$effort<=17999){
            $effort_user[$date][1] +=1;
        }
        if($effort>=18000&&$effort<=25199){
            $effort_user[$date][2] +=1;
        }
        if($effort>=25200&&$effort<=46799){
            $effort_user[$date][3] +=1;
        }
        if($effort>=46800&&$effort<=64799){
            $effort_user[$date][4] +=1;
        }
        if($effort>=64800&&$effort<=86399){
            $effort_user[$date][5] +=1;
        }
        if($effort>=86400){
            $effort_user[$date][6] +=1;
        }
    }
    $title = array(
        '时间',
        '气力<3600',
        '[17999~3600]',
        '[25199~18000]',
        '[46799~25200]',
        '[64799~46800]',
        '[86399~64800]',
        '气力＞86400',
    );
    $html = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($effort_user as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$_key . "</td>";
        foreach($_value as $count_user){
            $html .= "<td>" .($count_user==null?0:$count_user) . "人</td>";
        }
//        $html .= "<td>" .($_value[1]==null?0:$_value[1]) . "</td>";
        $html .= "</tr>";
        }
        $html .= "</table></div><br/>";
    }
    if($_REQUEST['battleType']==5){
        $total_start = date('Ymd',strtotime($start));
        $total_end = date('Ymd',strtotime($end));
        $sql = "select * from stat_allserver.stat_retention_kill_titan where sid in ($sids) and date>=$total_start and date<=$total_end;";
        $result= query_infobright($sql);
        $result = $result['ret']['data'];
        $title = array(
            '服',
            '时间',
            '参与过此活动的新玩家(包括迁服和重玩)',
            '第1天留存',
            '第2天留存',
            '第3天留存',
            '第4天留存',
            '第5天留存',
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title as $key=>$value){
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach($result as $user_day){
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td>" .$user_day['sid'] . "</td>";
            $html .= "<td>" .$user_day['date'] . "</td>";
            $html .= "<td>" .$user_day['addCount'] . "</td>";
            $html .= "<td>" .round($user_day['r1'] *100/$user_day['addCount'],2) . "%</td>";
            $html .= "<td>" .round($user_day['r2'] *100/$user_day['addCount'],2) . "%</td>";
            $html .= "<td>" .round($user_day['r3'] *100/$user_day['addCount'],2) . "%</td>";
            $html .= "<td>" .round($user_day['r4'] *100/$user_day['addCount'],2) . "%</td>";
            $html .= "<td>" .round($user_day['r5'] *100/$user_day['addCount'],2) . "%</td>";
            $html .= "</tr>";
        }
        $html .= "</table></div><br/>";
    }
    if($pager['pager'])
    $html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
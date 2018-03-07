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

if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $monthArr = monthList(strtotime($start),strtotime($end));

    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";

    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql = "select int_data2 from $db_start l where $time $whereSql and category=5 and type=1 ";
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql ;
        }else{
            $sql_sum = $sql;
        }
    }
    $sql_sum .=" order by l.int_data2 desc;";
    if (in_array($_COOKIE['u'],$privilegeArr)) {
        echo $sql_sum.PHP_EOL;
    }
    $result =query_infobright($sql_sum);
    $result = $result['ret']['data'];
    foreach($result as $bulv){
        $count = $bulv['int_data2'];
        $user[$count] +=1;
    }
    foreach($user as $_value){
        $user_count[0][] = $_value;
    }
    $html .= "<h3>第一次使用好友驻守功能时的玩家城堡等级分布</h3>";
    $html .= "<div style='float:left;width:90%;height:100px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    foreach ($user as $key=>$value){
        $html .= "<th>城堡等级：" . $key . "</th>";
    }
    $html .= "</tr>";
    foreach($user_count as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach($_value as $user_sum){
            $html .="<td>" .$user_sum."人 </td>";
        }
    }
    $html .= "</tr>";
    $html .= "</table></div><br/>";

    $sql_sum = false;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_user = "select * from (select int_data1, int_data3  from $db_start where $time $whereSql and category=5) info ";
        if($sql_sum){
            $sql_sum = $sql_sum . " union " . $sql_user ;
        }else{
            $sql_sum = $sql_user;
        }
    }
    $sql_sum .=" order by info.int_data3;";
    if (in_array($_COOKIE['u'],$privilegeArr)) {
        echo $sql_sum.PHP_EOL;
    }
    $result_user =query_infobright($sql_sum);
    $result_user = $result_user['ret']['data'];
    foreach($result_user as $post){
        $count_post = $post['int_data1'];
        $count_lv = $post['int_data3'];
        if(!isset($post_distribute[$count_lv])){
            $post_distribute[$count_lv]['419000'] =0;
            $post_distribute[$count_lv]['423000'] =0;
            $post_distribute[$count_lv]['424000'] =0;
            $post_distribute[$count_lv]['425000'] =0;
            $post_distribute[$count_lv]['427000'] =0;
        }
        $post_distribute[$count_lv][$count_post] +=1;
    }
    $title = array(
        '419000'=>'侍卫统领',
        '423000'=>'剑术大师',
        '424000'=>'前锋将军',
        '425000'=>'神射手',
        '427000'=>'内政官',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    $html .="<th>等级</th>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($post_distribute as $_key=>$_value){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
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

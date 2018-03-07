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

$battleType = array('按点击次数统计','按资源获取量统计');
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}
if ($_REQUEST ['analyze'] == 'platform'){
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    $monthArr = monthList(strtotime($start),strtotime($end));
    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";
if ($_REQUEST['battleType']==0) {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_hunting = "select date, count(l.userid) debris_count from $db_start l WHERE $time and  category=13 and type=1 $whereSql group by date,userid ";
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql_hunting ;
        }else{
            $sql_sum = $sql_hunting;
        }
    }
    $result_hunting =query_infobright($sql_sum);
    $result_hunting = $result_hunting['ret']['data'];
    $res_array = array();
    foreach ($result_hunting as $curRow) {
        $date = $curRow['date'];
        if($date == null){
            continue;
        }
        $get_debris = $curRow['debris_count'];
        if(!isset($get_sum_user[$date])){
            for($i=1;$i<=3;$i++){
                $get_sum_user[$date][$i] =0;
            }
        }
        if($get_debris>=3){
            $get_sum_user[$date][3] +=1;
        }else{
            $get_sum_user[$date][$get_debris] +=1;
        }
    }
    $title = array(
        '时间',
        '1~5次',
        '6~9次',
        '10次以上',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
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
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
}

    if ($_REQUEST['battleType']==1) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date ,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal from $db_start l WHERE $time
              and  category=13 and type=1 $whereSql group by date,userid  ";
            if(isset($sql_sum)){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
        $result =query_infobright($sql_sum);
        $result = $result['ret']['data'];
        foreach ($result as $curRow) {
            $date = $curRow['date'];
            if($date == null){
                continue;
            }
            $food = $curRow['food'];
            $wood = $curRow['wood'];
            $iron = $curRow['iron'];
            $crystal = $curRow['crystal'];
            if(!isset($resource_user[$date])){
                for($a=0;$a<=3;$a++) {
                    for ($i = 0; $i <= 3; $i++) {
                        $resource_user[$date][$a][$i] = 0;
                    }
                }
            }
            if($food>0&&$food<2000){
                $resource_user[$date][0][0] +=1;
            }
            if($food>=2000&&$food<6000){
                $resource_user[$date][0][1] +=1;
            }
            if($food>=6000&&$food<10000){
                $resource_user[$date][0][2] +=1;
            }
            if($food>=10000){
                $resource_user[$date][0][3] +=1;
            }
            if($wood>0&&$wood<2000){
                $resource_user[$date][1][0] +=1;
            }
            if($wood>=2000&&$wood<6000){
                $resource_user[$date][1][1] +=1;
            }
            if($wood>=6000&&$wood<10000){
                $resource_user[$date][1][2] +=1;
            }
            if($wood>=10000){
                $resource_user[$date][1][3] +=1;
            }
            if($iron>0&&$iron<2000){
                $resource_user[$date][2][0] +=1;
            }
            if($iron>=2000&&$iron<6000){
                $resource_user[$date][2][1] +=1;
            }
            if($iron>=6000&&$iron<10000){
                $resource_user[$date][2][2] +=1;
            }
            if($iron>=10000){
                $resource_user[$date][2][3] +=1;
            }
            if($crystal>0&&$crystal<2000){
                $resource_user[$date][3][0] +=1;
            }
            if($crystal>=2000&&$crystal<6000){
                $resource_user[$date][3][1] +=1;
            }
            if($crystal>=6000&&$crystal<10000){
                $resource_user[$date][3][2] +=1;
            }
            if($crystal>=10000){
                $resource_user[$date][3][3] +=1;
            }
        }
        $title = array(
            '时间',
            '粮食<2000',
            '粮食[2000~6000]',
            '粮食[6000~10000]',
            '粮食>10000',
            '木头<2000',
            '木头[2000~6000]',
            '木头[6000~10000]',
            '木头>10000',
            '铁<2000',
            '铁[2000~6000]',
            '铁[6000~10000]',
            '铁>10000',
            '水晶<2000',
            '水晶[2000~6000]',
            '水晶[6000~10000]',
            '水晶>10000',
        );
        $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($title as $key=>$value){
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach($resource_user as $_key=>$_value){
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99'onMouseOut=this.style.background='#fff'>";
            $html .= "<td>" .$_key . "</td>";
            foreach($_value as $debris_user){
                foreach($debris_user as $count_user) {
                    $html .= "<td>" . ($count_user == null ? 0 : $count_user) . "人</td>";
                }
            }
            $html .= "</tr>";
        }
        $html .= "</table></div><br/>";
    }

    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
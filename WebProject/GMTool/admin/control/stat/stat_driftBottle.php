<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($start)*1000;
    $end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
    $time = "timeStamp>=$start and timeStamp<=$end ";
    $monthArr = monthList($start/1000,$end/1000);

    $sids = implode(',', $selectServerids);
    $whereSql=" and server_id in ($sids) ";

    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql = "select date, type, count(*) throw_count, count(distinct l.userid) throw_user from $db_start l WHERE
             $time and category=1  $whereSql group by date, type; ";
        $result = query_infobright($sql);
        if($result['ret']['data'][0]['date']==null){
            continue;
        }
        $result = $result['ret']['data'];
        if($record==$result){
            continue;
        }
        $record = $result['ret']['data'];
        $res_array = array();
        foreach ($result as $curRow) {
            $date = $curRow['date'];
            if ($date == null) {
                continue;
            }
            $type = $curRow['type'];
            $everyday_throw_count = $curRow['throw_count'];
            $everyday_throw_user = $curRow['throw_user'];
            $res_array[$date][$type] = array($everyday_throw_count, $everyday_throw_user);
        }
        $con[] = $res_array;
    }
    $title = array(
        '0date'=>'时间',
        '1first_use_user'=>'新增人数',
        '2throw_count'=>'扔出次数',
        '2everyday_throw_user'=>'扔的人数',
        '3everyday_collect_count'=>'捡的次数',
        '3everyday_collect_user'=>'捡的人数',
        '4everyday_pick_user'=>'捡到的次数',
        '5everyday_throw_sea'=>'捡瓶子扔的次数',
        '6everyday_collect_return'=>'捡瓶子回复的次数',
        '7everyday_collect_close'=>'捡瓶子返回次数',
        'everyday_push'=>'每天推瓶子总次数',
        '8push_return'=>'推瓶子中回复的次数',
        '9push_sea'=>'推瓶子中扔回大海的次数',
        '10push_close'=>'推瓶子中关闭次数',
        '11push_return'=>'推瓶子中返回次数',
    );
    $count_array = array();
    foreach($con as $res_array) {
        foreach ($res_array as $_date => $values) {
            $tmp_array = array();
            $tmp_array[] = $_date;
            $tmp_array[] = $values["1"][0] != null ? $values['1'][0] : 0;
            $tmp_array[] = $values["2"][0] != null ? $values['2'][0] : 0;
            $tmp_array[] = $values["2"][1] != null ? $values['2'][1] : 0;
            $tmp_array[] = $values["3"][0] != null ? $values['3'][0] : 0;
            $tmp_array[] = $values["3"][1] != null ? $values['3'][1] : 0;
            $tmp_array[] = $values["4"][0] != null ? $values['4'][0] : 0;
            $tmp_array[] = $values["5"][0] != null ? $values['5'][0] : 0;
            $tmp_array[] = $values["6"][0] != null ? $values['6'][0] : 0;
            $tmp_array[] = $tmp_array[6] - $tmp_array[7] - $tmp_array[8];
            $tmp_array[] = $values["7"][0] != null ? $values['7'][0] : 0;
            $tmp_array[] = $values["8"][0] != null ? $values['8'][0] : 0;
            $tmp_array[] = $values["9"][0] != null ? $values['9'][0] : 0;
            $tmp_array[] = $values["10"][0] != null ? $values['10'][0] : 0;
            $tmp_array[] = $tmp_array[10] - $tmp_array[11] - $tmp_array[12] - $tmp_array[13];
            $count_array[] = $tmp_array;
        }
    }
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($count_array as $sqlData){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            foreach($sqlData as $_key=>$_value){
                $html .= "<td>" .$_value . "</td>";
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
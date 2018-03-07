<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d');
global $servers;
$allServerFlag=false;
//默认为当前服
if ($_REQUEST['selectServer']) {
    $sttt = $_REQUEST['selectServer'];
} else {
    //$currentServer是s+服号格式，需要去掉s
    $sttt = substr($currentServer, 1);
}
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];
$showType = $_REQUEST['showType'];

$statType_title = array('按照排名');
foreach ($statType_title as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}

if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
    $end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
    $monthArr = monthList(strtotime($start),strtotime($end));

    $sids = implode(',', $selectServerids);
    if(empty($_REQUEST['selectServer'])){
        $whereSql = "";
    }else {
        $whereSql = " and server_id in ($sids) ";
    }
    if($_REQUEST['userId']) {
        $user_id = $_REQUEST['userId'];
        $whereSql .= " and userid='$user_id' ";
    }
    if ($_REQUEST['showType']) {
        $showType = $_REQUEST['showType'];
        $whereSql .= " and type='$showType' ";
    }
    $html = '';
    //竞技场英雄排名的打点时间是在活动结束之后不久，因此不能用date字段来过滤，而是var_data1字段，该字段存取的是活动开始时间的timestamp的小时数
    $wheretime = "and cast(var_data1 as signed integer)>=(unix_timestamp(str_to_date('$start','%Y-%m-%d'))/3600) and cast(var_data1 as signed integer)<=(unix_timestamp(str_to_date('$end','%Y-%m-%d'))/3600) ";
    $statType = $_REQUEST['statType'];
    //int_data1是排名
    if($statType == 0) {
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            //timeStamp是奖励邮件发放的时间, var_data1是活动开始时间的小时time
            $sql_pass = "select var_data1 as activityTime, userid, timeStamp as mailedTime, int_data1 as rank from $db_start where category=45 $wheretime $whereSql group by var_data1,userid  ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        //时间按照从大到小，排名按照从小到大
        $sql_sum .= "order by activityTime desc, rank asc";
//        echo $sql_sum;
        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $rankArr =array();

        if (empty($result_pass['ret']['data'])) {
            $html .= "<div><h3 style='color:red;'>无数据！</h3></div>";
        } else {
            foreach ($result_pass['ret']['data'] as $currow) {
                $rank = $currow['rank'];
                $rankArr[$rank] = $rank;
                $user_id =  $currow['userid'];
                $mailedTime = date('Y-m-d H:i:s', $currow['mailedTime']/1000);
                $activityStartDate = date('Y-m-d', $currow['activityTime']*3600);
                $datearr[$activityStartDate] = $activityStartDate;
                $alldata[$activityStartDate][$rank]['uid'] = $user_id;
                $alldata[$activityStartDate][$rank]['mailedTime'] = $mailedTime;
            }

            $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
            $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='1'>开始日期</th>";
            foreach ($datearr as $date) {
                $html .= "<th colspan='2'>$date</th>";
            }
            $html .= "</tr></thead>";
            $html .= "<tr><th>排名</th>";
            foreach ($datearr as $date) {
                $html .= "<th>uid</th><th>奖励邮件发送时间</th>";
            }
            $html .= "</tr><tbody id='adDataTable'>";

            sort($rankArr);
            foreach ($rankArr as $value) {
                $htmltmp = '';
                $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$value}</font></td>";
                foreach ($datearr as $date) {
                    $uid_col = $alldata[$date][$value]['uid'];
                    $mailedTime_col = $alldata[$date][$value]['mailedTime'];
                    $htmltmp .= "<td>{$uid_col}</td><td>{$mailedTime_col}</td>";
                }
                $htmltmp .= "</tr>";
                $html .= $htmltmp;
            }
            $html .= '</tbody></table></div>';
        }
    }
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
$battleType = array('发布动态的次数','发布动态的人数','点赞的次数','点赞的人数','评论的次数','评论的人数');
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value</option>";
}
$lang = loadLanguage();
if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?$_REQUEST['start']:$start;
    $end = $_REQUEST['end']?$_REQUEST['end']:$end;

    $monthArr = monthList(strtotime($start),strtotime($end));
    $time = "l.date>=str_to_date('$start','%Y-%m-%d') and l.date<=str_to_date('$end','%Y-%m-%d') ";
    $type = (int)($_REQUEST ['battleType']/2)+2;
    $type ="l.type='$type'";

    $sid=substr($_COOKIE['Gserver2'],1);
    if($_REQUEST ['battleType']%2==0){
        foreach ($monthArr as $i) {
                $db_start = 'coklog_function.function_log_' . $i;
                $sql = "select r.country as country,l.date as date,count(1) as `sum` from $db_start l LEFT JOIN snapshot_s{$sid}.stat_reg r on l.userId=r.uid
                          where category=16 and {$time} and {$type} and l.server_id=$sid group by date,r.country ";
                if(isset($sql_sum)){
                    $sql_sum = $sql_sum . " union " . $sql ;
                }else{
                    $sql_sum = $sql;
                }
            }
    }else {
        foreach ($monthArr as $i) {
                $db_start = 'coklog_function.function_log_' . $i;
                $sql = "select r.country as country,l.date as date,count(distinct userid) as `sum` from $db_start l LEFT JOIN snapshot_s{$sid}.stat_reg r on l.userId=r.uid
                        where category=16 and {$time} and {$type} and l.server_id=$sid group by date,r.country  ";

                if($sql_sum){
                    $sql_sum = $sql_sum . " union " . $sql ;
                }else{
                    $sql_sum = $sql;
                }
            }
        }
    $sql_sum .=" order by date desc;";
    $result =query_infobright($sql_sum);
    $result = $result['ret']['data'];
    $start = date('Ymd',strtotime($start));
    $end = date('Ymd',strtotime($end));
    $start_time=$start;
    foreach($result as $users){
        $country = $lang[$users['country']];
        $date = date('Ymd',strtotime($users['date']));
//        $date = $users['date'];
        if(!isset($coun[$country][$date])){
            for($start=$start_time;$start<=$end;$start++){
                $coun[$country][$start]=0;
            }
        }
        $sum[$date] +=isset($users['sum'])?$users['sum']:0;
        $coun[$country][$date]=isset($users['sum'])?$users['sum']:0;
    }
//    $html = json_encode($sum);
    $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    $html .= "<th style='text-align:center';>国家</th>";
    for($start=$start_time;$start<=$end;$start++){
        $html .= "<th style='text-align:center';>" . $start . "</th>";
    }
    $html .= "</tr>";
    $html .= "<tr class='listTr'>";
    $html .= "<th style='text-align:center';>总计</th>";
    for($start=$start_time;$start<=$end;$start++){
            $html .= "<th style='text-align:center';>" . $sum[$start] . "</th>";
    }
    $html .= "</tr>";
    foreach($coun as $country=>$date){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#E6E6FA' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$country . "</td>";
        foreach($date as $key=>$value){
            $html .= "<td>" . $value . "</td>";
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
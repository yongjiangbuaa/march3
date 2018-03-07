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
$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

$total_start = date('Ymd',strtotime($start));
$total_end = date('Ymd',strtotime($end));

$total_time = "date>=$total_start and date<=$total_end ";
if ($_REQUEST ['analyze'] == 'platform') {
    $sids = implode(',', $selectServerids);
    $whereSql="  sid in ($sids) ";
    $sql = "select date,attackUser,countHit from stat_allserver.stat_half_orcs_npcBuild  where $whereSql and $total_time  group by date; ";
    $result = query_infobright($sql);
    $result = $result['ret']['data'];

    $sql_sum = "select date,sum(reg) reg,sum(replay) replay,sum(relocation) relocation from stat_allserver.stat_dau_daily_pf_country_referrer where $whereSql and $total_time group by  date desc;";
    $result_sum = query_infobright($sql_sum);
    $result_sum = $result_sum['ret']['data'];

    foreach($result as $new_user){
        $date = $new_user['date'];
        $coun[$date][0]=$new_user['attackUser'];
        $coun[$date][1]=$new_user['countHit'];
        foreach($result_sum as $sum_user){
            $date1 = $sum_user['date'];
            if($date==$date1){
                $coun[$date][2]=round($new_user['attackUser']*100/($sum_user['reg']+$sum_user['replay']+$sum_user['relocation']),2);
            }
        }
    }
    $title = array(
        '时间',
        '新注册用户中攻击NPC城堡的人数',
        'NPC城堡被攻击的次数',
        '攻击NPC城堡的人数占新增人数百分比',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($coun as $num=>$sqlData){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" .$num . "</td>";
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
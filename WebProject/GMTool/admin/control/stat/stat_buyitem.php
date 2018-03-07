<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());
$countType = array('道具购买统计');//防止还有类似简单小功能
foreach ($countType as $key=>$value){
    $options .= "<option id={$key}>{$value}</option>";
}

global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if ($_REQUEST ['analyze'] == 'platform') {
    $lang = loadLanguage();

    if($_REQUEST['start']){
        $start = strtotime($_REQUEST['start'])*1000;
    }else{
        $start = strtotime($start)*1000;
    }
    $start = date('Ymd',$start/1000);
    if($_REQUEST['end']) {
        $end = strtotime($_REQUEST['end']) * 1000+ 86400000;
    }else{
        $end = strtotime($end) * 1000+ 86400000;
    }
    $end = date('Ymd',$end/1000);

    $giftType = $_REQUEST['countType']; //防止还有类似简单小功能
    $itemid = $_REQUEST['itemid'];

    $m_allserver_data=0;
    $m_allserver_data1=0;

    $sids = implode(',',$selectServerids);


        $sql = "select count(1) num ,sum(users) cntuser,sum(times) as times  from stat_allserver.pay_goldStatistics_daily_groupByGoodsAndResource where sid in($sids) param1=$itemid and date>=$start and date< $end group by date; ";
        $displayResult = query_infobright($sql);
        foreach ($displayResult['ret']['data'] as $disRow){
            $m_allserver_data += $disRow['num'];
            $m_allserver_data1 += $disRow['cntuser'];
        }


    $disHtml1 = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $disHtml1 .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $disHtml1 .="<tr>
				<th><a href='#' onclick=\"sort_table(1, 0, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">道具id</a></th>
				<th><a href='#' onclick=\"sort_table(1, 1, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">数量</a></th>
				<th><a href='#' onclick=\"sort_table(1, 2, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">人数(去重)</a></th>
				</tr>";
    $disHtml1 .= "<tbody id='adDataTable'>";

    $disHtml1 .= "<tr><td>$itemid</td><td>$m_allserver_data</td><td>$m_allserver_data1</td></tr>";

    $disHtml1 .="</tbody></table></div>";
    echo $disHtml1;
    exit();
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
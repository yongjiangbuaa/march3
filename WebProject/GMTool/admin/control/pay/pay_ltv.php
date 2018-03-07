<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
    $startDate = date("Y-m-d",time()-86400*4);
}else{
    $startDate = $_REQUEST['startDate'];
}
if(!$_REQUEST['endDate']){
    $endDate = date("Y-m-d",time());
}else{
    $endDate = $_REQUEST['endDate'];
}
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if (!$_REQUEST['selectCountry']) {
    $currCountry = 'ALL';
}else{
    $currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
	$seletedpf = 'ALL';
}else{
	$seletedpf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['selectReferrer']) {
	$selectReferrer = 'ALL';
}else{
	$selectReferrer = $_REQUEST['selectReferrer'];
}


//查询LTV 用户总价值
if (isset($_REQUEST['getData'])) {
	$startDateYMD = strtotime($startDate);
	$endDateYMD = strtotime($endDate);

    $startDateYMD = date('Ymd',$startDateYMD);
    $endDateYMD = date('Ymd',$endDateYMD);

    $whereSql='1=1';
    $whereSql2='1=1';

    if (!empty($seletedpf) && $seletedpf != 'ALL') {
	    $whereSql .= " and pf='$seletedpf' ";
	    $whereSql2 .= " and r.pf='$seletedpf'";
    }
    if(!empty($currCountry) && $currCountry != 'ALL'){
        $whereSql .= " and country='$currCountry' ";
        $whereSql2 .= " and r.country='$currCountry'";
    }
    if(!empty($selectReferrer) && $selectReferrer != 'ALL'){
        $whereSql .= " and referrer='$selectReferrer' ";
        if($selectReferrer=='nature') {
            $whereSql2 .= " and (r.referrer='' or r.referrer='Organic' or r.referrer is NULL)";
        }else{
            $whereSql2 .= " and referrer='$selectReferrer' ";
        }

    }
    $serverarr = implode(',',$selectServerids);
    $whereSql .= " and sid in ($serverarr)";
    $whereSql .= " and date >= $startDateYMD and date<$endDateYMD";

    $eventAll = array();
    $totalReg =	$totalIncome = $today = $day3 = $day7 =$day15=$day30 =0;
    $nameLink = array('date'=>'LTV','reg'=>'注册人数',
        'today'=>'当日付费','today_all'=>'人数','1LTV'=>'当日LTV',
        '3day'=>'3日付费','3_all'=>'人数','3LTV'=>'3日LTV',
        '7day'=>'7日付费','7_all'=>'人数','7LTV'=>'7日LTV',
        '15day'=>'15日付费','15_all'=>'人数','15LTV'=>'15日LTV',
        '30day'=>'30日付费','30_all'=>'人数','30LTV'=>'30日LTV',
        'allday'=>'总付费','allLTV'=>'总LTV');

        $sql1 = "select sum(reg+replay+relocation) as sum,date as regDate from stat_allserver.stat_dau_daily_pf_country_referrer where $whereSql group by regDate";
        $result = query_infobright($sql1);
        if(is_array($result['ret']['data'])){
            foreach ($result['ret']['data'] as $curRow){
                $yindex = $curRow['regDate'];
                $eventAll[$yindex]['reg'] += $curRow['sum'];//注册人数
                $eventAll[$yindex]['date'] = $yindex;
                $totalReg += $curRow['sum'];
            }
        }
    $startTime = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
    $endTime  = strtotime($_REQUEST['endDate'])*1000;

    foreach ($selectServer as $server=>$value) {
        $sql2 = "select sum(p.spend) sum,count(DISTINCT p.uid) pay_number,date_format(from_unixtime(p.time/1000),'%Y%m%d') as payDate,date_format(from_unixtime(u.regTime/1000),'%Y%m%d') as regDate from paylog p inner join stat_reg r on p.uid = r.uid inner join userprofile u on p.uid=u.uid where u.regTime >= $startTime and u.regTime < $endTime and $whereSql2  group by regDate,payDate order by p.time asc;";
        $result = $page->executeServer($server, $sql2, 3);

        foreach ($result['ret']['data'] as $curRow) {
            $regDate = $curRow['regDate'];
            $payDate = $curRow['payDate'];

            $datetime1 = new DateTime($regDate);
            $datetime2 = new DateTime($payDate);
            $interVal = $datetime1->diff($datetime2);

            $newdate = date('Ymd', strtotime($curRow['regDate']));
            if ($interVal->format('%a day') == 0) {
                $eventAll[$newdate]['today'] += $curRow['sum']; //付费总额
                $eventAll[$newdate]['today_all'] += $curRow['pay_number'];//付费人数
            }
            if ($interVal->format('%a day') <= 3) {
                $eventAll[$newdate]['3day'] += $curRow['sum'];
                $eventAll[$newdate]['3_all'] += $curRow['pay_number'];
            }
            if ($interVal->format('%a day') <= 7) {
                $eventAll[$newdate]['7day'] += $curRow['sum'];
                $eventAll[$newdate]['7_all'] += $curRow['pay_number'];
            }
            if ($interVal->format('%a day') <= 15) {
                $eventAll[$newdate]['15day'] += $curRow['sum'];
                $eventAll[$newdate]['15_all'] += $curRow['pay_number'];
            }
            if ($interVal->format('%a day') <= 30) {
                $eventAll[$newdate]['30day'] += $curRow['sum'];
                $eventAll[$newdate]['30_all'] += $curRow['pay_number'];
            }
            $eventAll[$newdate]['allday'] += $curRow['sum'];//总额就是这天到现在所有的付费,<30天之内是,正好是30天的那个数,所以,只有>30天时才会有用
            $eventAll[$newdate]['date'] = $newdate; //注册那天
        }
    }

    ksort($eventAll);
    foreach ($eventAll as $yindex=>$value) {
        foreach ($nameLink as $xindex => $vinfo) {
            switch ($xindex) {
                case 'today':
                    $today += $eventAll[$yindex]['today'];
                    break;
                case '1LTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['today'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;
                case '3day':
                    $day3 += $eventAll[$yindex]['3day'];
                    break;
                case '3LTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['3day'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;
                case '7day':
                    $day7 += $eventAll[$yindex]['7day'];
                    break;
                case '7LTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['7day'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;
                case '15day':
                    $day15 += $eventAll[$yindex]['15day'];
                    break;
                case '15LTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['15day'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;
                case '30day':
                    $day30 += $eventAll[$yindex]['30day'];
                    break;
                case '30LTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['30day'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;
                case 'allday':
                    $totalIncome += $eventAll[$yindex]['allday'];
                    break;
                case 'allLTV':
                    $eventAll[$yindex][$xindex] = $eventAll[$yindex]['reg'] != '-' ? round($eventAll[$yindex]['allday'] / $eventAll[$yindex]['reg'], 2) : '-';
                    break;

            }
        }
    }

    ksort($eventAll);
    $eventAll['sum']['date'] = '总计';
    $eventAll['sum']['reg'] = $totalReg;
    $eventAll['sum']['today'] = $today;
    $eventAll['sum']['1LTV'] = round($today/$eventAll['sum']['reg'],2);;
    $eventAll['sum']['3day'] = $day3;
    $eventAll['sum']['3LTV'] = round($day3/$eventAll['sum']['reg'],2);
    $eventAll['sum']['7day'] = $day7;
    $eventAll['sum']['7LTV'] = round($day7/$eventAll['sum']['reg'],2);
    $eventAll['sum']['15day'] = $day15;
    $eventAll['sum']['15LTV'] = round($day15/$eventAll['sum']['reg'],2);
    $eventAll['sum']['30day'] = $day30;
    $eventAll['sum']['30LTV'] = round($day30/$eventAll['sum']['reg'],2);
    $eventAll['sum']['allday'] = $totalIncome;
    $eventAll['sum']['allLTV'] = round($totalIncome/$eventAll['sum']['reg'],2);
    $sumArr = array_pop($eventAll);
    array_unshift($eventAll, $sumArr);
    printStat2($eventAll,$nameLink,$nameLinkSort,$hightLight);
    exit;

}

function printStat2($eventAll,$nameLink,$nameLinkSort,$hightLight){
    //表头  数据
    $html = "<table class='listTable' style='text-align:center'><thead>";
    if(!$nameLinkSort){
        $nameLinkSort = array_keys($nameLink);
    }
    ksort($nameLinkSort);
    // 	foreach ($nameLink as $column){
    // 	$html .= "<th>$column</th>";
    foreach ($nameLinkSort as $xRow){
        $html .= "<th>$nameLink[$xRow]</th>";
    }
    $html .= "</thead>";
    foreach ($eventAll as $date=>$eventData)
    {
        $value=date('w',strtotime($date));
        $style='';
        if ($value==0||$value==6){
            $style='style="background:gray;"';
        }
        $html .= "<tbody><tr $style class='listTr'>";
        // 		foreach ($nameLink as $type=>$column){
        // 			$temp = $eventData[$type];
        foreach ($nameLinkSort as $xRow){
            $temp = $eventData[$xRow];
            if(!$temp){
                $temp = '-';
            }elseif($hightLight[$date]){
                $temp = "<a>$temp</a>";
            }
            $html .= "<td>$temp</td>";
        }
        $html .= "</tr></tbody>";
    }
    $html .= "</table>";
    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") )
?>
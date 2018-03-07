<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "每日支付总额";
$dateMax = date("Y-m-d", time());
$days = 8;
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);

$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

//$currPf为平台
if (!$_REQUEST['selectPf']) {
    $currPf = 'ALL';
} else {
    $currPf = $_REQUEST['selectPf'];
}
//$seletedpf支付渠道
if (!$_REQUEST['selectPayMethod']) {
    $seletedpf = 'all';
} else {
    $seletedpf = $_REQUEST['selectPayMethod'];
}

foreach ($optionsArr as $pf => $pfdisp) {
    $flag = ($seletedpf == $pf) ? 'selected="selected"' : '';
    $pfOptions .= "<option value='{$pf}' $flag>{$pfdisp}</option>";
}

if ($_REQUEST['display']) {
    $serverDate = $_REQUEST['serverDate'];
    //s1_total_all_ALL_ALL_1460505600000-1461110400000  第一列
//	   s1_2016-04-18_all_ALL_ALL 普通
//	1,2,3,4,5,6,25_2016-04-20_all_ALL_ALL  第一行
    if ($_COOKIE['u'] == 'xiaomi') {
        $temp = explode('_', $serverDate, 3);
    } else {
        $temp = explode('_', $serverDate);
    }
    $displayServer = $temp[0];
    $date = $temp[1];
    $payMethod = $temp[2];//支付渠道
    if($payMethod != 'all'){
        echo "<div><h5>支付渠道不支持查详情</h5></div>";
        exit();
    }

    $pf = $temp[3];//平台
    $country = $temp[4];
    //获取点击的 时间
    if ($date != "total") {
        $ts_start = strtotime($date);
        $ts_end = $ts_start + 86400;
        $ts_start = date('Ymd',$ts_start);
        $ts_end = date('Ymd',$ts_end);
    } else {
        $date2tmp = $temp[5];
        $date2 = explode('-', $date2tmp);
        $ts_start = date('Ymd',$date2[0]/1000);
        $ts_end = date('Ymd',$date2[1]/1000);
    }

    $wherepf = '1=1';
    if (!empty($pf) && $pf != 'ALL') {
        $wherepf .= " and pf='$pf' ";
    }
    if (!empty($country) && $country != "ALL") {
        $wherepf .= " and country ='$country' ";
    }
    if(strpos('s',$displayServer) !== false) {
        $displayServer = substr($displayServer,1);
    }
    $wherepf .= " and sid in($displayServer)";

    $sql = "select productId ,sum(num) as num from stat_allserver.stat_exchange_pf_country_send
        where date >= $ts_start and date < $ts_end group by productId ASC ;";

    $result = query_infobright($sql);

    $cot=array();
    $ids=array(); //礼包id
    foreach($result['ret']['data'] as $disRow){
        $tmp = $disRow['productId'];
        $cot[$tmp] += $disRow['num'];
        $ids[$tmp] = $tmp;
    }

    $disHtml = "服务器:$displayServer&nbsp;&nbsp;日期:$date<div><table class='listTable' style='text-align:center'><thead>";
    $disHtml .= "<th>Id</th><th>Name</th><th>单价</th><th>次数</th><th>总额</th></thead>"; //礼包简单统计
    foreach ($ids as $idValue) {

        $tot = $exchangeName[$idValue][1] * $cot[$idValue];
        $disHtml .= "<tr><td>$idValue</td><td>" . $exchangeName[$idValue][0] . "</td><td>" . $exchangeName[$idValue][1] . "</td><td>$cot[$idValue]</td><td>$tot</td></tr>";
    }
    $disHtml .= "</table></div>";
    echo $disHtml;
    exit();
}

if (isset($_REQUEST['getData'])) {
    $days = $_REQUEST['days'];
    $country = $_REQUEST['country'];
    $startYmd = date('Ymd', strtotime($_REQUEST['dateMax']) - ($days - 1) * 86400);
    $endYmd = date('Ymd', strtotime($_REQUEST['dateMax']));
    $nowYmd = date('Ymd');
    if ($nowYmd <= $endYmd) {
        $isNeed = true; //是否需要单独处理实时查询数据
//        $endYmd = date('Ymd', strtotime('-1 day'));//昨天日期
    } else {
        $isNeed = false;
    }
    $sids = implode(',', $selectServerids);
    $newWhereSql = " where sid in($sids) ";
    $newWhereSql .= " and date between $startYmd and $endYmd ";
    if (!empty($currPf) && $currPf != 'ALL') {
        $newWhereSql .= " and pf='$currPf' ";
    }
    if (!empty($country) && $country != 'ALL') {
        $newWhereSql .= " and country='$country' ";
    }
    //这2变量后边 点击查看详情会用到
    $endTime = strtotime(date('Y-m-d', strtotime($_REQUEST['dateMax']))) * 1000 + 86400000;
    $startTime = strtotime(date('Y-m-d', $endTime / 1000)) * 1000 - $days * 86400000;

    $wherepf = ' 1=1';
    if (!empty($seletedpf) && $seletedpf != 'all') {
        $wherepf .= " and p.pf='$seletedpf' ";
        $newWhereSql .= " and payChanel='$seletedpf' ";
    } else {
        $wherepf .= " and p.pf!='iostest' ";
        $newWhereSql .= " and payChanel!='iostest' ";
    }
    if(false)
    {
        $sql = "select sid, sum(payCount) as payCount, date as logDate
                from stat_allserver.pay_payTotle_pf_country $newWhereSql
                group by logDate,sid order by logDate desc,sid";

        $nameLink['server'] = '服务器';
        $nameLink['open'] = '开服天数';
        $nameLink['total'] = '服务器总和';

        $eventAll['sum']['server'] = '合计'; //副标题

        $nameLinkSort = array_keys($nameLink);//返回所有keys

        $result = query_infobright($sql);
        $serverMap = array();//server数组
        foreach ($result['ret']['data'] as $curRow) {
            $formatDate = $curRow['logDate'];
            $nameLink[$formatDate] = $formatDate;
            $nameLinkSort[$formatDate] = $formatDate;

            $server = 's' . $curRow['sid'];
            if (!in_array($server, $serverMap)) {
                $serverMap[] = $server;
                $eventAll[$server]['server'] = $server;
            }
            $eventAll[$server][$formatDate] = $curRow['payCount'];
            $eventAll[$server]['total'] += $curRow['payCount']; //这个服 的所有日期 总额

            $eventAll['sum'][$formatDate] += $curRow['payCount']; //所有服 这天 总额
        }
    }else
    {
        $sql = "select sid, sum(payCount) as payCount, date as logDate from stat_allserver.pay_payTotle_pf_country $newWhereSql group by logDate,sid order by sid;";
        $nameLink['server'] = '服务器';
        $nameLink['open'] = '开服天数';
        $nameLink['total'] = '服务器总和';
        $eventAll['sum']['server'] = '合计';
        $nameLinkSort = array_keys($nameLink);//返回所有keys
        $temp = $startTime;
        $charts_categories = array();
        $chartsPayArrTmp = array();
        $dateLink = array();

        while($temp < $endTime){
            $tempDate = date('Y-m-d',$temp/1000);
            $nameLink[$tempDate] = $tempDate;
            $charts_categories[] = $tempDate;
            $nameLinkSort[$endTime - $temp] = $tempDate;
            $temp += 86400000;
            $chartsPayArrTmp[$tempDate] = 0;
            $dateLink[$tempDate] = $tempDate;
        }

        krsort($chartsPayArrTmp);
        krsort($dateLink);
        $actAll = array();
        $dayCountAll = array();
        if($days>7){
            $countTotal=array_slice($chartsPayArrTmp,0,7);
        }else{
            $countTotal = $chartsPayArrTmp;
        }
        $charts_series = array();
        $sqlData = array();
        $chartsPayArr = $chartsPayArrTmp;
        $result = query_infobright($sql);
        $serverMap=array();
        foreach ($result['ret']['data'] as $curRow){
            $formatDate=date("Y-m-d",strtotime($curRow['logDate']));
            $server='s'.$curRow['sid'];
            if(!in_array($server, $serverMap)){
                $serverMap[]=$server;
                $eventAll[$server]['server'] = $server;
            }
            $eventAll['sum'][$formatDate] += $curRow['payCount'];
            $eventAll['sum'][$formatDate] = $eventAll['sum'][$formatDate];
            $eventAll[$server][$formatDate] = $curRow['payCount'];
            $eventAll[$server]['total'] += $curRow['payCount'];
            $chartsPayArr[$server][$formatDate] += $curRow['payCount'];

        }
//        foreach ($selectServer as $server=>$serverInfo){
//            if(in_array($server, $serverMap)){
//                $eventAll['sum']['total'] += $eventAll[$server]['total'];
//
//                if (isset($server_open_days[$server]) && $server_open_days[$server]) {
//                    $nowDate=date_create(date('Y-m-d'));
//                    $kaifuDate=date_create($server_open_days[$server]);
//                    $interval = date_diff($nowDate, $kaifuDate);
//                    $opendays = $interval->format('%a');
//
//                    $eventAll[$server]['open'] = $opendays + 1;
//                }else{
//                    $openDaySql = "select daoliangStart from server_info where uid='server'";
//                    $openDayresult = $page->executeServer($server,$openDaySql,3);
//                    $openTime = $openDayresult['ret']['data'][0]['daoliangStart'];
//                    if(empty($openTime)){
//                        $eventAll[$server]['open'] = 0;
//                    }else {
//                        $datetime1 = date_create(date('Y-m-d'));
//                        $datetime2 = date_create(date('Y-m-d',$openTime/1000));
//                        $interval = date_diff($datetime1, $datetime2);
//                        $opendays = $interval->format('%a');
//                        $eventAll[$server]['open'] = $opendays + 1;
//                        write_server_open_day($server,date('Y-m-d',$openTime/1000));
//                    }
//                }
//
//// 				krsort($chartsPayArr[$server]);
//// 				$chartsPayArrTemp = array_values($chartsPayArr[$server]);
//// 				$charts_series[] = array('name'=>$server,'data'=>$chartsPayArrTemp);
//            }
//        }
    }
    $eventAll['sum']['open'] = '-';
    //表头  数据
    if (in_array($_COOKIE['u'], $privilegeArr)) {
        $html = $sql . "<table class='listTable' style='text-align:center'><thead>";
    } else {
        $html = "<table class='listTable' style='text-align:center'><thead>";
    }

    ksort($nameLinkSort);
    foreach ($nameLinkSort as $xRow) {
        $html .= "<th>$nameLink[$xRow]</th>"; //标题
    }
    $html .= "</thead>";
    $i = 1;
    foreach ($eventAll as $date => $eventData) {
        $html .= "<tbody><tr class='listTr' onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\">";
        foreach ($nameLinkSort as $indexKey => $xRow) {
            $temp = $eventData[$xRow];
            if (!$temp) {
                $temp = '-';
            }
            if ($xRow != 'server' && $xRow != 'open' && $temp != '-') {
                $tp = '';
                if ($_COOKIE['u'] != 'xiaomi') {
                    if (empty($seletedpf)) {
                        $tp = '_all';
                    } else {
                        $tp = '_' . $seletedpf;
                    }
                }
                if (empty($currPf)) {
                    $tp .= '_ALL';
                } else {
                    $tp .= '_' . $currPf;
                }
                if ($_COOKIE['u'] != 'xiaomi') {
                    $tp .= '_' . $country;
                }
//					id="s1_2016-04-19_all_ALL_ALL"
//					id="s1_total_all_ALL_ALL_starttime-endtime"  //这服 全部日期
                if ($xRow == 'total') {
                    $dateset = $xRow . $tp . '_' . $startTime . '-' . $endTime;
                } else {
                    $dateset = $xRow . $tp;
                }
                if ($eventData['server'] == '合计') {
//                    $serverset = 'ALL';// TODO: 这个不支持 实时查
                    $serverset =  implode(',', $selectServerids);
                } else {
                    $serverset = $eventData['server'];
                }
                $html .= '<td style="text-align: right;" id="' . $serverset . '_' . $dateset . '"><a href="' . 'javascript:void(edit(' . "'" . $serverset . '_' . $dateset . "'))" . '">' . $temp . '</a></td>';
            } else {
                if ($indexKey == 'server' || $indexKey = 'open') {
                    $html .= "<td style='text-align: right;'>$temp</td>";
                } else {
                    $temp = number_format($temp, 2);
                    $html .= "<td style='text-align: right;'>" . $temp . "</td>";
                }
            }
        }
        $html .= "</tr></tbody>";
        $i++;
    }
    $html .= "</table><br><br>";
    $ret = array();
    $ret['html'] = $html;

    exit(json_encode($ret));
}

include(renderTemplate("{$module}/{$module}_{$action}"));

function getNameFromNumber($num)
{
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}

?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '1024M');
set_time_limit(0);
if (!$_REQUEST['end_time']) {
    $dateMax = date("Y-m-d 23:59:59", time());
} else {
    $dateMax = $_REQUEST['end_time'];
}
if (!$_REQUEST['start_time']) {
    $dateMin = date("Y-m-d 00:00:00", time() - 7 * 86400);
} else {
    $dateMin = $_REQUEST['start_time'];
}

$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);

if (!$_REQUEST['country']) {
    $currCountry = 'ALL';
} else {
    $currCountry = $_REQUEST['country'];
}
if (!$_REQUEST['pf']) {
    $currPf = 'ALL';
} else {
    $currPf = $_REQUEST['pf'];
}
//点击  游戏内礼包名称  按钮 (已经去掉,改用读取多语言,item表等)
if ($_REQUEST['event'] == 'gameName') {
    $contents = $_REQUEST['contents'];
    $contents = str_replace('｜', '|', $contents);//替换中文
    $contents = trim($contents, '|');//去掉2边 | 符
    $contents = str_replace('，', ',', $contents);//替换中文
    $packageArray = explode('|', $contents); //$contents为id1,name1|id2,name2...


    if (file_exists(ADMIN_ROOT . '/etc/gameName.php')) {
        $gameNameArr = require ADMIN_ROOT . '/etc/gameName.php';
    }
    $newNameArr=array();
    foreach ($packageArray as $packageValue) {
        $packageValue = trim($packageValue, ',');
        $temp = explode(',', $packageValue, 2);//最多分成2个元素
        $newNameArr['k' . $temp[0]] = $temp[1];
    }
    if (isset($gameNameArr)) {
        $resultArr = array_merge($gameNameArr, $newNameArr);
    } else {
        $resultArr = $newNameArr;
    }

    $strarr = var_export($resultArr, true);
    file_put_contents(ADMIN_ROOT . '/etc/gameName.php', "<?php\n \$gameNameMapping= " . $strarr . ";\nreturn \$gameNameMapping;\n?>");
    exit('添加游戏中的礼包名称成功');
}
//添加
if ($_REQUEST['event'] == 'add') {
    $contents = $_REQUEST['contents'];
    $contents = trim($contents, '|');
    $contents = str_replace('，', ',', $contents);
    $packageArray = explode('|', $contents);
    $exchangeName = require ADMIN_ROOT . '/etc/packageArray.php';
    copy(ADMIN_ROOT . '/etc/packageArray.php', ADMIN_ROOT . '/etc/packageArray.php_' . time());
    foreach ($packageArray as $packageValue) {
        $temp = explode(',', $packageValue);
        $exchangeName[$temp[0]] = array($temp[1], $temp[2]);
    }
    $strarr = var_export($exchangeName, true);
    file_put_contents(ADMIN_ROOT . '/etc/packageArray.php', "<?php\n \$exchangeName= " . $strarr . ";\nreturn \$exchangeName;\n?>");
    exit('礼包添加成功');
}

if ($_REQUEST['event'] == 'viewProduct') {
    $productId = $_REQUEST['productId'];
    $productArray = loadXml('exchange', 'exchange');
    $lang = loadLanguage();
    $clientXml = loadXml('goods', 'goods');
    $goods = array();

    $valueArray = $productArray[$productId];
    $gold = $valueArray['gold_doller'];

    if ($valueArray['item']) {
        $tempArray = explode('|', $valueArray['item']);
        foreach ($tempArray as $tempValue) {
            $one = array();
            $value = explode(';', $tempValue);
            $one[$lang[(int)$clientXml[$value[0]]['name']]] = $value[1];
            $goods[] = $one;
        }
    }

    $disHtml = "<div><table class='listTable' style='text-align:center; width:80%;'><thead><th colspan='2'>礼包" . $exchangeName[$productId][0] . "</th></thead>";
    $disHtml .= "<thead><th>物品</th><th>数量</th></thead>";
    $disHtml .= "<tr><td>金币</td><td>$gold</td></tr>";
    foreach ($goods as $row) {
        foreach ($row as $nameKey => $numVal) {
            $disHtml .= "<tr><td>$nameKey</td><td>$numVal</td></tr>";
        }
    }
    $disHtml .= "</table></div>";
    echo $disHtml;
    exit();
}

if ($_REQUEST['event'] == 'viewServerNum') {
    $productId = $_REQUEST['productId'];
    $date = $_REQUEST['date'];
    if ($date) {
        $title = $exchangeName[$productId][0] . "在" . $date . "的各服购买量";
        $sql = "select sid,sum(num) num from stat_allserver.stat_exchange_pf_country_send where date=$date and productId='$productId' GROUP BY sid;";
    } else {
        $dstart = date('Ymd', strtotime($_REQUEST['dateStart']));
        $dend = date('Ymd', strtotime($_REQUEST['dateEnd']));
        $title = $exchangeName[$productId][0] . "在" . $dstart . "至" . $dend . "期间的各服购买量";
        $sql = "select sid,sum(num) num from stat_allserver.stat_exchange_pf_country_send where date between $dstart and $dend and productId='$productId' GROUP BY sid;";
    }
    $result = query_infobright($sql);
    foreach ($result['ret']['data'] as $row) {
        $disData['s' . $row['sid']] = $row['num'];
    }
    $cnt = count($servers) + 1;
    $disHtml = "<div><table class='listTable' style='text-align:center; width:80%;'><thead><th colspan='$cnt'>" . $title . "</th></thead>";
    $disHtml .= "<thead><th>日期</th>";
    global $servers;

    foreach ($servers as $server => $serverInfo) {
        $disHtml .= "<th>$server</th>";
    }
    $disHtml .= "</thead><tr><td>$date</td>";

    foreach ($servers as $server => $serverInfo) {
        if (isset($disData[$server])) {
            $disHtml .= "<td>$disData[$server]</td>";
        } else {
            $disHtml .= "<td>0</td>";
        }
    }
    $disHtml .= "</tr></table></div>";
    echo $disHtml;
    exit();
}


$titleArray = array(
    'num' => '个数',
    'numPercent' => '个数占比(%)',
    'price' => '单价',
    'paySum' => '金额',
    'paySumPercent' => '金额占比(%)'
);

$showData = false;
$alertHead = '';
if (isset($_REQUEST['event']) == 'view') { //统计数据
    $exchangeName = require ADMIN_ROOT . '/etc/packageArray.php';
    $erversAndSidsArr = getSelectServersAndSids($_REQUEST['selectServer']);
    $selectServer = $erversAndSidsArr['withS'];
    $selectServerids = $erversAndSidsArr['onlyNum'];

    $start = strtotime($_REQUEST['start_time']) * 1000;
    $end = strtotime($_REQUEST['end_time']) * 1000;

    $startDate = substr($_REQUEST['start_time'], 0, 10);
    $endDate = substr($_REQUEST['end_time'], 0, 10);
    $sDdate = date('Ymd', strtotime($startDate));
    $eDate = date('Ymd', strtotime($endDate));

    if ($_REQUEST['productIds']) {
        $productIds = $_REQUEST['productIds'];
    }

    $currCountry = $_REQUEST['selectCountry'];
    $currPf = $_REQUEST['selectPf'];
    $whereSql = '';

    if (isset($productIds)) {
        $productIds = str_replace('，', ',', $productIds);
        $productIds = str_replace(' ', '', $productIds);
        $proArray = explode(',', $productIds);
        $productIds = implode("','", $proArray);
        $whereSql .= " and productId in('$productIds') ";
    }

    if ($currCountry && $currCountry != 'ALL') {
        $whereSql .= " and country='$currCountry' ";
    }
    if ($currPf && $currPf != 'ALL') {
        $whereSql .= " and pf='$currPf' ";
    }
    global $servers;
    $colDate = array();
    for ($i = $eDate; $i >= $sDdate;) {
        $colDate[] = $i;
        $i = date('Ymd', strtotime($i) - 86400);
    }
    $before30day = date('Ymd', strtotime($eDate) - 86400 * 30);
    $data = array();
    $dateArray = array();
    $dateList = array();
    $sortArray = array();
    $sids = implode(',', $selectServerids);
// 		$sql ="select sid, productId,sum(num) num,sum(sendNum) sendNum, date from stat_allserver.stat_exchange_pf_country_send where sid in($sids) and date between $sDdate and $eDate $whereSql GROUP BY sid,date,productId;";
    $sql = "select date,productId,sum(num) num from stat_allserver.stat_exchange_pf_country_send where sid in($sids) and date between $before30day and $eDate $whereSql GROUP BY date,productId;";
    $payResult = query_infobright($sql);
    foreach ($payResult['ret']['data'] as $payRow) {
        if ($payRow['date'] >= $sDdate && $payRow['date'] <= $eDate) {
            $data[$payRow['productId']][$payRow['date']]['num'] += $payRow['num'];
            $data[$payRow['productId']]['total']['num'] += $payRow['num'];
            // 			$sortArray[$payRow['productId']] += $payRow['num'];
            if (!in_array($payRow['date'], $dateArray)) {
                $dateArray[] = $payRow['date'];
            }
        }
        if (!in_array($payRow['date'], $dateList)) {
            $dateList[] = $payRow['date'];
        }
        $curvedata[$payRow['productId']][$payRow['date']] += $payRow['num'];
    }
    sort($dateList);
    $dateData = array();
    $allData = array();
    $chartArray = array();
    $showChart = array();
    $indexArr = array();
// 		$keys=array_keys($sortArray);
// 		array_multisort(array_values($sortArray), SORT_DESC, $keys);
// 		$skey=array_slice($keys, 0, 5);
    $i = 0;
    foreach ($data as $idKey => $dVal) {
        foreach ($dateArray as $dateValue) {
            $dateData[$dateValue]['num'] += $data[$idKey][$dateValue]['num'];
            $allData['num'] += $data[$idKey][$dateValue]['num'];
            if (isset($exchangeName[$idKey])) {
                $dateData[$dateValue]['paySum'] += $data[$idKey][$dateValue]['num'] * $exchangeName[$idKey][1];
                $allData['paySum'] += $data[$idKey][$dateValue]['num'] * $exchangeName[$idKey][1];
            } else {
                $dateData[$dateValue]['paySum'] += 0;
                $allData['paySum'] += 0;
            }
        }
        foreach ($dateList as $dv) {
            if (isset($curvedata[$idKey][$dv])) {
                $chartArray[$idKey][] = $curvedata[$idKey][$dv];
            } else {
                $chartArray[$idKey][] = 0;
            }
        }
        if ($exchangeName[$idKey][0]) {
            $kname = $exchangeName[$idKey][0];
        } else {
            $kname = $idKey;
        }
// 			$kname=$idKey;
        $showChart[$kname]['data'] = '[' . implode(',', $chartArray[$idKey]) . ']';
        $indexArr[$kname] = $kname;
        if ($i < 9) {
            $showChart[$kname]['dis'] = "true";
        } else {
            $showChart[$kname]['dis'] = "false";
        }
        $i++;
    }
    $dateStr = '[' . implode(',', $dateList) . ']';
//    $mappingArr = require ADMIN_ROOT . '/language/packageIdMapping.php';
//    $gameNames = require ADMIN_ROOT . '/etc/gameName.php';
    $exchageXml = loadXml('exchange','exchange');
    $lang = loadLanguage();

    $nameArray = array();
    foreach ($data as $proId => $dVal) {
        $data[$proId]['total']['numPercent'] = number_format($data[$proId]['total']['num'] / $allData['num'] * 100, 2);
        if (isset($exchangeName[$proId])) {
            $data[$proId]['total']['price'] = $exchangeName[$proId][1];
            $data[$proId]['total']['paySum'] = $data[$proId]['total']['num'] * $exchangeName[$proId][1];
        } else {
            $data[$proId]['total']['price'] = '--';
            $data[$proId]['total']['paySum'] = '--';
        }
        $nameArray[$proId]['packageName'] = $exchangeName[$proId][0];//自己看名称
//        if ($gameNames['k' . $mappingArr['p' . $proId]]) {
//            $name = $gameNames['k' . $mappingArr['p' . $proId]];
//        } elseif ($mappingArr['p' . $proId]) {
//            $name = $mappingArr['p' . $proId];
//        }
        $nameArray[$proId]['gameName'] = $lang[(int)$exchageXml[$proId]['name']]; //游戏显示名称

        $data[$proId]['total']['paySumPercent'] = number_format($data[$proId]['total']['paySum'] / $allData['paySum'] * 100, 2);
        foreach ($dateArray as $dateValue) {
            $data[$proId][$dateValue]['numPercent'] = number_format($data[$proId][$dateValue]['num'] / $dateData[$dateValue]['num'] * 100, 2);
            if (isset($exchangeName[$proId])) {
                $data[$proId][$dateValue]['price'] = $exchangeName[$proId][1];
                $data[$proId][$dateValue]['paySum'] = $data[$proId][$dateValue]['num'] * $exchangeName[$proId][1];
            } else {
                $data[$proId][$dateValue]['price'] = '--';
                $data[$proId][$dateValue]['paySum'] = '--';
            }
            if ($dateData[$dateValue]['paySum'] && $dateData[$dateValue]['paySum'] != '--') {
                $data[$proId][$dateValue]['paySumPercent'] = number_format($data[$proId][$dateValue]['paySum'] / $dateData[$dateValue]['paySum'] * 100, 2);
            } else {
                $data[$proId][$dateValue]['paySumPercent'] = '--';
            }
        }
    }
    if ($data) {
        rsort($dateArray);
        $showData = true;
    } else {
        $alertHead = '没有查到相关数据';
    }
}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>
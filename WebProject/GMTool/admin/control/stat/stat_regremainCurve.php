<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "各国家、平台的留存统计曲线图";
$dateMax = date("Y-m-d", time() - 86400);
$dateMin = date("Y-m-d", time() - 86400 * 7);
global $servers;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

if (!$_REQUEST['selectCountry']) {
    $currCountry = 'ALL';
} else {
    $currCountry = strtoupper($_REQUEST['selectCountry']);
}

$indexArr = array(
    'r1' => '次日留存',
    'r3' => '3日留存',
    'r7' => '7日留存'
);

$pfCountry = array(
    'market_global' => 'GooglePlay',
    'AppStore' => 'AppStore',
    'facebook' => 'facebook',
);

$displayCountryArr = array(
    'US' => '美国',
    'JP' => '日本',
    'CN' => '中国',
    'KR' => '韩国',
    'TW' => '台湾省',
    'RU' => '俄罗斯',
    'HK' => '香港特别行政区',
    'MO' => '澳门地区',
    'GB' => '英国',
    'DE' => '德国',
    'FR' => '法国',
    'TR' => '土耳其',
    'AE' => '阿联酋',
    'AU' => '澳大利亚',
    'NZ' => '新西兰',
    'IT' => '意大利',
    'ES' => '西班牙',
    'NO' => '挪威',
    'IR' => '伊朗',
    'ID' => '印度尼西亚',
    'SG' => '新加坡',
    'MY' => '马来西亚',
    'TH' => '泰国',
    'VN' => '越南',
    'BR' => '巴西',
    'SA' => '沙特阿拉伯',
    'OTHER' => '其它国家',
);
$displayPf = array(
    'market_global' => 'GooglePlay',
    'AppStore' => 'AppStore',
    'amazon' => 'amazon',
    'nstore' => 'nstore',
    'tstore' => 'tstore',
    'facebook' => 'facebook',
    'cafebazaar' => 'cafebazaar',
    'mycard' => 'mycard',
    'gash' => 'gash',
    'cn1' => 'cn1',
    'xiaomi' => 'xiaomi',
    'other' => 'other',
);
$mipf = array(
    'cn_360' => 'cn_360',
    'cn_am' => 'cn_am',
    'cn_anzhi' => 'cn_anzhi',
    'cn_baidu' => 'cn_baidu',
    'cn_dangle' => 'cn_dangle',
    'cn_ewan' => 'cn_ewan',
    'cn_huawei' => 'cn_huawei',
    'cn_kugou' => 'cn_kugou',
    'cn_kupai' => 'cn_kupai',
    'cn_lenovo' => 'cn_lenovo',
    'cn_mi' => 'cn_mi',
    'cn_mihy' => 'cn_mihy',
    'cn_mzw' => 'cn_mzw',
    'cn_nearme' => 'cn_nearme',
    'cn_pps' => 'cn_pps',
    'cn_pptv' => 'cn_pptv',
    'cn_sogou' => 'cn_sogou',
    'cn_toutiao' => 'cn_toutiao',
    'cn_uc' => 'cn_uc',
    'cn_vivo' => 'cn_vivo',
    'cn_wdj' => 'cn_wdj',
    'cn_wyx' => 'cn_wyx',
    'cn_youku' => 'cn_youku',
    'cn_sy37' => 'cn_sy37',
    'cn_mz' => 'cn_mz',
    'tencent' => 'tencent',
);

if ($_REQUEST['getData']) {
    $country = $_REQUEST['country'];
    $startYmd = date('Ymd', strtotime($_REQUEST['dateMin']));
    $endYmd = date('Ymd', strtotime($_REQUEST['dateMax']));
    $sids = implode(',', $selectServerids);

    foreach ($indexArr as $day => $nameVal) {
        $rfields[] = "sum($day) as $day";
    }
    $fields = implode(',', $rfields);

    $total_num = array();
    $dateList = array();

    $pfPieData = array();
    $countryPieData = array();

    $showChart = array();
    if ($currCountry != 'ALL') {
        $wheresql = " and country='$currCountry' ";
    }
    $sql = "select date,pf,country,sum(reg_all) regAll,sum(replay) replayAll,sum(relocation) relocationAll,$fields from stat_allserver.stat_retention_daily_pf_country_version where sid in($sids) and date >=$startYmd and date <= $endYmd $wheresql group by date,pf,country order by date;";

    $result = query_infobright($sql);
    $dayData = array();
    $tableData = array();
    $tableRegData = array();
    foreach ($result['ret']['data'] as $curRow) {
        if (array_key_exists($curRow['country'], $displayCountryArr)) {
            $cou = $curRow['country'];
        } else {
            $cou = 'OTHER';
        }

        if (array_key_exists($curRow['pf'], $displayPf)) {
            $findPf = $curRow['pf'];
        } elseif (array_key_exists($curRow['pf'], $mipf)) {
            $findPf = 'xiaomi';
        } else {
            $findPf = 'other';
        }

        if ($curRow['date'] == $endYmd) {
            $pfPieData[$findPf] += $curRow['regAll'];
            $countryPieData[$cou] += $curRow['regAll'];
        }

        $total_num[$curRow['date']] += $curRow['regAll'];

        if (!in_array($curRow['date'], $dateList)) {
            $dateList[] = $curRow['date'];
        }
        $tableRegData[$curRow['date']][$cou]['reg'] += $curRow['regAll'];

        foreach ($indexArr as $day => $nameVal) {
            $count = $curRow[$day] ? $curRow[$day] : 0;
            $remainData[$curRow['date']][$day]['count'] += $count;
            $tableData[$curRow['date']][$cou][$day]['count'] += $count;
        }
    }
    foreach ($dateList as $date) {
        foreach ($indexArr as $day => $nameVal) {
            $dayData[$day][$date] = $total_num[$date] > 0 ? (intval($remainData[$date][$day]['count'] / $total_num[$date] * 10000) / 100) : 0;
        }
    }
    foreach ($tableData as $dateKey => $couValue) {
        foreach ($couValue as $couKey => $dVal) {
            foreach ($indexArr as $day => $nameVal) {
                $tableData[$dateKey][$couKey][$day]['rate'] = $tableRegData[$dateKey][$couKey]['reg'] > 0 ? (intval($tableData[$dateKey][$couKey][$day]['count'] / $tableRegData[$dateKey][$couKey]['reg'] * 10000) / 100) : 0;
            }
        }
    }

    sort($dateList);
    $length = count($dateList);
    $dateStr = '[' . implode(',', $dateList) . ']';
    foreach ($indexArr as $day => $nameVal) {
        ksort($dayData[$day]);
        $showChart[$day] = '[' . implode(',', $dayData[$day]) . ']';
    }

    rsort($dateList);

    foreach ($tableRegData as $date => &$v1) {
        $weight = array();
        foreach ($v1 as $ite => $v2) {
            $weight[] = $v2['reg'];
        }
        array_multisort($weight, SORT_DESC, $v1);
    }
    foreach ($dateList as $d) {
        $i = 1;
        foreach ($tableRegData[$d] as $k => $v) {
            $tableRegData[$d][$k]['index'] = $i;
            $i++;
        }
    }

    //计算饼状数据

    $pfPie = array();
    $countryPie = array();
    $pfPieData['total'] = array_sum($pfPieData);
    $countryPieData['total'] = array_sum($countryPieData);
    foreach ($displayPf as $pk => $pv) {
        $pfPie[] = "['$pv'," . (intval($pfPieData[$pk] / $pfPieData['total'] * 10000) / 100) . "]";
    }
    if($currCountry == 'ALL') {

        foreach ($displayCountryArr as $ck => $cv) {
            $countryPie[] = "['$cv'," . (intval($countryPieData[$ck] / $countryPieData['total'] * 10000) / 100) . "]";
        }
    }


    $titleDate = date('Y-m-d', strtotime($endYmd));

}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>
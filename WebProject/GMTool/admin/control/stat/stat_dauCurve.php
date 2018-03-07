<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/19
 * Time: 15:56
 */

!defined('IN_ADMIN') && exit('Access Denied');
if (!$_REQUEST['startDate']) {
    $startDate = date("Y-m-d", time() - 86400 * 3);
}
if (!$_REQUEST['endDate'])
    $endDate = date("Y-m-d", time());
if ($_REQUEST['analyze'] == 'update') {

}
global $servers;
$allServerFlag = true;

$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

if (!$_REQUEST['selectCountry']) {
    $currCountry = 'ALL';
} else {
    $currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
    $currPf = 'ALL';
} else {
    $currPf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['selectReferrer']) {
    $currReferrer = 'ALL';
} else {
    $currReferrer = $_REQUEST['selectReferrer'];
}
if (!$_REQUEST['allServers']) {
    $allServerFlag = false;
}

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
if ($_REQUEST['event'] == 'search') {
    $startTime = strtotime($startDate, time());
    $sids = implode(',', $selectServerids);
    $whereSql = " where sid in ($sids) ";
    $startDate = substr($_REQUEST['startDate'], 0, 10);
    $sDdate = date('Ymd', strtotime($startDate));
    $startTime = strtotime($sDdate, time());
    $endDate = substr($_REQUEST['endDate'], 0, 10);
    $eDate = date('Ymd', strtotime($endDate) );
    $whereSql .= " and date >=$sDdate and date <= $eDate ";
//    if($currCountry&&$currCountry!='ALL'){
//        $whereSql .=" and country='$currCountry' ";
//    }
    if ($currPf && $currPf != 'ALL') {
        $whereSql .= " and pf='$currPf' ";
    } else if ($currPf == 'ALL' && $_COOKIE['u'] == 'xiaomi') {
        $whereSql .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
    }
    if ($currReferrer && $currReferrer != 'ALL') {
        $whereSql .= " and referrer='$currReferrer' ";
    }
    $pfPieData = $eventAll = $countryPieData = array();

    $sql = "select sid,date,pf,country,sum(dau) s_dau from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by sid, date ,pf,country order by sid, date asc;";
    $result = query_infobright($sql);

    if ($allServerFlag) {//只显示合计
        $start = $sDdate;
        while ($start < $eDate) {
            $eventAll['All'][] = array('x' => $start, 'y' => 0);
            $start = date('Ymd', strtotime($start) + 86400);
        }
        //x轴日期  y轴是日活跃数量
        foreach ($result['ret']['data'] as $curRow) {
            $server = 'All';
            $xIndex = $curRow['date'];
            if ($xIndex == $eDate) {
                $pfPieData[$curRow['pf']] += $curRow['s_dau'];
                //国家特殊,不能全部显示,只显示$displayCountryArr 里面的,太多
                $country = $curRow['country'];
                if (array_key_exists($country, $displayCountryArr)) {
                    $countryPieData[$curRow['country']] += $curRow['s_dau'];
                } else {
                    $countryPieData['OTHER'] += $curRow['s_dau'];
                }
            }
            $index = (strtotime($xIndex) - strtotime($sDdate)) / 86400;//间隔  第几天
            $eventAll[$server][$index] = array('x' => $xIndex, 'y' => $curRow['s_dau'] + $eventAll[$server][$index]['y']);//每次叠加
        }
    } else {
        foreach ($selectServerids as $sid) {//初始化数据,防止均匀间隔内,有的时间数值为空
            $start = $sDdate;
            while ($start < $eDate) {
                $eventAll[$sid][] = array('x' => $start, 'y' => 0);
                $start = date('Ymd', strtotime($start) + 86400);
            }
        }
        foreach ($result['ret']['data'] as $curRow) {
            $server = $curRow['sid'];
            $xIndex = $curRow['date'];
            if ($xIndex == $eDate) {
                $pfPieData[$curRow['pf']] += $curRow['s_dau'];
                //国家特殊,不能全部显示,只显示$displayCountryArr 里面的,太多
                $country = $curRow['country'];
                if (array_key_exists($country, $displayCountryArr)) {
                    $countryPieData[$curRow['country']] += $curRow['s_dau'];
                } else {
                    $countryPieData['OTHER'] += $curRow['s_dau'];
                }
            }
            $index = (strtotime($xIndex) - strtotime($sDdate)) / 86400;
            $eventAll[$server][$index] = array('x' => $xIndex, 'y' => $curRow['s_dau'] + $eventAll[$server][$index]['y']);
        }
    }

    //计算饼状数据

    $pfPie = array();
    $countryPie = array();
    $pfPieData['total'] = array_sum($pfPieData);
    $countryPieData['total'] = array_sum($countryPieData);

    foreach ($pfList as $pk => $pv) {
        $pfPie[] = "['$pv'," . (intval($pfPieData[$pk] / $pfPieData['total'] * 10000) / 100) . "]";
    }
    foreach ($displayCountryArr as $ck => $cv) {
        $countryPie[] = "['$cv'," . (intval($countryPieData[$ck] / $countryPieData['total'] * 10000) / 100) . "]";
    }

    $titleDate = date('Y-m-d', strtotime($eDate));
}

include(renderTemplate("{$module}/{$module}_{$action}"));
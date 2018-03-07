<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (!$_REQUEST['startDate']) {
    $startDate = date("Y-m-d", time() - 86400 * 7);
}
if (!$_REQUEST['endDate'])
    $endDate = date("Y-m-d", time());
if ($_REQUEST['analyze'] == 'update') {

}
global $servers;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);


if (!$_REQUEST['selectCountry']) {
    $currCountry[] = 'ALL';
} else {
    $currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
    $currPf = '';
} else {
    $currPf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['selectReferrer']) {
    $currReferrer = 'ALL';
} else {
    $currReferrer = $_REQUEST['selectReferrer'];
}
$showData = false;
$alertHeader = "";
$newAnalyse = false; //走新统计
if ($currReferrer == 'ALL' && !$sttt) { // 如果没选渠道,没选服
    $newAnalyse = true;
}
if ($_REQUEST['event']) {
    $erversAndSidsArr = getSelectServersAndSids($sttt);
    $selectServer = $erversAndSidsArr['withS'];
    $selectServerids = $erversAndSidsArr['onlyNum'];
}

if ($_REQUEST['event'] == 'output') {
    $sids = implode(',', $selectServerids);
    $whereSql = " where sid in ($sids) ";
    $startDate = substr($_REQUEST['startDate'], 0, 10);
    $sDdate = date('Ymd', strtotime($startDate));
    $endDate = substr($_REQUEST['endDate'], 0, 10);
    $eDate = date('Ymd', strtotime($endDate) + 86400);
    $whereSql .= " and date >=$sDdate and date <= $eDate ";
    if ($currCountry && (!in_array('ALL', $currCountry))) {
        $countries = implode("','", $currCountry);
        $whereSql .= " and country in('$countries') ";
    }
    if ($currReferrer && $currReferrer != 'ALL') {
        $whereSql .= " and referrer='$currReferrer' ";
    }
    $sql = "select country,pf,date,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(pdau_relocation) as pdau_relocation from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by country,pf,date ORDER by date DESC ;";
    $result = query_infobright($sql);
    $eventAll = array();
    $pfData = array();
    $dateArray = array();
    $countryArray = array();
    foreach ($result['ret']['data'] as $curRow) {
        $country = strtoupper($curRow['country']);
        $pf = $curRow['pf'];
        $dateIndesx = $curRow['date'];
        if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
            continue;
        }
        if (!in_array($dateIndesx, $dateArray)) {
            $dateArray[] = $dateIndesx;
        }
        if (!in_array($country, $countryArray)) {
            $countryArray[] = $country;
        }

        if (in_array('ALL', $currCountry)) {
            $country = '合计';
        }

        $eventAll[$dateIndesx][$country]['dau'] += $curRow['s_dau'];

        $eventAll[$dateIndesx][$country]['paid_dau'] += $curRow['paid_dau'];
        $eventAll[$dateIndesx][$country]['pdau_relocation'] += $curRow['pdau_relocation'];

        $eventAll[$dateIndesx][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

        $eventAll[$dateIndesx][$country]['reg'] += $curRow['s_reg'];

        if (in_array($pf, $currPf)) {
            $pfData[$dateIndesx][$country][$pf]['dau'] += $curRow['s_dau'];

            $pfData[$dateIndesx][$country][$pf]['paid_dau'] += $curRow['paid_dau'];
            $pfData[$dateIndesx][$country][$pf]['pdau_relocation'] += $curRow['pdau_relocation'];

            $pfData[$dateIndesx][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

            $pfData[$dateIndesx][$country][$pf]['reg'] += $curRow['s_reg'];
        }
    }

    $sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(firstPay) as firstPay
from stat_allserver.pay_analyze_pf_country_referrer_new $whereSql GROUP BY country,pf,date ORDER by date DESC;";
    $result = query_infobright($sql);
    foreach ($result['ret']['data'] as $row) {
        $country = strtoupper($row['country']);
        $pf = $row['pf'];
        $dateIndesx = $row['date'];

        if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
            continue;
        }
        if (!in_array($dateIndesx, $dateArray)) {
            $dateArray[] = $dateIndesx;
        }
        if (!in_array($country, $countryArray)) {
            $countryArray[] = $country;
        }

        if (in_array('ALL', $currCountry)) {
            $country = '合计';
        }

        $eventAll[$dateIndesx][$country]['payTotle'] += $row['payTotle'];

        $eventAll[$dateIndesx][$country]['payUsers'] += $row['payUsers'];

        $eventAll[$dateIndesx][$country]['firstPay'] += $row['firstPay'];

        if (in_array($pf, $currPf)) {
            $pfData[$dateIndesx][$country][$pf]['payTotle'] += $row['payTotle'];

            $pfData[$dateIndesx][$country][$pf]['payUsers'] += $row['payUsers'];

            $pfData[$dateIndesx][$country][$pf]['firstPay'] += $row['firstPay'];
        }
    }
    $sql = "select country,pf,date,sum(newTotalPay) as newTotalPay from stat_allserver.pay_ratio_analyze_pf_country_referrer_appVersion $whereSql GROUP BY country,pf,date ORDER by date DESC;";
    $result = query_infobright($sql);
    foreach ($result['ret']['data'] as $row) {
        $country = strtoupper($row['country']);
        $pf = $row['pf'];
        $dateIndesx = $row['date'];

        if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
            continue;
        }
        if (!in_array($dateIndesx, $dateArray)) {
            $dateArray[] = $dateIndesx;
        }
        if (!in_array($country, $countryArray)) {
            $countryArray[] = $country;
        }

        if (in_array('ALL', $currCountry)) {
            $country = '合计';
        }

        $eventAll[$dateIndesx][$country]['newTotalPay'] += $row['newTotalPay'];

        if (in_array($pf, $currPf)) {
            $pfData[$dateIndesx][$country][$pf]['newTotalPay'] += $row['newTotalPay'];
        }
    }

    $dayArr = array(1, 3, 7, 30);
    foreach ($dayArr as $day) {
        $rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
    }
    $fields = implode(',', $rfields);
    $sql = "select country,pf,date,$fields from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion $whereSql and reg_all>0  group by country,pf,date ORDER by date DESC;";
    $ret = query_infobright($sql);
    foreach ($ret['ret']['data'] as $row) {
        $country = strtoupper($row['country']);
        $pf = $row['pf'];
        $dateIndesx = $row['date'];

        if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
            continue;
        }
        if (!in_array($dateIndesx, $dateArray)) {
            $dateArray[] = $dateIndesx;
        }
        if (!in_array($country, $countryArray)) {
            $countryArray[] = $country;
        }

        if (in_array('ALL', $currCountry)) {
            $country = '合计';
        }

        foreach ($dayArr as $day) {
            $count = $row['r' . $day] ? $row['r' . $day] : 0;
            $eventAll[$dateIndesx][$country][$day]['count'] += $count;
            if (in_array($pf, $currPf)) {
                $pfData[$dateIndesx][$country][$pf][$day]['count'] += $count;
            }
        }
    }
    if (in_array('ALL', $currCountry)) {
        unset($countryArray);
        $countryArray[] = '合计';
    }
    foreach ($eventAll as $dateKey => $countryVal) {
        foreach ($countryVal as $countryKey => $value) {
            $eventAll[$dateKey][$countryKey]['filter'] = intval($eventAll[$dateKey][$countryKey]['payUsers'] * 10000 / $eventAll[$dateKey][$countryKey]['dau']) / 100 . "%";
            $eventAll[$dateKey][$countryKey]['ARPU'] = intval($eventAll[$dateKey][$countryKey]['payTotle'] * 100 / $eventAll[$dateKey][$countryKey]['payUsers']) / 100;
            foreach ($currPf as $pfVal) {
                $pfData[$dateKey][$countryKey][$pfVal]['filter'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['dau']) / 100 . "%";
                $pfData[$dateKey][$countryKey][$pfVal]['ARPU'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle'] * 100 / $pfData[$dateKey][$countryKey][$pfVal]['payUsers']) / 100;
            }
            foreach ($dayArr as $day) {
                $forDay = (time() - strtotime($dateKey)) / 86400;
                if ($forDay < $day) {
                    $eventAll[$dateKey][$countryKey][$day]['rate'] = "-";
                } else {
                    $eventAll[$dateKey][$countryKey][$day]['rate'] = intval($eventAll[$dateKey][$countryKey][$day]['count'] / $eventAll[$dateKey][$countryKey]['reg'] * 10000) / 100 . "%";
                }

                foreach ($currPf as $pfVal) {
                    if ($forDay < $day) {
                        $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = "-";
                    } else {
                        $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = intval($pfData[$dateKey][$countryKey][$pfVal][$day]['count'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['reg']) / 100;
                    }
                }
            }
        }
    }
//	$currPf=json_encode($currPf);
//	print_r($currPf);

    if (in_array('ALL', $currPf)) {
        $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0) as `ALL` from adcost where date>='$startDate' and date<='$endDate' group by date
				union
				select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0)  as `ALL` from adcost_ios where date>='$startDate' and date<='$endDate' group by date ;";
    } elseif (in_array('market_global', $currPf)) {
        $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0) as `ALL` from adcost where date>='$startDate' and date<='$endDate' group by date";
    } elseif (in_array('AppStore', $currPf)) {
        $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0)  as `ALL` from adcost_ios where date>='$startDate' and date<='$endDate' group by date";
    }


    $result = $page->globalExecute($costsql, 3, true);
    foreach ($result['ret']['data'] as $row) {
        $dateIndesx = date('Ymd', strtotime($row['date']));
        if (!in_array($dateIndesx, $dateArray)) {
            $dateArray[] = $dateIndesx;
        }
        $eventAll[$dateIndesx][$currReferrer] += $row[$currReferrer];

//			$pfData[$dateIndesx]['allcost'] += $row['allcost'];
    }
//=========================================================roi====================================================
    $serverList = array_keys($servers);
    $totalReg = $totalIncome = $day3 = $day7 = $day30 = 0;
    $startTime = $_REQUEST['startDate'] ? strtotime($_REQUEST['startDate']) * 1000 : 0;
    $endTime = strtotime($_REQUEST['endDate']) * 1000;
    foreach ($serverList as $server) {
        $sql = "select r.pf pf,sum(p.spend) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,
                date_format(from_unixtime(u.regTime/1000),'%Y-%m-%d') as regDate
                from paylog p inner join stat_reg r on p.uid = r.uid
                inner join userprofile u on p.uid=u.uid
                where u.regTime >= $startTime and u.regTime < $endTime   and r.type=0
                group by pf,regDate,payDate;";
        $result = $page->executeServer($server, $sql, 3);
        foreach ($result['ret']['data'] as $curRow) {
            $pf = $curRow['pf'];
            $regDate = $curRow['regDate'];
            $payDate = $curRow['payDate'];
            $datetime1 = new DateTime($regDate);
            $datetime2 = new DateTime($payDate);
            $interVal = $datetime1->diff($datetime2);

            $dateIndesx = date('Ymd', strtotime($curRow['regDate']));

            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }

            if ($interVal->format('%a day') <= 3) {
                $event[$dateIndesx]['3roi'] += $curRow['sum'];
            }
            if ($interVal->format('%a day') <= 7) {
                $event[$dateIndesx]['7roi'] += $curRow['sum'];
            }
            if ($interVal->format('%a day') <= 30) {
                $event[$dateIndesx]['30roi'] += $curRow['sum'];
            }
        }
    }
    ksort($event);
    foreach ($event as $dateIndesx => $date_value) {
        foreach ($date_value as $xindex => $vinfo) {
            $forMat = (time() - strtotime($dateIndesx)) / 86400;
            switch ($xindex) {
                case '3roi':
                    if ($forMat < 3) {
                        $eventAll[$dateIndesx][$xindex] = "-";
                    } else {
                        $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['3roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                    }
                    break;
                case '7roi':
                    if ($forMat < 7) {
                        $eventAll[$dateIndesx][$xindex] = "-";
                    } else {
                        $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['7roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                    }
                    break;
                case '30roi':
                    if ($forMat < 30) {
                        $eventAll[$dateIndesx][$xindex] = "-";
                    } else {
                        $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['30roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                    }
                    break;
            }
        }
    }


//
//	if($_REQUEST['type']==1){
//		$sql = "INSERT into operation_log (date,logs) VALUES (" . $_REQUEST['datekey'] . "," ."'" .$_REQUEST['num'] ."'". ") ";
//		$sql .= " ON DUPLICATE KEY UPDATE date=" . $_REQUEST['datekey'] . " ,logs=" . "'" .$_REQUEST['num']."'" ;
//		$page->globalExecute($sql, 2);
////		$sql = "update operation_log set logs = " . $_REQUEST['num'] . " where date = " . $_REQUEST['datekey'];
////		$page->globalExecute($sql, 2);
//	}
    $log_sql = "select * from operation_log where date >=$sDdate and date <= $eDate;";
    $log_ret = $page->globalExecute($log_sql, 3);
    foreach ($log_ret['ret']['data'] as $row) {
        $date = $row['date'];
        $num[$date] = $row['logs'];
    }

    foreach ($currPf as $pf) {
        if ($pf == 'ALL' || $pf == '') {
            $strPf = '----,';
        } else {
            $strPf .= $pf . ',';
        }
    }
    if ($strPf == null) {
        $strPf = '----,';
    }
    $titleStr = '日期,国家,新注册,' . $strPf . '日活跃,' . $strPf . '老玩家,' . $strPf . '付费DAU,' . $strPf . '付费DAU(迁服),' . $strPf . '付费总值,' . $strPf . '付费用户,' . $strPf . '首冲用户,' . $strPf . '首冲付费金额,' . $strPf . '付费渗透率,' . $strPf . 'ARPPU,' . $strPf . '次日留存,' . $strPf . '三日留存,' . $strPf . '七日留存,' . $strPf . '30日留存,' . $strPf . '广告费,' . '3日ROI,' . '7日ROI,' . '30日ROI,' . '运营备注信息,';
    $titleStr = rtrim($titleStr, ',');
    $title = explode(',', $titleStr);

    //导入PHPExcel类
    require ADMIN_ROOT . "/include/PHPExcel.php";
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Set properties
    $objPHPExcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
// 	$titleIndex = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
    //set title
    $Excel = $objPHPExcel->setActiveSheetIndex(0);
    $row = 1;
    //set data
    $line = 0;
    foreach ($title as $width => $value) {
        if (strlen($value) != mb_strlen($value)) {
            $width = (strlen($value) + iconv_strlen($value)) * 1.1 * 8.26 / 22;
            $objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setWidth($width);
        } else {
            $objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setAutoSize(true);
        }
        $Excel->setCellValue(getNameFromNumber($line++) . '' . $row, $value);
    }
    $row++;
    foreach ($countryArray as $counVal) {
        foreach ($dateArray as $dateVal) {
            $Excel->setCellValue(getNameFromNumber(0) . '' . $row, $dateVal);
            $Excel->setCellValue(getNameFromNumber(1) . '' . $row, $counVal);
            $Excel->setCellValue(getNameFromNumber(2) . '' . $row, $eventAll[$dateVal][$counVal]['reg']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(3 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(3 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['reg']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(3 + $length) . '' . $row, $eventAll[$dateVal][$counVal]['dau']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(4 + $length + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(4 + $length + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['dau']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(4 + $length * 2) . '' . $row, $eventAll[$dateVal][$counVal]['sdau']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(5 + $length * 2 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(5 + $length * 2 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['sdau']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(5 + $length * 3) . '' . $row, $eventAll[$dateVal][$counVal]['paid_dau']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(6 + $length * 3 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(6 + $length * 3 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['paid_dau']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(6 + $length * 4) . '' . $row, $eventAll[$dateVal][$counVal]['pdau_relocation']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(7 + $length * 4 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(7 + $length * 4 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['pdau_relocation']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(7 + $length * 5) . '' . $row, $eventAll[$dateVal][$counVal]['payTotle']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(8 + $length * 5 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(8 + $length * 5 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['payTotle']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(8 + $length * 6) . '' . $row, $eventAll[$dateVal][$counVal]['payUsers']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(9 + $length * 6 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(9 + $length * 6 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['payUsers']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(9 + $length * 7) . '' . $row, $eventAll[$dateVal][$counVal]['firstPay']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(10 + $length * 7 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(10 + $length * 7 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['firstPay']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(10 + $length * 8) . '' . $row, $eventAll[$dateVal][$counVal]['newTotalPay']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(11 + $length * 8 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(11 + $length * 8 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['newTotalPay']);
                }
            }


            $Excel->setCellValue(getNameFromNumber(11 + $length * 9) . '' . $row, $eventAll[$dateVal][$counVal]['filter']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(12 + $length * 9 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(12 + $length * 9 + $i) . '' . $row, intval(str_replace("", "%", $pfData[$dateVal][$counVal][$currPf[$i]]['filter']) . '%'), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                }
            }

            $Excel->setCellValue(getNameFromNumber(12 + $length * 10) . '' . $row, $eventAll[$dateVal][$counVal]['ARPU']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(13 + $length * 10 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(13 + $length * 10 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]]['ARPU']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(13 + $length * 11) . '' . $row, $eventAll[$dateVal][$counVal][1]['rate']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(14 + $length * 11 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(14 + $length * 11 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]][1]['rate'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                }
            }

            $Excel->setCellValue(getNameFromNumber(14 + $length * 12) . '' . $row, $eventAll[$dateVal][$counVal][3]['rate']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(15 + $length * 12 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(15 + $length * 12 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]][3]['rate']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(15 + $length * 13) . '' . $row, $eventAll[$dateVal][$counVal][7]['rate']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(16 + $length * 13 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(16 + $length * 13 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]][7]['rate']);
                }
            }

            $Excel->setCellValue(getNameFromNumber(16 + $length * 14) . '' . $row, $eventAll[$dateVal][$counVal][30]['rate']);
            $length = count($currPf);
            if (in_array('ALL', $currPf)) {
                $Excel->setCellValue(getNameFromNumber(17 + $length * 14 + $i) . '' . $row, '----');
            } else {
                for ($i = 0; $i < $length; $i++) {
                    $Excel->setCellValue(getNameFromNumber(17 + $length * 14 + $i) . '' . $row, $pfData[$dateVal][$counVal][$currPf[$i]][30]['rate']);
                }
            }
//====================================================新增广告费， ROI==============================
            if (in_array('ALL', $currCountry)) {
                $Excel->setCellValue(getNameFromNumber(18 + $length * 14) . '' . $row, $eventAll[$dateVal][$currReferrer]);
                $Excel->setCellValue(getNameFromNumber(19 + $length * 14) . '' . $row, $eventAll[$dateVal]['3roi']);
                $Excel->setCellValue(getNameFromNumber(20 + $length * 14) . '' . $row, $eventAll[$dateVal]['7roi']);
                $Excel->setCellValue(getNameFromNumber(21 + $length * 14) . '' . $row, $eventAll[$dateVal]['30roi']);
            } else {
                $Excel->setCellValue(getNameFromNumber(18 + $length * 14) . '' . $row, '----');
                $Excel->setCellValue(getNameFromNumber(19 + $length * 14) . '' . $row, '----');
                $Excel->setCellValue(getNameFromNumber(20 + $length * 14) . '' . $row, '----');
                $Excel->setCellValue(getNameFromNumber(21 + $length * 14) . '' . $row, '----');
            }


            $Excel->setCellValue(getNameFromNumber(22 + $length * 14) . '' . $row, $num[$dateVal]);
            $row++;
        }
    }
    //filename
    $file_name = '运营数据统计';
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle($file_name);
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    // Redirect output to a client鈥檚 web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename={$file_name}.xls");
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit();
}


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

if ($_REQUEST['event'] == 'user') {

    $sids = implode(',', $selectServerids);
    $whereSql = " where sid in ($sids) ";
    $startDate = substr($_REQUEST['startDate'], 0, 10);
    $sDdate = date('Ymd', strtotime($startDate));
    $endDate = substr($_REQUEST['endDate'], 0, 10);
    $eDate = date('Ymd', strtotime($endDate) + 86400);
    $whereSql .= " and date >=$sDdate and date <= $eDate ";
    $whereSqlNew = " date >=$sDdate and date <= $eDate ";
    if ($currCountry && (!in_array('ALL', $currCountry))) {
        $countries = implode("','", $currCountry);
        $whereSql .= " and country in('$countries') ";
        $whereSqlNew .= " and country in('$countries') ";
    }
    if ($currReferrer && $currReferrer != 'ALL') {
        $whereSql .= " and referrer='$currReferrer' ";
    }

    if (false && $newAnalyse && $_COOKIE['u'] == 'qinbinbin') {
        $eventAll = $dateArray = $countryArray = $pfData = array();
        $dayArr = array(1, 3, 7, 15,30);
        foreach ($dayArr as $day) {
            $rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
        }
        $fields = implode(',', $rfields);

        $sql = "select date,pf,country ,reg,dau,pdau,pdau_move,paytotal,payusers,firstpayusers,newTotalPay ,$fields from stat_allserver.basic_operation
			where $whereSqlNew
			group by date,pf,country order by date desc";
        $result = query_infobright($sql);
        foreach ($result['ret']['data'] as $curRow) {
            $pf = $curRow['pf'];
            $dateIndesx = $curRow['date'];
            $country = $curRow['country'];
            $dateArray[] = $dateIndesx;
            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }
            if (!in_array($country, $countryArray)) {
                $countryArray[] = $country;
            }

            if (in_array('ALL', $currCountry)) {
                $country = '合计';
            }

            $eventAll[$dateIndesx][$country]['reg'] += $curRow['reg'];
            $eventAll[$dateIndesx][$country]['dau'] += $curRow['dau'];//日活跃
            $eventAll[$dateIndesx][$country]['paid_dau'] += $curRow['pdau'];
            $eventAll[$dateIndesx][$country]['pdau_relocation'] += $curRow['pdau_move'];
            $eventAll[$dateIndesx][$country]['sdau'] += $curRow['dau'] - $curRow['reg']; //老玩家 (就是dau-新注册)
            $eventAll[$dateIndesx][$country]['payTotle'] += $row['paytotal'];

            $eventAll[$dateIndesx][$country]['payUsers'] += $row['payusers'];

            $eventAll[$dateIndesx][$country]['firstPay'] += $row['firstpayusers'];
            $eventAll[$dateIndesx][$country]['newTotalPay'] += $row['newTotalPay'];

            foreach ($dayArr as $day) {
                $count = $row['r' . $day] ? $row['r' . $day] : 0;
                $eventAll[$dateIndesx][$country][$day]['count'] += $count;
                if (in_array($pf, $currPf)) {
                    $pfData[$dateIndesx][$country][$pf][$day]['count'] += $count;
                }
            }

            if (in_array($pf, $currPf)) {
                $pfData[$dateIndesx][$country][$pf]['reg'] += $curRow['reg'];
                $pfData[$dateIndesx][$country][$pf]['dau'] += $curRow['dau'];
                $pfData[$dateIndesx][$country][$pf]['paid_dau'] += $curRow['pdau'];
                $pfData[$dateIndesx][$country][$pf]['pdau_relocation'] += $curRow['pdau_move'];
                $pfData[$dateIndesx][$country][$pf]['sdau'] += $curRow['dau'] - $curRow['reg'];

                $pfData[$dateIndesx][$country][$pf]['payTotle'] += $row['paytotal'];

                $pfData[$dateIndesx][$country][$pf]['payUsers'] += $row['payusers'];

                $pfData[$dateIndesx][$country][$pf]['firstPay'] += $row['firstpayusers'];
                $pfData[$dateIndesx][$country][$pf]['newTotalPay'] += $row['newTotalPay'];
            }
            if (in_array('ALL', $currCountry)) {
                unset($countryArray);
                $countryArray[] = '合计';
            }

            foreach ($eventAll as $dateKey => $countryVal) {
                foreach ($countryVal as $countryKey => $value) {
                    $eventAll[$dateKey][$countryKey]['filter'] = intval($eventAll[$dateKey][$countryKey]['payUsers'] * 10000 / $eventAll[$dateKey][$countryKey]['dau']) / 100 . "%";
                    $eventAll[$dateKey][$countryKey]['ARPU'] = intval($eventAll[$dateKey][$countryKey]['payTotle'] * 100 / $eventAll[$dateKey][$countryKey]['payUsers']) / 100;
                    foreach ($currPf as $pfVal) {
                        $pfData[$dateKey][$countryKey][$pfVal]['filter'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['dau']) / 100 . "%";
                        $pfData[$dateKey][$countryKey][$pfVal]['ARPU'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle'] * 100 / $pfData[$dateKey][$countryKey][$pfVal]['payUsers']) / 100;
                    }
                    foreach ($dayArr as $day) {
                        $forDay = (time() - strtotime($dateKey)) / 86400;
                        if ($forDay < $day) {
                            $eventAll[$dateKey][$countryKey][$day]['rate'] = "-";
                        } else {
                            $eventAll[$dateKey][$countryKey][$day]['rate'] = intval($eventAll[$dateKey][$countryKey][$day]['count'] / $eventAll[$dateKey][$countryKey]['reg'] * 10000) / 100 . "%";
                        }

                        foreach ($currPf as $pfVal) {
                            if ($forDay < $day) {
                                $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = "-";
                            } else {
                                $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = intval($pfData[$dateKey][$countryKey][$pfVal][$day]['count'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['reg']) / 100;
                            }
                        }
                    }
                }
            }
        }
    } else
    {


        $sql = "select country,pf,date,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(pdau_relocation) as pdau_relocation from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by country,pf,date ORDER by date ;";
        $result = query_infobright($sql);
        $eventAll = array();
        $pfData = array();
        $dateArray = array();
        $countryArray = array();
        foreach ($result['ret']['data'] as $curRow) {
            $country = strtoupper($curRow['country']);
            $pf = $curRow['pf'];
            $dateIndesx = $curRow['date'];

            if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
                continue;
            }
            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }
            if (!in_array($country, $countryArray)) {
                $countryArray[] = $country;
            }

            if (in_array('ALL', $currCountry)) {
                $country = '合计';
            }

            $eventAll[$dateIndesx][$country]['dau'] += $curRow['s_dau'];//日活跃

            $eventAll[$dateIndesx][$country]['paid_dau'] += $curRow['paid_dau'];
            $eventAll[$dateIndesx][$country]['pdau_relocation'] += $curRow['pdau_relocation'];

            $eventAll[$dateIndesx][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg']; //老玩家 (就是dau-新注册)

            $eventAll[$dateIndesx][$country]['reg'] += $curRow['s_reg'];

            if (in_array($pf, $currPf)) {
                $pfData[$dateIndesx][$country][$pf]['dau'] += $curRow['s_dau'];

                $pfData[$dateIndesx][$country][$pf]['paid_dau'] += $curRow['paid_dau'];
                $pfData[$dateIndesx][$country][$pf]['pdau_relocation'] += $curRow['pdau_relocation'];

                $pfData[$dateIndesx][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

                $pfData[$dateIndesx][$country][$pf]['reg'] += $curRow['s_reg'];
            }
        }
        $sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_referrer_new $whereSql GROUP BY country,pf,date;";
        $result = query_infobright($sql);
        foreach ($result['ret']['data'] as $row) {
            $country = strtoupper($row['country']);
            $pf = $row['pf'];
            $dateIndesx = $row['date'];
            if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
                continue;
            }
            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }
            if (!in_array($country, $countryArray)) {
                $countryArray[] = $country;
            }

            if (in_array('ALL', $currCountry)) {
                $country = '合计';
            }

            $eventAll[$dateIndesx][$country]['payTotle'] += $row['payTotle'];

            $eventAll[$dateIndesx][$country]['payUsers'] += $row['payUsers'];

            $eventAll[$dateIndesx][$country]['firstPay'] += $row['firstPay'];

            if (in_array($pf, $currPf)) {
                $pfData[$dateIndesx][$country][$pf]['payTotle'] += $row['payTotle'];

                $pfData[$dateIndesx][$country][$pf]['payUsers'] += $row['payUsers'];

                $pfData[$dateIndesx][$country][$pf]['firstPay'] += $row['firstPay'];
            }
        }
        $sql = "select country,pf,date,sum(newTotalPay) as newTotalPay from stat_allserver.pay_ratio_analyze_pf_country_referrer_appVersion $whereSql GROUP BY country,pf,date;";
        $result = query_infobright($sql);
        foreach ($result['ret']['data'] as $row) {
            $country = strtoupper($row['country']);
            $pf = $row['pf'];
            $dateIndesx = $row['date'];
            if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
                continue;
            }
            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }
            if (!in_array($country, $countryArray)) {
                $countryArray[] = $country;
            }

            if (in_array('ALL', $currCountry)) {
                $country = '合计';
            }

            $eventAll[$dateIndesx][$country]['newTotalPay'] += $row['newTotalPay'];

            if (in_array($pf, $currPf)) {
                $pfData[$dateIndesx][$country][$pf]['newTotalPay'] += $row['newTotalPay'];
            }
        }

        $dayArr = array(1, 3, 7, 30);
        foreach ($dayArr as $day) {
            $rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
        }
        $fields = implode(',', $rfields);
        $sql = "select country,pf,date,$fields from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion $whereSql and reg_all>0  group by country,pf,date;";
        $ret = query_infobright($sql);
        foreach ($ret['ret']['data'] as $row) {
            $country = strtoupper($row['country']);
            $pf = $row['pf'];
            $dateIndesx = $row['date'];
            if (($pf == 'roll') || ($pf == 'cn1' && ($dateIndesx >= '20160825' && $dateIndesx <= '20160826'))) {
                continue;
            }
            if (!in_array($dateIndesx, $dateArray)) {
                $dateArray[] = $dateIndesx;
            }
            if (!in_array($country, $countryArray)) {
                $countryArray[] = $country;
            }

            if (in_array('ALL', $currCountry)) {
                $country = '合计';
            }

            foreach ($dayArr as $day) {
                $count = $row['r' . $day] ? $row['r' . $day] : 0;
                $eventAll[$dateIndesx][$country][$day]['count'] += $count;
                if (in_array($pf, $currPf)) {
                    $pfData[$dateIndesx][$country][$pf][$day]['count'] += $count;
                }
            }
        }
        if (in_array('ALL', $currCountry)) {
            unset($countryArray);
            $countryArray[] = '合计';
        }
        foreach ($eventAll as $dateKey => $countryVal) {
            foreach ($countryVal as $countryKey => $value) {
                $eventAll[$dateKey][$countryKey]['filter'] = intval($eventAll[$dateKey][$countryKey]['payUsers'] * 10000 / $eventAll[$dateKey][$countryKey]['dau']) / 100 . "%";
                $eventAll[$dateKey][$countryKey]['ARPU'] = intval($eventAll[$dateKey][$countryKey]['payTotle'] * 100 / $eventAll[$dateKey][$countryKey]['payUsers']) / 100;
                foreach ($currPf as $pfVal) {
                    $pfData[$dateKey][$countryKey][$pfVal]['filter'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['dau']) / 100 . "%";
                    $pfData[$dateKey][$countryKey][$pfVal]['ARPU'] = intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle'] * 100 / $pfData[$dateKey][$countryKey][$pfVal]['payUsers']) / 100;
                }
                foreach ($dayArr as $day) {
                    $forDay = (time() - strtotime($dateKey)) / 86400;
                    if ($forDay < $day) {
                        $eventAll[$dateKey][$countryKey][$day]['rate'] = "-";
                    } else {
                        $eventAll[$dateKey][$countryKey][$day]['rate'] = intval($eventAll[$dateKey][$countryKey][$day]['count'] / $eventAll[$dateKey][$countryKey]['reg'] * 10000) / 100 . "%";
                    }

                    foreach ($currPf as $pfVal) {
                        if ($forDay < $day) {
                            $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = "-";
                        } else {
                            $pfData[$dateKey][$countryKey][$pfVal][$day]['rate'] = intval($pfData[$dateKey][$countryKey][$pfVal][$day]['count'] * 10000 / $pfData[$dateKey][$countryKey][$pfVal]['reg']) / 100;
                        }
                    }
                }
            }
        }
//	$currPf=json_encode($currPf);
//	print_r($currPf);
        if (false) {
//=========================================================广告费====================================================

            if (in_array('ALL', $currPf)) {
                $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0) as `ALL` from adcost where date>='$startDate' and date<='$endDate' group by date
				union
				select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0)  as `ALL` from adcost_ios where date>='$startDate' and date<='$endDate' group by date ;";
            } elseif (in_array('market_global', $currPf)) {
                $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0) as `ALL` from adcost where date>='$startDate' and date<='$endDate' group by date";
            } elseif (in_array('AppStore', $currPf)) {
                $costsql = "select date,googleplay as adwords, facebook,netunion, ifnull(googleplay,0)+ifnull(facebook,0)+ifnull(netunion,0)  as `ALL` from adcost_ios where date>='$startDate' and date<='$endDate' group by date";
            }


            $result = $page->globalExecute($costsql, 3, true);
            foreach ($result['ret']['data'] as $row) {
                $dateIndesx = date('Ymd', strtotime($row['date']));
                if (!in_array($dateIndesx, $dateArray)) {
                    $dateArray[] = $dateIndesx;
                }
                $eventAll[$dateIndesx][$currReferrer] += $row[$currReferrer];

//			$pfData[$dateIndesx]['allcost'] += $row['allcost'];
            }
//=========================================================roi====================================================
            $serverList = array_keys($servers);
            $totalReg = $totalIncome = $day3 = $day7 = $day30 = 0;
            $startTime = $_REQUEST['startDate'] ? strtotime($_REQUEST['startDate']) * 1000 : 0;
            $endTime = strtotime($_REQUEST['endDate']) * 1000;
            foreach ($serverList as $server) {
                $sql = "select r.pf pf,sum(p.spend) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,
                date_format(from_unixtime(u.regTime/1000),'%Y-%m-%d') as regDate
                from paylog p inner join stat_reg r on p.uid = r.uid
                inner join userprofile u on p.uid=u.uid
                where u.regTime >= $startTime and u.regTime < $endTime   and r.type=0
                group by pf,regDate,payDate;";
                $result = $page->executeServer($server, $sql, 3);
                foreach ($result['ret']['data'] as $curRow) {
                    $pf = $curRow['pf'];
                    $regDate = $curRow['regDate'];
                    $payDate = $curRow['payDate'];
                    $datetime1 = new DateTime($regDate);
                    $datetime2 = new DateTime($payDate);
                    $interVal = $datetime1->diff($datetime2);

                    $dateIndesx = date('Ymd', strtotime($curRow['regDate']));

                    if (!in_array($dateIndesx, $dateArray)) {
                        $dateArray[] = $dateIndesx;
                    }

                    if ($interVal->format('%a day') <= 3) {
                        $event[$dateIndesx]['3roi'] += $curRow['sum'];
                    }
                    if ($interVal->format('%a day') <= 7) {
                        $event[$dateIndesx]['7roi'] += $curRow['sum'];
                    }
                    if ($interVal->format('%a day') <= 30) {
                        $event[$dateIndesx]['30roi'] += $curRow['sum'];
                    }
                }
            }
            ksort($event);
            foreach ($event as $dateIndesx => $date_value) {
                foreach ($date_value as $xindex => $vinfo) {
                    $forMat = (time() - strtotime($dateIndesx)) / 86400;
                    switch ($xindex) {
                        case '3roi':
                            if ($forMat < 3) {
                                $eventAll[$dateIndesx][$xindex] = "-";
                            } else {
                                $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['3roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                            }
                            break;
                        case '7roi':
                            if ($forMat < 7) {
                                $eventAll[$dateIndesx][$xindex] = "-";
                            } else {
                                $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['7roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                            }
                            break;
                        case '30roi':
                            if ($forMat < 30) {
                                $eventAll[$dateIndesx][$xindex] = "-";
                            } else {
                                $eventAll[$dateIndesx][$xindex] = $eventAll[$dateIndesx][$currReferrer] ? round($event[$dateIndesx]['30roi'] * 100 / $eventAll[$dateIndesx][$currReferrer], 2) . '%' : '-';
                            }
                            break;
                    }
                }
            }
        }

    }
    if ($_REQUEST['type'] == 1) {
        $sql = "INSERT into operation_log (date,logs) VALUES (" . $_REQUEST['datekey'] . "," . "'" . $_REQUEST['num'] . "'" . ") ";
        $sql .= " ON DUPLICATE KEY UPDATE date=" . $_REQUEST['datekey'] . " ,logs=" . "'" . $_REQUEST['num'] . "'";
        $page->globalExecute($sql, 2);
//		$sql = "update operation_log set logs = " . $_REQUEST['num'] . " where date = " . $_REQUEST['datekey'];
//		$page->globalExecute($sql, 2);
    }
    $log_sql = "select * from operation_log where date >=$sDdate and date <= $eDate;";
    $log_ret = $page->globalExecute($log_sql, 3);
    foreach ($log_ret['ret']['data'] as $row) {
        $date = $row['date'];
        $num[$date] = $row['logs'];
    }

    if ($eventAll) {
        rsort($dateArray);
        $showData = true;
    } else {
        $alertHeader = '没有查询到相关数据';
    }

}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>

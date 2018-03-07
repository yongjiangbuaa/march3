<?php
!defined('IN_ADMIN') && exit('Access Denied');
// alter table adcost change ko kr  double(12,2) DEFAULT NULL;
// alter table adcost add column jp double(12,2) DEFAULT NULL;
// alter table adcost add column th double(12,2) DEFAULT NULL;
// alter table adcost add column ru double(12,2) DEFAULT NULL;
//$developer = in_array($adminid,array('liuyi','yaoduo'));
$developer = true;
if(!$_REQUEST['startDate']){
    $startDate = date("Y-m-d 00:00",time()-86400*14);
}else{
    $startDate = $_REQUEST['startDate'];
}
if(!$_REQUEST['endDate']){
    $endDate = date("Y-m-d 23:59",time());
}else{
    $endDate = $_REQUEST['endDate'];
}
$referrerList_own = array(
	'ALL'=>'ALL',
	'googleplay' => 'GoogleAdwords',
	'facebook' => 'Facebook',
	'netunion'=>'netunion',
    'nature'=>'nature',
);
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

// foreach ($pfList as $pf => $pfdisp){
//     if($pf==roll){
//         continue;
//     }
//     $flag = ($seletedpf==$pf)?'selected="selected"':'';
//     $pfOptions .= "<option id={$pf} value='{$pf}' $flag>{$pfdisp}</option>";
// }
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

$ct_field_list = array(
	'googleplay' => 'GoogleAdwords',
	'facebook' => 'Facebook',
	'netunion'=>'netunion',
);
$fcnt = count($ct_field_list);
$fields = implode(',', array_keys($ct_field_list));
$sumfields=array();
$title = array();
foreach ($ct_field_list as $k=>$v) {
	$sumfields[] = "ifnull($k,0)";
	$title[] = "<td>$v</td>";
}
$sum = implode('+', $sumfields);
$titles = implode('', $title);

//修改广告花费
if($_REQUEST['modifyAD'] == 'get'){
	$phonetype = $_REQUEST['phonetype'];

	$startDateYMD = substr($startDate, 0, 10);
	$endDateYMD = substr($endDate, 0, 10);

	if($phonetype == 1){ //安卓
		$tip = "android";
		$costsql = "select date, $fields, $sum as allcost from adcost where date>='$startDateYMD' and date<='$endDateYMD' order by date";
	}elseif($phonetype == 2){ //ios
		$tip="ios";
		$costsql = "select date, $fields, $sum as allcost from adcost_ios where date>='$startDateYMD' and date<='$endDateYMD' order by date";
	}

	$result = $page->globalExecute($costsql, 3, true);
	$adCostDB = $result['ret']['data'];
	foreach ($adCostDB as $record){
		$adCostTime[$record['date']] += $record['allcost'];
		foreach (array_keys($ct_field_list) as $c) {
			$adCostTimeCountry[$c][$record['date']] += $record[$c];//
		}
	}

    $html = "<table class='listTable' cellspacing=1 padding=0 style=' text-align: center'><form>";
    $html .= "<tr><td>日期</td><td>费用</td>$titles</tr>";
    foreach ($adCostTime as $keyvalue=>$costall){
        $html .='<tr><td>'.$keyvalue.'</td><td>'.$costall.'</td>';
        foreach ($ct_field_list as $k=>$v) {
            $html .='<td><input class="input-small datekey" name="key_'.$k.'_'.$keyvalue.'" value="'.$adCostTimeCountry[$k][$keyvalue].'" /></td>';
        }
        $html .='</tr>';
        $lastdate = $keyvalue;
    }
    if (empty($lastdate)) {
        $lastdate = date('Y-m-d',strtotime('-30 day'));
    }
    $count = 0;
    while(date('Y-m-d') != $lastdate){
        $lastdate = date('Y-m-d',strtotime($lastdate)+86400);
        $html .='<tr><td>'.$lastdate.'</td><td>尚未填写</td>';
        foreach ($ct_field_list as $k=>$v) {
            $html .='<td><input class="input-small datekey" name="key_'.$k.'_'.$lastdate.'" value="" /></td>';
        }
        $html .='</tr>';
        if(++$count >= 30)
            break;
    }
	if($phonetype == 1){//android
		$html .= '<tr><td colspan="2"><input type="button" onclick="cancelModify();" class="btn" value="取消" /></td><td colspan="'.$fcnt.'"><input type="button" class="btn btn-info" onclick="submitModify(1);"  value="提交" /></td></tr></table></form>';
	}elseif($phonetype == 2) {//ios
		$html .= '<tr><td colspan="2"><input type="button" onclick="cancelModify();" class="btn" value="取消" /></td><td colspan="' . $fcnt . '"><input type="button" class="btn btn-info" onclick="submitModify(2);"  value="提交" /></td></tr></table></form>';
	}

    echo $html;
    exit();
}
//提交修改
if($_REQUEST['modifyAD'] == 'modify'){
	$type = $_REQUEST['type'];
	switch ($type)
	{
		case 1:
			$table = 'adcost';
			break;
		case 2:
			$table = 'adcost_ios';
			break;
		default:
			return;
	}
    $param = $_POST;//key_facebook_2016-07-17
    foreach ($param as $key=>$value){
        if(strpos($key, 'key_') !== false && is_numeric($value)){
            $tokens = explode('_', $key, 3);
            $date = $tokens[2];
            $pf = $tokens[1];
            $adCostNew[$date][$pf] = $value;
        }
    }
    file_put_contents(ADMIN_ROOT.'/adcost_sql.txt', date('Y-m-d H:i:s')."\n".print_r($adCostNew,true)."\n", FILE_APPEND);
    foreach ($adCostNew as $date=>$fieldvalue){
        $str = "'$date'";
        $str .= ','. join(',', $fieldvalue);
        $fields = 'date';
        $fields .= ','. join(',', array_keys($fieldvalue));
        $insertSql = "INSERT into ".$table."($fields) VALUES "." ({$str}) ";
        $updKv = buildUpdateSql($fieldvalue);
        $ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
        $insertSql .= " $ondup;";
        $resultmody = $page->globalExecute($insertSql, 2);
	    if($resultmody['error']){
		   $tip = 'fail';
	    }
    }

	return $tip;

}
//查询ROI
if (isset($_REQUEST['getData'])) {
	$startDateYMD = substr($startDate, 0, 10);
	$endDateYMD = substr($endDate, 0, 10);

	echo "pf".$seletedpf."----referrer".$selectReferrer;
	echo "<br/>";
	//新增渠道查询,只有GoogleAdwords 和Facebook  2个渠道
	if($selectReferrer == 'facebook'){
		$ct_field_list = array('facebook' => 'Facebook');
	}elseif($selectReferrer == 'googleplay'){
		$ct_field_list = array('googleplay' => 'GoogleAdwords');
	}elseif($selectReferrer == 'netunion'){
		$ct_field_list = array('netunion' => 'netunion');
	}
	$fcnt = count($ct_field_list);
	$fields = implode(',', array_keys($ct_field_list));
	$sumfields=array();
	foreach ($ct_field_list as $k=>$v) {
		$sumfields[] = "ifnull($k,0)";
		$title[] = "<td>$v</td>";
	}
	$sum = implode('+', $sumfields);
	$titles = implode('', $title);
	//新增平台查询
    if($selectReferrer != 'nature'){
        if ($seletedpf == 'ALL') {
            $costsql = "select date,  $sum as allcost from adcost where date>='$startDateYMD' and date<='$endDateYMD' UNION ALL select date, $sum as allcost from adcost_ios where date>='$startDateYMD' and date<='$endDateYMD' order by date";

        } elseif ($seletedpf == 'AppStore') {
            $costsql = "select date,  $sum as allcost from adcost_ios where date>='$startDateYMD' and date<='$endDateYMD' order by date";
        } elseif ($seletedpf == 'market_global') {
            $costsql = "select date, $sum as allcost from adcost where date>='$startDateYMD' and date<='$endDateYMD' order by date";
        }
//	echo "$costsql";
        $result = $page->globalExecute($costsql, 3, true);
        $adCostDB = $result['ret']['data'];
        foreach ($adCostDB as $record) {
            $adCostTime[$record['date']] += $record['allcost'];

        }
    }

    $whereSql='1=1';
    $whereSql2='1=1';

    if (!empty($seletedpf) && $seletedpf != 'ALL') {
	    $whereSql .= " and pf='$seletedpf' ";
	    $whereSql2 .= " and r.pf='$seletedpf'";
	    //$serverList为空时用到   //stat_roi_pf_country_v2   //stat_roi_pf_country_reg 没有referrer字段
	    $whereSql_else .= " and pf='$seletedpf' ";
	    $whereSql_else2 .= " and r.pf='$seletedpf'";
    }
    if (!empty($selectReferrer) && $selectReferrer != 'ALL') {
		if($selectReferrer == 'facebook'){
			$whereSql .= " and referrer='$selectReferrer' ";
			$whereSql2 .= " and r.referrer='$selectReferrer'";
		}elseif($selectReferrer == 'googleplay'){
			$whereSql .= " and (referrer='adwords' or referrer='googlesearch' or referrer='uac' ) ";
			$whereSql2 .= " and (r.referrer='adwords' or r.referrer='googlesearch' or r.referrer='uac' )";
		}elseif($selectReferrer == 'netunion'){
			$whereSql .= " and referrer !='adwords' and referrer != 'facebook' and referrer != 'googlesearch' and referrer != 'uac' and referrer is not NULL and referrer != '' and referrer != 'organic' ";
			$whereSql2 .= " and r.referrer !='adwords' and r.referrer != 'facebook' and r.referrer != 'googlesearch' and r.referrer != 'uac' and r.referrer is not NULL and r.referrer != '' and r.referrer != 'organic' ";
		}elseif( $selectReferrer == 'nature'){
            $whereSql .=" and  (referrer is NULL or referrer ='' or referrer='Organic') ";
            $whereSql2 .=" and  (r.referrer is NULL or r.referrer ='' or r.referrer='Organic') ";
        }
    }else{
        if($selectReferrer == 'ALL'){
            $whereSql .=" and  referrer is not NULL and referrer !='' and referrer!='Organic' ";
            $whereSql2 .=" and  r.referrer is not NULL and r.referrer !='' and r.referrer!='Organic' ";
        }
    }

	$serverList = array();
	$pserverList = $_REQUEST['serverList'];
	if(!empty($pserverList)){
    	$t1 = explode(',', $pserverList);
    	foreach ($t1 as $tt) {
    	    $t2 = explode('-', $tt);
    	    if (count($t2) > 1) {
    	        for ($i = $t2[0]; $i <= $t2[1]; $i++) {
    	            $serverList[] =  's' . $i;
    	        }
    	    }else{
    	        $serverList[] = 's' . $t2[0];
    	    }
    	}
	}else{
        $serverList = array_keys($servers);
    }
    $eventAll = array();
    if (!empty($serverList)){
        $totalReg =	$totalIncome = $today = $day3 = $day7 =$day15=$day30 =0;
        $startTime = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
        $endTime  = strtotime($_REQUEST['endDate'])*1000;
       $nameLink = array('date'=>'ROI','reg'=>'注册人数','ad'=>'广告费','today'=>'当日付费','today_all'=>'人数','3day'=>'3日付费','3_all'=>'人数','3roi'=>'3日ROI',
            '7day'=>'7日付费','7_all'=>'人数','7roi'=>'7日ROI','15day'=>'15日付费','15_all'=>'人数','15roi'=>'15日ROI','30day'=>'30日付费','30_all'=>'人数','30roi'=>'30日ROI','allday'=>'总付费','allroi'=>'总转化率','realROI'=>'净ROI');
        foreach ($serverList as $server){
            $sql1 = "select count(1) sum,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regDate from stat_reg where type=0 and time > $startTime and time < $endTime and $whereSql group by regDate";
            $result = $page->executeServer($server,$sql1,3);
            if(is_array($result['ret']['data'])){
                foreach ($result['ret']['data'] as $key=>$curRow){
                    $yindex = $curRow['regDate'];
                    $eventAll[$yindex]['reg'] += $curRow['sum'];
                    $eventAll[$yindex]['date'] = $yindex;
                    $totalReg += $curRow['sum'];
                }
            }

            $sql2 = "select sum(p.spend) sum,count(p.uid) pay_number,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,date_format(from_unixtime(u.regTime/1000),'%Y-%m-%d') as regDate from paylog p inner join stat_reg r on p.uid = r.uid inner join userprofile u on p.uid=u.uid where u.regTime >= $startTime and u.regTime < $endTime and $whereSql2 and r.type=0 group by regDate,payDate order by p.time asc;";
            $result = $page->executeServer($server,$sql2,3);
// 	        if (count($result['ret']['data'])>0){
// 	        		echo $sql;
// 	        		echo $server."\n";
// 	        }
	        foreach ($result['ret']['data'] as $curRow){
		        	$regDate=$curRow['regDate'];
		        	$payDate=$curRow['payDate'];
		        	
		        	$datetime1 = new DateTime($regDate);
		        	$datetime2 = new DateTime($payDate);
		        	$interVal = $datetime1->diff($datetime2);
		        	
		        	$newdate = date('Y-m-d',strtotime($curRow['regDate']));
		        	if($interVal->format('%a day')==0){
		        		$eventAll[$newdate]['today']+=$curRow['sum'];
		        		$eventAll[$newdate]['today_all']+=$curRow['pay_number'];
		        	}
		        	if ($interVal->format('%a day')<=3){
		        		$eventAll[$newdate]['3day']+=$curRow['sum'];
		        		$eventAll[$newdate]['3_all']+=$curRow['pay_number'];
		        	}
		        	if ($interVal->format('%a day')<=7){
		        		$eventAll[$newdate]['7day']+=$curRow['sum'];
		        		$eventAll[$newdate]['7_all']+=$curRow['pay_number'];
		        	}
		        	if ($interVal->format('%a day')<=15){
		        		$eventAll[$newdate]['15day']+=$curRow['sum'];
		        		$eventAll[$newdate]['15_all']+=$curRow['pay_number'];
		        	}
		        	if ($interVal->format('%a day')<=30){
		        		$eventAll[$newdate]['30day']+=$curRow['sum'];
		        		$eventAll[$newdate]['30_all']+=$curRow['pay_number'];
		        	}
		        	$eventAll[$newdate]['allday']+=$curRow['sum'];
		        	$eventAll[$newdate]['date']=$newdate;
	        }
        }
        ksort($eventAll);
        foreach ($eventAll as $yindex=>$value){
            foreach ($nameLink as $xindex=>$vinfo){
                switch ($xindex){
                    case 'ad':$eventAll[$yindex][$xindex] = $adCostTime[$yindex]?$adCostTime[$yindex]:'-';
                        break;
                    case 'today':
                        $today += $eventAll[$yindex]['today'];
                        break;
                    case '3day':
                        $day3 += $eventAll[$yindex]['3day'];
                        break;
                    case '3roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['3day']/$eventAll[$yindex]['ad'],2) : '-';
                        break;
                    case '7day':
                        $day7 += $eventAll[$yindex]['7day'];
                        break;
                    case '7roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['7day']/$eventAll[$yindex]['ad'],2) : '-';
                        break;
                    case '15day':
                        $day15 += $eventAll[$yindex]['15day'];
                        break;
                    case '15roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['15day']/$eventAll[$yindex]['ad'],2) : '-';
                        break;
                    case '30day':
                        $day30 += $eventAll[$yindex]['30day'];
                        break;
                    case '30roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['30day']/$eventAll[$yindex]['ad'],2) : '-';
                        break;
                    case 'allday':
                        $totalIncome += $eventAll[$yindex]['allday'];
                        break;
                    case 'allroi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['allday']/$eventAll[$yindex]['ad'],2) : '-';
                        break;
                    case 'realROI':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad']!='-' ? round($eventAll[$yindex]['allday'] * 0.7 /$eventAll[$yindex]['ad'],2) : '-';
                        break;
                }
            }
        }
        ksort($eventAll);
        $eventAll['sum']['date'] = '总计';
        $eventAll['sum']['reg'] = $totalReg;
        $eventAll['sum']['ad'] = array_sum($adCostTime);
        $eventAll['sum']['today'] = $today;
        $eventAll['sum']['3day'] = $day3;
        $eventAll['sum']['3roi'] = round($day3/$eventAll['sum']['ad'],2);
        $eventAll['sum']['7day'] = $day7;
        $eventAll['sum']['7roi'] = round($day7/$eventAll['sum']['ad'],2);
        $eventAll['sum']['15day'] = $day15;
        $eventAll['sum']['15roi'] = round($day15/$eventAll['sum']['ad'],2);
        $eventAll['sum']['30day'] = $day30;
        $eventAll['sum']['30roi'] = round($day30/$eventAll['sum']['ad'],2);
        $eventAll['sum']['allday'] = $totalIncome;
        $eventAll['sum']['allroi'] = round($totalIncome/$eventAll['sum']['ad'],2);
        $eventAll['sum']['realROI'] = round($totalIncome*0.7/$eventAll['sum']['ad'],2);
        $sumArr = array_pop($eventAll);
        array_unshift($eventAll, $sumArr);
        printStat2($eventAll,$nameLink,$nameLinkSort,$hightLight);
        exit;
    }
    else {
	    exit();
	    //stat_roi_pf_country_v3  v2数据已不准,每次叠加,重跑数据会叠加
        $totalReg =	$totalIncome = $today = $day3 = $day7 =$day15=$day30 =0;
        $startTime = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
        $endTime  = strtotime($_REQUEST['endDate'])*1000;
        $startTimeSql = date('Ymd',$startTime/1000);
        $endTimeSql = date('Ymd',$endTime/1000);
        $nameLink = array('date'=>'ROI','reg'=>'注册人数','ad'=>'广告费','today'=>'当日付费','today_all'=>'人数','3day'=>'3日付费','3_all'=>'人数','3roi'=>'3日ROI',
            '7day'=>'7日付费','7_all'=>'人数','7roi'=>'7日ROI','15day'=>'15日付费','15_all'=>'人数','15roi'=>'15日ROI','30day'=>'30日付费','30_all'=>'人数','30roi'=>'30日ROI','allday'=>'总付费','allroi'=>'总转化率','realROI'=>'净ROI');
        //if ($_COOKIE ['u'] == 'yd'){
        	//分平台的
        		$sql = "select regDate,payDate,sum(spendSum) spendSum,sum(pay_number) pay_number from stat_allserver.stat_roi_pf_country_v3 where regDate >= $startTimeSql and regDate <= $endTimeSql  and $whereSql_else group by regDate,payDate;";
       // }else {
	   //     $sql = "select regDate,payDate,sum(spendSum) spendSum from stat_allserver.stat_roi_pf_country_v3 where regDate >= $startTimeSql and regDate <= $endTimeSql  and $whereSql group by regDate,payDate;";
       // }
        $ret = query_infobright($sql);
        $eventAll = array();
        foreach ($ret['ret']['data'] as $curRow){
        		$regDate=$curRow['regDate'];
        		$payDate=$curRow['payDate'];
        		$newdate = date('Y-m-d',strtotime($curRow['regDate']));
        		if($regDate==$payDate){
        			$eventAll[$newdate]['today']+=$curRow['spendSum'];
        			$eventAll[$newdate]['today_all']+=$curRow['pay_number'];
        		}
        		if ((strtotime($payDate)-strtotime($regDate))/86400<=3){
        			$eventAll[$newdate]['3day']+=$curRow['spendSum'];
        			$eventAll[$newdate]['3_all']+=$curRow['pay_number'];
        		}
        		if ((strtotime($payDate)-strtotime($regDate))/86400<=7){
        			$eventAll[$newdate]['7day']+=$curRow['spendSum'];
        			$eventAll[$newdate]['7_all']+=$curRow['pay_number'];
        		}
        		if ((strtotime($payDate)-strtotime($regDate))/86400<=15){
        			$eventAll[$newdate]['15day']+=$curRow['spendSum'];
        			$eventAll[$newdate]['15_all']+=$curRow['pay_number'];
        		}
        		if ((strtotime($payDate)-strtotime($regDate))/86400<=30){
        			$eventAll[$newdate]['30day']+=$curRow['spendSum'];
        			$eventAll[$newdate]['30_all']+=$curRow['pay_number'];
        		}
        		$eventAll[$newdate]['allday']+=$curRow['spendSum'];
        		$eventAll[$newdate]['all_number']+=$curRow['pay_number'];
        		$eventAll[$newdate]['date']=$newdate;
        }
        
        $sql= "select regDate,sum(reg) reg from stat_allserver.stat_roi_pf_country_reg where regDate >= $startTimeSql and regDate <= $endTimeSql  and $whereSql_else group by regDate;";
        $ret = query_infobright($sql);
        foreach ($ret['ret']['data'] as $curRow){
        		$newdate = date('Y-m-d',strtotime($curRow['regDate']));
	        	$eventAll[$newdate]['reg'] += $curRow['reg'];
	        	$totalReg += $curRow['reg'];

        }
        
//         $sql = "select date, sum(reg) as reg, sum(today) as today, sum(3day) as 3day, sum(7day) as 7day, sum(15day) as 15day, sum(30day) as 30day, sum(allday) as allday from stat_allserver.stat_roi_pf_country where date >= $startTimeSql and date <= $endTimeSql  and $whereSql group by date ;";
//         $ret = query_infobright($sql);
//         $eventAll = array();
//         foreach ($ret['ret']['data'] as $curRow){
//             $newdate = date('Y-m-d',strtotime($curRow['date']));
//             $curRow['date'] = $newdate;
//             $eventAll[$newdate] = $curRow;
//             $totalReg += $curRow['reg'];
//         }
        ksort($eventAll);
        foreach ($eventAll as $yindex=>$value){
            foreach ($nameLink as $xindex=>$vinfo){
                switch ($xindex){
                    case 'ad':$eventAll[$yindex][$xindex] = $adCostTime[$yindex];
                        break;
                    case 'today':
                        $today += $eventAll[$yindex]['today'];
                        break;
                    case '3day':
                        $day3 += $eventAll[$yindex]['3day'];
                        break;
                    case '3roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['3day']/$eventAll[$yindex]['ad'],2) : '无';
                        break;
                    case '7day':
                        $day7 += $eventAll[$yindex]['7day'];
                        break;
                    case '7roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['7day']/$eventAll[$yindex]['ad'],2) : '无';
                        break;
                    case '15day':
                        $day15 += $eventAll[$yindex]['15day'];
                        break;
                    case '15roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['15day']/$eventAll[$yindex]['ad'],2) : '无';
                        break;
                    case '30day':
                        $day30 += $eventAll[$yindex]['30day'];
                        break;
                    case '30roi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['30day']/$eventAll[$yindex]['ad'],2) : '无';
                        break;
                    case 'allday':
                        $totalIncome += $eventAll[$yindex]['allday'];
                        break;
                    case 'allroi':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['allday']/$eventAll[$yindex]['ad'],2) : '无';
                        break;
                    case 'realROI':
                        $eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['allday'] * 0.7 /$eventAll[$yindex]['ad'],2) : '无';
                        break;
                }
            }
        }
        $eventAll['sum']['date'] = '总计';
        $eventAll['sum']['reg'] = $totalReg;
        $eventAll['sum']['ad'] = array_sum($adCostTime);
        $eventAll['sum']['today'] = $today;
        $eventAll['sum']['3day'] = $day3;
        $eventAll['sum']['3roi'] = round($day3/$eventAll['sum']['ad'],2);
        $eventAll['sum']['7day'] = $day7;
        $eventAll['sum']['7roi'] = round($day7/$eventAll['sum']['ad'],2);
        $eventAll['sum']['15day'] = $day15;
        $eventAll['sum']['15roi'] = round($day15/$eventAll['sum']['ad'],2);
        $eventAll['sum']['30day'] = $day30;
        $eventAll['sum']['30roi'] = round($day30/$eventAll['sum']['ad'],2);
        $eventAll['sum']['allday'] = $totalIncome;
        $eventAll['sum']['allroi'] = round($totalIncome/$eventAll['sum']['ad'],2);
        $eventAll['sum']['realROI'] = round($totalIncome*0.7/$eventAll['sum']['ad'],2);
        $sumArr = array_pop($eventAll);
        array_unshift($eventAll, $sumArr);
        printStat2($eventAll,$nameLink,$nameLinkSort,$hightLight);
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
?>
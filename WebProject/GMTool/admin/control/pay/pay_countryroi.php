<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d 00:00",time()-86400*4);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59",time());




if($_REQUEST['getCountry']){
    $optionStr = '<option value="">请先选择国家！</option>';
    $sql = "SELECT DISTINCT(country) country from stat_reg";
    $ret = $page->execute($sql, 3);
    foreach ($ret['ret']['data'] as $item){
        $value = $item['country'] ? $item['country'] :'未统计到国家';
        $optionStr.="<option value='$value'>$value</option>";
    }
    exit($optionStr);
}




//adCost
if(FALSE && file_exists(ADMIN_ROOT.'/ROI/adcost.txt')){
	$adCost = array();
	$costStr = file_get_contents(ADMIN_ROOT.'/adcost.txt');
	$adCost = json_decode($costStr,true);
	$totalAd = array_sum($adCost);
	$adCostTime = array();
	$sTime = $_REQUEST['startDate'] ? strtotime($_REQUEST['startDate']) : strtotime($startDate);
	foreach ($adCost as $key=>$value){
		if(strtotime($key) < $sTime){
			continue;
		}
		$adCostTime[$key] = $value;
	}
}
if($_REQUEST['modifyAD'] == 'get'){
	$html = "<table class='listTable' cellspacing=1 padding=0 style=' text-align: center'><form>";
	$html .= '<tr><td>日期</td><td>费用</td><td>修改</td></tr>';
	foreach ($adCostTime as $key=>$value){
		$html .='<tr><td>'.$key.'</td><td>'.$value.'</td><td><input class="input-small datekey" name="key_'.$key.'" value="'.$value.'" /></td></tr>';
	}
	$lastTime = strtotime(end($adCost));
	$count = 0;
	while(date('Y-m-d') != $key){
		$key = date('Y-m-d',strtotime($key)+86400);
		$html .='<tr><td>'.$key.'</td><td>尚未填写</td><td><input class="input-small datekey" name="key_'.$key.'" value="" /></td></tr>';
		if(++$count >= 30)
			break;
	}
	$html .= '<tr><td><input type="button" onclick="cancelModify();" class="btn" value="取消" /></td><td></td><td><input type="button" class="btn btn-info" onclick="submitModify();"  value="提交" /></td></tr></table></form>';
	echo $html;
	exit();
}
if($_REQUEST['modifyAD'] == 'modify'){
	$param = $_POST;
	foreach ($param as $key=>$value){
		if(strpos($key, 'key_') !== false && is_numeric($value)){
			$realKey = substr($key, 4);
			$adCost[$realKey] = $value;
		}
	}
	file_put_contents(ADMIN_ROOT.'/adcost.txt', json_encode($adCost));
}
if (isset($_REQUEST['getData'])) {
	$totalReg =	$totalIncome = $today = $day3 = $day7 =$day15=$day30 =0;
	$startTime = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
	$endTime  = strtotime($_REQUEST['endDate'])*1000;

	$nameLink = array('date'=>'ROI','reg'=>'注册人数','ad'=>'广告费','today'=>'当日付费','3day'=>'3日付费','3roi'=>'3日ROI',
			'7day'=>'7日付费','7roi'=>'7日ROI','15day'=>'15日付费','15roi'=>'15日ROI','30day'=>'30日付费','30roi'=>'30日ROI','allday'=>'总付费','allroi'=>'总转化率','realROI'=>'净ROI');
	foreach ($servers as $server=>$serverInfo){
		$sql = "select count(1) sum,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regDate from stat_reg where time > $startTime and time < $endTime group by regDate";
		$result = $page->executeServer($server,$sql,3);
		if(is_array($result['ret']['data'])){
			foreach ($result['ret']['data'] as $key=>$curRow){
				$yindex = $curRow['regDate'];
				$eventAll[$yindex]['reg'] += $curRow['sum'];
				$eventAll[$yindex]['date'] = $yindex;
				$totalReg += $curRow['sum'];
			}
		}
		$sql = "select sum(p.spend) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,
			date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regDate from paylog p inner join 
			stat_reg r on p.uid = r.uid where r.time > $startTime and r.time < $endTime 
		 	group by regDate,payDate order by p.time asc";
		$result = $page->executeServer($server,$sql,3);
		if(is_array($result['ret']['data'])){
			foreach ($result['ret']['data'] as $curRow){
				$eventAll[$curRow['regDate']][$curRow['payDate']] += $curRow['sum'];
			}
		}
	}
	function getSumPayDay($info,$today,$num){
		$num = min($num,count($info));
		$sum =  $info[$today];
		$timestamp = strtotime($today);
		for($i=1;$i<$num;$i++){
			$key = date('Y-m-d',$timestamp + 3600 * 24 *$i);
			$sum += $info[$key];
		}
		return $sum;
	}
	foreach ($eventAll as $yindex=>$value){
		foreach ($nameLink as $xindex=>$vinfo){
			switch ($xindex){
				case 'ad':$eventAll[$yindex][$xindex] = $adCost[$yindex];
				break;
				case 'today':
					$eventAll[$yindex][$xindex] = $eventAll[$yindex][$yindex];
					$today += $eventAll[$yindex]['today'];
				break;
				case '3day':
					$eventAll[$yindex][$xindex] = getSumPayDay($eventAll[$yindex],$yindex,3);
					$day3 += $eventAll[$yindex]['3day'];
				break;
				case '3roi':$eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['3day']/$eventAll[$yindex]['ad'],2) : '无';
				break;
				case '7day':
					$eventAll[$yindex][$xindex] = getSumPayDay($eventAll[$yindex],$yindex,7);
					$day7 += $eventAll[$yindex]['7day'];
				break;
				case '7roi':$eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['7day']/$eventAll[$yindex]['ad'],2) : '无';
				break;
				case '15day':
					$eventAll[$yindex][$xindex] = getSumPayDay($eventAll[$yindex],$yindex,15);
					$day15 += $eventAll[$yindex]['15day'];
				break;
				case '15roi':
					$eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['15day']/$eventAll[$yindex]['ad'],2) : '无';
					break;
				case '30day':
					$eventAll[$yindex][$xindex] = getSumPayDay($eventAll[$yindex],$yindex,30);
					$day30 += $eventAll[$yindex]['30day'];
				break;
				case '30roi':$eventAll[$yindex][$xindex] = $eventAll[$yindex]['ad'] ? round($eventAll[$yindex]['30day']/$eventAll[$yindex]['ad'],2) : '无';
				break;
				case 'allday':
					$eventAll[$yindex][$xindex] = getSumPayDay($eventAll[$yindex],$yindex,365);
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
	$eventAll['sum']['realROI'] = round($totalIncome/$totalAd,2);
	$sumArr = array_pop($eventAll);
	array_unshift($eventAll, $sumArr);
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
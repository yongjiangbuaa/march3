<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d 00:00",time()-86400*8);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59",time());
if (!$_REQUEST['selectCountry']) {
	$currCountry = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectReferrer']) {
	$currReferrer = 'ALL';
}else{
	$currReferrer = $_REQUEST['selectReferrer'];
}
$seletedpf = $_REQUEST['platForm'];
/* $optionsArr = array(
		'all'=>'--ALL--',
		'google' => 'GooglePlay',
		'ios' => 'AppStore',
		'amazon' => 'amazon',
		'tstore' => 'tstore',
		'nstore' => 'nstore',
		'elex337mobile' => 'elex337mobile',
		'elex337web' => 'elex337web',
		'facebook' => 'facebook',
); */
$pf_pay_map = array(
		'market_global' =>'google',
		'AppStore' =>'ios',
);

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];


foreach ($pfList as $pf => $pfdisp){
	if($pf==roll){
		continue;
	}
	$flag = ($seletedpf==$pf)?'selected="selected"':'';
	$pfOptions .= "<option id={$pf} value='{$pf}' $flag>{$pfdisp}</option>";
}
if (isset($_REQUEST['getData'])) {
	$startTime = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
	$endTime  = strtotime($_REQUEST['endDate'])*1000;

	$nameLink['time'] = '';
	$eventAll['reg']['time'] = "注册人数";
// 	$eventAll['ad']['time'] = "广告花费";
	$eventAll['sum']['time'] = "总计";
	$nameLinkSort = array_keys($nameLink);
	$namLinkSortEnd = 10000;//用于表头排序
	//预先生成yindex
	$days = ceil((time() - $startTime/1000)/86400); //每次都是到今天
	$dayIndex = 0;
	while(++$dayIndex<=$days){
		if($dayIndex == 1)
			$eventAll[$dayIndex]['time'] = "当天";
		else
			$eventAll[$dayIndex]['time'] = "第{$dayIndex}天";
	}
	
	$whereSql='';
	$whereSql2='';
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
		$whereSql2 .=" and r.country='$currCountry' ";
	}
	if (!empty($seletedpf) && $seletedpf!='ALL') {
		$whereSql .= "and pf='$seletedpf' ";
		$whereSql2 .= "and r.pf='$seletedpf'";
	}elseif ($seletedpf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
		$whereSql2 .= " and r.pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if($currReferrer&&$currReferrer!='ALL'){
		if($currReferrer=="nature"){
			$whereSql .="and (referrer='' or referrer is null or referrer='Organic')";
			$whereSql2 .="and (r.referrer='' or r.referrer is null or r.referrer='Organic')";
		}else {
			$whereSql .= " and referrer='$currReferrer' ";
			$whereSql2 .= " and r.referrer='$currReferrer' ";
		}
	}
	//=========================================================
	if (isset($_REQUEST['clickDisplay'])) {
		$click = $_REQUEST['serverDate'];
		$vector = explode("_",$click);
		$time1 = strtotime($vector[0])*1000;
		$m_endtime = $time1 + $vector[1]*86400000; //[start,end)
		$m_starttime = $m_endtime-86400000;

		$m_regtime = $time1 + 86400000;//只有统计库才有date字段 [start ,regtime)
		$sql = "select p.productId ,count(1) num from paylog p INNER join stat_reg r on p.uid=r.uid WHERE r.time > $time1 and r.time < $m_regtime  $whereSql2 and p.time >= $m_starttime and p.time < $m_endtime  group by productId ASC ;";
		$cot=array();
		foreach ($selectServer as $server=>$serInfo){
//		echo "进来了<br />";
//			echo $server."<br/>";
			$displayResult = $page->executeServer($server, $sql, 3);
//			echo $sql."<br/>";
//			print_r($displayResult);
//			echo "<br/>";
//exit();
			foreach ($displayResult['ret']['data'] as $disRow){
				if(array_key_exists($disRow['productId'],$cot)){ //array_key_exists不能用这个
					$cot[$disRow['productId']] += $disRow['num'];
				}
				else {
					$cot[$disRow['productId']] = $disRow['num'];
				}
			}
		}
		ksort($cot);

		$disHtml = "服务器:$displayServer&nbsp;&nbsp;日期:$vector[0]<div><table class='listTable' style='text-align:center'><thead>";
		$disHtml .="<th>Id</th><th>Name</th><th>单价</th><th>次数</th><th>总额</th></thead>";
		foreach ($cot as $id=>$idValue){

			$tot= $exchangeName[$id][1]*$idValue;
			$disHtml .="<tr><td>$id</td><td>".$exchangeName[$id][0]."</td><td>".$exchangeName[$id][1]."</td><td>$idValue</td><td>$tot</td></tr>";
		}
		$disHtml .="</table></div>";
		echo $disHtml;
		exit();



	exit(print_r($cot));
	}
	//========================================================
	foreach ($selectServer as $server=>$serInfo){
		$sql = "select count(1) sum,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regDate from stat_reg where time > $startTime and time < $endTime $whereSql group by regDate";
		$result = $page->executeServer($server, $sql, 3);
		//echo $sql;
		if(is_array($result['ret']['data'])){
			foreach ($result['ret']['data'] as $key=>$curRow){
				$xindex = $curRow['regDate'];
				$nameLink[$xindex] = $xindex;
				$nameLinkSort[$namLinkSortEnd - ($endTime/1000 - strtotime($xindex))/86400] = $xindex;
				$eventAll['reg'][$xindex] += $curRow['sum'];
			}
		}
		
		$sql = "select sum(p.spend) sum,count(distinct(r.uid)) users,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regDate from paylog p inner join stat_reg r on p.uid = r.uid where r.time > $startTime and r.time < $endTime $whereSql2 group by regDate,payDate order by p.time asc";
		$result = $page->executeServer($server, $sql, 3);
//		$html = $sql;
		// 	echo $sql;
		//横向是新增付费用户的日期，纵向是某天新增付费用户持续付费
		if(is_array($result['ret']['data'])){
			foreach ($result['ret']['data'] as $key=>$curRow){
				$xindex = $curRow['regDate'];//%Y-%m-%d
				$nameLink[$xindex] = $xindex;
				$nameLinkSort[$namLinkSortEnd - ($endTime/1000 - strtotime($xindex))/86400] = $xindex;
				$yindex = (strtotime($curRow['payDate'])-strtotime($xindex))/86400+1; //第几天
				//$paySum[$xindex] += $curRow['sum'];
				//$payUsers[$xindex] +=$curRow['users'];
				$eventAll['sum'][$xindex]['pay'] += $curRow['sum'];
				$eventAll['sum'][$xindex]['users'] += $curRow['users'];
				$eventAll[$yindex][$xindex]['pay'] += $curRow['sum'];
				$eventAll[$yindex][$xindex]['users'] += $curRow['users'];
			}
		
//			print_r($eventAll);
		}
		
	}

	if (!$eventAll){
		$mesg= "未获得支付数据";
	}
	
	$html .= "<table class='listTable' style='text-align:center'><thead>";
	if(!$nameLinkSort){
		$nameLinkSort = array_keys($nameLink);
	}
	ksort($nameLinkSort);
	// 	foreach ($nameLink as $column){
	// 	$html .= "<th>$column</th>";
	foreach ($nameLinkSort as $xRow){ //日期排序
		$html .= "<th colspan=2>$nameLink[$xRow]</th>";
	}
	$html .= "</thead>";

//	exit(print_r($eventAll));

	foreach ($eventAll as $flag=>$eventData)
	{
		$html .= "<tbody><tr class='listTr'>";
		if($flag=='reg'){ //每天一共多少个新注册
			foreach ($nameLinkSort as $xRow){
				$temp = $eventData[$xRow];
				if(!$temp){
					$temp = '-';
				}
				$html .= "<td colspan=2><font color='red'>$temp</font></td>";
			}
		}else{
			foreach ($nameLinkSort as $xRow){//$xRow是日期
				if($flag=='sum'){
					if($xRow=='time'){ //第一列  第几天
						$html .= "<td colspan=2><strong>$eventData[$xRow]</strong></td>";
					}else{
						$temp = $eventData[$xRow];
						if(!$temp){
							$temp = '-';
						}
						$html .= "<td><strong>".$temp['pay']."</strong></td><td><strong>".$temp['users']."</strong></td>";
					}
				}else{
					if($xRow=='time'){
						$html .= "<td colspan=2>$eventData[$xRow]</td>";//第几天
					}else{
						$temp = $eventData[$xRow];
						if(!$temp){
							$temp = '-';
						}
						//2016-04-16_5
						$tp='';
						$tp .= $xRow;
						$tp .= '_'.$flag;//距离第几天

						$html .='<td style="text-align: right;" id="' .$tp. '"><a href="' . 'javascript:void(edit('  .  "'"  .$tp."'))"   .   '">'    .   $temp['pay'].'</a></td>'."<td>".$temp['users']."</td>";
//						$html .= "<td>".$temp['pay']."</td><td>".$temp['users']."</td>";
					}
				}
			}
		}
		$html .= "</tr></tbody>";
	}
	$html .= "</table>";
	$startDate=date("Y-m-d 00:00",$startTime/1000);
	$endDate=date("Y-m-d 23:59",$endTime/1000);
	//echo $html;
	//exit;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
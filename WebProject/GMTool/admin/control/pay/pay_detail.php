<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*3);
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59:59",time());
if($_REQUEST['analyze']=='user'){
	$currPf = $_REQUEST['selectPf'];
	if ($currPf && $currPf!='ALL'){
		$miSql=" and pf='$currPf' ";
	}else {
		$miSql=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:0;
	
	if($_REQUEST['user']){
		$account_list = cobar_getValidAccountList('name', $_REQUEST['user']);
		$userUid = $account_list[0]['gameUid'];
	}elseif ($_REQUEST['userUid']){
		$userUid=$_REQUEST['userUid'];
	}
	
	$whereSql = " where p.`time` > $start and p.`time` < $end "
	.($userUid?" and u.uid = '{$userUid}'":"")
	;
	if($_REQUEST['goods']){
		$where_goods = 'and p.productId = ' .$_REQUEST['goods'];
	}
	if($_REQUEST['bycountry']){
		$sql = "SELECT r.country,COUNT(DISTINCT (p.uid)) user , count(p.uid) times,SUM(p.spend) total from paylog p LEFT JOIN stat_reg r on p.uid=r.uid 
		where p.time >= $start and p.time <= $end $where_goods ";
		$result = $page->execute($sql,3);
		$totalNum = $result['ret']['data'][0]['total'];
		echo "      总充值金额：<font color='#0088CC'>".$totalNum . "</font>";
		$uniqueUser = $result['ret']['data'][0]['user'];
		echo "      充值人数：<font color='#0088CC'>".(int)$uniqueUser."</font>";
		$paycount = $result['ret']['data'][0]['times'];
		echo "      充值次数：<font color='#0088CC'>".(int)$paycount."</font>";
	 	$sql = "SELECT r.country,COUNT(DISTINCT (p.uid)) user , count(p.uid) times,SUM(p.spend) total from paylog p LEFT JOIN stat_reg r on p.uid=r.uid  
	 	where p.time >= $start and p.time <= $end   $where_goods GROUP BY r.country ";
	 	$dauSql = "select r.country, COUNT(DISTINCT (l.uid)) dau from stat_login l left join stat_reg r on l.uid=r.uid where l.time >= $start and l.time <= $end $where_goods GROUP BY r.country ";
	 	$dauResult =  $page->execute($dauSql,3);
	 	$result = $page->execute($sql,3);
	 	$result = $result['ret']['data'];
	 	
	 	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'>
	 			<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	 	$html .= '<tr><td>国家</td><td>付费人数</td><td>付费次数</td><td>付费总额</td><td>DAU</td><td>付费率</td><td>所占比例</td></tr>';
	 	$totalNum = 0;
	 	$payNum = 0;
	 	$dauNum = 0;
	 	$timeNum = 0;
	 	foreach ($result as $value){
	 		$totalNum += $value['total'];
	 		if(empty($value['country'])){
	 			$otherUser += $value['user'];
	 			$otherTimes +=$value['times'];
	 			$otherTotal +=$value['total'];
	 		}else {
	 			
		 	$country[$value['country']] = $value['country'];
		 	$payUser[$value['country']] = $value['user'];
		 	$payTimes[$value['country']] = $value['times'];
		 	$total[$value['country']] = $value['total'];
	 		}
	
	 	}
	 	foreach ($dauResult['ret']['data'] as $dauValue){
		 	if(empty($dauValue['country'])){
		 		$otherDau +=$dauValue['dau'];
		 	}else{
		 		$data[$dauValue['country']] = $dauValue['dau'];
		 	}
	 	}
	 	$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
	 	$html .= "<td>其它</td><td>".$otherUser."</td><td>".$otherTimes."</td><td>".$otherTotal."</td><td>".$otherDau."</td><td>".round($otherUser*100 / $otherDau,2)."%</td><td>".round($otherTotal*100 / $totalNum )."%</td></tr>";
	 	foreach ($data as $countryKey=>$dataValue)	{
	 		
	 		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		 	$html .= "<td>".$countryKey."</td><td>".$payUser[$countryKey]."</td><td>".$payTimes[$countryKey]."</td><td>".$total[$countryKey]."</td><td>".$dataValue."</td><td>".round($payUser[$countryKey]*100 / $dataValue,2)."%</td><td>".round($total[$countryKey]*100 / $totalNum )."%</td>";
		 	$html .= "</tr>";
		 	$payNum += $payUser[$countryKey];
		 	$timeNum += $payTimes[$countryKey];
		 	$dauNum += $dataValue;
	 	}
	 	
	 	$html .= "<tr><td>合计</td><td>$payNum</td><td>$timeNum</td><td>$totalNum</td><td>$dauNum</td><td>".round($paycount*100 / $dauNum, 2)."%</td><td>".round($totalNum*100 / $totalNum )."%</td></tr>";
	 	$html .= "</table></div><br/>";
	 	exit($html);
	}
	
// 	if($_REQUEST['user']){
// 		$sql = "select count(1) payCount,count(distinct(p.uid)) uniqueUser,sum(p.spend) as sum from paylog p LEFT JOIN userprofile u ON p.uid = u.uid where p.`time` > $start and p.`time` < $end and u.name = '{$_REQUEST['user']}';";
// 	}else {
// 		$sql = "select count(1) payCount,count(distinct(uid)) uniqueUser,sum(spend) as sum from paylog where `time` > $start and `time` < $end;";
// 	}
	if ($_COOKIE['u']=='xiaomi'){
		if($userUid){
			$sql = "select count(1) payCount,count(distinct(uid)) uniqueUser,sum(spend) as sum from paylog p where uid = '{$userUid}' and `time` > $start and `time` < $end and uid = '{$userUid}' $miSql $where_goods;";
		}else {
			$sql = "select count(1) payCount,count(distinct(uid)) uniqueUser,sum(spend) as sum from paylog p where `time` > $start and `time` < $end $miSql $where_goods;";
		}
	}else {
		if($userUid){
			$sql = "select count(1) payCount,count(distinct(uid)) uniqueUser,sum(spend) as sum from paylog p where `time` > $start and `time` < $end and uid = '{$userUid}' $where_goods;";
		}else {
			$sql = "select count(1) payCount,count(distinct(uid)) uniqueUser,sum(spend) as sum from paylog p where `time` > $start and `time` < $end $where_goods;";
		}
	}
	
	$result = $page->execute($sql,3);
// 	$count = $result['ret']['data'][0]['payCount'];
// 	echo "获得数据".(int)$count."条";
	$sum = $result['ret']['data'][0]['sum'];
	echo "      总充值金额：<font color='#0088CC'>".$sum . "</font>";
	$uniqueUser = $result['ret']['data'][0]['uniqueUser'];
	echo "      充值人数：<font color='#0088CC'>".(int)$uniqueUser."</font>";
	$paycount = $result['ret']['data'][0]['payCount'];
	echo "      充值次数：<font color='#0088CC'>".(int)$paycount."</font>";
	
	
// 	if($_REQUEST['user']){
// 		$sql = "select count(1) onePay from (select p.* from paylog p LEFT JOIN userprofile u ON p.uid = u.uid where p.`time` > $start and p.`time` < $end and u.name = '{$_REQUEST['user']}' group by uid having count(1) = 1);";
// 	}else {
// 		$sql = "select count(1) onePay from (select * from paylog where `time` > $start and `time` < $end group by uid having count(1) = 1);";
// 	}
	if ($_COOKIE['u']=='xiaomi'){
		if($userUid){
			$sql = "select count(1) onePay from (select count(uid) cnt from paylog p where uid = '{$userUid}'  and `time` > $start and `time` < $end $miSql $where_goods group by uid having cnt = 1) p;";
		}else {
			$sql = "select count(1) onePay from (select count(uid) cnt from paylog p where `time` > $start and `time` < $end $miSql $where_goods group by uid having cnt = 1) p;";
		}
	}else {
		if($userUid){
			$sql = "select count(1) onePay from (select count(uid) cnt from paylog p where `time` > $start and `time` < $end and uid = '{$userUid}' $where_goods group by uid having cnt = 1) p;";
		}else {
			$sql = "select count(1) onePay from (select count(uid) cnt from paylog p where `time` > $start and `time` < $end $where_goods group by uid having cnt = 1) p;";
		}
	}
	
	//$sql = "select count(1) onePay from (select p.* from paylog p LEFT JOIN userprofile u ON p.uid = u.uid ".$whereSql." group by uid having count(1) = 1) a";
	$result = $page->execute($sql,3);
	$oenPay = $result['ret']['data'][0]['onePay'];
	echo "      期间内充值一次的人数：<font color='#0088CC'>".$oenPay . "</font>";
	
	$page_limit = 100;
	$pager = page($paycount, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];

	if ($_COOKIE['u']=='xiaomi'){
		$sql = "select p.*,u.name,u.level nowLevel,u.regTime,r.country from paylog p LEFT JOIN userprofile u ON p.uid = u.uid  left join (select distinct uid,country from stat_reg where 1=1 $miSql) r on p.uid=r.uid ". $whereSql .$where_goods. " ORDER BY p.`time` DESC limit $index,$page_limit";
	}else {
		$sql = "select p.*,u.name,u.level nowLevel,u.regTime,r.country from paylog p LEFT JOIN userprofile u ON p.uid = u.uid  left join (select distinct uid,country from stat_reg where pf='$currPf') r on p.uid=r.uid ". $whereSql. $where_goods." ORDER BY p.`time` DESC limit $index,$page_limit";
	}
//	echo $sql;
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	foreach ($result as $curRow)
	{
		$data = $curRow;
		$logItem['时间'] = date('Y-m-d H:i:s',$data['time']/1000);
		$logItem['订单号'] = $data['orderId'];
		$logItem['订单信息'] = $data['orderInfo'];
		$logItem['平台'] = $data['pf'];
		$logItem['用户'] = $data['uid'];
		$logItem['名字'] = $data['name'];
		$logItem['送给用户'] = $data['receiverId'];
		$logItem['国家'] = $data['country'];
		$logItem['平台'] = $data['pf'];
		$logItem['注册时间'] = date('Y-m-d H:i:s',$data['regTime']/1000);
		$logItem['当前等级'] = $data['nowLevel'];
		$logItem['支付等级'] = $data['payLevel'];
		$logItem['支付金额'] = $data['spend'];
		$logItem['购买物品'] = $data['productId'];
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	/* if ($_COOKIE ['u'] == 'yaoduo') {
		print_r($log);
	} */
	foreach ($log as $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'><th>编号</th>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		foreach ($sqlData as $key=>$value){
			$html .= "<td>" . $value . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
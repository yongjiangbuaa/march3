<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
if(!$_REQUEST['end'])
	$start = date("Y-m-d 00:00:00",time() -86400 * 2);
	$end = date("Y-m-d 23:59:59",time());
$eventNames = $goldLink;
$eventOptions = '<option></option>';
foreach ($eventNames as $eventType => $eventName)
	$eventOptions .= "<option id={$eventType}>{$eventName}</option>";
$eventNames['sum'] = '合计';
if($_REQUEST['analyze']=='platform'){
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
	$removegm = '';
	if($_REQUEST['payuser']){
		$removegm .= " and u.payTotal > 0 ";
		if($_REQUEST['removegm']){
			$removegm .= " and a.goldType >0 ";
		}
	}
	else{
		if($_REQUEST['removegm']){
			$removegm .= " and a.goldType >0 ";
		}
	}
	$whereSql = " where time >= $start and time < $end "
	.($_REQUEST['event'] != null?" and type = ('{$_REQUEST['event']}')":" and type !=9 ")
	." and cost != 0 ";
	if($user){
		$whereSql .= " and userId = '$user' ) as a ) as b ";
	}else{
		$whereSql .= " ) as a left join userprofile u on a.userId = u.uid where u.gmflag = 0 $removegm ) as b ";
	}
	//根据类型分出购买的是什么
	$paySql = "(select a.* from (select g.* from gold_cost_record g $whereSql";
	$count = 0;
	$dateEvent = $eventAll = $events = $event = array();
	//购买总人数总次数
	$sql = "select count(distinct(userId)) as userCount,count(1) as total from $paySql";
	$result = $page->execute($sql,3);
	$payUser = $result['ret']['data'][0]['userCount'];
	$count = $result['ret']['data'][0]['total'];
	//没有选择消费类型
	if(!$_REQUEST['event']){
		//每种物品购买总人数总次数
		$sql = "select type,count(distinct(userId)) as user,count(1) as times from $paySql GROUP BY type";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		if($result){
			foreach ($result as $curRow)
			{
				$eventType = $curRow['type'];
				$userId = $curRow['ownerid'];
				$total = $curRow['total'];
				$times = $curRow['times'];
				$eventAll['sum']['times'] += $curRow['times'];
				$eventAll[$eventType]['times'] += $curRow['times'];
				$eventAll['sum']['user'] += $curRow['user'];
				$eventAll[$eventType]['user'] += $curRow['user'];
			}
		}
		
		//每种商品 或者 CD具体队列 购买总人数总次数
		$sql = "select type,param1,count(distinct(userId)) as user,count(1) as times from $paySql where  type=12 and ( param1 > 200349 or param1 < 200300 ) GROUP BY param1";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		if($result){
			foreach ($result as $curRow)
			{
				$eventType = (int)$curRow['param1'] > 0 ? $curRow['param1'] : $curRow['type'];
				$userId = $curRow['ownerid'];
				$total = $curRow['total'];
				$times = $curRow['times'];
				$eventAll['sum']['times'] += $curRow['times'];
				$eventAll[$eventType]['times'] += $curRow['times'];
				$eventAll['sum']['user'] += $curRow['user'];
				$eventAll[$eventType]['user'] += $curRow['user'];
			}
		}
		//购买物品详细
		$sql = "select type,param1,count(distinct(userId)) as user,SUM(cost) as total,count(0) as times,DATE_FORMAT(FROM_UNIXTIME(time/1000),'%Y-%m-%d') as date from $paySql"
		."where type=12 and ( param1 > 200349 or param1 < 200300 ) GROUP BY param1,date ORDER BY date desc,type desc";
		$page = new BasePage();
		$GoodsResult = $page->execute($sql,3);
		$GoodsResult = $GoodsResult['ret']['data'];
		
		$sql = "select type,count(distinct(userId)) as user,SUM(cost) as total,count(0) as times,DATE_FORMAT(FROM_UNIXTIME(time/1000),'%Y-%m-%d') as date from $paySql"
		." GROUP BY type,date ORDER BY date desc,total desc";
		$page = new BasePage();
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		
		//语言文件
		$lang = loadLanguage();
		$GoodsXml = loadXml('goods','goods');
		$BuildsXml = loadXml('building','building');
		$result = array_merge($result,$GoodsResult);
	}
	else{
		//每种物品购买总人数总次数
		$sql = "select type,count(distinct(userId)) as user,count(1) as times from $paySql GROUP BY type";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		if($result){
			foreach ($result as $curRow)
			{
				$eventType = $curRow['type'];
				$userId = $curRow['ownerid'];
				$total = $curRow['total'];
				$times = $curRow['times'];
				$eventAll['sum']['times'] += $curRow['times'];
				$eventAll[$eventType]['times'] += $curRow['times'];
				$eventAll['sum']['user'] += $curRow['user'];
				$eventAll[$eventType]['user'] += $curRow['user'];
			}
		}
		//每种物品每日购买数据
		$sql = "select type,count(distinct(userId)) as user,SUM(cost) as total,count(0) as times,DATE_FORMAT(FROM_UNIXTIME(time/1000),'%Y-%m-%d') as date from $paySql"
		." GROUP BY type,date ORDER BY type,date desc,total desc";
		$page = new BasePage();
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
	}

	echo "<div style='margin: 5px 0;'>获得数据".(int)$count."条"." 付费人数".(int)$payUser."人</div>";
	if($result){
		foreach ($result as $curRow)
		{
			if((int)$curRow['param1'] > 0){
				$eventType = (int)$curRow['param1'];
			}
			else{
				$eventType = $curRow['type'];
			}
			$userId = $curRow['ownerid'];
			$total = $curRow['total'];
			$times = $curRow['times'];
			$user = $curRow['user'];
			$date = $curRow['date'];
			if($_REQUEST['getUserCount'] == true){
				$total = -$user;
			}
			elseif($_REQUEST['getPayCount'] == true){
				$total = -$times;
			}
			if($total > 0){
				$get = $total;
				$deduct = 0;
			}else{
				$get = 0;
				$deduct = -$total;
			}
			$events['sum'] += $deduct - $get;
			$events[$eventType] += $deduct - $get;
			$dateEvent[$date] += $deduct - $get;
			$eventAll['sum']['result'] += $deduct - $get;
			$event['sum'][$date]['times'] += $times;
			$event['sum'][$date]['user'] += $user;
			$event['sum'][$date]['result'] += $deduct - $get;
			$eventAll[$eventType]['result'] += $deduct - $get;
			$event[$eventType][$date]['times'] += $times;
			$event[$eventType][$date]['user'] += $user;
			$event[$eventType][$date]['result'] += $deduct - $get;
		}
	}
	$html = "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><th>类型</th><th>数量</th>";
	if(!$_REQUEST['nodetail'])
		foreach ($dateEvent as $date=>$dateCount){
			$html .= "<th>$date</th>";
		}
	$html .= "</thead>";
	$sort = $events;
	unset($sort['sum']);
	$sortType = $_REQUEST['orderType'] ? $_REQUEST['orderType'] : 'result';
	foreach ($eventAll as $etype=>$edata){
		$ultSort[$etype] = $edata[$sortType];
	}
	$ultSort['sum'] = 999999999;
	arsort($ultSort);
	foreach ($eventAll as $key=>$value){
		
	}
	foreach ($ultSort as $eventType=>$count)
	{
		if($eventType < 200000){
			$eventName = $eventNames[$eventType];
		}
		elseif ($eventType < 400000 || $eventType>500000){
			$eventName = $lang[(int)$GoodsXml[$eventType]['name']];
		}
		else{
			$level = $eventType % 100;
			$eventTypeTmp = intval($eventType / 100) * 100;
			$eventName = $level.'级 '.$lang[(int)$GoodsXml[$eventTypeTmp]['name']];

		}
		if(!$eventName){
			$eventName = $eventType;
		}
		if($eventType == 52 || $eventType == 31){
			$eventName = "<span style='color: red;cursor: pointer;font-size: 14px;' onclick='getInfo($eventType)'>$eventName</span> ";
		}
		$html .= "<tbody><tr class='listTr'><td style='width:115px;'><font color='#0088CC'>$eventName</font></td><td><font color='#0088CC'>{$eventAll[$eventType]['result']}</font></td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['result'];
			if($temp == null)
				$temp = '-';
			$html .= "<td><span style='color: #0088CC;cursor: pointer;' onclick=\"getUserInfo('$eventType','$date')\">$temp</span></td>";
		}
		$html .= "</tr></tbody><tbody><tr class='listTr'><td>次数</td><td>{$eventAll[$eventType]['times']}</td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['times'];
			if($temp == null)
				$temp = '-';
			$html .= "<td>$temp</td>";
		}
		$html .= "</tr></tbody><tbody><tr class='listTr'><td>人数</td><td>{$eventAll[$eventType]['user']}</td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['user'];
			if($temp == null)
				$temp = '-';
			$html .= "<td>$temp</td>";
		}

		$html .= "</tr></tbody>";
	}
	$html .= "</table>";
	echo $html;
	exit();
}
if($_REQUEST['getUserInfo'] == true){
	$type = $_REQUEST['logtype'];
	$startTime = strtotime($_REQUEST['logdate']) * 1000;
	$endTime = $startTime + 24 * 3600  * 1000;
	//and g.goldType >0
	$removegm = ' and u.gmflag = 0 ';
	if($_REQUEST['payuser']){
		$removegm .= " and u.payTotal > 0 ";
		if($_REQUEST['removegm']){
			$removegm .= " and g.goldType >0 ";
		}
	}
	else{
		if($_REQUEST['removegm']){
			$removegm .= " and g.goldType >0 ";
		}
	}
	$sql = "select u.name,u.uid,u.level,g.type,g.time,g.cost,g.remainGold,g.param1 from gold_cost_record g left join userprofile u on g.userId=u.uid where g.type=$type "
			." and g.time >= $startTime and g.time<=$endTime $removegm";
	$page = new BasePage();
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	$html = "<br /><hr /><table class='listTable' style='text-align:center;float:left;margin-right:15px;'>";
	$html .= "<tr><td>序号</td><td>名称</td><td>Uid</td><td>级别</td><td>花费时间</td><td>花费类型</td><td>花费数量</td><td>剩余金币</td></tr>";
	foreach ($result as $key=>$value){
		$eventname = $eventNames[$value['type']];
		$html .='<tr><td>'.($key +1).'</td><td>'.$value['name'].'</td><td>'.$value['uid'].'</td><td>'.$value['level']
		.'</td><td>'.date('Y-m-d H:i:s',$value['time']/1000).'</td><td>'.
		$eventname.'</td><td>'.$value['cost'].'</td><td>'.$value['remainGold'].'</td>';
	}
	$html .= "</table>";
	echo $html;
	exit();
}
if($_REQUEST['getTypeInfo'] == true){
	$type = $_REQUEST['logtype'];
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
	$removegm = '';
	if($_REQUEST['payuser']){
		$removegm .= " and u.payTotal > 0 ";
		if($_REQUEST['removegm']){
			$removegm .= " and a.goldType >0 ";
		}
	}
	else{
		if($_REQUEST['removegm']){
			$removegm .= " and a.goldType >0 ";
		}
	}
	//根据类型分出购买的是什么
	$paySql = "( select g.* from gold_cost_record g left join userprofile u on g.userId = u.uid 
				where time >= $start and time < $end  and type = $type and cost != 0 and  u.gmflag = 0 $removegm ) b";
	//每种商品 或者 CD具体队列 购买总人数总次数
	$sql = "select type,param1,count(distinct(userId)) as user,count(1) as times from $paySql  GROUP BY param1";
// 	exit($sql);
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	foreach ($result as $curRow){
		$eventType = (int)$curRow['param1'] > 0 ? $curRow['param1'] : $curRow['type'];
		$userId = $curRow['ownerid'];
		$total = $curRow['total'];
		$times = $curRow['times'];
		$eventAll['sum']['times'] += $curRow['times'];
		$eventAll[$eventType]['times'] += $curRow['times'];
		$eventAll['sum']['user'] += $curRow['user'];
		$eventAll[$eventType]['user'] += $curRow['user'];
	}
	//购买物品详细
	$sql = "select type,param1,count(distinct(userId)) as user,SUM(cost) as total,count(0) as times,DATE_FORMAT(FROM_UNIXTIME(time/1000),'%Y-%m-%d') as date from $paySql"
	."  GROUP BY param1,date ORDER BY date desc,type desc";
	$GoodsResult = $page->execute($sql,3);
	$GoodsResult = $GoodsResult['ret']['data'];
	//每种商品 或者 CD具体队列 购买总人数总次数  ; 资源道具ItemId 200300~200349
		$sql = "select type,param1,count(distinct(userId)) as user,count(1) as times from $paySql GROUP BY param1";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		foreach ($result as $curRow){
			$eventType = (int)$curRow['param1'] > 0 ? $curRow['param1'] : $curRow['type'];
			$userId = $curRow['ownerid'];
			$total = $curRow['total'];
			$times = $curRow['times'];
			$eventAll['sum']['times'] += $curRow['times'];
			$eventAll[$eventType]['times'] += $curRow['times'];
			$eventAll['sum']['user'] += $curRow['user'];
			$eventAll[$eventType]['user'] += $curRow['user'];
		}
		//语言文件
		$lang = loadLanguage();
		$GoodsXml = loadXml('goods','goods');
		$BuildsXml = loadXml('building','building');
	echo "<div style='margin: 5px 0;'>获得数据".(int)$count."条"." 付费人数".(int)$payUser."人</div>";
	foreach ($result as $curRow){
		if((int)$curRow['param1'] > 0){
			$eventType = (int)$curRow['param1'];
		}
		else{
			$eventType = $curRow['type'];
		}
		$eventType = $curRow['type'];
		$userId = $curRow['ownerid'];
		$total = $curRow['total'];
		$times = $curRow['times'];
		$user = $curRow['user'];
		$date = $curRow['date'];
		if($_REQUEST['getUserCount'] == true){
			$total = -$user;
		}
		elseif($_REQUEST['getPayCount'] == true){
			$total = -$times;
		}
		if($total > 0){
			$get = $total;
			$deduct = 0;
		}else{
			$get = 0;
			$deduct = -$total;
		}
		$events['sum'] += $deduct - $get;
		$events[$eventType] += $deduct - $get;
		$dateEvent[$date] += $deduct - $get;
		$eventAll['sum']['result'] += $deduct - $get;
		$event['sum'][$date]['times'] += $times;
		$event['sum'][$date]['user'] += $user;
		$event['sum'][$date]['result'] += $deduct - $get;
		$eventAll[$eventType]['result'] += $deduct - $get;
		$event[$eventType][$date]['times'] += $times;
		$event[$eventType][$date]['user'] += $user;
		$event[$eventType][$date]['result'] += $deduct - $get;
	}
	$html = "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><th width='350'>类型</th><th>数量</th>";
	if(!$_REQUEST['nodetail'])
		foreach ($dateEvent as $date=>$dateCount){
			$html .= "<th>$date</th>";
		}
	$html .= "</thead>";
	$sort = $events;
	unset($sort['sum']);
	$sortType = $_REQUEST['orderType'] ? $_REQUEST['orderType'] : 'result';
	foreach ($eventAll as $etype=>$edata){
		$ultSort[$etype] = $edata[$sortType];
	}
	$ultSort['sum'] = 999999999;
	arsort($ultSort);
	foreach ($ultSort as $eventType=>$count){
		if($eventType < 200000){
			$eventName = $eventNames[$eventType];
		}
		elseif ($eventType < 400000 || $eventType > 500000){
			$eventName = $lang[(int)$GoodsXml[$eventType]['name']];
		}
		else{
			$level = $eventType % 100;
			$eventTypeTmp = intval($eventType / 100) * 100;
			$eventName = $level.'级 '.$lang[(int)$BuildsXml[$eventTypeTmp]['name']];
		}
		if(!$eventName){
			$eventName = $eventType;
		}
		$html .= "<tbody><tr class='listTr'><td style='width:115px;'><font color='#0088CC'>$eventName</font></td><td><font color='#0088CC'>{$eventAll[$eventType]['result']}</font></td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['result'];
			if($temp == null)
				$temp = '-';
			$html .= "<td><span style='color: #0088CC;cursor: pointer;' onclick=\"getUserInfo('$eventType','$date')\">$temp</span></td>";
		}
		$html .= "</tr></tbody><tbody><tr class='listTr'><td>次数</td><td>{$eventAll[$eventType]['times']}</td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['times'];
			if($temp == null)
				$temp = '-';
			$html .= "<td>$temp</td>";
		}
		$html .= "</tr></tbody><tbody><tr class='listTr'><td>人数</td><td>{$eventAll[$eventType]['user']}</td>";
		foreach ($dateEvent as $date=>$dateCount){
			$temp = $event[$eventType][$date]['user'];
			if($temp == null)
				$temp = '-';
			$html .= "<td>$temp</td>";
		}
		$html .= "</tr></tbody>";
	}
	$html .= "</table>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
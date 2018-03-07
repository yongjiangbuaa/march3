<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d');
$eventOptions = '';
$eventArr = array(
	'basicstat'=>'联盟基础统计',
    'regnoalli'=>'注册第二天无联盟统计',
	'samplestat'=>'抽样统计',
	'alliancescience'=>'查看联盟科技统计',
	'allianceChief'=>'盟主等级统计',
    'memberDonate'=>'各成员的贡献',
    'shop'=>'联盟商店物品',
    'shopBuy'=>'商店买入记录',
    'shopSell'=>'商店卖出记录'
);

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

foreach ($eventArr as $eventType => $eventName){
	$eventOptions .= "<option value='{$eventType}'>{$eventName}</option>";
}
//if ($_REQUEST['fixmail']) {
//	exit("invalid operation!");
//	$sql = "SELECT uid FROM alliance_member WHERE allianceId != ''";
//	$result = $page->execute($sql,3);
//	if(is_array($result['ret']['data'])){
//		$num = 0;
//		$insertsql = "INSERT INTO `mail` (`uid`, `toUser`, `fromUser`, `fromName`, `title`, `contents`, `status`, `type`, `rewardId`, `rewardStatus`, `createTime`) VALUES ";
//		$title = 'Join Alliance Awards';
//		$contents = 'Congratulations, you have successfully joined the alliance in the annex can receive a reward.'
//				.'After adding various alliances and allies can interact with each other to help build the city, '
//				.'given the resources, mutual collaborative defense troops, can also experience a more powerful alliance wars, alliances and coalitions store technology features, hurry to the channel in the league and allies They say hello!';
//		$reward = 'gold,0,200';
//		$createTime = time()*1000;
//		$errorData = array();
//		foreach ($result['ret']['data'] as $value){
//			$uid = md5($value['uid'].microtime(true));
//			$user = $value['uid'];
//			if($num >= 500){
//				$insertsql .="('$uid','$user','','system','$title','$contents', '0', '13', '$reward', 0, $createTime)";
//				$errorData[] = $page->execute($insertsql,2);
//				$num = 0;
//				$insertsql = "INSERT INTO `mail` (`uid`, `toUser`, `fromUser`, `fromName`, `title`, `contents`, `status`, `type`, `rewardId`, `rewardStatus`, `createTime`) VALUES "; //这里需要增加 srctype
//			}else{
//				$insertsql .= "('$uid','$user','','system','$title','$contents', '0', '13', '$reward', 0, $createTime),";
//				$num++;
//			}
//		}
//		$errorData[] = $page->execute(rtrim($insertsql,','),2);
//		exit(print_r($errorData,true).' over');
//	}
//	else{
//		exit("no data");
//	}
//}

if ($_REQUEST['dotype'] == 'territory') {
	
	global $servers;
	$maxServer='';
	foreach ($servers as $server=>$serverInfo){
		if(substr($server, 0 ,1) != 's'){
			continue;
		}
		if (substr($server,1)>900000){
			continue;
		}
		$maxServer=max($maxServer,substr($server,1));
	}
	$sttt = $_REQUEST['selectServer'];
	if (!empty($sttt)) {
		$sttt = str_replace('，', ',', $sttt);
		$sttt = str_replace(' ', '', $sttt);
		$tmp = explode(',', $sttt);
		foreach ($tmp as $tt) {
			$tt = trim($tt);
			if (!empty($tt)) {
				if(strstr($tt,'-')){
					$ttArray=explode('-', $tt);
					$min=min($ttArray[1],$maxServer);
					for ($i=$ttArray[0];$i<=$min;$i++){
						$selectServer['s'.$i] = '';
						$selectId[]=intval($i);
					}
				}else {
					if($tt<=$maxServer){
						$selectServer['s'.$tt] = '';
						$selectId[]=intval($tt);
					}
				}
			}
		}
	}

	if (empty($selectServer)){
		$selectServer = $servers;
		foreach ($servers as $server=>$serverInfo){
			$selectId[]=intval(substr($server, 1));
		}
	}
	
	$sids=implode(',', $selectId);
	$sql="select * from stat_allserver.stat_alliance_territory where sid in($sids) order by sid;";
	$result = query_infobright($sql);
	$data=array();
	foreach ($result['ret']['data'] as $curRow){
		$server='s'.$curRow['sid'];
		$data[$server]['territoryNum']=$curRow['territoryNum'];
		$data[$server]['allianceNum']=$curRow['allianceNum'];
		$data[$server]['attackTimes']=$curRow['attackTimes'];
		$data[$server]['callBackTimes']=$curRow['callBackTimes'];
		
		$data[$server]['ironCount']=$curRow['ironCount'];
		$data[$server]['warehouseCount']=$curRow['warehouseCount'];
		$data[$server]['towerNum']=$curRow['towerNum'];
		$data[$server]['allianceTower']=$curRow['allianceTower'];
	}
	
	$actNameArr=array('territoryNum'=>'联盟领地拥有总数量','allianceNum'=>'拥有联盟领地的联盟数量','attackTimes'=>'进攻联盟领地次数','callBackTimes'=>'联盟领地被打回仓库的次数','ironCount'=>'拥有联盟超级矿的联盟数量','warehouseCount'=>'拥有联盟仓库的联盟数量','towerNum'=>'联盟箭塔数量','allianceTower'=>'拥有联盟箭塔的联盟数量');
	$html = "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><td>服</td>";
		foreach ($actNameArr as $indexKey=>$nameVal){
			$html.="<td>$nameVal</td>";
		}
	$html.="</tr>";
	foreach ($data as $serverKey=>$value){
		$html .= "<tr class='listTr'><td>$serverKey</td>";
		foreach ($actNameArr as $indexKey=>$nameVal){
			$html .="<td>".$value[$indexKey]."</td>";
		}
		$html .= "</tr>";
	}
	exit($html);
	
}


if ($_REQUEST['dotype'] == 'getPageData') {
	try {
		$limit = 100;
		$sql = "select count(1) sum from alliance";
		$result = $page->execute($sql,3);
		$count = $result['ret']['data'][0]['sum'];
		//实现分页
		$pager = page($count, $_REQUEST['page'], $limit);
		$index = $pager['offset'];
		$className = '';
		$result = $page->execute($sql,3);
		$order = '';
		$allianceName='';
		if($_REQUEST['allianceName'])
			$allianceName = $_REQUEST['allianceName'];
		$allianceabbr='';
		if($_REQUEST['allianceabbr'])
			$allianceabbr = $_REQUEST['allianceabbr'];

		if($_REQUEST['bynum'])
			$order = "order by curMember desc";
		if($_REQUEST['bydau']){
			if($order)
				$order = "order by curMember desc,dau1 desc";
			else
				$order = "order by dau1 desc";
		}
		if($_REQUEST['bypower']){
			if($order)
				$order = "order by fightpower desc,curMember desc";
			else
				$order = "order by fightpower desc";
		}
		$where = '';
		if($_REQUEST['AlliUid']){
			$strUid = trim($_REQUEST['AlliUid'],',');
			$where .= " and a.uid in ('' ";
			if(strpos($strUid,',')){
				$UidArr = explode($strUid, ',');
				foreach ($UidArr as $Uid){
					$where .= ",'$Uid'";
				}
			}else{
				$where.= ",'$strUid'";
			}
			$where .=') ';
		}
		if($allianceName!=''&&$allianceName!=null){
			$where .= " and a.allianceName like '$allianceName%' ";
		}elseif($allianceabbr!= '' && $allianceabbr!=null){
			$where .= " and a.abbr='$allianceabbr' ";
		}
		/*
		 * 精简
		 *left join ( select sum(if(param1 =0,1,0)) abat0, sum(if(param1 =1,1,0)) abat1,sum(if(param1 =2,1,0)) abat2,sum(if(param1 =3,1,0)) abat3, allianceId from logstat l 
				 LEFT JOIN userprofile u on l.`user`=u.uid where l.type=6 and l.timeStamp >= UNIX_TIMESTAMP(DATE_FORMAT(NOW(),'%Y-%m-%d')) *1000  and u.allianceId != '' GROUP BY u.allianceId ) as abla on abla.allianceId = a.uid 
				left join ( select sum(if(param1 =0,1,0)) bbat0, sum(if(param1 =1,1,0)) bbat1,sum(if(param1 =2,1,0)) bbat2,sum(if(param1 =3,1,0)) bbat3, allianceId from logstat l 
				 LEFT JOIN userprofile u on l.`user`=u.uid where l.type=6  and l.timeStamp >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')) *1000 and  l.timeStamp < UNIX_TIMESTAMP(DATE_FORMAT(NOW(),'%Y-%m-%d')) *1000 and u.allianceId != '' GROUP BY u.allianceId ) as ablb on ablb.allianceId = a.uid 
				left join ( select sum(if(param1 =0,1,0)) cbat0, sum(if(param1 =1,1,0)) cbat1,sum(if(param1 =2,1,0)) cbat2,sum(if(param1 =3,1,0)) cbat3, allianceId from logstat l 
				 LEFT JOIN userprofile u on l.`user`=u.uid where l.type=6  and u.allianceId != '' and l.timeStamp >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 2 DAY),'%Y-%m-%d')) *1000 and  l.timeStamp < UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')) *1000 GROUP BY u.allianceId ) as ablc on ablc.allianceId = a.uid 
				LEFT JOIN (SELECT uid FROM (SELECT user uid, action FROM logaction WHERE `timeStamp` >= ".(strtotime(date('Y-m-d')) * 1000).") log  WHERE action = 'al.science.donate' OR action = 'al.science.research' GROUP BY uid) s ON am.uid = s.uid
		if(abat0 > 0,abat0,0) abat0,if(bbat0 > 0,bbat0,0) bbat0,if(cbat0 > 0,cbat0,0) cbat0,
				if(abat1 > 0,abat1,0) abat1,if(bbat1 > 0,bbat1,0) bbat1,if(cbat1 > 0,cbat1,0) cbat1,
				if(abat2 > 0,abat2,0) abat2,if(bbat2 > 0,bbat2,0) bbat2,if(cbat2 > 0,cbat2,0) cbat2,
				if(abat3 > 0,abat3,0) abat3,if(bbat3 > 0,bbat3,0) bbat3,if(cbat3 > 0,cbat3,0) cbat3,
				COUNT(s.uid) scienceUse
		 */
		$table1=date('Y',time()).'_'.(date('m',time())-1);;
		$table2=date('Y',time()-86400).'_'.(date('m',time()-86400)-1);
		$table3=date('Y',time()-86400*2).'_'.(date('m',time()-86400*2)-1);
		$sql = "select a.*,u.name leader,
				if(tableDau1.dau1 > 0,tableDau1.dau1,0) dau1,if(tableDau2.dau2> 0,tableDau2.dau2,0) dau2,if(tableDau3.dau3 > 0,tableDau3.dau3,0) dau3,sum(i.power) as fightpower  from alliance a 
				inner join alliance_member am on a.uid = am.allianceId  
                inner join alliance_member al on a.uid = al.allianceId and al.rank = 5  
                inner join userprofile u on al.uid = u.uid  
                inner join playerinfo i on am.uid = i.uid  
				left join (SELECT a.allianceId,COUNT(DISTINCT (a.uid)) dau1  
				FROM alliance_member a LEFT JOIN stat_login_$table1 l on a.uid=l.uid  
				where a.allianceId != '' and l.time >= UNIX_TIMESTAMP(DATE_FORMAT(NOW(),'%Y-%m-%d')) *1000 GROUP BY a.allianceId ) as tableDau1 on tableDau1.allianceId = a.uid 
				left join (SELECT a.allianceId,COUNT(DISTINCT (a.uid)) dau2  
				FROM alliance_member a LEFT JOIN stat_login_$table2 l on a.uid=l.uid 
				where a.allianceId != '' and l.time >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')) *1000 and  l.time < UNIX_TIMESTAMP(DATE_FORMAT(NOW(),'%Y-%m-%d')) *1000 
				 GROUP BY a.allianceId ) as tableDau2 on tableDau2.allianceId = a.uid 
				left join (SELECT a.allianceId,COUNT(DISTINCT (a.uid)) dau3  
				FROM alliance_member a LEFT JOIN stat_login_$table3 l on a.uid=l.uid 
				where a.allianceId != ''  and l.time >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 2 DAY),'%Y-%m-%d')) *1000 and  l.time < UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d')) *1000 GROUP BY a.allianceId ) 
				 as tableDau3 on tableDau3.allianceId = a.uid  
				 where 1 
					$where 
		GROUP BY a.uid  $order limit $index ,$limit";
// 		exit($sql);
		$result = $page->execute($sql,3);
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
		exit();
	}
// 	exit(var_dump($result['ret']['data'],true));
// 	exit($sql);
	$html = "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array('num'=>'编号',
			'alliancename'=>'联盟名称',
			'abbr'=>'联盟简称',
			'leader'=>'盟主名称',
			'createtime'=>'创建时间',
			'Member'=>'当前人数/最大人数',
// 	        'alliancepoint'=>'联盟积分',
// 	        'scienceUse'=>'当天科技使用人数',
			'fightpower'=>'当前联盟战力',
			'dau1'=>'当天DAU',
			'dau2'=>'昨天DAU',
			'dau3'=>'前天DAU',
			'alliancepoint'=>'当前联盟积分',
// 			'rbat0'=>'盟主创建队伍次数',
// 			'rbat1'=>'成员加入队伍次数',
// 			'rbat2'=>'联盟派遣援军次数',
// 			'rbat3'=>'被派遣援军次数',
			);
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		if(in_array($key, array('name','uid')))
			$html .= "<th width=90px>" . $value . "</th>";
		else
			$html .= "<th width=40px>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($result['ret']['data'] as $no=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$user = '';
		foreach ($index as $key=>$title){
			$value = $sqlData[$key];
			switch ($key){
				case 'alliancename':
					$html .= "<td ><a href=\"javascript:getMember('".$sqlData['uid']."');\">" . $value . "</a></td>";
					break;
				case 'num':
					$html .= "<td>" . ++$no . "</td>";
					break;
				case 'abbr':
					$html .= "<td>(" . $value . ")</td>";
					break;
				case 'createtime':
					$html .= "<td>" .($value ? date('Y-m-d H:i:s',$value/1000) : '-' ). "</td>";
					break;
				case 'Member':
					$html .= "<td>" . $sqlData['curMember'].'/'.$sqlData['maxMember'] . "</td>";
					break;
				case 'rbat0':
					$html .= "<td>" .' ('.$sqlData['abat0'] .' / '. $sqlData['bbat0'].' / '.$sqlData['cbat0'].")</td>";
					break;
				case 'rbat1':
					$html .= "<td>" .' ('.$sqlData['abat1'] .' / '. $sqlData['bbat1'].' / '.$sqlData['cbat1'].")</td>";
					break;
				case 'rbat2':
					$html .= "<td>" .' ('.$sqlData['abat2'] .' / '. $sqlData['bbat2'].' / '.$sqlData['cbat2'].")</td>";
					break;
				case 'rbat3':
					$html .= "<td>" .' ('.$sqlData['abat3'] .' / '. $sqlData['bbat3'].' / '.$sqlData['cbat3'].")</td>";
					break;
				default:
					$html .= "<td>" . $value . "</td>";
					break;
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
if($_REQUEST['dotype'] == 'getmember' && $_REQUEST['alUid']){
	$alUid = trim($_REQUEST['alUid']);
	$className = 'userprofile u inner join user_world uw on u.uid = uw.uid left join worldpoint p on uw.pointId = p.id'.
			' inner join user_resource ur on u.uid = ur.uid'.
			' inner join playerinfo pi on u.uid = pi.uid'.
			' inner join user_building ub on u.uid = ub.uid and ub.itemId = 400000';
	$sql = "select u.*,p.*,ur.*,ub.level ubLevel,am.rank alrank ,am.accPoint,pi.power as ppower from {$className} inner join alliance_member am on u.uid=am.uid where am.allianceId='$alUid';";
	$result = $page->execute($sql,3);
	if ($_COOKIE['u']=='yd'){
		echo $sql."\n";
		print_r($result);
		exit();
	}
	if(!$result['error'] && $result['ret']['data']){
		$userList = $soldiersList = array();
		foreach ($result['ret']['data'] as $key => $curRow) {
			$userList[] = $curRow['uid'];
		}
		$sql = "select sum(free+pve+march+defence+train) sum,uid from user_army where uid in ('" .implode("','", $userList) . "') group by uid";
		$soldiersResult = $page->execute($sql,3);
		foreach ($soldiersResult['ret']['data'] as $key => $curRow) {
			$soldiersList[$curRow['uid']] = $curRow['sum'];
		}
	}
	$i = 0;
	foreach ($result['ret']['data'] as $key => $curRow) {
		$temp = $curRow;
		$temp['num'] = $i++;
		$temp['soldiers'] = $soldiersList[$temp['uid']];
		$temp['gold'] = $curRow['gold'] +$curRow['paidGold']; //金币是 充值+非充值
		$sqlDatas[] = $temp;
	}
	$html = "<div style='margin-top:100px;float:left;width:100%;height:460px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array('num'=>'编号',
			'name'=>'游戏昵称',
			'uid'=>'UID',
			'alrank'=>'职位',
			'level'=>'等级',
			'ubLevel' => '大本等级',
			// 'country'=>'阵营',
			'lang' => '所用语言',
			'gold' => '当前金币',
			'payTotal' => '总充值金币',
			'gmFlag' => 'GM标记',
			'soldiers' => '士兵数',
			'wood' => '木',
			'iron' => '铁',
			'food' => '粮',
			'silver' => '银',
			'x' => '世界X',
			'y' => '世界Y',
			'regTime' => '注册时间',
			// 'banTime' => '封号结束',
			'offLineTime' => '离线时间',
			'appVersion' => '游戏版本',
			'ppower' => '总战力值',
			'accPoint' => '个人联盟荣誉'
	);
	$gender = array('','男','女');
	$country = array('史塔克','兰尼斯特','拜拉席恩');
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		if(in_array($key, array('banTime','regTime','offLineTime')))
			$html .= "<th width=80px>" . $value . "</th>";
		elseif(in_array($key, array('name','uid')))
		$html .= "<th width=90px>" . $value . "</th>";
		else
			$html .= "<th width=40px>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($sqlDatas as $sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$user = '';
		foreach ($index as $key=>$title){
			$value = $sqlData[$key];
			switch ($key){
				case 'uid':
					$user = $value;
					$html .= "<td> $value </td>";
					break;
				case 'alrank':
					$html .= "<td>" . ($value == 5 ? '盟主' : 'R'.$value). "</td>";
					break;
				case 'gender':
					$html .= "<td>" . $gender[$value] . "</td>";
					break;
				case 'country':
					$html .= "<td>" . ($value > 7999 ? $country[$value-8000] : '') . "</td>";
					break;
				case 'banTime':
				case 'offLineTime':
				case 'regTime':
					$html .= "<td>" . ($value > 0 ? date('Y-m-d H:i:s',$value/1000) : '') . "</td>";
					break;
				case 'onlineTime':
					$timeArr = array(array('秒','60'),array('分','60'),array('时','24'),array('天','365'),array('年','10'));
					$index = 0;
					$temp = '';
					while($value > 0 && $timeArr[$index]){
						$temp = $value%$timeArr[$index][1] . $timeArr[$index][0] . $temp;
						$value = intval($value/$timeArr[$index][1]);
						$index++;
					}
					$html .= "<td>" . $temp . "</td>";
					break;
				case 'gmFlag':
					$html .= "<td>" . ($value == 1 ? "<font color=red>是</font>" : '') . "</td>";
					break;
				default:
					$html .= "<td>" . $value . "</td>";
					break;
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}
if($_REQUEST['dotype'] == 'update_rank' && $_REQUEST['alUid'] && $_REQUEST['rank']) {
	$alUid = trim($_REQUEST['alUid']);
	$rank = trim($_REQUEST['rank']);
	$sql = "update alliance_member set rank=$rank where uid='$alUid'";
	$result = $page->execute($sql,2);
	exit('ok');
}

if($_REQUEST['dotype'] =='basicstat'){
	$beforeDay=date('Ymd',time()-86400);
	$dayStart = strtotime($_REQUEST['dayStart'])*1000;
	$dayEnd = strtotime($_REQUEST['dayEnd'])*1000 + 86400*1000;
	$sDay=date("Ymd",($dayStart/1000));
	$eDay=date("Ymd",($dayEnd/1000));
	$server=$currentServer;
	$serverId=substr($server, 1);
	$regSql = "select date,sum(reg) regsum from stat_allserver.stat_dau_daily_pf_country_referrer where sid=$serverId and date between $sDay and $eDay GROUP BY date;";
	$ret = query_infobright($regSql);
	$AllData = $ret['ret']['data'];
	$log = array();
	foreach ($AllData as $value){
		$log[$value['date']]['date'] = $value['date'];
		$log[$value['date']]['regsum'] = $value['regsum'];
	}
	$SqlA = "select l.date,count(DISTINCT (l.`user`)) alNum,count(l.user) alTimes,l.type,l.param1,l.data1,
			COUNT(DISTINCT (log.uid)) lognum from snapshot_$server.logrecord_alliance l 
			left JOIN  (select  date,uid from snapshot_$server.stat_login where date >= $beforeDay GROUP BY uid ) log 
			on log.uid=l.user 
			where l.timeStamp >= $dayStart and l.timeStamp < $dayEnd and l.category=5 
			GROUP BY date,l.type;";
/* 	$SqlA = "SELECT DATE_FORMAT(FROM_UNIXTIME(l.timeStamp / 1000 ),'%Y%m%d') date,
				COUNT(DISTINCT (l.`user`)) alNum,count(l.user) alTimes,l.type ,l.param1,l.data1,
				COUNT(DISTINCT (log.uid)) lognum from logrecord l  
				left JOIN  (select  MAX(time),uid from  stat_login log  where log.time >= UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL 1 Day)) GROUP BY uid ) log 
				on log.uid=l.user 
				where l.timeStamp >= $dayStart and l.timeStamp < $dayEnd and l.category=5 
 				GROUP BY date,l.type"; */
	$ret = query_snapshot($SqlA);
	$AllData = $ret['ret']['data'];
	
	/* $Sql = "select u.date,COUNT(distinct(l.user)) as num from snapshot_$server.userprofile u inner JOIN snapshot_$server.logrecord_alliance l on l.`user`=u.uid
			WHERE l.category=5 and l.type=2 and u.allianceId !='' and u.regTime >= $dayStart and u.regTime<= $dayEnd GROUP BY date;"; */
	
	$Sql = "select log.date,COUNT(distinct(l.user)) as num from snapshot_$server.userprofile u inner JOIN snapshot_$server.logrecord_alliance l on l.`user`=u.uid 
	inner join snapshot_$server.stat_login log on u.uid=log.uid 
	WHERE l.category=5 and l.type=2 and u.allianceId !='' and log.time >= $dayStart and log.time<= $dayEnd GROUP BY date;";
	
	/* $Sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(u.regTime /1000),'%Y%m%d') date,COUNT(distinct(l.user)) as num from userprofile u inner JOIN logrecord l on l.`user`=u.uid 
WHERE l.category=5 and l.type=2 and u.allianceId !='' and u.regTime >= $dayStart and u.regTime<= $dayEnd GROUP BY date ";
	$ret = $page->execute($Sql,3); */
	$ret = query_snapshot($Sql);
	$RemainData = $ret['ret']['data'];
	
	$sql = "select DATE_FORMAT(FROM_UNIXTIME(createTime /1000),'%Y%m%d') date,event_type,COUNT(uid) timesNum from alliance_stats where createTime >= $dayStart and createTime<= $dayEnd GROUP BY date,event_type;";
	$result = $page->execute($sql,3);
	$timesData = $result['ret']['data'];
	
	$html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array(
			'date'=>'日期',
			'regsum'=>'注册人数',
			'searchUser'=>'搜索人数',
			'searchTimes'=>'搜索次数',
			//'join'=>'join人数',
			'apply'=>'apply人数',
			'create'=>'创建联盟人数',
			'leave'=>'退出联盟人数',
			'dismis'=>'解散联盟人数',
// 			'app1'=>'当日审批人数',
// 			'app2'=>'次日审批人数',
// 			'app3'=>'三日审批人数',
			'into'=>'当日加入联盟人数',
			'ratio'=>'入盟比例',
			//'stay'=>'留存(总/join/apply)',
			'remain'=>'今日有联盟人数',
			'invite'=>'邀请迁地次数',
			'response'=>'邀请迁地应答次数',
			'battle'=>'分享战报次数',
			'detect'=>'侦察报告次数',
	);
	foreach ($AllData as $value){
// 		if($value['type'] == 6 && $value['param1'] == 0){
// 			$log[$value['date']]['viewgold'] += $value['alNum'];
// 		}
		if($value['type'] == 6 ){
			$log[$value['date']]['searchUser'] += $value['alNum'];
			$log[$value['date']]['searchTimes'] += $value['alTimes'];
		}
		if($value['type'] == 1 ){
			$log[$value['date']]['join'] += $value['alNum'];
			$log[$value['date']]['joinStay'] += $value['lognum'];
		}
		if($value['type'] == 0 ){
			$log[$value['date']]['apply'] += $value['alNum'];
			$log[$value['date']]['appStay'] += $value['lognum'];
		}
		if($value['type'] == 2 ){
			$log[$value['date']]['into'] += $value['alNum'];
		}
// 		if($value['type'] == 2 && $value['param1'] == 1){
// 			$log[$value['date']]['app1'] += $value['alNum'];
// 		}
// 		if($value['type'] == 2 && $value['param1'] == 2){
// 			$log[$value['date']]['app2'] += $value['alNum'];
// 		}
// 		if($value['type'] == 2 && $value['param1'] == 3){
// 			$log[$value['date']]['app3'] += $value['alNum'];
// 		}
		if($value['type'] == 3 ){
			$log[$value['date']]['create']['total'] += $value['alNum'];
// 			$alluid .=  $value['data1'];
// 			$log[$value['date']]['create']['uid'] = $alluid;
		}
		if($value['type'] == 4 ){
			$log[$value['date']]['dismis'] += $value['alNum'];
		}
		if($value['type'] == 5 ){
			$log[$value['date']]['leave'] += $value['alNum'];
		}
		$log[$value['date']]['ratio'] = round($log[$value['date']]['into'] * 100 / $log[$value['date']]['regsum'] ).'%' ;
	}
	foreach ($RemainData as $value){
		$log[$value['date']]['remain'] = $value['num'];
	}
	foreach ($timesData as $value){
		if($value['event_type']==0){
			$log[$value['date']]['invite'] += $value['timesNum'];//邀请迁地次数
		}
		if($value['event_type']==1){
			$log[$value['date']]['response'] += $value['timesNum'];//邀请迁地应答次数
		}
		if($value['event_type']==2){
			$log[$value['date']]['battle'] += $value['timesNum'];//分享战报次数
		}
		if($value['event_type']==3){
			$log[$value['date']]['detect'] += $value['timesNum'];//侦察报告次数
		}
	}
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($log as $date=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($index as $key=>$aaa){
			switch ($key){
				case 'stay':
					$html .= "<td>" . ($sqlData['joinStay']+$sqlData['appStay']) .'/'.($sqlData['joinStay'] ? $sqlData['joinStay'] : '0').'/'.($sqlData['appStay'] ? $sqlData['appStay'] : '0'). "</td>";
					break;
				case 'create':
// 					$onclick = ' onclick=viewAlli("'.$sqlData['create']['uid'].'")';  $onclick style='color:blue;'
					$html .= "<td>" . ($sqlData['create']['total'] ? $sqlData['create']['total'] : '0'). "</td>";
					break;
				default:
					$html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : '0') . "</td>";
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();

}
if($_REQUEST['dotype'] =='alliancebattle'){
	$dayStart = strtotime($_REQUEST['dayStart'])*1000;
	$dayEnd = strtotime($_REQUEST['dayEnd'])*1000 + 86400*1000;
	$applySql = "  select COUNT(1) as times,l.param1,u.allianceId,a.alliancename from logstat l LEFT JOIN userprofile u on 
 				l.`user`=u.uid  LEFT JOIN  alliance a on u.allianceId=a.uid 
				 where l.type=6 and l.`timeStamp`>= $dayStart  and l.`timeStamp`<=  $dayEnd GROUP BY u.allianceId,l.param1";
	$alliresult = $page->execute($applySql,3);
	$html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array('num'=>'编号',
			'alliancename'=>'联盟名称',
			'times'=>'次数',
			'applysum'=>'当天apply人数',
	);
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";
	$no = 1;
	foreach ($data as $date=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($index as $key=>$tmp)
		{
			if($key == 'num'){
				$html .= "<td>" . $no++ . "</td>";
			}elseif($key =='ratio'){
				$html .= "<td>" . round($sqlData['acceptsum']/$sqlData['regsum']*100,2) . "%</td>";
			}
			else{
				$html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : 0). "</td>";
			}
				
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}

if ($_REQUEST['dotype']=='allianceChief'){
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	$data=array();
	foreach ($selectServer as $server=>$serInfo){
		$sql="select ub.level,count(ub.uid) cnt from user_building ub inner join alliance_member am on ub.uid=am.uid where ub.itemId=400000 and am.rank=5 group by level;";
		$ret=$page->executeServer($server, $sql, 3);
		foreach ($ret['ret']['data'] as $row){
			$data[$row['level']] +=$row['cnt'];
		}
	}
	$html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array(
		'level'=>'大本等级',
		'cnt'=>'人数',
	);
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";
	
	
	foreach ($data as $levelKey=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>" .$levelKey. "</td>";
		$html .= "<td>" .$sqlData. "</td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}

if($_REQUEST['dotype'] =='alliancescience') {
	$lang = loadLanguage();
	$clintXml = loadXml('database.local','alliancescience');
    $alliance = $page->execute("SELECT * FROM alliance WHERE binary alliancename = '{$_REQUEST['allianceName']}'",3);
    if (count($alliance['ret']['data']) < 1) {
        exit('no');
    }
    $allianceId = $alliance['ret']['data'][0]['uid'];
    $allianceScience = $page->execute("SELECT scienceId, level, donateprogress FROM alliance_science WHERE allianceId = '$allianceId'",3);
    $html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array(
        'scienceId'=>'科技',
        'level'=>'等级',
        'donateprogress'=>'当前进度值',
    );
    $html .= "<tr class='listTr'>";
    foreach ($index as $key=>$value)
    {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $no = 1;
    $data = $allianceScience['ret']['data'];
    foreach ($data as $sqlData)
    {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key=>$tmp)
        {
        	if($key=='scienceId'){
        		$html .= "<td>" . $lang[(int)$clintXml[$sqlData['scienceId']]['name']]. "</td>";
        	}else {
	            $html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : 0). "</td>";
        	}
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
	exit();
}
if($_REQUEST['dotype'] =='memberDonate') {
    $alliance = $page->execute("SELECT * FROM alliance WHERE binary alliancename = '{$_REQUEST['allianceName']}'",3);
    if (count($alliance['ret']['data']) < 1) {
        exit('no');
    }
    $allianceId = $alliance['ret']['data'][0]['uid'];
    $allianceScience = $page->execute("SELECT a.*, u.`name` FROM ( SELECT * FROM alliance_member WHERE allianceId = '$allianceId' ) a INNER JOIN userprofile u ON a.uid = u.uid",3);
    $html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array(
        'name'=>'名称',
        'rank'=>'rank',
        'point'=>'总贡献',
        'accPoint'=>'当前联盟贡献',
        'usedPoint'=>'已使用贡献',
        'todaydonate'=>'当日贡献',
        'jointime'=>'入盟时间',
        'wood'=>'木材捐献',
        'stone'=>'秘银捐献',
        'iron'=>'铁捐献',
        'food'=>'食物捐献',
        'gold'=>'金币捐献',
    );
    $html .= "<tr class='listTr'>";
    foreach ($index as $key=>$value)
    {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $no = 1;
    $data = $allianceScience['ret']['data'];
    foreach ($data as $sqlData)
    {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key=>$tmp)
        {
            $html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : 0). "</td>";
             
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}
if($_REQUEST['dotype'] == 'shop') {
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
    $alliance = $page->execute("SELECT * FROM alliance WHERE binary alliancename = '{$_REQUEST['allianceName']}'",3);
    if (count($alliance['ret']['data']) < 1) {
        exit('no');
    }
    $allianceId = $alliance['ret']['data'][0]['uid'];
    $allianceShop = $page->execute("SELECT * FROM alliance_shop WHERE allianceId = '$allianceId'",3);
    $html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array(
        'goodsId' => '道具ID',
        'count' => '剩余数量'
    );
    $html .= "<tr class='listTr'>";
    foreach ($index as $key=>$value)
    {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $no = 1;
    $data = $allianceShop['ret']['data'];
    foreach ($data as $sqlData)
    {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key=>$tmp)
        {
        	if($key=='goodsId'){
        		$html .= "<td>" . $lang[(int)$clintXml[$sqlData['goodsId']]['name']]. "</td>";
        	}else {
	            $html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : 0). "</td>";
        	}
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}
if($_REQUEST['dotype'] == 'shopBuy' || $_REQUEST['dotype'] == 'shopSell') {
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
    $dayStart = strtotime($_REQUEST['dayStart'])*1000;
	$dayEnd = strtotime($_REQUEST['dayEnd'])*1000 + 86400*1000;
    $alliance = $page->execute("SELECT * FROM alliance WHERE binary alliancename = '{$_REQUEST['allianceName']}'",3);
    if (count($alliance['ret']['data']) < 1) {
        exit('no');
    }
    $allianceId = $alliance['ret']['data'][0]['uid'];
    $isBuy = $_REQUEST['dotype'] == 'shopBuy';
    $type = 10;
    if ($isBuy) {
        $type = 9;
    }
    //$sql = "SELECT l.*, u.name FROM (SELECT * FROM logstat WHERE `user` IN (SELECT uid FROM alliance_member WHERE allianceId = '$allianceId') AND type = $type AND `timeStamp` >= $dayStart AND `timeStamp` < $dayEnd) l "."INNER JOIN userprofile u ON l.user = u.uid ORDER BY `timeStamp` ";
    
    $sql = "SELECT l.*, u.name FROM logstat l inner join alliance_member am on l.user=am.uid INNER JOIN userprofile u ON l.user = u.uid WHERE am.allianceId = '$allianceId' AND l.type = $type AND l.`timeStamp` >= $dayStart AND l.`timeStamp` < $dayEnd ORDER BY l.`timeStamp`";
    $allianceShop = $page->execute($sql,3);
    $html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    if ($isBuy) {
        $index = array(
            'name' => '玩家名称',
            'param1' => '道具ID',
        		'data2' => '剩余联盟积分',
        		'data3' => '买入个数',
            'timeStamp' => '时间'
        );
    } else {
        $index = array(
            'name' => '玩家名称',
            'param1' => '道具ID',
            'data1' => '剩余联盟荣誉',
        		'data3' => '卖出个数',
            'timeStamp' => '时间'
        );
    }
    $html .= "<tr class='listTr'>";
    foreach ($index as $key=>$value)
    {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $no = 1;
    $data = $allianceShop['ret']['data'];
    foreach ($data as $sqlData)
    {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key=>$tmp)
        {
            if ($key == 'timeStamp') {
                
                $html .= "<td>" . date('Y-m-d H:i:s',$sqlData[$key]/1000). "</td>";
            } else {
            	if($key == 'param1'){
            		$html .= "<td>" . $lang[(int)$clintXml[$sqlData['param1']]['name']]. "</td>";
            	}else {
	                $html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : 0). "</td>";
            	}
            }
             
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}
if($_REQUEST['dotype'] == 'regnoalli') {
    $dayStart = $_REQUEST['dayStart'];
    $dayEnd = $_REQUEST['dayEnd'];
    $sql = "SELECT FROM_UNIXTIME(u.lastOnlineTime/1000) time,u.uid,u.name FROM user_lord l INNER JOIN userprofile u on l.uid=u.uid 
            WHERE l.firstJoinAllianceFlag=1 and u.regTime >= UNIX_TIMESTAMP('$dayStart 00:00:00') * 1000 and 
            u.regTime <= UNIX_TIMESTAMP('$dayEnd 00:00:00') * 1000 
            and u.lastOnlineTime >= UNIX_TIMESTAMP('$dayEnd 00:00:00') * 1000  ORDER BY u.lastOnlineTime desc limit 100";
    $result = $page->execute($sql,3);
    if (count($result['ret']['data']) < 1) {
        var_dump($result);exit($sql);
        exit('no');
    }
    $html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array(
        'time' => '最后登陆时间',
        'uid' => '玩家Uid',
        'name' => '玩家名称',
    );
    $html .= "<tr class='listTr'>";
    foreach ($index as $key=>$value)
    {
        $html .= "<th style='text-align:center;'>" . $value . "</th>";
    }
    $html .= "</tr>";
    $data = $result['ret']['data'];
    foreach ($data as $sqlData)
    {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key=>$tmp)
        {
            $html .= "<td>" . $sqlData[$key] . "</td>";
             
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    echo $html;
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
if (isset($_REQUEST['getData'])) {
	$itemValue = array(
			'200300'=>array(10000,0),
			'200301'=>array(50000,0),
			'200302'=>array(150000,0),
			'200303'=>array(500000,0),
			'200304'=>array(1500000,0),
			'200305'=>array(5000000,0),
			'200306'=>array(1000,0),
			'200307'=>array(2000,0),
			'200308'=>array(4000,0),
			'200309'=>array(30000,0),
			'200310'=>array(400,1),
			'200311'=>array(2000,1),
			'200312'=>array(6250,1),
			'200313'=>array(20000,1),
			'200314'=>array(62500,1),
			'200315'=>array(200000,1),
			'200316'=>array(40,1),
			'200317'=>array(80,1),
			'200318'=>array(160,1),
			'200319'=>array(1250,1),
			'200320'=>array(1600,2),
			'200321'=>array(8000,2),
			'200322'=>array(25000,2),
			'200323'=>array(80000,2),
			'200324'=>array(250000,2),
			'200325'=>array(800000,2),
			'200326'=>array(150,2),
			'200327'=>array(300,2),
			'200328'=>array(600,2),
			'200329'=>array(5000,2),
			'200330'=>array(10000,3),
			'200331'=>array(50000,3),
			'200332'=>array(150000,3),
			'200333'=>array(500000,3),
			'200334'=>array(1500000,3),
			'200335'=>array(5000000,3),
			'200336'=>array(1000,3),
			'200337'=>array(2000,3),
			'200338'=>array(4000,3),
			'200339'=>array(30000,3),
			'200360'=>array(5,5),
			'200361'=>array(10,5),
			'200362'=>array(20,5),
			'200363'=>array(50,5),
			'200364'=>array(100,5),
			'200365'=>array(200,5),
	);
	$limit = 100;
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	$levelMin = $_REQUEST['levelMin'];
	$levelMax = $_REQUEST['levelMax'];
	$buildMin = $_REQUEST['buildMin'];
	$buildMax = $_REQUEST['buildMax'];
	$yesterDay = (time() -86400) *1000;
	if($_REQUEST['user']){
		$sql  = "SELECT u.name,u.uid,ub.level ulv,ub.level blv,ur.stone,ur.wood,ur.iron,ur.food ,(u.gold+u.paidGold) gold,lc.*,la.*
			from userprofile u INNER JOIN user_resource ur   on ur.uid=u.uid
			 left join (select 
			sum(if(param1=0,param2,0)) addwood,
			sum(if(param1=1,param2,0)) addstone,
			sum(if(param1=2,param2,0)) addiron,
			sum(if(param1=3,param2,0)) addfood,
			sum(if(param1=5,param2,0)) addgold,user usera,COUNT(1) times 
			 from logstat  where type =7 and timeStamp >= $yesterDay GROUP BY usera)la ON la.usera =ur.uid 
			 LEFT JOIN (SELECT sum(param1) decwood,
			sum(param2) decstone,
			sum(param3) deciron,
			sum(param4) decfood,
			sum(param5) decgold,user userc from logstat where type=11 and timeStamp >= $yesterDay GROUP BY userc)lc on lc.userc = ur.uid  
			 LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000 where  u.uid =".$_REQUEST['user'];
		$result = $page->execute($sql,3);
		$ALLresult = $result['ret']['data'];
		$count = 1;
	}
	else{
		$sql  = "SELECT count(1) sum from userprofile u  left JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000 
				where u.level >=  $levelMin and  u.level <= $levelMax   and ub.level>= $buildMin  and ub.level <= $buildMax 
				and u.regTime >=  $start   and   u.regTime <= $end ";
		$result = $page->execute($sql,3);
		$count = $result['ret']['data'][0]['sum'];
		$pager = page($count, $_REQUEST['page'], $limit);
		$index = $pager['offset'];
		$sql  = "SELECT u.name,u.uid,ub.level blv,u.level ulv,ur.stone,ur.wood,ur.iron,ur.food ,(u.gold+u.paidGold) gold,lc.*,la.*
		from userprofile u INNER JOIN user_resource ur   on ur.uid=u.uid
		left join (select
		sum(if(param1=0,param2,0)) addwood,
		sum(if(param1=1,param2,0)) addstone,
		sum(if(param1=2,param2,0)) addiron,
		sum(if(param1=3,param2,0)) addfood,
		sum(if(param1=5,param2,0)) addgold,user usera,COUNT(1) times
		from logstat  where type =7 and timeStamp >= $yesterDay GROUP BY usera)la ON la.usera =ur.uid
		LEFT JOIN (SELECT sum(param1) decwood,
		sum(param2) decstone,
		sum(param3) deciron,
		sum(param4) decfood,
		sum(param5) decgold,user userc from logstat where type=11 and timeStamp >= $yesterDay GROUP BY userc)lc on lc.userc = ur.uid
		LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000 where u.level >=  $levelMin and  u.level <= $levelMax   and ub.level>= $buildMin  and ub.level <= $buildMax 
				and u.regTime >=  $start   and   u.regTime <= $end limit $index,$limit";
		exit($sql);
		$result = $page->execute($sql,3);
		$ALLresult = $result['ret']['data'];
	}
	// 0 木头。1 秘银 ；2 铁； 3 粮食，5金币
	$itemArr = array();
	function getResourceFromItem($result){
		foreach ($result as $value){
			if($itemValue[$value['itemId']]){
				$type = $itemValue[$value['itemId']][1];
				$itemArr[$value['ownerId']][$type] += $value['count'] * $itemValue[$value['itemId']][0];
			}
			elseif($value['itemId'] == 200500){
				$itemArr[$value['ownerId']][0] += $value['count'] * 50000;
				$itemArr[$value['ownerId']][3] += $value['count'] * 50000;
			}
			elseif($value['itemId'] == 200501 ){
				$itemArr[$value['ownerId']][0] += $value['count'] * 150000;
				$itemArr[$value['ownerId']][3] += $value['count'] * 150000;
				$itemArr[$value['ownerId']][2] += $value['count'] * 150000;
			}
			elseif($value['itemId'] ==200502 ){
				$itemArr[$value['ownerId']][0] += $value['count'] * 500000;
				$itemArr[$value['ownerId']][3] += $value['count'] * 500000;
				$itemArr[$value['ownerId']][2] += $value['count'] * 500000;
				$itemArr[$value['ownerId']][1] += $value['count'] * 500000;
			}
			elseif($value['itemId'] == 200564){
				$itemArr[$value['ownerId']][0] += $value['count'] * 1000;
				$itemArr[$value['ownerId']][3] += $value['count'] * 1000;
			}
			elseif($value['itemId'] == 200565){
				$itemArr[$value['ownerId']][0] += $value['count'] * 2800;
				$itemArr[$value['ownerId']][3] += $value['count'] * 2800;
				$itemArr[$value['ownerId']][2] += $value['count'] * 400;
			}
			elseif($value['itemId'] == 200566){
				$itemArr[$value['ownerId']][0] += $value['count'] * 6100;
				$itemArr[$value['ownerId']][3] += $value['count'] * 6100;
				$itemArr[$value['ownerId']][2] += $value['count'] * 500;
				$itemArr[$value['ownerId']][1] += $value['count'] * 200;
			}
			elseif($value['itemId'] == 200567){
				$itemArr[$value['ownerId']][0] += $value['count'] * 30500;
				$itemArr[$value['ownerId']][3] += $value['count'] * 30500;
				$itemArr[$value['ownerId']][2] += $value['count'] * 2500;
				$itemArr[$value['ownerId']][1] += $value['count'] * 1000;
			}
		}
	}
	if($_REQUEST['user']){
		$sql = "select * from user_item where ownerId='".trim($_REQUEST['user'])."'";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		$itemArr = getResourceFromItem($result);
	}
	else{
		$where = "where ownerId in ('1',";
		foreach ($result as $value){
			$where.=",'".$value['uid']."'";
		}
		$where.=')';
		$sql = "select * from user_item $where";
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		foreach ($result as $value){
			$itemArr = getResourceFromItem($result);
		}
	}
	
	foreach ($ALLresult as $curRow)
	{
		$data = $curRow;
		$logItem['名称'] = $data['name'];
		$logItem['UID'] = $data['uid'];
		$logItem['当前等级'] = $data['ulv'];
		$logItem['大本等级'] = $data['blv'];
		$logItem['当前木材'] = $data['wood'];
		$logItem['当前秘银'] = $data['stone'];
		$logItem['当前铁'] = $data['iron'];
		$logItem['当前粮食'] = $data['food'];
		$logItem['当前金币'] = $data['gold'];
		$logItem['背包木材'] = $itemArr[$data['uid']][0];
		$logItem['背包秘银'] = $itemArr[$data['uid']][1];
		$logItem['背包铁'] = $itemArr[$data['uid']][2];
		$logItem['背包粮食'] = $itemArr[$data['uid']][3];
		$logItem['背包金币'] = $itemArr[$data['uid']][5];
		$logItem['昨日消耗木材'] = $data['decwood'];
		$logItem['昨日消耗秘银'] = $data['decstone'];
		$logItem['昨日消耗铁'] = $data['deciron'];
		$logItem['昨日消耗粮食'] = $data['decfood'];
		$logItem['昨日消耗金币'] = $data['decgold'];
		$logItem['昨日采集木材'] = $data['addwood'];
		$logItem['昨日采集秘银'] = $data['addstone'];
		$logItem['昨日采集铁'] = $data['addiron'];
		$logItem['昨日采集粮食'] = $data['addfood'];
		$logItem['昨日采集金币'] = $data['addgold'];
		$logItem['采集次数'] = $data['times'];
		$log[] = $logItem;
	}
	function formatNum($num){
		if($num >= 1000000){
			return ''.round($num/1000000,2).'M';
		}
		elseif($num >= 1000){
			return ''.round($num/1000,2).'K';
		}
		elseif($num >= 0){
			return ''.$num;
		}
		else{
			return '0';
		}
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<h4> 共  $count 条</h4> ";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
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
			$html .= "<td>" .($value ? formatNum($value) : '0')  . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
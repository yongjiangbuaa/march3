<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
$levelMin = $_REQUEST['levelMin'];
$levelMax = $_REQUEST['levelMax'];
$buildMin = $_REQUEST['buildMin'];
$buildMax = $_REQUEST['buildMax'];
$regDate = $_REQUEST['regDate'];
if (isset($_REQUEST['getData'])) {
	$limit = 100;
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	$regTimStamp = (time() - $regDate * 24 *3600 ) * 1000;
	if($_REQUEST['user']){
		$sql = "select count(1) as sum from logstat 
		where type = 4  and timeStamp >= $start and timeStamp < $end and user=".$_REQUEST['user']."  and param5 > 1";
	}
	else{
		$sql = "select count(DISTINCT(l.user)) as sum from userprofile u INNER JOIN user_building b on b.uid=u.uid INNER JOIN logstat l on l.user=u.uid 
		where u.level >= $levelMin and u.level <= $levelMax and b.itemId=400000 and b.level >= $buildMin and b.level <= $buildMax
		 and l.type = 4 and u.regTime >= $regTimStamp  and l.param5 > 1  and l.timeStamp >= $start and l.timeStamp < $end ";
	}
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['sum'];
	//实现分页
	$pager = page($count, $_REQUEST['page'], $limit);
	$index = $pager['offset'];
	if($_REQUEST['user']){
		$uid = $_REQUEST['user'];
		$sql = "SELECT u.uid,u.level ulv ,b.level blv,u.name,l.*  
		 from logstat l left join userprofile u on u.uid=l.user left join user_building b on b.uid=u.uid 
		 where l.user=$uid and l.type=4  and l.timeStamp >= $start and l.timeStamp < $end and b.itemId=400000 and l.param5 > 1
		limit $index,$limit";
	}
	else{
		$sql = "SELECT u.name,u.uid,u.level ulv,b.level blv, 
		sum(IF(param1 = 1,1,0)) num1,sum(IF(param1 = 2,1,0)) num2,sum(IF(param1 = 3,1,0)) num3, 
		sum(IF(param1 = 1,param3,0)) dead1,sum(IF(param1 = 2,param3,0)) dead2,sum(IF(param1 = 3,param3,0)) dead3 
		 from logstat l 
		 left join userprofile u on u.uid=l.user left join user_building b on b.uid=u.uid 
		 where  l.type=4  and l.timeStamp >= $start and l.timeStamp < $end and b.itemId=400000 and u.level >= $levelMin and u.level <= $levelMax and b.level >= $buildMin 
		  and b.level <= $buildMax and u.regTime > $regTimStamp   and l.param5 > 1 group by l.user 
		limit $index,$limit";
	}
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	if($_REQUEST['user']){
		foreach ($result as $curRow)
		{
			$data = $curRow;
			$logItem['名称'] = $data['name'];
			$logItem['UID'] = $data['uid'];
			$logItem['当前等级'] = $data['ulv'];
			$logItem['大本等级'] = $data['blv'];
			$logItem['战斗时间'] = date('Y-m-d H:i:s',$data['timeStamp']/1000);
			$logItem['怪物等级'] = $data['param1'];
			$logItem['死亡兵力'] = $data['param3'];
			$logItem['造成伤害'] = $data['param5'];
			$log[] = $logItem;
		}
	}
	else{
		foreach ($result as $curRow)
		{
			$data = $curRow;
			$logItem['名称'] = $data['name'];
			$logItem['UID'] = $data['uid'];
			$logItem['当前等级'] = $data['ulv'];
			$logItem['大本等级'] = $data['blv'];
			$logItem['打1级怪次数'] = $data['num1'];
			$logItem['打1级怪损失'] = $data['dead1'];
			$logItem['打2级怪次数'] = $data['num2'];
			$logItem['打2级怪损失'] = $data['dead2'];
			$logItem['打3级怪次数'] = $data['num3'];
			$logItem['打3级怪损失'] = $data['dead3'];
			$log[] = $logItem;
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
			$html .= "<td>" . $value . "</td>";
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
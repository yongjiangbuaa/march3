<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
$levelMin = $_REQUEST['levelMin'];
$levelMax = $_REQUEST['levelMax'];
if (isset($_REQUEST['getData'])) {
	$start = $_REQUEST['dateMin']?strtotime($_REQUEST['dateMin'])*1000:0;
	$end = $_REQUEST['dateMax']?strtotime($_REQUEST['dateMax'])*1000:time()*1000;
	$removegm = $_REQUEST['removegm']?" and g.goldType > 0 ":"";
	$levelStr = "  and u.level >=  $levelMin and u.level<= $levelMax ";
	$whereSql = "where g.time > $start and g.time < $end $removegm  $levelStr and g.cost != 0 ";
	//根据类型分出购买的是什么
	$paySql = "(select g.*,u.uid as userUid,u.name as userName from gold_cost_record g left join userprofile u on g.userId = u.uid $whereSql ";
// 	$count = 0;
// 	$dateEvent = $eventAll = $events = $event = array();
// 	//购买总人数总次数
// 	$sql = "select count(1) as total from $paySql and type='12' ) as a";
// 	$result = $page->execute($sql,3);
// 	$count = $result['ret']['data'][0]['total'];
// 	echo "获得数据".(int)$count."条";
			
// 	$page_limit = 100;
// 	$pager = page($count, $_REQUEST['page'], $page_limit);
// 	$index = $pager['offset'];
	$sql = "select a.*,count(param1) as count from $paySql and type=12 ) as a group by param1 order by count desc";
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	//语言文件
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
	foreach ($result as $curRow)
	{
		$data = $curRow;
// 		$logItem['时间'] = date('Y-m-d H:i:s',$data['time']/1000);
// 		$logItem['用户UID'] = $data['userUid'];
// 		$logItem['用户'] = $data['userName'];
		$logItem['金币类型'] = $curRow['goldType']?'充值金币':'赠送';
		if($data['param1']){
			$logItem['参数1'] = $lang[(int)$clintXml[$data['param1']]['name']];
			if(!$logItem['参数1']){
				$logItem['参数1'] = $data['param1'];
			}
		}
		else{
			$logItem['参数1'] = '-';
		}
		$logItem['单价'] = $clintXml[$data['param1']]['price'];
		$logItem['数量'] = $data['count'];
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:700px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
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
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'></div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
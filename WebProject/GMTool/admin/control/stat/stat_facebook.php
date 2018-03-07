<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d",time());
$dateMin = date("Y-m-d",time()-7*86400);
$version = $_REQUEST['version'];
if (isset($_REQUEST['getData'])) {
	$limit = 100;
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	if(strlen($version)){
		$version = " and u.appVersion='$version' ";
	}
	if($currentServer == 'test'){
		$db1 = '`cokdb2`.`userprofile`';
		$db2 = '`cokdb2_global`.`account_new`';
	}
	else{
		$db1 = '`cokdb1`.`userprofile`';
		$db2 = '`cokdb_global`.`account_new`';
	}
	$sql = "SELECT count(1) as sum from $db1 u LEFT JOIN $db2 a on u.uid=a.gameUid
where a.facebookAccount !='' and u.regTime>=$start and u.regTime<=$end $version";
// 	exit($sql);
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['sum'];
	//实现分页
	$pager = page($count, $_REQUEST['page'], $limit);
	$index = $pager['offset'];
	$sql = "SELECT * from $db1 u LEFT JOIN $db2 a  on u.uid=a.gameUid
where a.facebookAccount !='' and u.regTime>=$start and u.regTime<=$end $version limit $index,$limit";
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	foreach ($result as $curRow)
	{
		$data = $curRow;
		$logItem['名称'] = $data['name'];
		$logItem['UID'] = $data['uid'];
		$logItem['版本'] = $data['appVersion'];
		$logItem['当前等级'] = $data['level'];
		$log[] = $logItem;
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
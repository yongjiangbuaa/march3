<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d H:i",time()-86400*7);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59",time());
if($_REQUEST['analyze']=='user'){
	//激活 点击 绑定
	$start = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
	$end  = strtotime($_REQUEST['endDate'])*1000;
	$sql = "select count(1) sum ,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regdate from stat_reg where time >= $start and time < $end group by regdate";
	$result = $page->execute($sql,3);
	$logName = array('');
	foreach ($result['ret']['data'] as $key => $value) {
		$log[$value['regdate']]['reg'] = $value['sum'];
		$logName[] = $value['regdate'];
	}
	// $sql = "select count(distinct(r.uid)) sum,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regdate from stat_reg r left join stat_tutorial t on r.uid = t.uid where r.time >= $start and r.time < $end and t.tutorial = 3011000 group by regdate ";
	// $result = $page->execute($sql,3);
	// foreach ($result['ret']['data'] as $key => $value) { 
	// 	$log[$value['regdate']]['click'] = $value['sum'];
	// }
	// $sql = "select count(1) sum,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regdate from (select * from stat_reg where time >= $start and time < $end) r inner join `cokdb_global`.account_new a on a.gameuid = r.uid where googleAccount != '' || facebookAccount != '' group by regdate";
	// $result = $page->execute($sql,3);
	// foreach ($result['ret']['data'] as $key => $value) { 
	// 	$log[$value['regdate']]['fix'] = $value['sum'];
	// }
	$redisKeys = array();
	foreach (array_keys($log) as $date) {
		$redisKeys[] = 'tutorial:'.strtotime($date)*1000;
	}
	if($redisKeys){
		$result = $page->redis(2,$redisKeys);
		foreach ($result['ret'] as $key => $value) {
			list($redisKey,$timeStamp) = explode(':', $key);
			$redisDate = date('Y-m-d',$timeStamp/1000);
			foreach ($value as $k => $v) {
				$log[$redisDate][$k] = $v;
			}
		}
	}
	$title = array('4'=>'显示loading','reg'=>'注册');
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th></th>";
	foreach ($title as $key=>$value)
		$html .= "<th>" . $value . "</th>";
	$html .= "</tr>";
	$i = 1;
	foreach ($log as $date=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>{$date}</td>";
		$i++;
		foreach ($title as $key=>$value){
			$html .= "<td>" . $sqlData[$key] . "</td>";
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
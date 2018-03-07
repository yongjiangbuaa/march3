<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
foreach ($_REQUEST as $server=>$value)
{
	if($servers[$server] && $value == 'on')
		$selectServer[] = $server;
}
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
if (isset($_REQUEST['end'])) {
	try {
		$start = $_REQUEST['start'];
		$end = $_REQUEST['end'];
		$dayStart = strtotime($start,time()) * 1000;
		$dayEnd = strtotime($end,time()) * 1000 + 86400 * 1000;
		if(!$_REQUEST['reconnect'])
		{
			$dayArr = array(1,3,7,30);
		}
		else 
		{
			$reconnect = $_REQUEST['reconnect'];
			$dayArr = explode(',',$reconnect);
			if(count($dayArr) == 1)
			{
				$explodeArr = explode('-',$reconnect);
				if(count($explodeArr) > 1)
				{
					$dayArr = array();
					$index = $explodeArr[0];
					while($index <= $explodeArr[1])
						$dayArr[] = $index++;
				}
			}
		}
		$reconnectStart = $dayStart + 86400 * 1000 * min($dayArr);
		$reconnectEnd = $dayEnd + 86400 * 1000 * max($dayArr);
		$regSql = "select country,count(distinct(uid)) as total,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regdate from stat_reg where time >= $dayStart and time < $dayEnd GROUP BY regdate,country";
		$remainSql = "select country,count(distinct(r.uid)) as total,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regdate,date_format(from_unixtime(l.time/1000),'%Y-%m-%d') as relogindate from (select * from stat_reg where time >= $dayStart and time < $dayEnd) r inner join stat_login l on r.uid = l.uid where l.time > $reconnectStart and l.time < $reconnectEnd group by regdate,country,relogindate";
		foreach ($selectServer as $server)
		{
			$sqlData = array();
			$testKeys = array('all'=>'all');
			$registerResult = $page->executeServer($server,$regSql,3);
			$remainResult = $page->executeServer($server,$remainSql,3);
			foreach ($registerResult['ret']['data'] as $regData){
				$regdate = $regData['regdate'];
				$country = $regData['country'];
				$registerUser['sum'][$country] += $regData['total'];
				$registerUser[$regdate][$country] = $regData['total'];
			}
			foreach ($remainResult['ret']['data'] as $everyDay){
				$reloginDay = (strtotime($everyDay['relogindate']) - strtotime($everyDay["regdate"]))/86400;
				$regdate = $everyDay["regdate"];
				$country = $everyDay["country"];
				$sqlData['sum'][$country] += $everyDay['total'];
				$sqlData[$regdate][$country] = $everyDay['total'];
			}
			foreach ($dayArr as $day){
				foreach ($registerUser as $regdate => $value) {
					$count = $sqlData[$regdate][$day]?$sqlData[$regdate][$day]:0;
					$remainData[$server][$regdate][$day] = array('count'=>$count,'rate'=>($registerUser[$regdate][$server]>0?intval($count/$registerUser[$regdate][$server]*10000)/100:0));
				}
			}
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if(!$_REQUEST['date'])
	$rDate = date("Y-m-d",time());
$timeFix = strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'));
if (isset($_REQUEST['date'])) {
	try {
		$rDate = $_REQUEST['date'];
		$dayStart = strtotime($rDate,time()) * 1000;
		$dayEnd = $dayStart + 86400 * 1000;
		$ym=date('Y',$dayStart/1000).'_'.(date('m',$dayStart/1000)-1);
		$sql = "select count(distinct l.uid) as total,floor(($dayEnd - r.time)/86400000) as regDay from (select * from stat_login_$ym where time > $dayStart and time < $dayEnd) l inner join stat_reg r on l.uid = r.uid group by regDay";
		$page = new BasePage();
		$param["type"] = 11;
		$param["sql"] = $sql;
		$param = array(
				"changes"=>null,
				"params"=>$param,
			);
		$sendParam = array('info'=>'','data'=>$param,'mainDB'=>1);
		$legoinArr = array();
		foreach ($selectServer as $server=>$serInfo)
		{
			$result = $page->callByServer($server,'gm/gm/Mysql', $sendParam );
			if(!$result['ret']['error']){
				foreach ($result['ret']['data'] as $curRow)
				{
					$statDay = $regDay = $curRow['regDay'];
					if($regDay > 30)
						$statDay = '30';
					elseif($regDay > 15)
						$statDay = '16';
					elseif($regDay > 7)
						$statDay = '7';
					$legoinArr[$statDay] += $curRow['total'];
				}
			}
		}
		ksort($legoinArr);
		$dayLang = array(
				0=>"注册当天",
				1=>"注册1天",
				2=>"注册2天",
				3=>"注册3天",
				4=>"注册4天",
				5=>"注册5天",
				6=>"注册6天",
				7=>"注册7天到15天",
				16=>"注册16天到30天",
				30=>"注册30天以上",
		);
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
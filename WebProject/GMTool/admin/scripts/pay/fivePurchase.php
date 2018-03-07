<?php
	define('IN_ADMIN',true);
	define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
	include ADMIN_ROOT.'/config.inc.php';
	include ADMIN_ROOT.'/servers.php';
	include ADMIN_ROOT.'/admins.php';

	ini_set('mbstring.internal_encoding','UTF-8');
	includeModel("BasePage");
	global $servers;
	set_time_limit(0);
	
	$endTime = floor(microtime(true) * 1000);
	$startTime = $endTime - 3600*1000 * 2;//2小时

	$sql = "select concat(date_format(from_unixtime(p.time/1000),'%Y-%m-%d %H '),floor(date_format(from_unixtime(p.time/1000),'%i')/5)*5) as 		date, sum(spend) as paysum, p.pf, r.country,count(1) as paytimes
		from paylog p inner join (select distinct uid,country from stat_reg )r on p.uid = r.uid
		where p.time > $startTime and p.time < $endTime
		group by date,r.country,p.pf order by p.time asc";

//	$sql = "select concat(date_format(from_unixtime(p.time/1000),'%Y-%m-%d %H '),floor(date_format(from_unixtime(p.time/1000),'%i')/5)*5) as date, sum(spend) as paysum, p.pf, r.country,count(1) as paytimes"
//			." from paylog p inner join stat_reg r on p.uid = r.uid"
//			." where p.time > $startTime and p.time < $endTime"
//			." group by date,r.country,p.pf order by p.time asc";

	echo $startTime.'---'.$endTime.PHP_EOL;
	$sumData = array();
	$page = new BasePage();
	foreach ($servers as $server=>$serverInfo){
		echo "$server\n";
		$sqlData = $page->executeServer($server,$sql,3,false);
		if($sqlData['ret']['data']==NULL){
			echo "-no data-".PHP_EOL;
			echo "$server---$sql".PHP_EOL;
			continue;
		}
		foreach ($sqlData['ret']['data'] as $curRow){
			$date = $curRow['date'];
			$country = $curRow['country'];
			$pf = $curRow['pf']; //这里是所有paylog的pf  包括 iostest .最后读取显示时,去掉iostest
			$paysum = $curRow['paysum'];
			$paytimes = $curRow['paytimes'];
			$sumData[$date][$pf][$country]['paysum'] += $paysum;
			$sumData[$date][$pf][$country]['paytimes'] += $paytimes;
		}
	}
	include dirname(__FILE__).'/../../include/pay/payment.php';
	try {
		$payment = payment::singleton();
		$payment->insertFiveData($sumData);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
?>

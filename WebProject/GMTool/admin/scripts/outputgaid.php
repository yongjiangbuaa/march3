<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');

global $servers;
$where = "where r.time < 1431684000000 and u.offLineTime < 1431273600000 and r.type = 0 and r.country = 'KR' and u.gaid != '' and binary upper(u.gaid) != binary u.gaid";
$sumsql = "select count(1) sum from stat_reg r inner join userprofile u on r.uid =u.uid $where";
foreach ($servers as $server=>$serverInfo){
	echo $server."\n";
	if(substr($server, 0 ,1) != 's'){
		continue;
	}
//continue;
	$result = $page->executeServer($server, $sumsql, 3, true);
	if(!$result['ret']||!isset($result['ret']['data']))
	{
		writeLog("-- $server error\n");
		continue;
	}
	writeLog("-- $server\n");
	$sum = $result['ret']['data'][0]['sum'];
	$pageLimit = 1000;
	$pages = ceil($sum/$pageLimit);
	for($pageIndex=0;$pageIndex<$pages;$pageIndex++){
		$sql = "select u.gaid from stat_reg r inner join userprofile u on r.uid =u.uid $where order by r.time asc limit " .$pageIndex*$pageLimit. ",$pageLimit";
		$result = $page->executeServer($server, $sql, 3, true);
		if($result['ret']&&isset($result['ret']['data']))
		{
			$sqlDatas = $result['ret']['data'];
			$insertList = array();
			$size = 0;
			foreach ($sqlDatas as $sqlData)
			{
				$insertList[] = implode("','", $sqlData);
				$size++;
				if($size >= 500){
					$insertSql = "insert into idfa values ('" . implode("'),('", $insertList)."');"; 
					writeLog($insertSql);
					$insertList = array();
					$size = 0;
				}
			}
			if($size > 0){
				$insertSql = "insert into idfa values ('" . implode("'),('", $insertList)."');";
				writeLog($insertSql);
			}
		}else{
			writeLog("-- $server $pageIndex页数据丢失\n");
		}
	}
	//break;
}
function writeLog($row){
	file_put_contents( ADMIN_ROOT .'/gaidlist20150526.txt', $row . "\n",FILE_APPEND);
}
?>

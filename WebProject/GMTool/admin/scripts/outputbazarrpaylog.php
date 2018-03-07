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
$where = "where time >=1431446400000 and pf ='cafebazaar'";
$sumsql = "select count(1) sum from paylog $where";
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
		$sql = "select * from paylog $where order by time asc limit " .$pageIndex*$pageLimit. ",$pageLimit";
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
					$insertSql = "insert into paylog_tmp values ('" . implode("'),('", $insertList)."');"; 
					writeLog($insertSql);
					$insertList = array();
					$size = 0;
				}
			}
			if($size > 0){
				$insertSql = "insert into paylog_tmp values ('" . implode("'),('", $insertList)."');";
				writeLog($insertSql);
			}
		}else{
			writeLog("-- $server $pageIndex页数据丢失\n");
		}
	}
	//break;
}
function writeLog($row){
	file_put_contents( ADMIN_ROOT .'/paylogcafe.txt', $row . "\n",FILE_APPEND);
}
?>

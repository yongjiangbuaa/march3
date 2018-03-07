<?php
!defined('IN_ADMIN') && exit('Access Denied');

$title = "留存优化功能配置";
global $servers;

////////
$selectedServers = array();

$host = gethostbyname(gethostname());
if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP') {
	$selectedServers[] = 'localhost';
	$selectedServers[] = 'localhost2';
	$selectedServers[] = 's1';
	$selectedServers[] = 's2';
}elseif ($host == 'IPIPIP'){
	$selectedServers[] = 'test';
	$selectedServers[] = 's0';
	$selectedServers[] = 's999001';
}else {
	$maxServer=0;
	foreach ($servers as $server=>$serverInfo){
		if(substr($server, 0 ,1) != 's'){
		 continue;
		}
		if (substr($server,1)>900000){
			continue;
		}
		$maxServer=max($maxServer,substr($server,1));
	}
	$sttt = $_REQUEST['selectServer'];
	if (!empty($sttt)) {
		$sttt = str_replace('，', ',', $sttt);
		$sttt = str_replace(' ', '', $sttt);
		$tmp = explode(',', $sttt);
		foreach ($tmp as $tt) {
			$tt = trim($tt);
			if (!empty($tt)) {
				if(strstr($tt,'-')){
					$ttArray=explode('-', $tt);
					$min=min($ttArray[1],$maxServer);
					for ($i=$ttArray[0];$i<=$min;$i++){
						$selectedServers[$i] = 's'.$i;
					}
				}else {
					if($tt<=$maxServer){
						$selectedServers[$tt] = 's'.$tt;
					}
				}
			}
		}
	}else{
		$client = new Redis();
		$client->connect(GLOBAL_REDIS_SERVER_IP);
		$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
		if (!empty($serverRatioConf)) {
			$serverConfArr = explode(';',$serverRatioConf);
			foreach($serverConfArr as $serverItem) {
				$idRatioArr = explode(':', $serverItem);
				$keyList[] = $idRatioArr[0];
				$selectedServers[$idRatioArr[0]] = 's'.$idRatioArr[0];
			}
			$defaultselectServer = min($keyList).'-'.max($keyList);
		}
		$sttt = $defaultselectServer;
	}
	krsort($selectedServers, SORT_NUMERIC);
}

//更新时间
if($_REQUEST['type'] == 'modify')
{
	$serverTemp = $_REQUEST['server'];
	$temp = explode('_', $serverTemp, 2);
	$modifyServer = $temp[0];
	$modifyActName = $temp[1];
	$statValue = $_REQUEST['newDate'];
	$modifySql = "insert into switches(name,stat) values('$modifyActName',$statValue) ON DUPLICATE KEY update stat = $statValue";
	$result = $page->executeServer($modifyServer,$modifySql,2);
}

//批量更新
if($_REQUEST['event'] == 'batchDo')
{
	$erversAndSidsArr=getSelectServersAndSids($_REQUEST['selectServer']);
	$selectServer=$erversAndSidsArr['withS'];
	$columnName=$_REQUEST['columnName'];
	$columnValue=$_REQUEST['columnValue']?$_REQUEST['columnValue']:0;
	$modifySql = "insert into switches(name,stat) values('$columnName',$columnValue) ON DUPLICATE KEY update stat = $columnValue";
	$i=0;
	foreach ($selectServer as $server=>$servInfo){
		$result = $page->executeServer($server,$modifySql,2);
		if(!$result['error'] && $result['ret']['result']==1){
			$i+=1;
		}
	}
	//exit(print_r($result,true));
	exit('执行个数:'.$i);
}

//添加开关
if($_REQUEST['event'] == 'addSwitch')
{
	$switchName=trim($_REQUEST['switchName']);
	$switchName=str_replace('｜', '|', $switchName);
	$switchName=str_replace('；', ';', $switchName);
	$temp=explode('|', $switchName);
	foreach ($temp as $val){
		$temp2=explode(';', $val);
		writeHeaderInfo(trim($temp2[0]),trim($temp2[1]));
	}
	exit('添加成功');
}

$header=getHeader();
$sql="select name,stat from switches;";
$data=array();
foreach ($selectedServers as $server){
	if(substr($server, 0 ,1) != 's' && strpos($server, 'test')===false && strpos($server, 'localhost')===false){
		continue;
	}
	foreach ($header as $hKey=>$hValue){
		$data[$server][$hKey]=0;
	}
	$result = $page->executeServer($server,$sql,3);
	foreach ($result['ret']['data'] as $curRow){
		$data[$server][$curRow['name']]=$curRow['stat'];
	}
}

function getHeader() {
	$a = require ADMIN_ROOT . '/etc/headerArray.php';
	return $a;
}

function writeHeaderInfo($enName,$cnName){
	$beforeArray=array();
	if(file_exists(ADMIN_ROOT . '/etc/headerArray.php')){
		$beforeArray = getHeader();
	}
	$beforeArray[$enName]="<br>($cnName)";
	$newHeaderArray=$beforeArray;
	$strarr = var_export ( $newHeaderArray, true );
	file_put_contents ( ADMIN_ROOT . '/etc/headerArray.php', "<?php\n \$header= " . $strarr . ";\nreturn \$header;\n?>" );
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
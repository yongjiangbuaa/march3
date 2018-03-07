<?php
!defined('IN_ADMIN') && exit('Access Denied');
function compare($stra,$strb){
	$stra = trim($stra,"'");
	$strb = trim($strb,"'");

	$a = explode('.', $stra);
	$b = explode('.', $strb);
	if($a[0] > $b[0]){
		return 1;
	}
	elseif($a[0] < $b[0]){
		return -1;
	}
	elseif($a[1] > $b[1]){
		return 1;
	}
	elseif($a[1] < $b[1]){
		return -1;
	}
	elseif($a[2] > $b[2]){
		return 1;
	}
	elseif($a[2] < $b[2]){
		return -1;
	}
	elseif($a[3] > $b[3]){
		return 1;
	}
	elseif($a[3] < $b[3]){
		return -1;
	}
}

global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$dateMax = strtotime(date("Y-m-d 00:00:00",time()))*1000;
$dateMin = strtotime(date("Y-m-d 00:00:00",time() - 14*3600*24))*1000;
$logdateMin = isset($_REQUEST['dateMin'])?strtotime($_REQUEST['dateMin'])*1000:$dateMin;
$logdateMax = isset($_REQUEST['dateMax'])?strtotime($_REQUEST['dateMax'])*1000:$dateMax;
if (!$_REQUEST['selectCountry']) {
	$currCountry = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
	$currPf = 'ALL';
}else{
	$currPf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['statType']) {
	$stattype = '1';
}else{
	$stattype = $_REQUEST['statType'];
}

$wheresql='';
if($currPf != 'ALL'){
	$wheresql .= " and sr.pf=". "'" . $currPf . "'" ;
}
if($currCountry == 'ALL'){
//	$wheresql .= '';
}else{
	$wheresql .= " and sr.country=". "'" . $currCountry . "'" ;
}
if (isset($_REQUEST['getdate'])) {
	$where = " where p.lastOnlineTime >=$logdateMin and  p.lastOnlineTime <= $logdateMax ";

	$chartdata = $tabledata = $countall= array();
	$pfArr = $pfList ;
	unset($pfArr['ALL']);
	if($stattype == 1) {
		$sql = "select pf,appversion ,sum(cnt) as cnt from stat_allserver.stats_version sr where date in(select max(date) from stat_allserver.stats_version) $wheresql group by pf,appversion;";
		$ret = query_infobright($sql);
		foreach ($ret['ret']['data'] as $row) {
			$tmp = "'" . $row['appversion'] . "'"; //必须加单引号,表格图才会出来!!!
			$tabledata['all'][$tmp] += $row['cnt'];
			$chartdata[$tmp] += $row['cnt'];
			$countall['all'] += $row['cnt'];
			$pf = $row['pf'];
			if (array_key_exists($pf, $pfList)) {
				$tabledata[$pf][$tmp] += $row['cnt'];
				$countall[$pf] += $row['cnt'];
			}
		}
	}else {
		foreach ($selectServerids as $sid) {
			if ($sid == 0) continue;
				$sql = "select sr.pf , p.appVersion as appversion,count(p.appVersion) cnt from userprofile p INNER JOIN stat_reg sr on p.uid=sr.uid  $where $wheresql and p.banTime<2422569600000  and sr.pf != '' group by sr.pf,p.appversion";

			$sid = 's' . $sid;
			$ret = $page->executeServer($sid, $sql, 3);

			foreach ($ret['ret']['data'] as $row) {
				$tmp = "'" . $row['appversion'] . "'"; //必须加单引号,表格图才会出来!!!
				$tabledata['all'][$tmp] += $row['cnt'];
				$chartdata[$tmp] += $row['cnt'];
				$countall['all'] += $row['cnt'];
				$pf = $row['pf'];
				if (array_key_exists($pf, $pfList)) {
					$tabledata[$pf][$tmp] += $row['cnt'];
					$countall[$pf] += $row['cnt'];
				}
			}
		}
	}
	if (in_array($_COOKIE['u'],$privilegeArr)) {
		$html .= $sql;
	}
	uksort($tabledata['all'],"compare2");

	$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='3'>合计</th>";
	foreach ($pfArr as $value) {
		$html .= "<th colspan='2'>$value</th>";
	}
	$html .= "</tr></thead>";
	//副标题
	$html .= "<tr><th>版本</th><th>个数</th><th>占比</th>";
	foreach ($pfArr as $pf=>$value) {
		$html .= "<th>个数</th><th>占比</th>";
	}
	$html .= "</tr><tbody id='adDataTable'>";

	$allsum = array_sum($tabledata['all']);
	foreach ($tabledata['all'] as $version=>$count) {
		$versiontmp = $version;
		$versiontmp = trim($versiontmp,"'");

		$htmltmp = '';
		$rateall = intval($count*10000/$allsum)/100 . "%";
		$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td>$versiontmp</td><td>{$count}</td><td>{$rateall}</td>";
		foreach ($pfArr as $pf=>$value) {
			$rate =  intval($tabledata[$pf][$version]*10000/array_sum($tabledata[$pf]))/100 . "%";
			$htmltmp .= "<td>{$tabledata[$pf][$version]}</td><td>{$rate}</td>";
		}
		$htmltmp .= "</tr>";
		$html .= $htmltmp;
	}
	$html .= '</tbody></table></div>';
}
$logdateMin = date('Y-m-d',$logdateMin/1000);
$logdateMax = date('Y-m-d',$logdateMax/1000);


function compare2($stra,$strb){
	return -1 * compare($stra,$strb);
}
function checkiferror($ret,$sql){
	if(! $ret['ret']['data']){
		print_r("错误sql--$sql");
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
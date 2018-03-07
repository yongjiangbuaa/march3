<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$userSql = "select uid,level from userprofile order by level desc limit 10;";
	$buildSql = "select uid,level from user_building where itemId=400000 order by level desc limit 10;";
	
	foreach ($selectServer as $server=>$serInfo){
	// 	if(substr($server, 0 ,1) != 's'){
	// 		continue;
	// 	} 
		$userResult = $page->executeServer($server,$userSql,3);
		$buildResult = $page->executeServer($server,$buildSql,3);
		$i=0;
		foreach ($userResult['ret']['data'] as $userRow){
			$userInfo[$server][$i]['uid'] = $userRow['uid'];
			$userInfo[$server][$i]['level'] = $userRow['level'];
			$i++;
		}
		$i=0;
		foreach ($buildResult['ret']['data'] as $buildRow){
			$buildInfo[$server][$i]['uid'] = $buildRow['uid'];
			$buildInfo[$server][$i]['level'] = $buildRow['level'];
			$i++;
		}
	}

// $html = "<table class='listTable' style='text-align:center'><thead><th></th><th colspan='2'>玩家等级</th><th colspan='2'>大本等级</th></thead>";
// $html .="<tr><td></td><td>uid</td><td>level</td><td>uid</td><td>level</td></tr>";
// foreach ($selectServer as $server){
// 	/* if(substr($server, 0 ,1) != 's'){
// 		continue;
// 	} */
// 	$html .= "<tr><td>".$server."</td><td>".$userInfo[$server]['uid']."</td><td>".$userInfo[$server]['level']."</td><td>".$buildInfo[$server]['uid']."</td><td>".$buildInfo[$server]['level']."</td></tr>";
// }
// $html .= "</table>";

// echo $html;
// exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
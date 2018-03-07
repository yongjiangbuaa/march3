<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
/* $dbArray = array(
		'name' => '游戏昵称',
		'uid' => 'UID',
		'level' => '等级',
		'ublevel' => '大本等级',
		'lang' => '所用语言',
		'gold' => '当前金币',
		'gmFlag' => 'GM标记',
		'appVersion'=>'游戏版本',
		'facebookAccountName' => '绑定Facebook',
		'googleAccountName' => '绑定Google'
); */
$data = array();
if (isset($_REQUEST['getData'])) {
	$currentServer = $_COOKIE['Gserver2'];
	$server = substr($currentServer, 1);
	
	$buildMin = $_REQUEST['buildMin']?$_REQUEST['buildMin']:1;
	$buildMax = $_REQUEST['buildMax'];
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	
	$sql = "select count(*) as num from cokdb_global.account_new  where server=$server and ((googleAccount is not null and googleAccount!='') or (facebookAccount is not null and facebookAccount != ''));";
	$result = $page->globalExecute($sql,3);
	$count = $result['ret']['data'][0]['num'];
	if($count < 1){
		exit('<h3>没有数据!</h3>');
	}
	echo "      <strong>当前绑定人数:</strong><font color='#0088CC'>".$count . "</font>";
	$acceptSql = "select count(*) as sum from user_lord where firstBindAccountRewardFlag=1;";
	$acceptResult = $page->execute($acceptSql,3);
	$acceptCount = $acceptResult['ret']['data'][0]['sum'];
	echo "      <strong>领取绑定奖励的人数:</strong><font color='#0088CC'>".$acceptCount . "</font>";
	
	//$accountSql = "select gameuid, googleAccount,googleAccountName,facebookAccount,facebookAccountName from cokdb_global.account_new where server=1 and (googleAccount is not null and googleAccount!='') or (facebookAccount is not null and facebookAccount != '');";
	$accountSql = "select gameuid, googleAccount,googleAccountName,facebookAccount,facebookAccountName from cokdb_global.account_new where server=$server and ((googleAccount is not null and googleAccount!='') or (facebookAccount is not null and facebookAccount != ''));";
	$accountResult = $page->globalExecute($accountSql,3);
	foreach ($accountResult['ret']['data'] as $accountRow){
		$gameUid = $accountRow['gameuid'];
		if($accountRow['facebookAccountName']==null ||$accountRow['facebookAccountName']==''){
			$data[$gameUid]['facebookAccountName'] =$accountRow['facebookAccount'];
		}else{
			$data[$gameUid]['facebookAccountName'] =$accountRow['facebookAccountName'];
		}
		if($accountRow['googleAccountName']==null||$accountRow['googleAccountName']==''||$accountRow['googleAccountName'] =='Logged in'){
			$data[$gameUid]['googleAccountName'] =$accountRow['googleAccount'];
		}else {
			$data[$gameUid]['googleAccountName'] =$accountRow['googleAccountName'];
		}
		$uids[]=$gameUid;
	}
	$str = implode(',', $uids);
	$sql1 ="select count(*) as sum from userprofile u 
				left join user_building ub on u.uid =ub.uid 
				where u.uid in ($str) and u.regTime >= $start and u.regTime <= $end 
				and ub.level >= $buildMin and ub.level <= $buildMax and ub.itemId=400000;";
	$result1 =$page->execute($sql1,3);
	$count1 = $result1['ret']['data'][0]['sum'];
	$limit = 100;
	$pager = page($count1, $_REQUEST['page'], $limit);
	$index = $pager['offset'];
	$sql = "select u.uid,u.name,u.level,ub.level as ublevel,u.lang,u.gold,u.gmFlag,u.appVersion from userprofile u 
				left join user_building ub on u.uid =ub.uid 
				where u.uid in ($str) and u.regTime >= $start and u.regTime <= $end 
				and ub.level >= $buildMin and ub.level <= $buildMax and ub.itemId=400000 LIMIT  $index,$limit;";
	$result = $page->execute($sql,3);
	foreach ($result['ret']['data'] as $curRow)
	{
		$data2[$curRow['uid']]['name'] =$curRow['name'];
		$data2[$curRow['uid']]['uid'] =$curRow['uid'];
		$data2[$curRow['uid']]['level'] =$curRow['level'];
		$data2[$curRow['uid']]['ublevel'] =$curRow['ublevel'];
		$data2[$curRow['uid']]['lang'] =$curRow['lang'];
		$data2[$curRow['uid']]['gold'] =$curRow['gold'];
		$data2[$curRow['uid']]['gmFlag'] =$curRow['gmFlag'];
		$data2[$curRow['uid']]['appVersion'] =$curRow['appVersion'];
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th>编号</th><th>游戏昵称</th><th>UID</th><th>等级</th><th>大本等级</th><th>所用语言</th>
			<th>当前金币</th><th>GM标记</th><th>游戏版本</th><th>绑定Facebook</th><th>绑定Google</th></tr>";
	$i=1;
	foreach ($data2 as $uidKey=>$value)
	{
		/* if(!$data[$value]['uid']){
			continue;
		} */
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$html .= "<td>" .$value['name'] . "</td><td>".$value['uid']."</td><td>".$value['level']."</td><td>".$value['ublevel']."</td><td>".$value['lang']."</td>
					<td>".$value['gold']."</td><td>".$value['gmFlag']."</td><td>".$value['appVersion']."</td><td>".
				$data[$uidKey]['facebookAccountName']."</td><td>".$data[$uidKey]['googleAccountName']."</td></tr>";
		$i++;
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
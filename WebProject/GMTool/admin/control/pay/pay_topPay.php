<?php
!defined('IN_ADMIN') && exit('Access Denied');
$title="至今为止，玩家总付费排行榜";

$dbArray = array(
		'rank' => array('name'=>'rank',),
		'gameUid' => array('name'=>'uid',),
		'gameUserName' => array('name'=>'名字',),
		'server' => array('name'=>'服',),
		'blevel' => array('name'=>'大本等级',),
		'country' => array('name'=>'国家',),
		'regTime' => array('name'=>'注册时间',),
		'lastOnlineTime' => array('name'=>'最后登陆时间',),
		'paySum' => array('name'=>'总充值(美元)',),
		'gmail' => array('name'=>'gmail',),
		'facebookAccount' => array('name'=>'Facebook账号',),
		'payTotal' => array('name'=>'总充值金币'),
		'paidGold' => array('name'=>'剩余充值金币'),
		'ip' => array('name'=>'最后登录ip'),
);

if($_REQUEST['analyze']=='user'){
	if ($_REQUEST['rankNumber']){
		$rankNumber=$_REQUEST['rankNumber'];
	}else {
		$rankNumber=1;
	}
	if ($_REQUEST['toRankNumber']){
		$toRankNumber=$_REQUEST['toRankNumber'];
	}else {
		$toRankNumber=1000;
	}
	if ($_REQUEST['uid']){
		$user_uid=$_REQUEST['uid'];
	}

	$paySumArray=array();
	$uidArray=array();
	$client = new Redis ();
	$client->connect ( GLOBAL_REDIS_SERVER_IP2, GLOBAL_REDIS_SERVER_IP2_PORT );
	$key = "topPay";
	if(isset($user_uid)){
		$ret = $client->zScore($key,$user_uid);
		$html = "<div style='float:left;width:60%;height:100px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 60%; text-align: center'>";
		$html .= "<tr class='listTr'>";

		$html .= "<th>玩家的充值总额{$user_uid}</th><th>{$ret}</th></tr>";

		$html .= "</table></div><br/>";
		echo $html;
		exit();

	}
	$ret=$client->zrevrange($key,$rankNumber-1,$toRankNumber-1,'WITHSCORES');//array('val0' => 0, 'val2' => 2, 'val10' => 10)
	$client->close();
	foreach ($ret as $uKey=>$pValue){
		$paySumArray[$uKey]=$pValue;
		$uidArray[]=$uKey;
	}
	$data=array();
	$account_list = cobar_getAccountInfoByGameuids($uidArray);

	$yearMonth=date('Y',time()).'_'.(date('m',time())-1);
	$table = 'stat_login_'.$yearMonth;

	foreach ($account_list as $dbValue){
		$logTemp=array();
		$server='s'.$dbValue['server'];
		$uid=$dbValue['gameUid'];

		$sql="select u.regTime,u.lastOnlineTime,u.gmail,ub.level blevel,r.country,u.gmFlag,u.payTotal,u.paidGold from userprofile u
			inner join user_building ub on u.uid=ub.uid
			inner join stat_reg r on u.uid=r.uid
			where u.uid='$uid' and ub.itemId = 400000;";
		$result=$page->executeServer($server, $sql, 3);
		if(count($result['ret']['data']) > 0){
			$temp=$result['ret']['data'][0];
			if($temp['gmFlag'] == 1){
				continue;
			}
			$logTemp['blevel']=$temp['blevel'];
			$logTemp['country']=$temp['country']?$temp['country']:'';
			$logTemp['regTime']=$temp['regTime']?date('Y-m-d H:i:s',$temp['regTime']/1000):0;
			$logTemp['lastOnlineTime']=$temp['lastOnlineTime']?date('Y-m-d H:i:s',$temp['lastOnlineTime']/1000):0;
			$logTemp['gmail']=$temp['gmail']?$temp['gmail']:'';
			$logTemp['payTotal']=$temp['payTotal'];
			$logTemp['paidGold']=$temp['paidGold'];
		}
		$sql = "select ip from $table where uid='$uid' order by time desc limit 1";
		$result=$page->executeServer($server, $sql, 3);
		if(count($result['ret']['data']) > 0){
			$temp=$result['ret']['data'][0];
			if($temp['gmFlag'] == 1){
				continue;
			}
			$logTemp['ip']=$temp['ip'];
		}
		$logTemp['gameUid']=$dbValue['gameUid'];
		$logTemp['gameUserName']=$dbValue['gameUserName'];
		$logTemp['server']=$dbValue['server'];
		$logTemp['facebookAccount']=$dbValue['facebookAccount'];

		$logTemp['paySum']=$paySumArray[$uid];
		$data[$uid]=$logTemp;
	}
	
	$html = "<div style='float:left;width:105%;height:700px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
	foreach ($dbArray as $indexKey=>$indexName){
		$html .= "<th>".$indexName['name']."</th>";
	}
	$html .= "</tr>";
	$i=1;
	foreach ($paySumArray as $uidKey=>$value){//
		if(!$data[$uidKey]){
			continue;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($dbArray as $indexKey=>$indexName){
			if($indexKey == 'rank'){
				$html .= "<td>".$i."</td>";
			}else {
				$html .= "<td>" . $data[$uidKey][$indexKey] . "</td>";
			}
		}
		++ $i;
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
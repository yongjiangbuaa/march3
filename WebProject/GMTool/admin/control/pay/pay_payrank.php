<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '256M');
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d 00:00",time()-86400*5);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59",time());

if (!$_REQUEST['selectCountry']) {
	$currCountry = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}
//$seletedpf支付渠道
if (!$_REQUEST['selectPayMethod']) {
	$seletedpf = 'all';
}else{
	$seletedpf = $_REQUEST['selectPayMethod'];
}

foreach ($optionsArr as $pf => $pfdisp){
	$flag = ($seletedpf==$pf)?'selected="selected"':'';
	$pfOptions .= "<option value='{$pf}' $flag>{$pfdisp}</option>";
}

if($_REQUEST['analyze']=='user'){
	$start = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
	$end  = strtotime($_REQUEST['endDate'])*1000;
	$wherepf='';
	if (!empty($seletedpf) && $seletedpf!='all') {
		$wherepf .= " pf='$seletedpf' and ";
	}else{
		$wherepf .= " pf !='iostest' and ";
	}

	$whereCountry='';
	if($currCountry&&$currCountry!='ALL'){
		$whereCountry .=" inner join stat_reg r on (p.uid=r.uid and r.country='$currCountry')";
	}
	
	$sql = "select u.name,u.regTime,u.lastOnlineTime,u.gmail,p.* from (select uid,sum(spend) as payCount,date_format(from_unixtime(`time`/1000),'%Y-%m-%d') as logDate from paylog where $wherepf time >= $start and time < $end group by logDate,uid) p inner join userprofile u on p.uid = u.uid $whereCountry;";
	$payData = $sumData = $serverData = array();
	$now = time()*1000;
	$title = array();
	$today = strtotime(date("Y-m-d",time()));
	foreach ($selectServer as $server=>$serverInfo){
		$result = $page->executeServer($server,$sql,3);
		foreach ($result['ret']['data'] as $key => $value) {
			$uid = 'IF'.$value['uid'];
			$gmailData[$uid] = $value['gmail'];
			$payData[$value['logDate']][$uid] = $value['payCount'];
			$sumData[$uid] += $value['payCount'];
			if(!empty($serverData[$uid])){//用户在多个服务器都有id，则取最近登录的一次
				$oldServerLastLogin = $yIndex[$uid][2];
				if($oldServerLastLogin < $value['lastOnlineTime']){
					$serverData[$uid] = $server;
					$yIndex[$uid] = array($value['name'],$value['regTime'],$value['lastOnlineTime']);
				}
			}else{
				$serverData[$uid] = $server;
				$yIndex[$uid] = array($value['name'],$value['regTime'],$value['lastOnlineTime']);
			}
			$day = $today - strtotime($value['logDate']); 
			$title[$day] = $value['logDate'];
			
		}
	}
	array_multisort($sumData,SORT_DESC,$yIndex);
	$yIndex=array_slice($yIndex,0,100);
	foreach ($yIndex as $uidKey=>$valueArray){
		$uids[]=substr($uidKey, 2);
	}
	$account_list = cobar_getAccountInfoByGameuids($uids);
	foreach ($account_list as $row){
		$googleAccountNameData[$row['gameUid']]=$row['googleAccountName'];
		$facebookAccountData[$row['gameUid']]=$row['facebookAccount'];
	}
	
	ksort($title);
	$html = "<div style='float:left;width:105%;height:700px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th>UID</th><th>名字</th><th>服务器</th><th>注册时间</th><th>最后登陆时间</th><th>googleAccountName</th><th>facebookAccount</th><th>gmail</th><th>合计</th>";
	foreach ($title as $key=>$value)
		$html .= "<th>" . $value . "</th>";
	$html .= "</tr>";
	foreach ($yIndex as $ykey=>$yname){
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$userName = $yname[0];
		$lastOnlineTime = date("Y-m-d H:i:s",$yname[2]/1000);
		$regTime = date("Y-m-d H:i:s",$yname[1]/1000);
		$uid = substr($ykey, 2);
		$html .= "<td onclick='jumpUser($uid);'><a href='javascript:void();'>{$uid}</a></td><td>{$userName}</td><td>{$serverData[$ykey]}</td><td>{$regTime}</td><td>{$lastOnlineTime}</td><td>{$googleAccountNameData[$uid]}</td><td>{$facebookAccountData[$uid]}</td><td>{$gmailData[$ykey]}</td><td>{$sumData[$ykey]}</td>";
		foreach ($title as $date)
		{
			$td = $payData[$date][$ykey];
			$html .= "<td>" . $td . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
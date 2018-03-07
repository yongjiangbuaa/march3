<?php
!defined('IN_ADMIN') && exit('Access Denied');
$eventOptions = '';
$eventArr = array(
	'3'=>'9月3日注册(1000人)',
	'4'=>'9月4日注册(1000人)',
	'5'=>'9月5日注册(1000人)',
	'6'=>'9月6日注册(1000人)',
);
foreach ($eventArr as $eventType => $eventName){
	$eventOptions .= "<option value='{$eventType}'>{$eventName}</option>";
}
if (isset($_REQUEST['getData'])) {
	$tag = $_REQUEST['tag'];
	$time1= strtotime('2014-09-03 00:00:00') * 1000;
	$time2= $time1 + 86400000;
	$time3= $time2 + 86400000;
	$time4= $time3 + 86400000;
	$time5= $time4 + 86400000;
	$time6= $time5 + 86400000;
	switch ($tag){
		case 3:
			$startTime = $time2;
			$endTime = $time3;
			break;
		case 4:
			$startTime = $time3;
			$endTime = $time4;
			break;
		case 5:
			$startTime = $time4;
			$endTime = $time5;
			break;
		case 6:
			$startTime = $time5;
			$endTime = $time6;
			break;
		default:
			exit("error");
	}
	//插入抽样数据
	$sql = "select count(1) sum from sampleuser";
	$ret = $page->execute($sql,3);
	$sum = $ret['ret']['data'][0]['sum'];
	if($sum < 100){
		$sql = "insert into sampleuser (select uid,time regTime,3 tag from stat_reg where time >= $time1 and time <= $time2 order by rand() limit 1000)";
		$page->execute($sql,3);
		$sql = "insert into sampleuser (select uid,time regTime,4 tag from stat_reg where time >= $time2 and time <= $time3 order by rand() limit 1000)";
		$page->execute($sql,3);
		$sql = "insert into sampleuser (select uid,time regTime,5 tag from stat_reg where time >= $time3 and time <= $time4 order by rand() limit 1000)";
		$page->execute($sql,3);
		$sql = "insert into sampleuser (select uid,time regTime,6 tag from stat_reg where time >= $time4 and time <= $time5 order by rand() limit 1000)";
		$page->execute($sql,3);
	}
	
	//留存数据
	$remainSql = "select DISTINCT(l.uid) from stat_login l INNER JOIN sampleuser s on s.uid=l.uid and s.tag=$tag where l.time >= $startTime and l.time <= $endTime";
	$ret = $page->execute($remainSql,3);
	$result = $ret['ret']['data'];
	$remainNum = count($result);
	//流失数据
	$lostSql = "select DISTINCT(uid) from stat_login WHERE uid not in ( select uid from sampleuser where tag=$tag) and time >= $startTime and time <= $endTime";
	$ret = $page->execute($lostSql,3);
	$result = $ret['ret']['data'];
	$lostNum = count($result);
	//remain入盟统计
	$joinAlliRemain = "select * from logrecord where category=5 and type=2 and user in ($remainSql)";
	$ret = $page->execute($joinAlliRemain,3);
	$result = $ret['ret']['data'];
	$remainInfo = $lostInfo = array();
	$remainOne = $RemainTwo = $lostOne =$lostTwo = $lostMore =$remainMore = $remainList =$remainMail =$remainCreate= $lostCreate=$lostList =$LostMail =0;
	foreach ($result as $value){
		$remainInfo[$value['user']]++;
		if($value['param1'] < 4){
			$remainList++;
		}
		elseif($value['param1'] == 4){
			$remainCreate++;
		}
		elseif($value['param1'] == 5){
			$remainMail++;
		}
	}
	$remainInfo = array_values($remainInfo);
	$remainOne = $remainInfo[1];
	$RemainTwo = $remainInfo[2];
	$remainMore = array_sum($remainInfo) - $remainInfo[0]-$remainInfo[1]-$remainInfo[2];
	//lost入盟统计
	$joinAlliLost = "select * from logrecord where category=5 and type=2 and user in ($lostSql)";
	$ret = $page->execute($joinAlliLost,3);
	$result = $ret['ret']['data'];
	foreach ($result as $value){
		$lostInfo[$value['user']]++;
		if($value['param1'] < 4){
			$lostList++;
		}
		elseif($value['param1'] == 4){
			$lostCreate++;
		}
		elseif($value['param1'] == 5){
			$LostMail++;
		}
	}
	$lostInfo = array_values($lostInfo);
	$lostOne = $lostInfo[1];
	$lostTwo = $lostInfo[2];
	$lostMore = array_sum($lostInfo) - $lostInfo[0]-$lostInfo[1]-$lostInfo[2];
	$html = "<div style='margin-top:20px;float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>
			<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><td>次日留存</td><td>有联盟人数</td><td>加入联盟1次</td><td>加入联盟2次</td><td>加入联盟多次</td><td>通过联盟列表</td><td>通过创建联盟</td><td>通过邮件邀请</td></tr>";
	$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>
			<td>".$remainNum."</td><td>".'-'."</td><td>".$remainOne."</td><td>".$RemainTwo."</td><td>"
					.$remainMore."</td><td>".$remainList."</td><td>".$remainCreate."</td><td>".$remainMail."</td></tr>";
	
	$html .= "<tr class='listTr'><td>次日流失</td><td>有联盟人数</td><td>加入联盟1次</td><td>加入联盟2次</td><td>加入联盟多次</td><td>通过列表</td><td>通过创建联盟</td><td>通过邮件</td></tr>";
	$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>
				<td>".$lostNum."</td><td>".'-'."</td><td>".$lostOne."</td><td>".$lostTwo."</td><td>".$lostMore
				."</td><td>".$lostList."</td><td>".$lostCreate."</td><td>".$LostMail."</td></tr>";
	
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
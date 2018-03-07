<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59");
$dateMin = date("Y-m-d 00:00:00",strtotime('-1 day'));
$title = array(
		'uid'=>'玩家UId',
		'date'=>'部队行为时间',
		'armid'=>'兵种',
		'type'=>'部队状态',
		'original'=>'初始值',
		'before'=>'变化值',
		'after'=>'结果',
		'act'=>'部队行动',
);
$arms =array(
		'ALL' => '--ALL--',
		'107000'=>'民兵',
		'107001'=>'步兵',
		'107002'=>'矛手',
		'107003'=>'剑士',
		'107004'=>'枪兵',
		'107005'=>'贵族剑士',
		'107006'=>'近卫军',
		'107007'=>'重装长枪兵',
		'107008'=>'戟兵',
		'107009'=>'狂战士',
		'107100'=>'骑手',
		'107101'=>'轻骑兵',
		'107102'=>'重骑兵',
		'107103'=>'弓骑兵',
		'107104'=>'骑射手',
		'107105'=>'圣殿骑士',
		'107106'=>'重装骑射手',
		'107107'=>'皇家骑士',
		'107108'=>'突骑射手',
		'107109'=>'战象',
		'107200'=>'短弓手',
		'107201'=>'长弓手',
		'107202'=>'弩手',
		'107203'=>'劲弩手',
		'107204'=>'精锐长弓手',
		'107205'=>'护卫射手',
		'107206'=>'重弩手',
		'107207'=>'雄鹰射手',
		'107208'=>'巨弩手',
		'107209'=>'神射手',
		'107300'=>'投石器',
		'107301'=>'冲车',
		'107302'=>'投石车',
		'107303'=>'攻城锤',
		'107304'=>'重型投石车',
		'107305'=>'攻城车',
		'107306'=>'抛石机',
		'107307'=>'弩炮',
		'107308'=>'攻城塔',
		'107309'=>'火炮',
);
$armType=array(
	'ALL' => '--ALL--',
	'free'=>'空闲',
	'march'=>'出征',
	'defence'=>'守城',
);
$armAct=array(
		'ALL' => '--ALL--',
		'REWARD'=>'奖励',
		'TRAIN'=>'训练',
		'HEAL'=>'治疗',
		'DISMISS'=>'解雇',
		'MARCH'=>'出征',
		'MARCH_FIGHT'=>'出征战斗',
		'MARCH_BACK'=>'出征返回',
		'DEFENCE'=>'防守',
		'DEFENCE_FIGHT'=>'防守战斗',
		'DEFENCE_BACK'=>'守城返回',
		'SYNCHRONIZE'=>'登陆修复',
		'ADD_ARMY_ITEM'=>'道具增加',
);

if (isset($_REQUEST['getData'])) {
	//$name = $_REQUEST['username'];
	$uid = $_REQUEST['useruid'];
	$selectArm=$_REQUEST['selectArm'];
	$selectArmType=$_REQUEST['selectArmType'];
	$selectArmAct=$_REQUEST['selectArmAct'];
	if(empty($_REQUEST['useruid']) && !$privileges['dropdownlist_view']){
		echo '<div><font color="red">请输入用户uid</font></div>';
		exit();
	}else{
		$uid = $_REQUEST['useruid'];
	}

	$dateMin = strtotime($_REQUEST['dateMin'])*1000;
	$dateMax = strtotime($_REQUEST['dateMax'])*1000;
	
	$timeFlag=1435543200000;
	if($timeFlag>=$dateMin && $timeFlag<=$dateMax){
		$where1 = " where time >= $dateMin and time <= $timeFlag ";
		$where2 = " where time >= $timeFlag and time <= $dateMax ";
		if(trim($uid)){
			$where1 .= " and uid='{$uid}' ";
			$where2 .= " and uid='{$uid}' ";
		}
		else{
			// 		exit("error!!! no name or Uid!");
		}
		if((!empty($selectArm))&&$selectArm!='ALL'){
			$where1 .= " and armid='{$selectArm}' ";
			$where2 .= " and armid='{$selectArm}' ";
		}
		if((!empty($selectArmType))&&$selectArmType!='ALL'){
			$where1 .= " and type='{$selectArmType}' ";
			$where2 .= " and type='{$selectArmType}' ";
		}
		if((!empty($selectArmAct))&&$selectArmAct!='ALL'){
			$where1 .= " and act='{$selectArmAct}' ";
			$where2 .= " and act='{$selectArmAct}' ";
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql1 = "select count(1) sum from $db.arm  $where1 ";
		$result1 = $page->queryInfoBright2($db, $sql1);
		$sql2 = "select count(1) sum from $db.arm  $where2 ";
		$result2 = $page->queryInfoBright3($db, $sql2);
		$sum1 = $result1['data'][0]['sum'];
		$sum2 = $result2['data'][0]['sum'];
		$sum = $sum1 + $sum2;
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		
		$sqlDatas = array();
		if ($sum1 > $index) {
			$page_limit1 = 100;
			$sql1 = "SELECT uid,armid,type,act,`before`,`after`,from_unixtime(CAST(time / 1000 as signed)) as date
					from $db.arm   $where1 order by time limit $index,$page_limit";
			$result1 = $page->queryInfoBright2($db, $sql1);
			$sqlDatas = $result1['data'];
		}
		
		$count1 = count($sqlDatas);
		$page_limit2 = $page_limit - $count1;
		if ($index > $sum1) {
			$index = $index - $sum1;
		}else{
			$index = 0;
		}
		if ($page_limit2 > 0) {
			$sql2 = "SELECT uid,armid,type,act,`before`,`after`,from_unixtime(CAST(time / 1000 as signed)) as date
					from $db.arm   $where2 order by time limit $index,$page_limit";
			$result2 = $page->queryInfoBright3($db, $sql2);
			$sqlDatas = array_merge($sqlDatas,$result2['data']);
		}
	}elseif ($timeFlag<$dateMin){
		$where = " where time >= $dateMin and time <= $dateMax ";
		if(trim($uid)){
			$where .= " and uid='{$uid}' ";
		}
		else{
			// 		exit("error!!! no name or Uid!");
		}
		if((!empty($selectArm))&&$selectArm!='ALL'){
			$where .= " and armid='{$selectArm}' ";
		}
		if((!empty($selectArmType))&&$selectArmType!='ALL'){
			$where .= " and type='{$selectArmType}' ";
		}
		if((!empty($selectArmAct))&&$selectArmAct!='ALL'){
			$where .= " and act='{$selectArmAct}' ";
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql = "select count(1) sum from $db.arm  $where ";
		$result = $page->queryInfoBright3($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sum = $result['data'][0]['sum'];
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		$sql = "SELECT uid,armid,type,act,`before`,`after`,from_unixtime(CAST(time / 1000 as signed)) as date
		from $db.arm   $where order by time limit $index,$page_limit";
		$result = $page->queryInfoBright3($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sqlDatas = $result['data'];
	}elseif ($timeFlag>$dateMax){
		$where = " where time >= $dateMin and time <= $dateMax ";
		if(trim($uid)){
			$where .= " and uid='{$uid}' ";
		}
		else{
			// 		exit("error!!! no name or Uid!");
		}
		if((!empty($selectArm))&&$selectArm!='ALL'){
			$where .= " and armid='{$selectArm}' ";
		}
		if((!empty($selectArmType))&&$selectArmType!='ALL'){
			$where .= " and type='{$selectArmType}' ";
		}
		if((!empty($selectArmAct))&&$selectArmAct!='ALL'){
			$where .= " and act='{$selectArmAct}' ";
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql = "select count(1) sum from $db.arm  $where ";
		$result = $page->queryInfoBright2($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sum = $result['data'][0]['sum'];
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		$sql = "SELECT uid,armid,type,act,`before`,`after`,from_unixtime(CAST(time / 1000 as signed)) as date
		from $db.arm   $where order by time limit $index,$page_limit";
		$result = $page->queryInfoBright2($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sqlDatas = $result['data'];
	}
	$where = " where time >= $dateMin and time <= $dateMax ";
	if(trim($uid)){
		$where .= " and uid='{$uid}' ";
	}
	else{
		// 		exit("error!!! no name or Uid!");
	}
	if((!empty($selectArm))&&$selectArm!='ALL'){
		$where .= " and armid='{$selectArm}' ";
	}
	if((!empty($selectArmType))&&$selectArmType!='ALL'){
		$where .= " and type='{$selectArmType}' ";
	}
	if((!empty($selectArmAct))&&$selectArmAct!='ALL'){
		$where .= " and act='{$selectArmAct}' ";
	}
	$db = 'coklog_s'.substr($currentServer, 1);
	$sql = "select count(1) sum from $db.arm  $where ";
	$result = $page->queryInfoBright3($db, $sql);
	if(!$result['num']){
		exit();
	}
	$sum = $result['data'][0]['sum'];
	$page_limit = 100;
	$pager = page($sum, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];
	$sql = "SELECT uid,armid,type,act,`before`,`after`,from_unixtime(CAST(time / 1000 as signed)) as date
	from $db.arm   $where order by time limit $index,$page_limit";
	$result = $page->queryInfoBright3($db, $sql);
	if(!$result['num']){
		exit();
	}
	$sqlDatas = $result['data'];
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'><td>编号</td>";
	foreach ($title as $key=>$value){
		$html .= "<td>" . $value . "</td>";
	}
	$html .= "</tr>";
	foreach ($sqlDatas as $sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		$html .= "<td>".$sqlData['uid']."</td>";
		$html .= "<td>".$sqlData['date']."</td>";
		$html .= "<td>".$arms[$sqlData['armid']]."</td>";
		$html .= "<td>".$armType[$sqlData['type']]."</td>";
		$html .= "<td>".($sqlData['after']-$sqlData['before'])."</td>";
		$html .= "<td>".$sqlData['before']."</td>";
		$html .= "<td>".$sqlData['after']."</td>";
		$html .= "<td>".$armAct[$sqlData['act']]."</td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . '<br />'.$pager['pager'] . "</div>";
	echo $html;
	exit();
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
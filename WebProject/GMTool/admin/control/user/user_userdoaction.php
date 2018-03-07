<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59");
$dateMin = date("Y-m-d 00:00:00",strtotime('-1 day'));
$actionArr = array(
'AcceptInvitation',
'AcceptPraiseTask',
'AcceptTaskReward',
'ActivationCode',
'AddComplete',
'AddResourceOutPut',
'AddSoldier',
'AddWorldFavorite',
'AllianceAbbrCheck',
'AllianceAcceptApply',
'AllianceAcceptInvite',
'AllianceApply',
'AllianceApplyList',
'AllianceCallHelp',
'AllianceCancelApply',
'AllianceChangeAttr',
'AllianceCheck',
'AllianceCreate',
'AllianceDismiss',
'AllianceDonateCD',
'AllianceDonateRankAll',
'AllianceDonateRankToday',
'AllianceEventHistory',
'AllianceFreshDonate',
'AllianceHelpAll',
'AllianceInvite',
'AllianceKickUser',
'AllianceLeave',
'AllianceLeaveV2',
'AllianceMemberRank',
'AllianceMessage',
'AllianceNameCheck',
'AllianceRefuseApply',
'AllianceRefuseInvite',
'AllianceReinforceCount',
'AllianceReinforceList',
'AllianceReinforceReturn',
'AllianceRenderHelp',
'AllianceScienceDonate',
'AllianceScienceReserch',
'AllianceSearch',
'AllianceSetRank',
'AllianceSetRankName',
'AllianceShopAlBuy',
'AllianceShopShow',
'AllianceShopUsrBuy',
'AllianceShowHelp',
'AllianceTransferLeader',
'ArmyCDClear',
'BindingAccountHandler',
'BuildingRankingHandler',
'BuildWall',
'BuildWallComplete',
'BuildWallDirectly',
'BuyCityDef',
'BuyHotItem',
'BuyItem',
'CallFriendHelp',
'CancelAllianceTeam',
'CancelBind',
'CancelSave',
'ChangeNewAccount',
'ChangeRole',
'CheckNickName',
'ChooseMod',
'ChristmasTreeEntrance',
'CleanUserParseId',
'ClearSkillPoint',
'CollectResource',
'CreateBuilding',
'CureDirectly',
'CureFinish',
'CureSoldier',
'DeleteFromUser',
'DeleteMail',
'DelWorldFavorite',
'DestroyBuilding',
'DestroyWall',
'FinishQueue',
'FireSoldier',
'GetActivityInfo',
'GetAllianceLeaderWorldPoint',
'GetAllianceTeamList',
'GetAllianceWorldFightBulletin',
'GetBuildingInfo',
'GetChatMail',
'GetChatShieldList',
'GetChattingRecords',
'GetExchangeInfo',
'GetFightOfKingBulletin',
'GetHotItemInfoHandler',
'GetInviterInfo',
'GetLoginInfoHandler',
'GetMarchRecord',
'GetPlayerInfo',
'GetPowerInfo',
'GetScoutInfo',
'GetThroneInfo',
'GetUserLevelInfo',
'GetUserPrayInfo',
'GetWorldFavorites',
'GetWorldPointDetail',
'GetWorldServerList',
'InitiativeSkillHandler',
'LeaveWorld',
'LockChatPlayer',
'MailReward',
'MarchWorldPoint',
'MarkFromUserRead',
'MarkMailStatus',
'MineDig',
'MineFriendList',
'MineHelpFriend',
'MineHelpInfo',
'ModifyUserNickNameHandler',
'MonthLyCardsReward',
'MoveWorldPoint',
'MoveWorldPointCrossServer',
'OpenNewCityPos',
'PayAndroid',
'PayIOS',
'PowerRankingHandler',
'PrayResourceHandler',
'ReadMail',
'ReceiveLeveUp',
'RecoverCityDef',
'RecreateWorld',
'RentQueue',
'RetreatMarchArmy',
'SaveFromUser',
'SaveMail',
'SaveSkillPoint',
'ScienceResearch',
'ScienceUpgrade',
'ScoreHistory',
'ScoreInfo',
'ScoreLogin',
'ScoreTopHistory',
'SelectBatchMail',
'SendCountryMsg',
'SendMail',
'SetParseInfo',
'SetTutorial',
'ShowStatusItem',
'SpeedQueue',
'SpeedUpMarch',
'StatGameLog',
'SynchronizeCityResource',
'UnlockChatPlayer',
'UpdateGaid',
'UpgradeBuilding',
'UpgradeDirectly',
'UseItem',
'ReceiveLeveUp',
'MoveWorldPointByGold',
'KingExecutionRights',
'ComposeEquip',
'SendGiftMail',
'LoginEventHandler',
'CreateTerritoryResource',
'CancelTerritory',
'CancelTerritoryResource',
'ClearTerritory',
'CreateTerritory',
'BreakEquip',
'FinishEquipQueue',
'AllianceLeaveV2',
'CrossKingdomFightAccess',
);
$eventOptions = '<option>ALL</option>';
foreach ($actionArr as $eventType)
	$eventOptions .= "<option id={$eventType}>{$eventType}</option>";
if (isset($_REQUEST['medusa'])) {
	$uid = $_REQUEST['useruid'];
	$sqlData = $page->execute("DELETE FROM exchange_activity WHERE uid = '$uid'", 2, true);
	if($pager['pager'])
		$html .= '<div class="alert alert-info">成功 ！<font color="red"></font></div>';
	echo $html;
	if ($sqlData['ret']['effect']) {
		exit('success! '.$uid);
	} else {
		exit('uid not found');
	}
}
if (isset($_REQUEST['getData'])) {
	//$name = $_REQUEST['username'];
	$uid = $_REQUEST['useruid'];
	if(empty($_REQUEST['useruid']) && !$privileges['dropdownlist_view']){
		echo '<div><font color="red">请输入用户uid</font></div>';
		exit();
	}else{
		$uid = $_REQUEST['useruid'];
	}
	
	$dateMin = strtotime($_REQUEST['dateMin'])*1000;
	$dateMax = strtotime($_REQUEST['dateMax'])*1000;
	
	$timeFlag = 1435399200000;
	if ($timeFlag>=$dateMin && $timeFlag<=$dateMax){
		$where1 = " where time >= $dateMin and time <= $timeFlag ";
		$where2 =" where time >= $timeFlag and time <= $dateMax ";
		if(trim($uid)){
			$where1 .= " and uid='{$uid}' ";
			$where2 .= " and uid='{$uid}' ";
		}
		if($_REQUEST['event']){
			$eventStr=trim($_REQUEST['event'],'|');
			if($eventStr){
				$events=explode('|', $eventStr);
				$eve=implode("','", $events);
				$where1 .=  "  and action in('".$eve."') ";
				$where2 .=  "  and action in('".$eve."') ";
			}
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql1 = "select count(1) sum from $db.logaction_v3  $where1 ";
		$result1 = $page->queryInfoBright2($db, $sql1);
		$sql2 = "select count(1) sum from $db.logaction_v3  $where2 ";
		$result2 = $page->queryInfoBright3($db, $sql2);
		$sum1 = $result1['data'][0]['sum'];
		$sum2 = $result2['data'][0]['sum'];
		$sum = $sum1 + $sum2;
		echo "      行为总数：<font color='#0088CC'>".$sum . "</font>".'<br />';
		
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		
		$sqlDatas = array();
		if ($sum1 > $index) {
			$page_limit1 = 100;
			$sql1 = "SELECT *,from_unixtime(CAST(time / 1000 as signed)) as date
			from $db.logaction_v3   $where1 order by time limit $index,$page_limit";
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
			$sql2 = "SELECT *,from_unixtime(CAST(time / 1000 as signed)) as date
			from $db.logaction_v3   $where2 order by time limit $index,$page_limit2";
			$result2 = $page->queryInfoBright3($db, $sql2);
			$sqlDatas = array_merge($sqlDatas,$result2['data']);
		}
	}else if ($dateMax<$timeFlag){
		$where = " where time >= $dateMin and time <= $dateMax ";
		if(trim($uid)){
			$where .= " and uid='{$uid}' ";
		}
		else{
			// 		exit("error!!! no name or Uid!");
		}
		if($_REQUEST['event']){
			//$where .=  "  and action ='".trim($_REQUEST['event'])."' ";
				
			$eventStr=trim($_REQUEST['event'],'|');
			if($eventStr){
				//file_put_contents('/home/elex/php/log/db.log', $eventStr."\r\n", FILE_APPEND);
				$events=explode('|', $eventStr);
				$eve=implode("','", $events);
				$where .=  "  and action in('".$eve."') ";
			}
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql = "select count(1) sum from $db.logaction_v3  $where ";
		//file_put_contents('/home/elex/php/log/db.log', $sql."\r\n", FILE_APPEND);
		$result = $page->queryInfoBright2($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sum = $result['data'][0]['sum'];
		echo "      行为总数：<font color='#0088CC'>".$sum . "</font>".'<br />';
		
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		$sql = "SELECT *,from_unixtime(CAST(time / 1000 as signed)) as date
		from $db.logaction_v3   $where order by time limit $index,$page_limit";
		$result = $page->queryInfoBright2($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sqlDatas = $result['data'];
	}else if ($dateMin>$timeFlag){
		$where = " where time >= $dateMin and time <= $dateMax ";
		if(trim($uid)){
			$where .= " and uid='{$uid}' ";
		}
		else{
			// 		exit("error!!! no name or Uid!");
		}
		if($_REQUEST['event']){
			//$where .=  "  and action ='".trim($_REQUEST['event'])."' ";
				
			$eventStr=trim($_REQUEST['event'],'|');
			if($eventStr){
				//file_put_contents('/home/elex/php/log/db.log', $eventStr."\r\n", FILE_APPEND);
				$events=explode('|', $eventStr);
				$eve=implode("','", $events);
				$where .=  "  and action in('".$eve."') ";
			}
		}
		$db = 'coklog_s'.substr($currentServer, 1);
		$sql = "select count(1) sum from $db.logaction_v3  $where ";
		//file_put_contents('/home/elex/php/log/db.log', $sql."\r\n", FILE_APPEND);
		$result = $page->queryInfoBright3($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sum = $result['data'][0]['sum'];
		echo "      行为总数：<font color='#0088CC'>".$sum . "</font>".'<br />';
		
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		$sql = "SELECT *,from_unixtime(CAST(time / 1000 as signed)) as date
		from $db.logaction_v3   $where order by time limit $index,$page_limit";
		$result = $page->queryInfoBright3($db, $sql);
		if(!$result['num']){
			exit();
		}
		$sqlDatas = $result['data'];
	}
	
	
	$title = array(
		'uid'=>'玩家UId',
// 		'level'=>'级别',
		'time'=>'行为时间',
		'action'=>'行为',
// 	    'costtime'=>'行为',
	    'param'=>'参数',
	    'result'=>'结果',
		'chip'=>'chip',
	    'wood'=>'木头',
	    'food'=>'粮食',
		'silver'=>'钢材',
		'diamond'=>'钻石',
	    'stone'=>'秘银',
	    'iron'=>'铁矿',
// 		'resource'=>'资源',
	);
	foreach ($sqlDatas as &$sqlData){
		unset($sqlData['date']);
		$resource=json_decode($sqlData['resource'],true);
		foreach ($resource as $sName=>$sValue){
			$sqlData[$sName]=$sValue;
		}
	}
	unset($sqlData);
	//语言文件
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
	
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
		foreach ($title as $key=>$value){
			if($key == 'param'  && $sqlData[$key] && $sqlData['action'] =='UseItem'){
				$html .= "<td>" . ($lang[(int)$clintXml[$sqlData[$key]]['name']]) . "</td>";
			}else if (($key == 'param' || $key=='result') && $sqlData['action'] !='UseItem'){
				if ($_COOKIE ['u'] == 'tongyue' || $_COOKIE ['u'] == 'liaoyun') {
					$str =$sqlData[$key];
				}else {
					$str='待定';
				}
				$html .= "<td>" . $str . "</td>";
			}elseif ($key=='time'){
				$html .= "<td>".date('Y-m-d H:i:s', $sqlData['time']/1000)."</td>";
			}else{
				$html .= "<td>" . substr($sqlData[$key], 0,200) . "</td>";
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . '<br />'.$pager['pager'] . "</div>";
	echo $html;
	exit();
}

if (!$privileges['dropdownlist_view']) {
	$eventOptions = '<option>ALL</option>';
	$selectEventCtl = '<select id="selectEvent" onchange="" style="visibility: hidden;">
			'.$eventOptions.'
	</select><br>
	';
}else{
	$selectEventCtl = '<br>
	活动行为
	<select id="selectEvent" onchange="" size="3" MULTIPLE>
			'.$eventOptions.'
	</select>
	';
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
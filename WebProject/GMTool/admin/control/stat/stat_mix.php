<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
	$uid = $_REQUEST['user'];
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59:59",time());
if(!$_REQUEST['start'])
	$start = date("Y-m-d 00:00:00",time()-7*86400);

$eventNames = array(
		'ActionBeforeLost'=>'流失前行为统计',
// 		'SkillPanel'=>'首次进入技能面板统计',
		'LastAction'=>'玩家流失前游戏行为',
// 		'ArenaChallenge'=>'竞技场擂台详情'
		// 'UserAction'=>'功能使用次数',
		);
$selectServer = $_REQUEST['server'];
if(!$selectServer)
	$selectServer = getCurrServer();
$eventOptions = '';
foreach ($eventNames as $eventType => $eventName)
	$eventOptions .= "<option id={$eventType} value='{$eventType}'>{$eventName}</option>";


if($_REQUEST['analyze']=='getVersion'){
	$sql = "SELECT DISTINCT(appVersion) from userprofile where appVersion is not null;";
	$ret = $page->executeServer($selectServer,$sql,3);
	$optionStr = ' <option value="all" >all</option>';
	foreach ($ret['ret']['data'] as $value){
		$version = $value['appVersion'];
		$optionStr .= "<option  value='$version'>$version</option>";
	}
	echo $optionStr;
	exit();
}

if($_REQUEST['analyze']=='platform'){
	set_time_limit(0);
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime(date("Y-m-d 23:59:59"))*1000;
	$namLinkSortEnd = $end/1000 + 86400;//用于表头排序
	$levelMin = $_REQUEST['levelMin'];
	$levelMax = $_REQUEST['levelMax'];
	$user = ($user && $user != 'undefined')?" and user = '{$user}'":"";
	$nameLinkSort = $nameLink = $eventAll = $hightLight = array();
	$buildMin = $_REQUEST['buildMin'];
	$buildMax = $_REQUEST['buildMax'];
	$version = $_REQUEST['appVersion'];
	if($version != 'all'){
		$versionStr = " and u.appVersion='$version'";
	}
	else{
		$versionStr = '';
	}
	/**
	 * 显示说明
	 * $nameLink表头
	 * $nameLinkSort表头排序，默认可以不设置
	 * $eventAll表数据
	 * $nameLinkSort[0] = x1;
	 * $nameLinkSort[5] = x2;
	 * $nameLinkSort[9] = x3;
	 *	$nameLink[x1]='x1name';
	 *	$nameLink[x2]='x2name';
	 *	$nameLink[x3]='x3name';
	 *	$eventAll[y1][x1]='data11';
	 *	$eventAll[y1][x2]='data12';
	 *	$eventAll[y1][x3]='data13';
	 *	$eventAll[y2][x1]='data21';
	 *	$eventAll[y2][x2]='data22';
	 *	$eventAll[y2][x3]='data23';
	 *	yIndex只用来表明行标
	 * -----------------------------------------
	 * |	x1name	|	x2name	|	x3name	|
	 * -----------------------------------------
	 * |	data11		|	data12		|	data13		|
	 * -----------------------------------------
	 * |	data21		|	data22		|	data23		|
	 * -----------------------------------------
	 */
	//全局变量名
	$globalArr = array('page','funcList','goldLink','rewardLink','eventAll','nameLink','nameLinkSort','hightLight','start','end','namLinkSortEnd','levelMin','levelMax','user'
	,'buildMin','buildMax','version','versionStr','selectServer');
	$function = $_REQUEST['event'];
	$stat = new STAT();
	$stat->$function();
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit();
}
class STAT{
	public function __construct(){
		global $globalArr;
		$this->globalArr = $globalArr;
	}
	public function ActionBeforeLost(){
		foreach ($this->globalArr as $key => $value) {
			global $$value;
		}
		$sql = "select count(data1) sum,param1 as func,date_format(from_unixtime(timestamp/1000),'%Y-%m-%d') as date from "
				."(select l.* from logstat l inner join userprofile u on l.user = u.uid where `timestamp` >= $start and `timestamp` < $end "
				."and type = 0 "
				."and u.level >= $levelMin and u.level <= $levelMax) a group by date,param1";
		$ret = $page->executeServer($selectServer,$sql,3);
		$nameLink['func'] = '功能';
		$nameLinkSort = array_keys($nameLink);
		foreach ($funcList as $key=>$func) {
			$eventAll[$key]['func'] = $func;
		}
		foreach ($ret['ret']['data'] as $curRow){
			$xindex = $curRow['date'];
			$nameLink[$xindex] = $xindex;
			$nameLinkSort[$namLinkSortEnd-strtotime($xindex)] = $xindex;
			$yIndex = $curRow['func'];
			$eventAll[$yIndex][$xindex] = $curRow['sum'];
		}
	}
	public function SkillPanel(){
		foreach ($this->globalArr as $key => $value) {
			global $$value;
		}
		$sql = "select count(data1) sum,param1 as func,date_format(from_unixtime(timestamp/1000),'%Y-%m-%d') as date from  logstat l "
				." inner join userprofile u on l.user = u.uid  inner join user_building b on b.uid=l.`user` "
				." where u.`regTime` >= $start and u.`regTime` < $end and l.type = 0 and u.level >= $levelMin and u.level <= $levelMax $versionStr "
				." and b.itemId=400000 and b.level >= $buildMin and b.level <= $buildMax "
				." group by date,param1";
		$ret = $page->executeServer($selectServer,$sql,3);
		$nameLink['func'] = '功能';
		$nameLinkSort = array_keys($nameLink);
		foreach ($funcList as $key=>$func) {
			$eventAll[$key]['func'] = $func;
		}
		foreach ($ret['ret']['data'] as $curRow){
			$xindex = $curRow['date'];
			$nameLink[$xindex] = $xindex;
			$nameLinkSort[$namLinkSortEnd-strtotime($xindex)] = $xindex;
			$yIndex = $curRow['func'];
			$eventAll[$yIndex][$xindex] = $curRow['sum'];
		}
	}
	public function LastAction(){
		foreach ($this->globalArr as $key => $value) {
			global $$value;
		}
		if(isset($_REQUEST['UserAction']) && $_REQUEST['UserAction']){
			$UserAction = $_REQUEST['UserAction'];
			$sqlAction = "SELECT distinct(u.name),u.level,u.appVersion,u.uid 
			from logaction l INNER JOIN userprofile u on l.`user`=u.uid  INNER JOIN user_building b on b.uid=l.`user`
			where l.action='$UserAction' and u.`regTime` >= $start and u.`regTime` < $end  and u.level >= $levelMin and u.level <= $levelMax
			and b.itemId=400000 and b.level >= $buildMin and b.level <= $buildMax $versionStr";
			$result = $page->executeServer($selectServer, $sqlAction,3);
			$html = '<div><table class="listTable" cellspacing="1" padding="0" ><tr><td colspan=5 style="text-align:center;">'.$UserAction.'  '.count($result['ret']['data']).'人</td></tr><tr><td>dd</td><td>UID</td><td>名字</td><td>级别</td><td>版本</td></tr>';
			foreach ($result['ret']['data'] as $key=>$value){
				$html .='<tr class="listTr"><td>'.($key+1).'</td><td>'.$value['uid'].'</td><td>'.$value['name'].'</td><td>'.$value['level'].'</td><td>'.$value['appVersion'].'</td></tr>';
			}
			$html .= '</table></div>'; 
			echo $html;
			exit();
		}
		$sqlAllUser = "SELECT COUNT(DISTINCT l.user) as sum
		from logaction l INNER JOIN userprofile u on l.`user`=u.uid  INNER JOIN user_building b on b.uid=l.`user`
		where u.`regTime` >= $start and u.`regTime` < $end  and u.level >= $levelMin and u.level <= $levelMax
		and b.itemId=400000 and b.level >= $buildMin and b.level <= $buildMax $versionStr ";
		$retAlluser = $page->executeServer($selectServer, $sqlAllUser,3);
		
		$sql = "SELECT l.action,COUNT(l.action) as sum,COUNT(DISTINCT (l.user)) as userNum  
				from logaction l INNER JOIN userprofile u on l.`user`=u.uid  INNER JOIN user_building b on b.uid=l.`user`  
				where u.`regTime` >= $start and u.`regTime` < $end  and u.level >= $levelMin and u.level <= $levelMax  
				and b.itemId=400000 and b.level >= $buildMin and b.level <= $buildMax $versionStr
				GROUP BY l.action";
		$ret = $page->executeServer($selectServer,$sql,3);
		$nameLink = array('func'=>'action','func2'=>'行为','sum'=>'次数','userNum'=>'人数');
		$nameLinkSort = array_keys($nameLink);
		$eventAll[0]['func'] = '总人数';
		$eventAll[0]['func2'] = '-';
		$eventAll[0]['sum'] = '-';
		$eventAll[0]['userNum'] = $retAlluser['ret']['data'][0]['sum'];
		$actionList = array(
				'ability.confirm'=>'学习能力','ability.random'=>'获得新能力/遗忘能力','al.acceptapply'=>'同意入盟申请','al.apply'=>'申请入盟',
				'al.applylist'=>'查看申请列表','al.attr'=>'修改联盟信息','al.cancelapply'=>'取消入盟申请','al.create'=>'创建联盟','al.dismiss'=>'解散联盟',
				'al.kick'=>'联盟踢人','al.leave'=>'退盟','al.msg'=>'联盟聊天','al.name'=>'检查联盟名字','al.rank'=>'查看联盟排行','al.refuseapply'=>'拒绝入盟申请',
				'al.search'=>'搜索联盟','army.add'=>'造兵','army.cd'=>'直接造兵','army.complete'=>'收兵','build.create'=>'建造建筑','build.info'=>'查看建筑信息',
				'build.upgrade'=>'升级建筑','chat.country'=>'','chat.get'=>'','chat.shield.list'=>'','common.res.syn'=>'','fort.build'=>'造陷阱',
				'fort.build.done'=>'获得陷阱(类似收兵)','get.user.info'=>'聊天获得玩家信息','item.buy'=>'买道具','item.use'=>'使用道具','login.init'=>'登录',
				'mail.cancel.save'=>'邮件取消保存','mail.delete'=>'删除邮件','mail.read'=>'读邮件','mail.read.status'=>'邮件已读','mail.save'=>'保存邮件',
				'mail.send'=>'发邮件','mread.batch'=>'获取邮件内容','queue.ccd'=>'加速队列','queue.finish'=>'队列完成','science.research'=>'研究科技',
				'science.upgrade'=>'科技升级了','show.status.item'=>'查看状态作用列表','skill.clear'=>'重置技能点','skill.save'=>'保存新的技能点',
				'stat.log'=>'后台打点(统计用)','stat.tt'=>'','task.reward.get'=>'领取任务奖励','tile.open'=>'','user.lv'=>'玩家断线后重连',
				'user.modify.nickName'=>'玩家改名','world.favo.get'=>'','world.get'=>'','world.get.detail'=>'','world.get.march'=>'',
				'world.leave'=>'','world.march'=>'','world.march.retreat'=>'',
				'world.march.spd'=>'','world.mv'=>'','world.scout.detail'=>'','world.user.army'=>'', 
		);
		foreach ($ret['ret']['data'] as $key =>$curRow){
			$yIndex = $curRow['action'];
			$eventAll[$yIndex]['func'] = $curRow['action'];
			$eventAll[$yIndex]['func2'] = $actionList[$curRow['action']];
			$eventAll[$yIndex]['sum'] = $curRow['sum'];
			$eventAll[$yIndex]['userNum'] = '<a href=javascript:getUserByAction("'.$curRow['action'].'")>'.$curRow['userNum'].'</a>';
		}
	}
	public function UserAction(){
		foreach ($this->globalArr as $key => $value) {
			global $$value; 
		}
		$sql = "select count(data1) sum,param1 as func,date_format(from_unixtime(timestamp/1000),'%Y-%m-%d') as date from "
				."(select l.* from logstat l inner join userprofile u on l.user = u.uid where `timestamp` >= $start and `timestamp` < $end "
				."and type = 0 "
				."and u.level >= $levelMin and u.level <= $levelMax) a group by date,param1";
		$ret = $page->execute($sql,3);
		$nameLink['func'] = '功能';
		$nameLinkSort = array_keys($nameLink);
		foreach ($funcList as $key=>$func) {
			$eventAll[$key]['func'] = $func;
		}
		foreach ($ret['ret']['data'] as $curRow){
			$xindex = $curRow['date'];
			$nameLink[$xindex] = $xindex;
			$nameLinkSort[$namLinkSortEnd-strtotime($xindex)] = $xindex;
			$yIndex = $curRow['func'];
			$eventAll[$yIndex][$xindex] = $curRow['sum'];
		}
	}
	public function PayPanel(){
		foreach ($this->globalArr as $key => $value) {
			global $$value; 
		}
		$sql = "select count(data1) sum,param1 as func,date_format(from_unixtime(timestamp/1000),'%Y-%m-%d') as date from "
				."(select l.* from logstat l inner join userprofile u on l.user = u.uid where `timestamp` >= $start and `timestamp` < $end "
				."and type = 2 "
				."and u.level >= $levelMin and u.level <= $levelMax) a group by date,param1";
		$ret = $page->execute($sql,3);
		$nameLink['func'] = '功能';
		$nameLinkSort = array_keys($nameLink);
		foreach ($funcList as $key=>$func) {
			$eventAll[$key]['func'] = $func;
		}
		foreach ($ret['ret']['data'] as $curRow){
			$xindex = $curRow['date'];
			$nameLink[$xindex] = $xindex;
			$nameLinkSort[$namLinkSortEnd-strtotime($xindex)] = $xindex;
			$yIndex = $curRow['func'];
			$eventAll[$yIndex][$xindex] = $curRow['sum'];
		}
	}
	public function ArenaChallenge(){
		foreach ($this->globalArr as $key => $value) {
			global $$value; 
		}
		$now = time();
		$sql = "SELECT u.`name`, u.`level`, c.reputation, c.defendNum, c.startTime, c.endTime, a.effect1, a.effect2, a.effectEnd, c.ownerBefore "
				."FROM challengestage c LEFT JOIN userprofile u ON c.ownerId = u.uid LEFT JOIN arena a ON c.ownerId = a.uid "
				."WHERE c.endTime > ($now * 1000) ORDER BY c.reputation";
		$ret = $page->execute($sql,3);
		$nameLink = array (
			'name'=>'占领者姓名 ', 'level'=>'占领者等级', 'reputation'=>'声望', 'effect1' => '攻击加成', 'effect2' => '生命加成', 
			'defendNum'=>'防守次数', 'time'=>'已占时长(分钟)', 
			'startTime'=>'开始占领时间', 'endTime'=>'结束占领时间', 'ownerBefore'=>'易主情况');
		$num = 0;
		$sumTime = 0;
		$sumDefendNum = 0;
		$eventAll[0]['name'] = '合计';
		foreach ($ret['ret']['data'] as $yIndex => $curRow){
			if ($curRow['effectEnd'] < $now * 1000) {
				$curRow['effect1'] = '';
				$curRow['effect2'] = '';
			}
			$curRow['time'] = ceil(($now - $curRow['startTime'] / 1000) / 60);
			$curRow['startTime'] = date('Y-m-d H:i:s',$curRow['startTime'] / 1000);
			$curRow['endTime'] = date('Y-m-d H:i:s',$curRow['endTime'] / 1000);
			$eventAll[$yIndex + 1]= $curRow;
			$num++;
			$sumTime += $curRow['time'];
			$sumDefendNum += $curRow['defendNum'];
		}
		$eventAll[0]['time'] = $sumTime;
		$eventAll[0]['defendNum'] = $sumDefendNum;
		$eventAll[0]['level'] = sprintf("%.2f",($sumDefendNum / $sumTime));
		$sql = "SELECT COUNT(*) AS num FROM arena WHERE reputation > 0";
		$ret = $page->execute($sql,3);
		echo "擂台总数量   : $num     在竞技场中参与过战斗的玩家总数  : {$ret['ret']['data'][0]['num']}";
		echo '<br/>合计里占领者等级为 总防御次数  / 总防御时间';
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
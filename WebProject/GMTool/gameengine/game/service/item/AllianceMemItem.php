<?php
/**
 * AllianceMemItem
 * 联盟成员属性
 */
import('persistence.dao.RActiveRecord');
class AllianceMemItem extends RActiveRecord {
	protected $AllianceId;        //联盟ID
	protected $MemberId;		//成员ID
	protected $type;			//成员官职类型  2：盟主， 1：副盟 ，0：成员
	protected $status;			// 0：申请状态，1：已加入	状态，2:删除
	protected $createTime; 		//创建时间
	protected $contribution; 		//贡献度
	protected $power;
	protected $totalExp;			//入盟后，对联盟经验贡献的总经验
	protected $dailyExp;				//每日，联盟经验贡献
	protected $time;				//每日时间，用于清零每日经验贡献。
	protected $welfareCount; //联盟福利今日领取的次数
	protected $welfareTime; //联盟福利领取cd
	protected $attProclaimFlag; //是否参加联盟天降活动
	
	
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		return self::toObject(__CLASS__, $results, $retArr);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($allianceUid){
		return self::getOne(__CLASS__, $allianceUid);
	}
	
	/**
	 * 获得联盟内所有已加入的成员
	 * @param unknown_type $AllianceId
	 */
	static function getAllMember ($AllianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId = '{$AllianceId}' and status = 1";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	//取得用户申请的联盟表
	static function getApplyAlliance($MemberId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where MemberId='{$MemberId}' and status = 0 limit 5";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	//根据成员的用户ID取得Member
	static function getAllianceMem($AllianceId,$MemberId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where MemberId='{$MemberId}' and  AllianceId='{$AllianceId}' limit 1";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	//返回成员页数的数据，有$range指定数据库范围
	static function getMemItems($allianceUid,$loweLimit,$upLimit){
		$loweLimit = $loweLimit - 1;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId = '{$allianceUid}' and status = 1 order by type DESC,totalExp DESC,createTime ASC limit {$loweLimit},{$upLimit}";
//		$sql="select alliancemem.* from alliancemem,userprofile WHERE alliancemem.AllianceId = '{$allianceUid}' and alliancemem.status = 1 and alliancemem.MemberId = userprofile.uid order by alliancemem.type DESC,userprofile.level DESC limit {$loweLimit},{$upLimit}";
		$num = $upLimit - $loweLimit;
		$res = $mysql->execResult($sql, $num);
		if(!$res)
			$res = array();
		$arr = array();
		import('service.action.GeneralClass');
		$general = General::singleton();
		foreach($res as $resItem){
			$playerProfile = UserProfile::getWithUID($resItem['MemberId']);
			$general->setUserUid($resItem['MemberId']);
			$power= $general->getUserFightPower();
			if($resItem['power'] != $power ){
				$sql2 = "update alliancemem set power = $power where AllianceId = '{$allianceUid}' and MemberId = '{$resItem['MemberId']}'";
				$mysql->execute($sql2);
				$resItem['power'] = $power;
			}
			if(self::getPlayerOnlineState($resItem['MemberId']))
				$resItem['offTime'] = 0;
			else {
				//import("service.item.CityItem");
				//$cityItem = CityItem::getWithUID($resItem['MemberId']);
				$resItem['offTime'] = time() - $playerProfile->lastLoadTime ;
			}
			import('service.item.ArenaItem');
			$arenaItem = ArenaItem::getWithUID($resItem['MemberId']);
			$resItem['arenaRank'] = $arenaItem->rank;
			$resItem['name'] = $playerProfile->name;
			$resItem['level'] = $playerProfile->level;
			$resItem['face'] = $playerProfile->pic;
			$resItem['vip'] = $playerProfile->vip;
//			$resItem['contribution'] = 0;
			if($resItem['type']==2){//返回盟主的登陆时间，用于弹劾
				import('service.item.ItemSpecManager');
				$data_config = ItemSpecManager::singleton()->getItem("alliance2");
				$validTime = $data_config->k1*24*3600;
				$resItem['validTime'] = $validTime + $playerProfile->date;
			}else{
				$resItem['validTime']=0;
			}
			$arr[] = $resItem;	
		}
		if($resItem && date('y-m-d',time()) != date('y-m-d',$resItem['time'])){//清空每日信息
			
			self::cleardailyExp($resItem['AllianceId']);
			$dir = date('Y-m-d').'_alliancemem';
			$filename = $allianceUid;
			import('service.action.LoggerClass');
			$resItem['changetime'] = time();
			$logger = new FileLogger($filename, $resItem, $dir);
			$logger->log();
		}
		$count = self::getMemberCount($allianceUid);
		return array(
		'count' => $count,
		'MemberList' => $arr,
		);
	
	}
	//返回申请成员页数的数据，有$range指定数据库范围
	static function getApplyItems($allianceUid,$loweLimit,$upLimit){
		self::removeAllapply();
		$loweLimit = $loweLimit - 1;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId='{$allianceUid}' and status=0 limit {$loweLimit},{$upLimit}";
		$num = $upLimit - $loweLimit;
		$res = $mysql->execResult($sql, $num);
		$count = self::getApplyCount($allianceUid);
		$arr = array();
		import('service.action.GeneralClass');
		$general = General::singleton();
		if($count){
			foreach($res as $resItem){
				$playerProfile = UserProfile::getWithUID($resItem['MemberId']);
				$general->setUserUid($resItem['MemberId']);
				$resItem['power']= $general->getUserFightPower();
				$resItem['name'] = $playerProfile->name;
				$resItem['level'] = $playerProfile->level;
				$resItem['face'] = $playerProfile->pic;
				$resItem['vip'] = $playerProfile->vip;
				$resItem['MemberId'] = $resItem['MemberId'];
				$arr[] = $resItem;	
			}
		}
		
		return array(
		'count' => $count,
		'ApplyList' => $arr,
		);
	
	}
	
	 //取得现有成员总数
	static function getMemberCount($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(*) as count from alliancemem where AllianceId = '{$allianceUid}' and status = 1 ";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	 //取得申请成员总数
	static function getApplyCount($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(*) as count from alliancemem where AllianceId='{$allianceUid}' and status=0 ";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	 //取得某一成员申请总数
	static function getOneApplyCount($MemberId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(*) as count from alliancemem where MemberId='{$MemberId}' and status=0";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	//判断某一成员是否已经申请过该联盟
	static function cheackOneApply($allianceUid,$MemberId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->exist('alliancemem', array('AllianceId' => $allianceUid, 'MemberId' => $MemberId,'status' => 0));
		return $res;
	}
	
	//删除全部过期申请
	static function removeAllapply(){
		$time = time();
		$diffTime = $time - 24*3600;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "delete from alliancemem where createTime < {$diffTime} and status=0";
		return $mysql->execute($sql);
	}
	
	//申请成员
	static function apply($allianceUid, $uid, $type=0, $status=0){
		$allianceMemItem = new self;
		$allianceMemItem->AllianceId = $allianceUid;
		$allianceMemItem->MemberId = $uid;
		$allianceMemItem->type = $type;
		$allianceMemItem->status = $status;
		$allianceMemItem->createTime = time();
		$allianceMemItem->contribution = 0;
		$allianceMemItem->totalExp = 0;
		$allianceMemItem->dayExp = 0;
		$allianceMemItem->time = time();
		$allianceMemItem->save();
		return $allianceMemItem;

	}
	
	
	//申请成员
	static function applyMember($allianceUid,$uid,$type=0,$status=0){
		import('service.action.ConstCode');
		//判断，不让申请
		if(self::cheackOneApply($allianceUid,$uid) > 0)
			return ConstCode::ERROR_INVALID;
		$selfcount = self::getOneApplyCount($uid);
		if($selfcount >= 5 )
			return ConstCode::ERROR_APPLY_ALLIANCE_COUNT;
		$allianceMemItem->applyNum = $selfcount + 1;
		$allianceMemItem = new self;
		$allianceMemItem->AllianceId = $allianceUid;
		$allianceMemItem->MemberId = $uid;
		$allianceMemItem->type = $type;
		$allianceMemItem->status = $status;
		$allianceMemItem->createTime = time();
		$allianceMemItem->contribution = 0;
		$allianceMemItem->totalExp = 0;
		$allianceMemItem->dayExp = 0;
		$allianceMemItem->time = time();
		$selfcount = self::getOneApplyCount($uid);
		$allianceMemItem->save();
//		p($allianceMemItem);
		return $allianceMemItem;

	}
	
	////删除申请
	static function removeApply($allianceUid,$UserId){

		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('alliancemem', array('AllianceId' => $allianceUid, 'MemberId' => $UserId,'status' => 0));
	
	}
	//成批删除申请
	static function removeBatchApply($allianceUid,$UserUids){

		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from alliancemem where AllianceId ='{$allianceUid}' and status ='0' and (";
		$sql1 = "";
		foreach ($UserUids as $uid){
			if(strlen($sql1)>0){
				$sql1.=" or ";
			}
			$sql1.="MemberId='".$uid."'";
		}
		$sql1.=")";
		$sql.=$sql1;
		return $mysql->execute($sql);
	
	}
	////成批删除某一成员的申请
	static function removeApplyList($UserId){

		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('alliancemem', array('MemberId' => $UserId,'status' => 0));
	
	}
	
	//批准添加成员
	static function addMember($allianceMemItem){

/*		import('service.item.AllianceItem');
		$allianceItem = AllianceItem::getWithUID($allianceMemItem->AllianceId);
		if($allianceItem->memberNum >= $allianceItem->memLimitNum){
			import('service.action.ConstCode');
			return ConstCode::ERROR_INVALID;
		}*/
		import('service.action.GeneralClass');
		$general = General::singleton();
		$general->setUserUid($allianceMemItem->MemberId);
		$power= $general->getUserFightPower();
		$allianceMemItem->power = $power;
		$allianceMemItem->status = 1;
		$allianceMemItem->time = time();
		return $allianceMemItem->save();
	}
	
	 //删除成员
	static function removeMember($uid){
		$allianceMemItem = self::getWithUID($uid);
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId = '{$allianceMemItem->AllianceId}' and status = 1 order by type DESC,contribution DESC,createTime ASC ";
		$result = $mysql->execute($sql);
		$currentIndex = 1;
		while($curRow = mysql_fetch_assoc($result)) {
			if($curRow['uid'] == $uid)
				break;
			else
				$currentIndex++;
		}
	//	if(count($curRow) == $currentIndex){
			$currentIndex = $currentIndex -1;	
	//	}
		$currentPage = ceil($currentIndex/4);
		$slectedIndex = (($currentIndex%4) ? ($currentIndex%4 -1):3);	
		$mysql->del('alliancemem', array('uid' => $uid));
		return Array('currentPage'=>$currentPage,
				'slectedIndex'=>$slectedIndex,
					);	
	}
	 //升降职称
	static function changeTitle($uid,$type){
		$allianceMemItem = self::getWithUID($uid);
		if($allianceMemItem){
			$allianceMemItem->type = $type;
			$allianceMemItem->save();
		}
		//取得职称改变后所在的页数，前台需要该页数显示
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId = '{$allianceMemItem->AllianceId}' and status = 1 order by type DESC,contribution DESC,createTime ASC ";
		$result = $mysql->execute($sql);
		$currentIndex = 1;
		while($curRow = mysql_fetch_assoc($result)) {
			if($curRow['uid'] == $uid)
				break;
			else
				$currentIndex++;
		}
		$currentPage = ceil($currentIndex/4);
		$slectedIndex = (($currentIndex%4) ? ($currentIndex%4 -1):3);
		return Array('currentPage'=>$currentPage,
				'slectedIndex'=>$slectedIndex,
					);	
		
	}
	//记录个人联盟经验贡献
	static public function addAllianceMemExp($allianceId,$userUid,$value){
		$time = time();
		
		$member = self::getAllianceMem($allianceId,$userUid);
		if(!$member)
			return;
		if($member && date('y-m-d',$time) != date('y-m-d',$member->time)){//清空每日信息
			self::cleardailyExp($allianceId);
			$member->dailyExp = $value;
			$sqlData = array('AllianceId'=>$member->AllianceId,'MemberId'=>$member->MemberId,'type'=>$member->type,'changetime'=>$time,
			'status'=>$member->status,'createTime'=>$member->createTime,'time'=>$member->time,'dailyExp'=>$member->dailyExp,'totalExp'=>$member->totalExp);
			$dir = date('Y-m-d').'_alliancemem';
			$filename = $allianceId;
			import('service.action.LoggerClass');
			$logger = new FileLogger($filename, $sqlData, $dir);
			$logger->log();
		}else{
			$member->dailyExp += $value;
		}
				
		$member->totalExp += $value;
		$member->save();
		
	}
	 //清空每日贡献表
	static function cleardailyExp($allianceUid){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update alliancemem set dailyExp=0,time = '{$time}' where AllianceId = '{$allianceUid}' ";
		$resdata = $mysql->execute($sql);
	}
	 //获得玩家在线状态
	static public function getPlayerOnlineState($playerUid)
	{
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$key = 'ONLINE_USER_' . $playerUid;
		if($cache->get($key))
			$onLine = true;
		else
			$onLine = false;
		return $onLine;
	}
	
	/**
	 * 取得联盟的邀请CD和用户联盟身份
	 */
	static function selectAllianceInviteInfo($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " select a.uid, a.name, a.lastInviteTime, am.type from alliancemem am left join alliance a on a.uid = am.AllianceId  
				where am.MemberId = '{$uid}' ";
		$data = $mysql->execResult($sql);
		if($data) {
			$data = $data[0];
		} 
		return $data;
	}
	
	/**
	 * 取得联盟身份
	 */
	static function selectLeagueRoleByUserId($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select type from alliancemem where MemberId = '{$uid}' ";
		$data = $mysql->execResult($sql);
		if($data) {
			return $data[0]['type'] == 0 ? 'member' : ($data[0]['type'] == 1 ? 'secLeader' : 'leader');
		}
		return false;
	}
	
	/**
	 * 取得参与联盟天降奇兵的成员
	 */
	static function selectAttAllianceProclaimPlayers() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select am.MemberId as uid, u.name, am.AllianceId, am.attProclaimFlag from alliancemem am
				left join userprofile u on am.MemberId = u.uid  
				where am.attProclaimFlag != 0";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 清除参与联盟天降活动的标志
	 */
	static function clearAttAllianceProclaimFlag() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update alliancemem set attProclaimFlag = 0";
		return $mysql->execute($sql);
	}
}
?>
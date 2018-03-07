<?php
/**
 * AllianceItem
 * 联盟列表属性
 */
import('persistence.dao.RActiveRecord');
class AllianceItem extends RActiveRecord {
	protected $leader;      //联盟盟主
	protected $name;		//联盟名称
	protected $exp;			//联盟经验
	protected $level;		//联盟等级
	protected $createTime; 	//创建时间
	protected $post;		//联盟公告
	protected $declaration;	//联盟宣言
	protected $country;		//联盟国籍
	protected $memberNum;	//成员数量
	protected $vpNum;		//副盟数量
	protected $vpLimitNum;	//副盟主上限
	protected $memLimitNum;	//成员上限
	protected $time1;		//记录人数小于3的时间
	protected $time2;		//记录最后成员登陆时间
	protected $points;		//目前联盟资源争夺战: 联盟积分
	protected $resource;		//联盟物资，用于宣战
	protected $agreeCount;     //用于实现：每收1个人给一份奖励， 只有收前30人有奖
	protected $lastInviteTime;           //联盟上次邀请时间戳 
	protected $allianceWarfareRemainNum; //联盟福利今日还剩余的次数，每日0点重置
	protected $flushTime; //联盟每日刷新
	protected $heroLevel; //英雄等级
	protected $heroExp;   //英雄经验
	protected $currMaxDarkLevel; //黑暗入侵当前最大可选难度
	protected $firstDarkFlag; //标志已通过的关卡
	
	//请求联盟列表。
	
	public  function getItems($uid){
		$playerProfile = UserProfile::getWithUID($uid);
		if(!$playerProfile->league)
			return Array();
		$data = array();
		$data[]= self::getSelfAlliance($uid,$playerProfile->league);
		return $data;
	}
	
		
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
		return self::getOne(__CLASS__,$allianceUid);
	}
	
	
	//检查是否重名
	static function cheackCRName($name){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->exist('alliance', array('name' => $name));
		if($res > 0)
			return true;
		else 
			return false;
	}
	
	//检查某一国联盟是否存在
	static function cheackAllianceBycountry($country){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->exist('alliance', array('country' => $country));
		if($res > 0)
			return true;
		else 
			return false;
	}
	//取得用户申请的联盟表
	static function getApply($MemberId){
		$arr = array();
		import('service.item.AllianceMemItem');
		$apply = AllianceMemItem::getApplyAlliance($MemberId);
		if(count($apply)){
			foreach($apply as $applyItem){
				$allianceItem = self::getWithUID($applyItem[AllianceId]);
				$arr[] = array(
				'uid' => $allianceItem->uid,
				'itemId' => null,
				'leader' => $allianceItem->leader,
				'name' => $allianceItem->name,
				'exp' => $allianceItem->exp,
				'level' => $allianceItem->level,		
				'createTime' => $allianceItem->createTime,
				'post' => $allianceItem->post,
				'declaration' => $allianceItem->declaration,
				'country' => $allianceItem->country,
				'memberNum' => $allianceItem->memberNum,
				'vpNum' => $allianceItem->vpNum,
				'vpLimitNum' => $allianceItem->vpLimitNum,
				'memLimitNum' => $allianceItem->memLimitNum,
				);
			}
		}
		
		return $arr;
		
	}
	
	//返回相应页数的数据，有$range指定数据库范围
	static function getPageItems($loweLimit,$upLimit,$user){
		//清除到期的敌对关系
		import('service.item.AllianceEnemyItem');
		$allianceEnemyItem = AllianceEnemyItem::deleteAllianceEnemy();
		
		self::removeAlliance();
		
		$allianceOrder = self::getAllianceOrder($user, $loweLimit, $upLimit);
		$count = self::getAllianceCount();
		//取得最多的三个申请表
		$apply = self::getApply($user->uid);
		if($user->league){
			import('service.item.AllianceEnemyItem');
			$challengeCount = AllianceEnemyItem::getAllianceEnemyCount($user->league);
		}else{
			$challengeCount=0;
		}
		
		return array(
		'count' => $count,
		'challengeCount' => $challengeCount,
		'AllianceList' => $allianceOrder,
		'ApplyList' => $apply,
		);
	
	}
	
	//联盟列表排序（玩家未加入联盟前适用，加入联盟后采用现有排序）
	static function getAllianceOrder($user, $loweLimit, $upLimit) {
		$loweLimit = $loweLimit - 1;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		if($user->league) {
			$sql = "select a.*, sum(am.power) as totalFightPower, apr.currRank as RPRank from alliance a left join alliancemem am on a.uid = am.AllianceId 
					left join alliancepointsrank apr on a.uid = apr.uid group by a.uid order by level DESC,createTime ASC limit {$loweLimit},{$upLimit}";
			$num = $upLimit - $loweLimit;
			$res = $mysql->execResult($sql, $num);
			return $res;
		}
		$sql = "select a.*, sum(am.power) as totalFightPower, apr.currRank as RPRank from alliance a left join alliancemem am on a.uid = am.AllianceId 
				left join alliancepointsrank apr on a.uid = apr.uid group by a.uid 
				order by a.memLimitNum - a.memberNum desc, a.level desc, a.createTime asc limit {$loweLimit},{$upLimit}";
		$allianceOrder = $mysql->execResultWithoutLimit($sql);
		$allianceLeaderSql = "select a.uid, am.MemberId from alliance a left join alliancemem am on a.uid = am.AllianceId where am.type != 0";
		$allianceLeaders = $mysql->execResultWithoutLimit($allianceLeaderSql);
		$finalAllianceOrder = array();
		if($allianceOrder) {
			foreach($allianceOrder as $key => $allianceItem) {
				$leaderOnline = self::checkAllianLeaderOnline($allianceLeaders, $allianceItem['uid']);
				if($leaderOnline) {
					unset($allianceOrder[$key]);
					$tempItems =Array();
					if($finalAllianceOrder){
						$isExist= false;
						foreach($finalAllianceOrder as $tempItem){
							if(($allianceItem['memLimitNum']-$allianceItem['memberNum'])>($tempItem['memLimitNum']-$tempItem['memberNum'])){
								$tempItems[] = $allianceItem;
								$isExist;
							}else{
								$tempItems[]= $tempItem;
							}
						}
						if(!$isExist){
							$tempItems[] = $allianceItem;
						}
						$finalAllianceOrder= $tempItems;
					}else{
						$finalAllianceOrder[] = $allianceItem;
					}
				}
			}
			if($allianceOrder) {
				foreach($allianceOrder as $allianceItem) {
					$finalAllianceOrder[] = $allianceItem;
				}
			}
			return $finalAllianceOrder;
		}
		return array();
	}
	
	static function checkAllianLeaderOnline($allianceLeaders, $allianceId) {
		if($allianceLeaders) {
			$leaders = array();
			foreach($allianceLeaders as $leaderItem) {
				if($leaderItem['uid'] == $allianceId) {
					$leaders[] = $leaderItem['MemberId'];
				}
			}
			if($leaders) {
				foreach($leaders as $leaderUid) {
					if(UserProfile::checkUserOnline($leaderUid)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	static function getAllianceProclaimOrder($country) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select a.uid,a.name,a.country,a.level,a.memberNum,a.memLimitNum, sum(am.power) as totalFightPower from alliance a left join alliancemem am on a.uid = am.AllianceId 
				where am.status = 1 and a.country != {$country} group by a.uid order by totalFightPower";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}

 	//取得联盟总数
	static function getAllianceCount(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(*) as count from alliance ";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	//添加联盟
	static function addAlliance($from){
		import('service.action.ConstCode');
//		$rank = self::getMailCount();
		
		$allianceItem = new self;
		$allianceItem->leader = $from->leader;
		$allianceItem->name = $from->name;
		$allianceItem->exp = 0;
		$allianceItem->level = 1;
		$allianceItem->createTime = time();
		$allianceItem->post = $from->post;

		$allianceItem->declaration = $from->declaration;
		$allianceItem->country = $from->country;
		$allianceItem->memberNum = 1;
		$allianceItem->vpNum = 0;
		
		$allianceItem->vpLimitNum = $from->vpLimitNum;
		$allianceItem->memLimitNum = $from->memLimitNum;
		$allianceItem->time1 = time();
		$allianceItem->time2 = time();
		$allianceItem->resource = 0;
		$allianceItem->heroLevel = 1;
		$allianceItem->heroExp = 0;
		$allianceItem->save();
		
		return $allianceItem;
	}
	
	
	 //更新自己联盟信息
	static function updateSelfAlliance($allianceUid,$type,$value){
		$allianceItem = self::getWithUID($allianceUid);
		if($allianceItem){
			switch ($type) {
				case 1:
					$allianceItem->memberNum = $value;	
					break;	
				case 2:
					$allianceItem->vpNum = $value;	
					break;				
	
				case 3:
					$allianceItem->exp = $value;
					//判断是否升级level	
					break;		
				case 4:
					$allianceItem->post = $value;	
					break;			
				case 5:
					$allianceItem->declaration = $value;	
					break;		
				case 6:
					$allianceItem->leader = $value;	
					break;
				case 7:				
					$allianceItem->name = $value;	
					break;	
				default:
					;
				break;
			}
			
			$allianceItem->save();
		}
		return array();
	}
	
	 //取得自己联盟信息
	static function getSelfAlliance($uid,$allianceUid){
		$allianceItem = self::getWithUID($allianceUid);
		if($allianceItem) {
			$allianceItem->unserializeProperty('firstDarkFlag');
		}
//		p($allianceItem);
		$data= self::resArr($uid,$allianceItem);
		return $data;
	}
	
	//
	//
	static function resArr($uid,$allianceItem){
		import('service.item.AllianceMemItem');
		$allianceMemItem = AllianceMemItem::getAllianceMem($allianceItem->uid,$uid);
		import('service.item.ItemSpecManager');
		$AllianceXml = ItemSpecManager::singleton('default','role.xml')->getItem($allianceItem->level  + 2000);
		$updateExp= $AllianceXml->alliance_exp;
		if($AllianceXml->member_num != $allianceItem->memLimitNum){	
			$allianceItem->memLimitNum = $AllianceXml->member_num;
			$allianceItem->save();
		}
		if($AllianceXml->vp_num != $allianceItem->vpLimitNum){	
			$allianceItem->vpLimitNum = $AllianceXml->vp_num;
			$allianceItem->save();
		}
		if($allianceItem->resource==-5){
			import('service.item.ContrRankItem');
			$resource = ContrRankItem::getTotalResource($allianceItem->uid);
			$allianceItem->resource = $resource;
			$allianceItem->save();
		}
		//取得敌对联盟信息
		import('service.item.AllianceEnemyItem');
		$enemyItems = AllianceEnemyItem::getAllianceEnemy($allianceItem->uid);
		
		$welfareCD = ItemSpecManager::singleton('default','item.xml')->getItem('alliance3')->k2 * 60 * 60;
		import("service.action.CalculateUtil");
		$allies_reward = CalculateUtil::getInfoByRewardId($AllianceXml->allies_reward);
		
		import('service.item.UserAllianceWelfareItem');
		$userAllianceWelfareItem = UserAllianceWelfareItem::getWithUID($uid);
		if(!$userAllianceWelfareItem) {
			$userAllianceWelfareItem = UserAllianceWelfareItem::init($uid);
		}
		import('service.item.AlliancePointsRankItem');
		$RPRank = AlliancePointsRankItem::selectRPRank($allianceItem->uid);
		$allianceHeroInfo = self::getAllianceHeroInfo($allianceItem);
		$leader = UserProfile::getWithName($allianceItem->leader);
		$AllianceNextXml = ItemSpecManager::singleton('default','role.xml')->getItem($allianceItem->level + 1 + 2000);
		if($allianceItem->exp>=$updateExp &&(!$AllianceNextXml->vp_num))
			$isMax =1;
		else
			$isMax =0;
		return array(
			'uid' => $allianceItem->uid,
			'itemId' => null,
			'leader' => $allianceItem->leader,
			'leaderUid' => $leader->uid,
			'name' => $allianceItem->name,
			'exp' => $allianceItem->exp,
			'level' => $allianceItem->level,		
			'createTime' => $allianceItem->createTime,
			'post' => $allianceItem->post,
			'declaration' => $allianceItem->declaration,
			'country' => $allianceItem->country,
			'memberNum' => $allianceItem->memberNum,
			'vpNum' => $allianceItem->vpNum,
			'vpLimitNum' => $allianceItem->vpLimitNum,
			'memLimitNum' => $allianceItem->memLimitNum,
			'updateExp' => $updateExp,
			'type' => $allianceMemItem->type,
			'allianceMemUid' => $allianceMemItem->uid,
			'contribution' => $allianceMemItem->contribution,
			'enemyLeagues' =>$enemyItems,
			'resource' =>$allianceItem->resource,
			'time1' =>$allianceItem->time1,
			'welfareCount' => $userAllianceWelfareItem->welfareCount,
			'welfareTime' => $userAllianceWelfareItem->welfareTime + $welfareCD,
			'allies_reward' => $allies_reward,
			'allies_reward_cost' => $AllianceXml->allies_reward_cost,
			'RPRank' => $RPRank,
			'heroInfo' => $allianceHeroInfo['heroInfo'],
			'worshipConfig' => $allianceHeroInfo['worshipConfig'],
			'currMaxDarkLevel' => $allianceItem->currMaxDarkLevel,
			'firstDarkFlag' => $allianceItem->firstDarkFlag,
			'isMaxLv'=>$isMax,
		);
	}
	
	static function getAllianceHeroInfo($allianceItem) {
		$heroItemConfigXml = ItemSpecManager::singleton('default','role.xml')->getItem($allianceItem->heroLevel  + 2000);
		$nextHeroItemConfigXml = ItemSpecManager::singleton('default','role.xml')->getItem($allianceItem->heroLevel + 1 + 2000);
		$isMaxLevel = false;
		if($allianceItem->heroExp == $heroItemConfigXml->alliance_heroexp && $nextHeroItemConfigXml->alliance_heroexp == 0) {
			$isMaxLevel = true;
		}
		$heroInfo = array(
			'heroLevel' => $allianceItem->heroLevel,
			'heroCurrExp' => $allianceItem->heroExp,
			'heroCurrLevelMaxExp' => $heroItemConfigXml->alliance_heroexp,
			'alliance_heroreward' => $heroItemConfigXml->alliance_heroreward,
			'isMaxLevel' => $isMaxLevel,
		);
		$worshipConfig = array();
		$worshipConfigXml = ItemSpecManager::singleton('default','jibai.xml')->getGroup('jibai');
		import('service.action.CalculateUtil');
		if($worshipConfigXml) {
			foreach($worshipConfigXml as $key => $wcItem) {
				$rewardData = CalculateUtil::getInfoByRewardId($wcItem->reward);
				$worshipConfig[] = array('id' => $wcItem->id, 'costtype' => $wcItem->type, 
								'cost' => $wcItem->cost, 'reward' => $rewardData, 'exp' => $wcItem->exp);
			}
		}
		$data['heroInfo'] = $heroInfo;
		$data['worshipConfig'] = $worshipConfig;
		return $data;
	}
	
	/**
	 * 获得联盟成员中各职位的人数
	 * @param string $allianceUid
	 * @return array	
	 */
	static function getMemberCount($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(1) as count,type from alliancemem where allianceId = '$allianceUid' group by type";
		$data = $mysql->execResultWithoutLimit($sql);
		return $data;
	}
	
	 //删除联盟,同时删除排行榜的数据
	static function deleteAlliance($allianceUid){
		import('service.item.AlliancePointsRankItem');
		AlliancePointsRankItem::deleteAlliance($allianceUid);
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('alliance', array('uid' => $allianceUid));
	}
	
	 //删除成员和清除联盟标志
	static function deleteMember($allianceUid){
		$res = array();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem where AllianceId = '{$allianceUid}' ";
		$result = $mysql->execute($sql);
		while ($curRow = mysql_fetch_assoc($result)) 
			$res[] = $curRow;
		foreach($res as $Member){
				//清除成员联盟标志
			if($Member[status] == 1){
				$playerProfile = UserProfile::getWithUID($Member['MemberId']);
				$playerProfile->league = null;
				$playerProfile->save();
				//清除成员的联盟科技
				import('service.item.ScienceItem');
				ScienceItem::resetScience($Member['MemberId'],7);
				//清除联盟建筑每日捐献
				import('service.item.LordItem');
				$lordItem = LordItem::getWithUID($Member['MemberId']);
				if($lordItem) {
					$lordItem->donatedMoneyDaily = 0;
					$lordItem->save();
				}
			}
			//删除成员和申请列表
			import('service.item.AllianceMemItem');
			AllianceMemItem::removeMember($Member['uid']);

		}
		
	}
	
	//判断解散联盟
	static function removeAlliance(){
		$resdata = array();
//		import('service.action.ConstCode');
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliance";
		$result = $mysql->execute($sql);
//		$resdata = $mysql->execResult($sql,3);
		while ($curRow = mysql_fetch_assoc($result)) 
			$resdata[] = $curRow;

		import('service.action.AllianceBattleClass');
		import('service.action.WorldClass');
		foreach ($resdata as $resItem){
			//满足条件1解散：人数不足三人，持续七天
			if($resItem['memberNum']<3 && $resItem['time1']+3600*24*7 < time()){
				$isOperateAlliance = AllianceBattle::checkAllianceOperate($resItem['uid']);
				if(!$isOperateAlliance) {
					continue;
				}
				//清除成员联盟标志
				self::deleteMember($resItem['uid']);
				//清除联盟	
				self::deleteAlliance($resItem['uid']);
				//清除占有的遗迹，部队返回
				World::handleRelicAfterAllianceDismiss($resItem['uid']);
				//清除联盟建筑数据
				import('service.item.ScienceItem');
				ScienceItem::clearAllianceBigScience($resItem['uid']);
				import('service.item.ContrRankItem');
				ContrRankItem::removeAlliance($resItem['uid']);	
				continue;
			}
			//满足条件2解散：10天，全部成员未登陆
			if($resItem['time2']+3600*24*10 < time()){
				//清除成员联盟标志
				self::deleteMember($resItem['uid']);
				//清除联盟	
				self::deleteAlliance($resItem['uid']);
				//清除占有的遗迹，部队返回
				World::handleRelicAfterAllianceDismiss($resItem['uid']);
				//清除联盟建筑数据
				import('service.item.ScienceItem');
				ScienceItem::clearAllianceBigScience($resItem['uid']);
				import('service.item.ContrRankItem');
				ContrRankItem::removeAlliance($resItem['uid']);
			}
		
		}
		
	}
	
	static function updateAllianceInviteCD($uid, $inviteCD) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " update alliance set lastInviteTime = {$inviteCD} where uid = '{$uid}'";
		return $mysql->execute($sql);
	}
	
	static function getAllianceBigScienceMemberRank($allianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select am.MemberId, u.name, ld.donatedTotalMoney from alliancemem am left join lord ld on am.MemberId = ld.uid 
				left join userprofile u on ld.uid = u.uid where am.status != 0 and am.AllianceId = '{$allianceId}' order by ld.donatedTotalMoney desc";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	static function selectAlliancePowerInfo($allianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select a.uid,a.name,a.country,a.level,a.memberNum,a.memLimitNum,sum(am.power) as power 
				from alliance a left join alliancemem am on a.uid = am.AllianceId
				where a.uid = '{$allianceId}'";
		$alliance = $mysql->execResultWithoutLimit($sql);
		if($alliance) {
			return $alliance[0];
		}
		return array();
	}
	
	public function save() {
		$this->serializeProperty('firstDarkFlag');
		parent::save();
		$this->unserializeProperty('firstDarkFlag');
	}
}
?>
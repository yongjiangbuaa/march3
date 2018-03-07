<?php
/**
 * WorldItem.php
 * 
 * 世界数据表world对应的Item
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class WorldItem extends RActiveRecord {
	
	protected $x;
	protected $y;
	protected $type; //类型: 0：无类型; 1：核心建城点; 2:周边建城点; 3:联盟BOSS
	protected $country; //该点所属国家,0为国界
	protected $occupant; //占领者
	protected $relicId; //遗迹ID, (联盟ID_BOSSID)
	protected $npcRemainForces; //遗迹NPC剩余兵力
	protected $occupantStartTime; //占领开始时间，目前神迹用,联盟BOSS召唤时间
	protected $time;
	
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
	 * 获取地图数据
	 */
	static function getWorldMapData($x1, $y1, $x2, $y2, $user) {
		$userUid = $user -> uid;
		$data = array('world' => array(), 'relic' => array(), 'allianceBoss' => array ());
		import('util.mysql.XMysql');
		$time = time();
		$sql = "select w.x, w.y, w.type as worldType, w.occupant, u.name as userName, u.pic, u.vip,
				u.level as userLevel, u.league, a.name as allianceName, b.level, p.endTime, p.waitTime, uw.whiteBanner as banner, e.effectList
				from world w left join building b on w.occupant = b.ownerId 
				left join userprofile u on w.occupant = u.uid
				left join userworld uw on w.occupant = uw.uid
				left join effect e on w.occupant = e.uid
				left join alliance a on u.league = a.uid
				left join proclaimwar p on (((p.ownerId = '$userUid' and p.targetId = w.occupant) or (p.targetId = '$userUid' and p.ownerId = w.occupant)) and p.type = 1 and {$time} < p.endTime)
				where w.x >= {$x1} and w.y >= {$y1} and w.x <= {$x2} and w.y <= {$y2} 
				and w.type = 1 and b.itemId = 1301000 and w.occupant != '' ";
		$sqlDatas = XMysql::singleton()->execResultWithoutLimit($sql);
		foreach ($sqlDatas as $key=>$sqlData) {
			$sqlDatas[$key]['worldFightStatus']['endTime'] = $sqlData['endTime'];
			$sqlDatas[$key]['worldFightStatus']['waitTime'] = $sqlData['waitTime'];
			unset($sqlDatas[$key]['endTime']);
			unset($sqlDatas[$key]['waitTime']);
			if($sqlData['banner']) {
				$sqlDatas[$key]['banner'] = self::getWhiteBanner($sqlData['banner']);
			}
			$cityPicStatusIds = array('1100019', '1100020');
			if($sqlData['effectList']) {
				$effectList = json_decode($sqlData['effectList'], true);
				$statusIds = array();
				foreach ($effectList as $k => $effect){
					if($time > $effect['endTime']) continue;
					$cityStatusFlag = in_array($effect['statusId'], $cityPicStatusIds);
					if($cityStatusFlag) {
						$xmlStatus = ItemSpecManager::singleton('default', 'item.xml')->getItem($effect['statusId']);
						$statusIds[] = array(
							'statusId' => $effect['statusId'], 
							'cityStatusFlag' => 1,
							'cityPic' => $effect['cityPic'], 
							'endTime' => $effect['endTime'],
							'effect1' => $xmlStatus->effect1,
							'value1' => $xmlStatus->value1,
							'effect2' => $xmlStatus->effect2,
							'value2' => $xmlStatus->value2,
						);
					}
					if(($effect['statusId'] > 1100191 && $effect['statusId'] < 1100200)) {
						$statusIds[] = array('statusId' => $effect['statusId']);
					}
				}
				$sqlDatas[$key]['effectList'] = $statusIds;
			}
		}
		$data['world'] = $sqlDatas;
		$sql = "select w.x, w.y, w.occupant, w.type as worldType, a.name as allianceName, w.relicId, w.occupantStartTime, w.npcRemainForces
				from world w left join alliance a on w.occupant = a.uid 
				where w.type = 2 and w.x >= {$x1} and w.y >= {$y1} and w.x <= {$x2} and w.y <= {$y2}";
		$relicData = XMysql::singleton()->execResultWithoutLimit($sql);
		if($relicData) {
			import('service.item.ItemSpecManager');
			foreach($relicData as $k => $relicItem) {
				$relicXml = ItemSpecManager::singleton('default','relic.xml')->getItem($relicItem['relicId']);
				$relicItem['lv_require'] = $relicXml->lv_require;
				$relicData[$k] = $relicItem;
			}
			$data['relic'] = $relicData;
		}
		$sql = "select w.x, w.y, w.occupant as caller, w.type as worldType, w.npcRemainForces, w.occupantStartTime as callTime, a.bossId, a.attack ".
				"from world w left join allianceboss a on w.occupant = a.uid ".
				"where w.type = 3 and w.x >= {$x1} and w.y >= {$y1} and w.x <= {$x2} and w.y <= {$y2}";
		$allianceBossData = XMysql::singleton() -> execResultWithoutLimit($sql);
		if ($allianceBossData) {
			foreach ($allianceBossData as $key => $allianceBossItem) {
				$bossId = explode('_', $allianceBossItem['bossId']);
				$allianceBossItem['bossId'] = $bossId[1];
				$allianceBossItem['leagueId'] = $bossId[0];
				import ('service.item.AllianceItem');
				$allianceItem = AllianceItem::getWithUID($bossId[0]);
				$allianceBossItem['caller'] = $allianceItem -> name;
				import('service.item.ItemSpecManager');
				$xml = ItemSpecManager::singleton('default','allianceboss.xml')->getItem($bossId[1]);
				$allianceBossItem['leftTime'] = $allianceBossItem['callTime'] + $xml -> lefttime;
//				if($allianceBossItem['attack'] === NULL || !is_string($allianceBossItem['attack']) || $allianceBossItem['attack'] == '') {
//					$allianceBossItem['attack'] = 0;//无需解压缩
//				}
//				else {
//					$attackList = json_decode($allianceBossItem['attack'],true);
//					$nums = 7;
//					$rankPlayer = self::getRankTen($attackList, $nums, $xml);
//					$allianceBossItem['attack'] = $rankPlayer;
//					$allianceBossItem['selfAttack'] = $attackList[$user -> name];
//				}
				import ('service.action.WorldFightClass');
				$marchTime = WorldFight::calMarchTime($user->x, $user->y, $allianceBossItem['x'], $allianceBossItem['y']);
				$allianceBossItem['oneWayTime'] = $marchTime;
				$allianceBossData[$key] = $allianceBossItem;
			}
			$data['allianceBoss'] = $allianceBossData;
		}
		return $data;
	}
	
	/**
	 * 排名
	 * @param unknown_type $attackList
	 * @param int $nums 前几名
	 */
	static function getRankTen ($attackList, $nums, $xml, $bossValue = NULL) {
		if ($attackList) {
			$bossValue = $bossValue ? $bossValue : $xml -> army;
			foreach ($attackList as $playerUid => $attackValue) {
				$rankList[] = $attackValue;
				if (isset($attackListCon[$attackValue]))
					$attackListCon[$attackValue + 1] = array ('name' => $playerUid, 'attackValue' => ceil ($attackValue), 'attackPercent' => round ($attackValue / $bossValue * 100, 2));
				else
					$attackListCon[$attackValue] = array ('name' => $playerUid, 'attackValue' => ceil ($attackValue), 'attackPercent' => round ($attackValue / $bossValue * 100, 2));
			}
//			import ('service.action.WorldFightClass');
//			$rankList = WorldFight::getRank($rankList);
			krsort($attackListCon);
			foreach ($attackListCon as $value) {
				$rankArray[] = $value;
			}
			for ($i = 0; $i < $nums; $i++) {
				if (!$rankList[$i])
					break;
				$rankPlayer[] = $rankArray[$i]; 
			}
		}
		else {
			$rankPlayer = array ();
		}
		return $rankPlayer;
	}
	
	/**
	 * 获取指定区域内无人占领的建城点数量
	 */
	static function getOccupantPoints($x1, $y1, $x2, $y2) {
		import('util.mysql.XMysql');
		$time = time();
		$sql = "select count(*) as count from world
				where x >= {$x1} and y >= {$y1} and x <= {$x2} and y <= {$y2} 
				and type = 1 and occupant != '' ";
		$data = XMysql::singleton()->execResultWithoutLimit($sql);
		if($data) {
			return $data[0]['count'];
		}
		return 0;
	}
	
	/**
	 * 获取指定区域内无人占领的建城点数量
	 */
	static function getUnOccupantRndPoints($x1, $y1, $x2, $y2) {
		import('util.mysql.XMysql');
		$sql = "select x,y from world
				where x >= {$x1} and y >= {$y1} and x <= {$x2} and y <= {$y2} 
				and type = 1 and occupant = '' order by rand() limit 1";
		$data = XMysql::singleton()->execResultWithoutLimit($sql);
		return $data[0];
	}
	
	/**
	 * 在全国范围内随机一个点
	 */
	static function getRndPointInCountry($userCountry) {
		import('util.mysql.XMysql');
		$sql = "select x,y from world where country = {$userCountry} and type = 1 and occupant = '' order by rand() limit 1";
		$data = XMysql::singleton()->execResultWithoutLimit($sql);
		if($data) {
			return $data[0];
		}
		return $data;
	}
	
	static function getWhiteBanner($strWhiteBanner) {
		$whiteBanner = json_decode($strWhiteBanner, true);
		$data = array();
		if($whiteBanner && $whiteBanner['endTime']) {
			if(time() > $whiteBanner['endTime']) {
				return $data;
			}
			$leagueUid = '';
			$leagueName = '';
			if($whiteBanner['league']) {
				import('service.item.AllianceItem');
				$allianceItem = AllianceItem::getWithUID($whiteBanner['league']);
				$leagueUid = $allianceItem->uid;
				$leagueName = $allianceItem->name;
			}
			$data = array(
				'leagueUid' => $leagueUid,
				'leagueName' => $leagueName, 
				'fightTime' => $whiteBanner['fightTime'], 
				'bannerEndTime' => $whiteBanner['endTime'],
			);
		}
		return $data;
	}
	
	/**
	 * 获取地图数据
	 */
	static function getPowerRangeWorldMapData($x1, $y1, $x2, $y2) {
		import('util.mysql.XMysql');
		$sql = "select w.x, w.y, w.occupant, b.level,u.league from world w 
				left join building b on w.occupant = b.ownerId 
				left join userprofile u on w.occupant = u.uid
				where w.x >= {$x1} and w.y >= {$y1} and w.x <= {$x2} and w.y <= {$y2} 
				and w.occupant != '' and b.itemId = 1301000 ";
		return XMysql::singleton()->execResultWithoutLimit($sql);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 */
	static function getWithKey($x, $y){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from world where x = {$x} and y = {$y}";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	
	static public function getCreateCityPoint($userCountry, $whereSql) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select x,y from world where country = {$userCountry} and type = 1 and occupant = ''
				 and $whereSql order by rand() limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res) {
			return $res[0];
		}
		return false;
	}
	
	static public function updataOccupant($uid, $x, $y, $occupantStartTime=null) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		if($occupantStartTime) {
			$sql = "update world set occupant = '{$uid}', occupantStartTime = {$occupantStartTime} where x = {$x} and y = {$y}";
		} else {
			$sql = "update world set occupant = '{$uid}' where x = {$x} and y = {$y}";
		}
		return $mysql->execute($sql);
	}
	
	static function updateNpcRemainForces($npcRemainForces, $x, $y) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update world set npcRemainForces = {$npcRemainForces} where x = {$x} and y = {$y}";
		return $mysql->execute($sql);
	}
	
	static function getWithOccupant($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from world where occupant = '{$uid}'";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	
	/**
	 * 取得遗迹记录
	 */
	static function selectRelicRecord($targetX, $targetY) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select w.x, w.y, w.occupant, w.type, a.name as allianceName, w.occupantStartTime, w.npcRemainForces 
				from world w left join alliance a on w.occupant = a.uid 
				where w.x = {$targetX} and w.y = {$targetY} ";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 取得遗迹的联盟名称
	 */
	static function selectRelicOccupantLeagueName($relicId) {
		$data = array();
		if(!$relicId) {
			return $data;
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select w.occupant as allianceId, a.name as allianceName
				from world w left join alliance a on w.occupant = a.uid 
				where relicId = '{$relicId}' ";
		$data = $mysql->execResult($sql);
		if($data) {
			return $data[0];
		}
		return $data;
	}
	
	//获取有世界的联盟成员
	static function getLeagueMembersWithWorld($league) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select am.MemberId, w.x, w.y from alliancemem am left join world w on am.MemberId = w.occupant 
				where am.AllianceId = '{$league}' and am.status = 1 and w.occupant != '' ";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 获取我的联盟遗迹占有情况
	 */
	static function getLeagueRelics($league) {
		if(!$league) {
			return null;
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " select x,y,occupant,relicId,occupantStartTime from world where occupant = '{$league}' and type != 3";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 获取地图上某点的联盟BOSS
	 */
	static function selectAllianceBoss ($x, $y) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from world where x = $x and y = $y LIMIT 1";
		$res = $mysql->execResultWithoutLimit($sql);
		$bossWorldItem = self::to($res, false);
		return $bossWorldItem;
	}
	
	/**
	 * 联盟BOSS被召唤出来 12小时之内未打败 将会消失
	 * 需要考虑  离线玩家仍在征讨的问题
	 */
	static function removeAllianceBoss ($bossId = null) {
		import('service.item.ItemSpecManager');
		if ($bossId)
			$bossXml = ItemSpecManager::singleton('default','allianceboss.xml')->getItem($bossId);
		else { 
			$xmlGroup = ItemSpecManager::singleton('default','allianceboss.xml')->getGroup('allianceboss');
			foreach ($xmlGroup as $xml) {
				$bossXml = $xml;
				break;
			}
		}
		$bossCDTime = $bossXml -> lefttime;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		//先取得已召唤出 来12个小时的 联盟BOSS
		$sql = "SELECT 
					wf.ownerId as userUid, wf.status, w.x, w.y, w.relicId as bossId, wf.takeForces, wf.startTime, wf.waitTime 
				FROM 
					world w LEFT JOIN worldfight wf ON w.relicId = wf.targetUid 
				WHERE 
					w.type = 3 AND UNIX_TIMESTAMP(NOW()) - w.occupantStartTime >= $bossCDTime
				ORDER BY
					wf.waitTime";
		$data = $mysql->execResultWithoutLimit($sql);
		import('service.action.WorldFightClass');
		if ($data) {
			foreach ($data as $oneRecord) {
				if ($oneRecord['status'] == 0) {
					//有玩家在征讨 需要处理
					import('service.user.UserProfile');
					$user = UserProfile::getWithUID($oneRecord['userUid']);
					$bossId = $oneRecord['bossId'];
					WorldFight::singleton($user)->fightAllianceBoss($bossId, FALSE);
				}
				$needRemove["{$oneRecord['x']}"] = $oneRecord['y'];
			}
			foreach ($needRemove as $key => $value) {
				self::deleteWithXY($key, $value);
				import('service.action.CalculateUtil');
				CalculateUtil::writeLog('allianceBossDeath', 'allianceBossDeath', array ('finishTime'), array ($bossCDTime / 60),'logstat');
			}
		}
	}
	
	static function deleteWithXY ($x, $y) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from world where x = {$x} and y = {$y}";
		$res = $mysql->execute($sql);
	}
	
	/**
	 * 根据联盟BOSSID获得 ITEM
	 */
	static function getWithRelicId($bossId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from world where type = 3 and relicId = '$bossId'";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	
	/**
	 * 获得所有的联盟Boss
	 */
	static function getAllallianceBoss () {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from world where type = 3";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RActiveRecord::save()
	 */
	public function save() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		if($this->isSaved()){
			$sql = "UPDATE world SET `x` = {$this -> x}, `y` = {$this -> y}, `type` = {$this -> type}, `country` = {$this -> country}, `occupant` = '{$this -> occupant}', `relicId` = '{$this -> relicId}', `npcRemainForces` = {$this -> npcRemainForces}, `occupantStartTime` = {$this -> occupantStartTime}, `time` = '{$this -> time}' WHERE x = {$this -> x} AND y = {$this -> y}";
		} else {
			$sql = "INSERT INTO world (`x`, `y`, `type`, `country`, `occupant`, `relicId`, `npcRemainForces`, `occupantStartTime`, `time`) VALUES ({$this -> x}, {$this -> y}, '{$this -> type}', '{$this -> country}', '{$this -> occupant}', '{$this -> relicId}', '{$this -> npcRemainForces}', '{$this -> occupantStartTime}', '{$this -> time}')";
		}
		return $mysql->execute($sql);
	}
}
?>
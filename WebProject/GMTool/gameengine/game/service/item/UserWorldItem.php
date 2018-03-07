<?php
/**
 * 用户世界纪录表 
 */
import('persistence.dao.RActiveRecord');
class UserWorldItem extends RActiveRecord {
	protected $fightCount;  //征讨次数
	protected $buyFightCount; //购买的征讨次数
	protected $healthDegree; //健康度
	protected $failCount; //城市被攻破的次数
	protected $dailyFailCount; //每日城市被攻破的次数，流放逻辑
	protected $defenseForces; //防御中心的兵力
	protected $sign; //玩家签名
	protected $hdRefreshTime; //健康度刷新时间
	protected $ccRecoverTime; //指挥中心降级后恢复选用的时间， 每一级恢复所需时间k2， 迁城后，每一级恢复所需时间为k3
	protected $ccRecoverFlag; //迁城加速恢复状态
	protected $whiteBanner; //在世界地图中被其他玩家成功征讨后，会在头像上显示白旗, 白旗持续一段时间后消失
	protected $hisMaxCCLevel;
	protected $firstBanish; //当玩家第一次被流放的时候要给一个高迁
	protected $time;
	
	static function getWithUID($uid) {
		$res = self::getOne(__CLASS__, $uid);
		$currentTime = time();
		if($res && date('Y-m-d',$res->time) != date('Y-m-d',$currentTime)){
			$res->time = $currentTime;
			$res->fightCount = 0;
			$res->failCount = 0;
			$res->dailyFailCount = 0;
			$res->buyFightCount = 0;
			$res->save();
		}
		return $res;
	}
	
	public function init($uid, $fightCount=0, $failCount=0, $healthDegree=100) {
		$this->uid = $uid;
		$this->fightCount = $fightCount;
		$this->healthDegree = $healthDegree;
		$this->failCount = $failCount;
		$this->time = time();
		$this->save();
	}
	
	public function getItems($uid){
		$userWorldItem = self::getWithUID($uid);
		if(!$userWorldItem) {
			return array();
		}
		$i = 0;
		$data[$i]['itemId'] = $uid;
		$user = UserProfile::getWithUID($uid);
		$data[$i]['fightCount'] = $userWorldItem->getMaxFightCount($user) - $userWorldItem->fightCount;
		$data[$i]['healthDegree'] = intval($userWorldItem->healthDegree);
		$data[$i]['buyFightCount'] = intval($userWorldItem->buyFightCount);
		import('service.item.WorldFightItem');
		$worldFightItem = WorldFightItem::getMarchAndReinForcesRecord($uid);
		if($worldFightItem) {
			$currTime = time();
			import('service.action.WorldFightClass');
			$worldFightService = WorldFight::singleton($user);
			import('service.item.ItemSpecManager');
			$xmlActivity = ItemSpecManager::singleton('default', 'activity.xml')->getItem('8884');
			$isInActivityTime = $worldFightService->checkActivityTime($xmlActivity);
			foreach($worldFightItem as $key => $item) { //处理由于离线返回城市时间已过的情况
				if($item['status'] != 0 and $currTime > $item['endTime']) {
					import('service.item.CityItem');
					$remainForces = CityItem::updateForces($uid, $item['remainForces'], 'ReturnCity');
					$data[$i]['returnCityForces'] = $remainForces;
					WorldFightItem::delete($item['uid']);
					unset($worldFightItem[$key]);
				} elseif(!$isInActivityTime && ($item['type'] == 3 || $item['type'] == 4) && $item['status'] == 0 
					&& $item['endTime'] != -1 && $currTime > $item['waitTime']) {
					$this->setWorldFightStatus($user, $item, $currTime);
					$worldFightItem[$key] = $item;
				}
			}
			sort($worldFightItem);
		}
		$data[$i]['worldFight'] = $worldFightItem;
		$helpers = WorldFightItem::getHelpers($uid);
		$data[$i]['helpers'] = $this->getFightPower($helpers);
		$data[$i]['defenseForces'] = $userWorldItem->defenseForces;
		import('service.action.WorldFightClass');
		$data[$i]['powerRangeRatio'] = WorldFight::getPRO($uid);
		return $data;
	}
	
	public function setWorldFightStatus($user, &$worldFightItem, $currTime) {
		$marchTime = WorldFight::calMarchTime($user->x, $user->y, $worldFightItem['targetX'], $worldFightItem['targetY']);
		$marchTime = $marchTime - ($currTime - $worldFightItem['waitTime']); //考虑离线情况
		$marchTime = max($marchTime , 1);
		$endTime = $currTime + $marchTime;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$wfUid = $worldFightItem['uid'];
		$worldFightItem['status'] = 1;
		$worldFightItem['endTime'] = $endTime;
		$sql = " update worldfight set status = 1, endTime = {$endTime}, remainForces = takeForces, timestamp = {$currTime} where uid = '{$wfUid}' ";
		return $mysql->execute($sql);
	}
	
	public function getNewItems($uid, $itemUids){
		$data = self::getItems($uid); 
		if(!$data){
			$userWorldItem = new self();
			$userWorldItem->init($uid);
			$data = self::getItems($uid);
		}
		return $data;
	}
	
	static function getFightPower($data) {
		if(!$data) return $data;
		import('service.action.GeneralClass');
		$general = General::singleton();
		foreach($data as $key => $value) {
			$general->setUser(UserProfile::getWithUID($value['uid']));
			$value['fightPower'] = $general->getUserFightPower();
			$data[$key] = $value;
		}
		return $data;
	}
	
	/**
	 * 刷新健康度
	 */
	static function flushUserHealthDegree($uid) {
		$current_time = time();
		$userWorldItem = self::getWithUID($uid);
		$healthDegree = null;
		if($userWorldItem) {
			$healthDegree = $userWorldItem->healthDegree;
			if($userWorldItem->hdRefreshTime == 0) {
				$userWorldItem->hdRefreshTime = $current_time;
				$userWorldItem->save();
			} else {
				$diff = $current_time - $userWorldItem->hdRefreshTime;
				$count = intval($diff / 3600);
				if($count > 0) {
					import('service.item.ItemSpecManager');
					$hdDelta = ItemSpecManager::singleton('default', 'item.xml')->getItem('worldwar3')->k1;
					$hdDelta = $count * $hdDelta;
					$userWorldItem->healthDegree = min(100, $userWorldItem->healthDegree + $hdDelta);
					$userWorldItem->hdRefreshTime += $count * 3600;
					$userWorldItem->save();
					$healthDegree = $userWorldItem->healthDegree;
				}
			}
		}
		return $healthDegree;
	}
	
	/**
	 * 获得属性
	 */
	static function getField($uid, $field) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select {$field} from userworld where uid = '{$uid}'";
		$res = $mysql->execResult($sql);
		return $res[0][$field];
	}
	
	public function getMaxFightCount($user) {
		import('service.item.ItemSpecManager');
		$worldWarXml = ItemSpecManager::singleton('default', 'item.xml')->getItem('worldwar');
		$maxFigthCount = $worldWarXml->k3;
		import('service.action.CalculateUtil');
		$vipEffect = CalculateUtil::getVipEffect($user);
		$maxFigthCount += $vipEffect['eft26']['value'];
		$maxFigthCount += $this->buyFightCount;
		return $maxFigthCount;
	}
	
	public function save() {
		$this->time = time();
		$this->serializeProperty('ccRecoverTime');
		$this->serializeProperty('whiteBanner');
		parent::save();
		$this->unserializeProperty('ccRecoverTime');
		$this->unserializeProperty('whiteBanner');
	}
}
?>
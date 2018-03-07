<?php
/**
 * TreasureItem
 * 
 * 寻宝
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class TreasureItem extends RActiveRecord {
	
	protected $uid; //每个玩家对应一个
	protected $treasureTimes;	//今日寻宝次数
	protected $freeTimes;	//今日免费次数
	protected $lastUpdate;	//上一次刷新时间
	protected $unlockPos;	//当前开启位置
	
/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		$currentTime = time();
		if(!$res){
			$item = new self();
			$item->uid = $uid;
			$item->treasureTimes = 0;
			$item->freeTimes = 0;
			$item->lastUpdate = $currentTime;
			$item->save();
			return $item;
		}else{
			//每日重置次数
			if(date('Y-m-d',$res->lastUpdate) != date('Y-m-d',$currentTime)){
				$res->treasureTimes = 0;
				$res->freeTimes = 0;
				$res->lastUpdate = $currentTime;
				$res->save();
			}
		}
		return $res;
	}
}
?>
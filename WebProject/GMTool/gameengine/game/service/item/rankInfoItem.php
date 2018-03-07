<?php
import('persistence.dao.RActiveRecord');
class rankInfoItem extends RActiveRecord
{
	protected $defaultForce;	//默认兵力
	protected $fightPower;		//战斗力
		
	public  static function getWithUID($uid)
	{
		$res = self::getOne(__CLASS__, $uid);
		if(!$res)
			$res = self::initRankInfoItem($uid);
		return $res;	
	}
	
	public static function initRankInfoItem($uid)
	{
		$rankInfoItem = new rankInfoItem();
		$rankInfoItem->uid = $uid;
		$rankInfoItem->defaultForce = 0;
		$rankInfoItem->fightPower = 0;
		$rankInfoItem->save();
		
		return $rankInfoItem;
	}
}
?>
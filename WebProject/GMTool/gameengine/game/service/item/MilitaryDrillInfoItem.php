<?php
/**
 * 联合军演奖励特殊规则 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class MilitaryDrillInfoItem extends RActiveRecord
{
	protected $uid;
	protected $randomNum;		//随机数
	protected $currentNum;		//当前数
		
	public  static function getWithUID($uid)
	{
		$res = self::getOne(__CLASS__, $uid);
		if(!$res)
			$res = self::initMilitaryDrillInfoItem($uid);
		return $res;	
	}
	
	public static function initMilitaryDrillInfoItem($uid)
	{
		$militaryDrillInfo = new MilitaryDrillInfoItem();
		$militaryDrillInfo->uid = $uid;
		$militaryDrillInfo->randomNum = rand(10, 20);
		$militaryDrillInfo->currentNum = 0;
		$militaryDrillInfo->save();
		
		return $militaryDrillInfo;
	}
}

?>
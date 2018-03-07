<?php
import('persistence.dao.RActiveRecord');
class KillGeneralItem extends RActiveRecord
{
	protected $uid;
	protected $currentPoint;	//当前积分
	protected $totalPoint;		//总积分
	protected $buyGold;			//活动期间充值的金币
	protected $buyGoldPoint;	//活动期间充值金币获得的积分
	protected $shootTime;		//银币已射击次数
	protected $general1;		//1号将军是否已兑换	1表示已兑换 0表示未兑换
	protected $general2;		//2号将军是否已兑换
	protected $general3;		//3号将军是否已兑换
	protected $refreshTime;		//上一次刷新时间
	protected $rewardFlag;		//积分排行榜领奖标记0表示未领奖，1表示已领奖
	protected $generalFlag;
	
	
	public  static function getWithUID($uid)
	{
		$res = self::getOne(__CLASS__, $uid);
		if(!$res)
			$res = self::initKillGeneralItem($uid);
		
		$res->unserializeProperty('generalFlag');
		return $res;	
	}
	
	public static function refresh($uid)
	{
		$res = self::getWithUID($uid);	
		if(date('Y-m-d', $res->refreshTime)!= date('Y-m-d'))
		{	
			$res->shootTime = 0;
			$res->refreshTime = time();		
		}
		$res->save();
	}
	
	public function save(){
		$this->serializeProperty('generalFlag');
		parent::save();
		$this->unserializeProperty('generalFlag');
	}	
		
	public static function initKillGeneralItem($uid)
	{
		$killGeneralItem = new KillGeneralItem();
		$killGeneralItem->uid = $uid;
		$killGeneralItem->currentPoint = 0;
		$killGeneralItem->totalPoint = 0;
		$killGeneralItem->buyGold = 0;
		$killGeneralItem->buyGoldPoint = 0;
		$killGeneralItem->shootTime = 0;
		$killGeneralItem->general1 = 0;
		$killGeneralItem->general2 = 0;
		$killGeneralItem->general3 = 0;
		$killGeneralItem->refreshTime = time();
		$killGeneralItem->rewardFlag = 0;
		$killGeneralItem->generalFlag = NULL;
		$killGeneralItem->save();
		
		return $killGeneralItem;
	}
	
}
?>
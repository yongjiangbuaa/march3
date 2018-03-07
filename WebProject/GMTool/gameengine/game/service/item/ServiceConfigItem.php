<?php
/**
 * ServiceConfigItem
 * 
 * 服务器配置属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class ServiceConfigItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//服务器配置uid
	protected $lordDouble = 0;//君主经验翻倍
	protected $moneyDouble = 0;//银币翻倍
	protected $mineralDouble = 0;//矿物翻倍
	protected $oilDouble = 0;//石油翻倍
	protected $foodDouble = 0;//粮食翻倍
	protected $soldiersDouble = 0;//新兵翻倍
	protected $generalExpDouble = 0;//武将经验翻倍;
	protected $generalFeatsDouble = 0;//武将功勋翻倍
	protected $startTime = 0;//双倍开始时间
	protected $endTime = 0;//双倍结束时间
	protected $rewardGoods = 0;//双倍奖励物品数目
	protected $arena = 0;//双倍竞技场挑战数目
	
	public function isDouble($item)
	{
		if($this->$item == 1 && $this->startTime < time() && $this->endTime > time())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	//设置item所包含的key 的双倍情况
	static public function setDouble($item, $uid)
	{
		if(!is_array($item))
		{
			return XServiceResult::clientError("no start time or end time");
		}
		$ServiceConfigItem = self::getWithUID('config');
		if(!isset($ServiceConfigItem))
			$ServiceConfigItem = self::init();
		foreach ($item as $key=>$value)
		{
			$ServiceConfigItem->$key=$value;
		}
		$ServiceConfigItem->save();
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		if($cache->get('SERVERCONFIG_' . $uid) != NULL)
			$cacheData = $cache->delete('SERVERCONFIG_'.$uid);		
	}
	//查询双倍活动
	static public function viewDouble()
	{
		$ServiceConfigItem = self::getWithUID('config');
		if(!isset($ServiceConfigItem))
			$ServiceConfigItem = self::init();
		$data[startTime]=date("Y-m-d H:i",$ServiceConfigItem->startTime);
		$data[endTime]=date("Y-m-d H:i",$ServiceConfigItem->endTime);
//		$data[startTime]=$ServiceConfigItem->startTime;
//		$data[endTime]=$ServiceConfigItem->endTime;
		$data[lordDouble]=$ServiceConfigItem->lordDouble+1;
		$data[moneyDouble]=$ServiceConfigItem->moneyDouble+1;
		$data[mineralDouble]=$ServiceConfigItem->mineralDouble+1;
		$data[oilDouble]=$ServiceConfigItem->oilDouble+1;
		$data[foodDouble]=$ServiceConfigItem->foodDouble+1;
		$data[soldiersDouble]=$ServiceConfigItem->soldiersDouble+1;
		$data[generalExpDouble]=$ServiceConfigItem->generalExpDouble+1;
		$data[generalFeatsDouble]=$ServiceConfigItem->generalFeatsDouble+1;
		$data[rewardGoods]=$ServiceConfigItem->rewardGoods+1;
		$data[arena]=$ServiceConfigItem->arena+1;
		$data['test']='test';
		return $data;
	}
	//取消双倍效果
	static public function unsetDouble($uid)
	{
		$ServiceConfigItem = self::getWithUID('config');
		if(!isset($ServiceConfigItem))
			$ServiceConfigItem = self::init();
//		$data[startTime]=date("Y-m-d H:m",$ServiceConfigItem->startTime);
//		$data[endTime]=date("Y-m-d H:m",$ServiceConfigItem->endTime);
		$data[lordDouble]=1+$ServiceConfigItem->lordDouble=0;
		$data[moneyDouble]=1+$ServiceConfigItem->moneyDouble=0;
		$data[mineralDouble]=1+$ServiceConfigItem->mineralDouble=0;
		$data[oilDouble]=1+$ServiceConfigItem->oilDouble=0;
		$data[foodDouble]=1+$ServiceConfigItem->foodDouble=0;
		$data[soldiersDouble]=1+$ServiceConfigItem->soldiersDouble=0;
		$data[generalExpDouble]=1+$ServiceConfigItem->generalExpDouble=0;
		$data[generalFeatsDouble]=1+$ServiceConfigItem->generalFeatsDouble=0;
		$data[rewardGoods]=1+$ServiceConfigItem->rewardGoods=0;
		$data[arena]=1+$ServiceConfigItem->arena=0;		
		$ServiceConfigItem->save();
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		if($cache->get('SERVERCONFIG_' . $uid) != NULL)
			$cacheData = $cache->delete('SERVERCONFIG_' . $uid);			
		return $data;
	}

	static function init(){
		$ServiceConfigItem = new self;
		$ServiceConfigItem->uid = 'config';
		$ServiceConfigItem->save();
		return $ServiceConfigItem;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$cacheData = $cache->get('SERVERCONFIG_'.$uid);
		if($cacheData != null)
		{
			return self::toObject(__CLASS__,array(get_object_vars($cacheData)));
		}
		$res = self::getOne(__CLASS__, $uid);
		if($res != null)
		{
			$cacheData = $cache->set('SERVERCONFIG_'.$uid,$res,900);
		}
		return $res;
	}
}
?>
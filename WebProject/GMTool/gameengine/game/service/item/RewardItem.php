<?php
/**
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class RewardItem extends RActiveRecord {
	protected $uid;
	protected $title;
	protected $contents;
	protected $type;		//领取类型
	protected $createTime;	//生成礼包时间
	protected $startTime;
	protected $endTime;		
	protected $enabled;		//开启状态，0不可用1可用
	protected $typeParam;	//领取类型参数，控制谁可以领取   blob
	protected $goods;
	protected $exp;
	protected $exp1;
	protected $money;
	protected $general;
	protected $honor;
	protected $pvpHonor;
	protected $gold;
	protected $gift;
	protected $soul1;
	protected $soul2;
	protected $soul3;
	protected $soul4;
	
	static function getItems($uid){
		import('service.item.InventoryItem');
		import('service.item.RewardRecordItem');
		$record = RewardRecordItem::getRecords($uid);
// 		$user = UserProfile::getWithUID($uid);
		//查询玩家的所有科技
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$currentTime = time();
		$sql = "select * from reward where enabled = 1 and startTime <= $currentTime and endTime >$currentTime";
		$res = $mysql->execResultWithoutLimit($sql);
		$rewardItems = self::toObject('RewardItem',$res,true);
		$data = array();
		foreach ($rewardItems as $rewardItem){
			if(in_array($rewardItem->uid, $record->rewardList))
				continue;
			switch ($rewardItem->type){
				case 1://全服奖励
					$data[] = self::getResArr($rewardItem);
					break;
				default:
					break;
			}
		}
		return $data;
	}
	private function getResArr($rewardItem){
//		$rewardItem->reward['item'] = array('8010401'=>array('level'=>'1','count'=>'1'));
		if($rewardItem->goods){
			import('service.action.InventoryClass');
			$goodsList = explode(',', $rewardItem->goods);
			$temp = array();
			foreach ($goodsList as $goodsStr){
				if(!strpos($goodsStr, '_'))continue;
				$goodsDetail = explode('_',$goodsStr);
				$goods = new InventoryItem();
				$goods->itemId = $goodsDetail[0];
				$goods->level = $goodsDetail[1];
				$goods->fillXMLProperty('goods.xml');
				if($goods->overlap != 1){//可叠加
					$goods->count = $goodsDetail[2];
					$temp[] = Inventory::singleton()->getGoodsResArr($goods);
				}
				else{
					for ($i = 0;$i<$goodsDetail[2];$i++){
						$goods->count = 1;
						$temp[] = Inventory::singleton()->getGoodsResArr($goods);
					}
				}
			}
			$rewardItem->goods = $temp;
		}
		if($rewardItem->general){
			import('service.action.GeneralClass');
			$generalList = explode(',', $rewardItem->general);
			$temp = array();
			foreach ($generalList as $generalId){
				if(!ItemSpecManager::singleton('default', 'general.xml')->getItem($generalId))
					continue;
				$general = General::singleton()->createOneGeneral($generalId);
				$general = General::getGeneralFiveProperty($general, null, null, true);
				$temp[] = $general;
			}
			$rewardItem->general = $temp;
		}
		return $rewardItem;
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
}
?>
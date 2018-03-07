<?php
/**
 * ShopItem
 * 
 * 商店列表
 * 
 * @Entity
 * @package item
 */
class ShopItem {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	public function getItems($uid)
	{
		import('service.item.ItemSpecManager');
		$inventoryItems = ItemSpecManager::singleton('default', 'goods.xml')->getGroup('goods');
		$data = array();
		import('service.item.UserShopRecordItem');
		import('service.item.ShopActivityNumsItem');
		$goodsNums = ShopActivityNumsItem::getShopGoodsActivityNums();
// 		$goodsRecord = UserShopRecordItem::getRecords($uid);
		$goodsArr = array();
		if(is_array($goodsNums)){
			foreach ($goodsNums as $goods){
				$goodsArr[$goods['uid']] = $goods;
			}
		}
		foreach ($inventoryItems as $inventoryItem)
		{
			if($inventoryItem->shop || $inventoryItem->mall)
			{
				$data[$inventoryItem->id] = get_object_vars($inventoryItem);
// 				$data[$inventoryItem->id]['level'] = 1;
				unset($data[$inventoryItem->id]['level'] );
				unset($data[$inventoryItem->id]['id'] );
				$data[$inventoryItem->id]['itemId'] = $inventoryItem->id;
// 				$data[$inventoryItem->id]['className'] = 'ShopItem';
//				$data[$inventoryItem->id]['sell_num'] = 0;
// 				if($inventoryItem->mall){
// 					if(isset($goodsRecord[$inventoryItem->id])){
// 						$data[$inventoryItem->id]['recentBuyTime'] = $goodsArr[$inventoryItem->id];
// 					}else{
// 						$data[$inventoryItem->id]['recentBuyTime'] = 0;
// 					}
// 				}
			}
		}
		//商城打折
		foreach ($goodsArr as $discountUid=>$value){
			$goodsId = $value['itemId'];
			$inventoryItem = $inventoryItems->$goodsId;
			$data[$discountUid] = get_object_vars($inventoryItem);
			$data[$discountUid]['itemId'] = $inventoryItem->id;
// 			$data[$discountUid]['className'] = 'ShopItem';
// 			$data[$discountUid]['price_mall'] = $value['price'];
// 			$data[$discountUid]['level'] = 1;
			unset($data[$discountUid]['level']);
			unset($data[$discountUid]['id']);
			$data[$discountUid]['sell_num'] = $value['nums'];
			$data[$discountUid]['num_mall'] = $value['limit'];
			$data[$discountUid]['buy_limit'] = $value['userLimit'];
			$data[$discountUid]['mall'] = 1;
			$data[$discountUid]['price_activity'] = $value['price'];
			$data[$discountUid]['priceType'] = $value['priceType'];//0使用商城默认配置	1不能礼券  2可用礼券
			$data[$discountUid]['activity'] = 0;//打折活动，暂时失效
			$data[$discountUid]['hot'] = 0;//热卖
			$data[$discountUid]['dailyDiscount'] = 1;//每日限购
			$data[$discountUid]['startTime'] = $value['startTime'];
			$data[$discountUid]['endTime'] = $value['endTime'];
			if($value['limit'] > 100000){
				$data[$discountUid]['dailyDiscount'] = 0;//每日限购
				$data[$discountUid]['noLimitDiscount'] = 1;//五一活动
			}
		}
		return $data;
	}
}
?>
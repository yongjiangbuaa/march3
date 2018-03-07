<?php
/** 
 * 获得举荐信息
 * @Pointcut('lock')
 * @Lock(policy='retrieve')
 * @package action
 */
class GeneralFireItem
{
	public function getItems($uid)
	{
		import('service.item.ItemSpecManager');
		$generalFireGroup = ItemSpecManager::singleton('default','generalFire.xml')->getGroup('generalFire',true);
		$data = array();
		foreach ($generalFireGroup as $generalFireItem)
		{
			$items = explode(',', $generalFireItem->item_id);
			$temp = array();
			foreach ($generalFireItem as $key=>$value)
			{
				$temp[$key] = $value;
			}
			foreach ($items as $item)
			{
				$temp['randomInventory'][] = $this->getInventory($item);
			}
			$data[$generalFireItem->id] = $temp;
		}
		return $data;
	}
	private function getInventory($itemId){
		$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($itemId);
		$value1 = $xmlGoods->value1;
		$value2 = $xmlGoods->value2;
		$value3 = $xmlGoods->value3;
		return array(
			'uid' => 'temp'.$itemId,
			'itemId' => $itemId,
			'ownerId' => '',
			'level' => 1,
			'count' => 1,
			'useGeneralId' => '',
			'type' => $xmlGoods->type,
			'operate_type' => $xmlGoods->operate_type,
			'color' => $xmlGoods->color,
			'require_level' => $xmlGoods->require_level,
			'status' => $xmlGoods->status,
			'effect1' => $xmlGoods->effect1,
			'effect2' => $xmlGoods->effect2,
			'effect3' => $xmlGoods->effect3,
			'value1' => $value1,
			'value2' => $value2,
			'value3' => $value3,
			'para1' => $xmlGoods->para1,
			'para2' => $xmlGoods->para2,
			'para3' => $xmlGoods->para3,
			'lock' => $xmlGoods->islock,
			'overlap' => $xmlGoods->overlap,
			'destroy' => $xmlGoods->destroy,
			'order_num' => $xmlGoods->order_num,
			'price_sell'=> $xmlGoods->price_sell,
			'price_buy'=> $xmlGoods->price_buy,
			'resolve_money'=>$xmlGoods->resolve_money,
			'pos' => 0,
			'invId1'=>0,
			'deductCount1'=>0,
			'invInfo1'=>array(),
			'invId2'=>0,
			'deductCount2'=>0,
			'invInfo2'=>array(),
			'upgrade1'=>0,
			'upgrade2'=>0,
			'upgrade3'=>0,);
	}
}
?>
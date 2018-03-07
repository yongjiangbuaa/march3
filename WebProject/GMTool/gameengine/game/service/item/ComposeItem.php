<?php
/**
 * 
 * 重铸类
 *
 */
class ComposeItem  {
	public function getItems($uid)
	{
		import('service.item.ItemSpecManager');
		$composeItems = ItemSpecManager::singleton('default', 'forge.xml')->getGroup('compose');
		$data = array();
		foreach ($composeItems as $composeItem)
		{
			$data[$composeItem->id] = $composeItem;
			$data[$composeItem->id]->itemId = $composeItem->id;
			
			$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($composeItem->item);
			$data[$composeItem->id]->afterLevel = $xmlGoods->require_level;
			$data[$composeItem->id]->afterEffect1 = $xmlGoods->effect1;
			$data[$composeItem->id]->afterEffect2 = $xmlGoods->effect2;	
			$data[$composeItem->id]->afterValue1 = $xmlGoods->value1;										
			$data[$composeItem->id]->afterValue2 = $xmlGoods->value2;
					
			$xmlGoods1 = ItemSpecManager::singleton('default', 'goods.xml')->getItem($composeItem->data1);
			$data[$composeItem->id]->beforeLevel = $xmlGoods1->require_level;
			$data[$composeItem->id]->beforeEffect1 = $xmlGoods1->effect1;
			$data[$composeItem->id]->beforeEffect2 = $xmlGoods1->effect2;	
			$data[$composeItem->id]->beforeValue1 = $xmlGoods1->value1;										
			$data[$composeItem->id]->beforeValue2 = $xmlGoods1->value2;
		}
		return $data;
	}
}
?>
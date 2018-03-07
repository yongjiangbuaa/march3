<?php
class ContributionItem {
	
	//返回捐献资源和物品列表
	static function getItems($uid){
		$data = array();
		import('service.item.ItemSpecManager');
		$xmlData = ItemSpecManager::singleton('default', 'item.xml')->getGroup('contribution');
		foreach ($xmlData as $dataItem){	
			$dataItem->itemId = null;
			$data[] = $dataItem;			
		}

		return $data;
	}
	
	// 计算类型的平均贡献度
	static function getContrItem($id){
		import('service.item.ItemSpecManager');
		$dataItem = ItemSpecManager::singleton()->getItem($id);
		return $dataItem;
	}
	
/*	// 获取类型
	static function getType($id){
		import('service.item.ItemSpecManager');
		$dataItem = ItemSpecManager::singleton()->getItem($id);
		return $dataItem->type;
	}
*/	
	
	
	
	
}
?>
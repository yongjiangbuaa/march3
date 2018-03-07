<?php
/** 
 * 获得所有一级建筑建造信息
 * @Pointcut('lock')
 * @Lock(policy='retrieve')
 * @package action
 */
class BuildingInfoItem
{
	public function getItems($uid)
	{
		import('service.item.ItemSpecManager');
		//主岛
		$buildingXml = ItemSpecManager::singleton('default','building.xml')->getGroup('1301000');
		$freeBuilding = array(1308001,1309001,1310001);
		import('service.action.CalculateUtil');
		import('service.action.ConstCode');
		$effectGoods = CalculateUtil::getGoodsStatus($uid, array(ConstCode::EFFECT_BUILDING));
		foreach ($buildingXml as $key=>$value)
		{
			if($value->level == 1)
			{
				if(in_array($value->id,$freeBuilding))
					continue;
				$value->time = $value->time * max(1 - ($effectGoods[ConstCode::EFFECT_BUILDING]['value']) / 100, 0.001);
				$info = clone $value;
				if($info->building1)
				{
					$info->blv1 = $info->building1%1000;
					$info->building1 = $info->building1 - $info->blv1;
				}
				if($info->building2)
				{
					$info->blv2 = $info->building2%1000;
					$info->building2 = $info->building2 - $info->blv2;
				}
				$data['building1'][$key-1] = $info;
			}
		}
		 //副岛
		$buildingOrderXml = ItemSpecManager::singleton('default','building.xml')->getGroup('building_order');
		foreach ($buildingOrderXml as $key=>$xmlIsLand)
		{
			if($xmlIsLand->id%10 == 2)continue;
			for ($i = 1; $i <= $xmlIsLand->floor_num; $i++){
				$res = explode('|', $xmlIsLand->{floor . $i});
				$buildingArr = explode(',', $res[0]);
				foreach ($buildingArr as $key => $building){
					if(!$building)
						continue;
					$itemId = $building + 1;
					$xmlBuilding = $buildingXml->$itemId;
					$temp[$building] = $xmlBuilding;
					$temp[$building]->floor = $i;
					$temp[$building]->nums = $res[1];
					if($temp[$building]->building1)
					{
						$temp[$building]->blv1 = $temp[$building]->building1%1000;
						$temp[$building]->building1 = $temp[$building]->building1 - $temp[$building]->blv1;
					}
					if($temp[$building]->building2)
					{
						$temp[$building]->blv2 = $temp[$building]->building2%1000;
						$temp[$building]->building2 = $temp[$building]->building2 - $temp[$building]->blv2;
					}
				}
			}
			$id = $xmlIsLand->id%10 - 1;
			$data['building'.$id] = $temp;
		}
		return array($data);
	}

}
?>
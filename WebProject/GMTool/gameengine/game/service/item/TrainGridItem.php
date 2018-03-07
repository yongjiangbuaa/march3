<?php
/**
 * TrainGrid
 * 
 * 训练位属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class TrainGridItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $gridList; //训练位列表 array(grid_id => array('id' => $grid_id, 'level' => $level, 'endTime' => $endTime), ...)

	static function getTrainGrid($uid){
		return self::getWithUID($uid);
	}
	
	public function getItems($uid){
		import('service.item.ItemSpecManager');
		$gridItem = self::getTrainGrid($uid);
		$xmlGrid = ItemSpecManager::singleton('default', 'general.xml')->getGroup('train_grid');
		$data = array();
		foreach ($xmlGrid as $grid){
			$data[] = self::resArr($gridItem, $grid,$uid);
		}
		return $data;
	}
	
	static function resArr($gridItem, $xmlGrid,$uid=null){
		if($gridItem->gridList[$xmlGrid->id]['level']){
			$level = $gridItem->gridList[$xmlGrid->id]['level'];
		}else{
			$level = 1;
		}
		$xmlRole = ItemSpecManager::singleton('default', 'role.xml')->getItem($level + 2000);
		$trainTime = $xmlRole->train_cd;
		$playerProfile = UserProfile::getWithUID($uid);
		//1.考虑联盟科技对时间的影响
		if($playerProfile->league){
			import('service.action.ScienceClass');
			$trainEffect = Science::singleton($playerProfile)->getScienceWithEffectId(112);	
			if($trainEffect){
				$trainTime = $trainTime-$trainEffect['value'];
			}		
		}
		return array(
			'itemId' => $xmlGrid->id,
			'open_type' =>$xmlGrid->open_type,
			'level' => $gridItem->gridList[$xmlGrid->id]['level'],
			'endTime' => $gridItem->gridList[$xmlGrid->id]['endTime'],
			'status' => $gridItem->gridList[$xmlGrid->id] ? 1 : 0,
			'player_lv' => $xmlGrid->player_lv,
			'city_lv' => $xmlGrid->city_lv,
			'vip_lv' => $xmlGrid->vip_lv,
			'grid_id' => $xmlGrid->grid_id,
			'gold_cost' => $xmlGrid->gold_cost,
			'train_para1' => $xmlRole->train_para1,
			'train_para2' => $xmlRole->train_para2,
			'train_cd' => $trainTime,
		);
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if($res)
			$res->unserializeProperty('gridList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('gridList');
		parent::save();
		$this->unserializeProperty('gridList');
	}
}
?>
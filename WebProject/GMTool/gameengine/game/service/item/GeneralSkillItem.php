<?php
/**
 * GeneralSkillItem
 * 武将技能表
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class GeneralSkillItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//武将uid
	protected $skillList; //技能列表
						/*array(
							array(
								'id' => $id, //技能id
								'level' => $level //技能等级
							)
						)*/
	
	
	/**
	 * 根据武将uid查找对应技能
	 *
	 * @param String $uid
	 * @return Array
	 */
	static function getSkills($generalItemId,$owernId){
		import('service.item.ItemSpecManager');
		$xmlRule = ItemSpecManager::singleton('default', 'general.xml')->getItem($generalItemId);
// 		if(!$generalId){
// 			$generalSkillItem = self::getWithUID($uid);
// 			$skillArr = array();
// 			if(is_array($generalSkillItem->skillList)){
// 				foreach ($generalSkillItem->skillList as $skill){
// 					$skillArr[] = $skill;
// 				}
// 			}
// 		}
		$skillArr = array();
		if($xmlRule->type2 == 1){
			$skills = explode('|', $xmlRule->skid);
			$idArr = explode(',', $skills[0]);
			$skillId = $idArr[0];
			$skillArr[] = array('id' => $skillId,'level' => 1,);
			import('service.item.MedalItem');
			$medalItem = MedalItem::getWithUID($owernId);
			$medallist = $medalItem->medallist;
			$honorXmlGroup = ItemSpecManager::singleton('default','honor.xml')->getGroup('honor');
			foreach ($honorXmlGroup as $honorXml){
				if($medallist[$honorXml->id] && $honorXml->skill){
					$skillArr[] = array('id' => $honorXml->skill,'level' => 1,);
				}
			}
		}else{
			$skills = explode('|', $xmlRule->skid);
			$idArr = explode(',', $skills[0]);
			$skillId = $idArr[0];
			$skillArr = array(
					array(
						'id' => $skillId,
						'level' => 1,
					),
			);
		}
		return $skillArr;
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
			$res->unserializeProperty('skillList');
		return $res;
	}

	public function save(){
		$this->serializeProperty('skillList');
		parent::save();
		$this->unserializeProperty('skillList');
	}
}
?>
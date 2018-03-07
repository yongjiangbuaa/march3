<?php
/**
 * GeneralInitItem
 * 用户酒馆武将列表
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class GeneralInitItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//主键key
	protected $itemId;
	protected $refreshTime1 = 0; //普通刷新时间点,timestamp
	protected $refreshTime2 = 0; //白银刷新时间点,timestamp
	protected $refreshTime3 = 0; //普通刷新时间点,timestamp
	protected $generalList; //生成的武将列表,结构如下：
							/*array(
								'uid' => $generalId //武将唯一标示
								'face' => $face //头像编号
								'name' => $name     //武将名字
								'level' => $level   //武将等级
								'sex' => $sex  //武将性别
								'personality' => $personality //性格
								'exp' => $exp //武将当前经验
								'rank' => $rank //军阶
								'feats' => $feats //功勋
								'hp' => 100 //健康度
								'zhuangjia' => $zhuangjia //装甲专精值
								'tuji' => $tuji //突击专精值
								'yuancheng' => $yuancheng //远程专精值
								'fuzhu' => $fuzhu //辅助专精值
								#'skypro1' => $skypro1 //空战专精值,（该属性已移除）
								#'skypro2' => $skypro2 //空辅专精值,(该属性已移除)
								'pro' => $pro //武将职业
								'baseBattle' => $battle //战斗基数
								'baseDefence' => $defence //防御基数
								'baseTech' => $tech //战术基数
								'baseLuck' => $luck //幸运基数
								'attrGrow' => $attrGrow //属性成长值
								'skillLimit' => $skillLimit //技能格数
								'skill' => array(
									array(
										'id' => $skillId //技能id
										'level' => $level //技能等级
									)
								) //技能id
								'status' => 1, //1可以招募，0不能招募
							);*/
 	public function getItems($uid){
 		import('service.action.GeneralClass');
		$data = array();
		$data[] = General::singleton()->setUserUid($uid)->getHireGeneralList();
		return $data;
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
			$res->unserializeProperty('generalList');
		return $res;
	}

	public function save(){
		$this->serializeProperty('generalList');
		parent::save();
		$this->unserializeProperty('generalList');
	}
}
?>
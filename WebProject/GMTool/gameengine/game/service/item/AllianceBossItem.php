<?php
import('persistence.dao.RActiveRecord');
class AllianceBossItem extends RActiveRecord {
	protected $bossId = 0;	//BossID
	protected $time = 0;	//Boss召唤的时间
	protected $attack;	//记录各玩家
	protected $leftTime = 0;//boss离开的时间
	
	/**
	 * 根据主键取得记录对象实例
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if ($res) {
			$res->unserializeProperty('attack');
		}
		return $res;
	}
	
	/**
	 * 保存
	 */
	public function save(){
		$this->serializeProperty('attack');
		parent::save();
		$this->unserializeProperty('attack');
	}
}
?>
<?php
/**
 * 激活码
 */
import('persistence.dao.RActiveRecord');
class CodeItem extends RActiveRecord {
 	protected $uid = null;
 	protected $delivery;		//批次
 	protected $startTime;		//生效时间
 	protected $endTime;			//失效时间
 	protected $goods;			//物品
 	protected $playerName;		//玩家姓名
 	protected $playerUid;		//玩家UID
 	protected $receiveTime;		//领取时间

 	/*
 	 * 根据主键取得记录对象实例
 	 *
 	 * @param String $uid
 	 * @return Object
 	 */
 	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
 	}
}
?>
<?php
/**
 * 名将堂记录玩家曾刷新出来的武将
 */
import('persistence.dao.RActiveRecord');
class UserFamousGeneralItem extends RActiveRecord{
	protected $generals;
	protected $genTimes;	//每一个 金将/橙将 刷新出现的次数 
	protected $fixTime;
	const TABLE = 'userfamousgeneral';
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		return self::toObject(__CLASS__, $results, $retArr);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if (!$res)
		{
			$res = new UserFamousGeneralItem();
			$res -> uid =$uid;
			import('service.item.GeneralItem');
			$ownerAllGeneral = GeneralItem::getAllGeneral ($res -> uid);
			//获得玩家所有的将军Id
			foreach ($ownerAllGeneral as $general) {
				$res -> addGenerals($general['itemId']);
			}
			$res -> save ();
		}
		if ($res -> fixTime < 1377228072) {//2013年8月23日 上午11:21:12
			import('service.item.GeneralItem');
			$ownerAllGeneral = GeneralItem::getAllGeneral ($res -> uid);
			$fix = FALSE;
			foreach ($ownerAllGeneral as $general) {
				if ($general['itemId'] == 1285306) {
					$fix = TRUE;
				}
			}
			if($fix) {
				$res -> addGenerals(1285306);
				$res -> fixTime = 1377228072;
				$res -> save ();
			}
		}
		$res->unserializeProperty('genTimes');
		return $res;
	}

	/**
	 * 保存
	 */
	public function save(){
		$this->serializeProperty('genTimes');
		parent::save();
		$this->unserializeProperty('genTimes');
	}
	
	public function getGenerals () {
		return explode(',', $this -> generals);
	}
	
	/**
	 * 更新已刷新的武将
	 * 参数为新刷新的武将Id列表字符串
	 */
	public function addGenerals ($generalId) {
		if(strpos($this -> generals, $generalId) === false){	
			$this -> generals = $this -> generals.$generalId.',';
		}
	}

}
?>
<?php
/**
 * 开服小活动中
 * 玩家购买记录和招募记录
 */
import('persistence.dao.RActiveRecord');
class UserOpenActivityItem extends RActiveRecord{
	protected $generals;	//招募过的武将
	const TABLE = 'useropenactivity';
	
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
			$res = new UserOpenActivityItem();
			$res -> uid =$uid;
			$res -> save ();
		}
		return $res;
	}

	public function getGenerals () {
		return $this -> generals;
	}
	
	/**
	 * 更新已招募的武将
	 * 参数为新招募的武将Id列表字符串
	 */
	public function addGenerals ($user,$generalId) {
		if(strpos($this -> generals, $generalId) === false && !in_array($generalId, array(1290000,1290001,1290002,1280005))){
			//发送分享	首次招募武将
			import('service.action.CalculateUtil');
			CalculateUtil::sendShareId($user,5,null,$generalId);
			
			$this -> generals = $this -> generals.$generalId.',';
			import('service.action.CalculateUtil');
			CalculateUtil::writeLog($this->uid, 'OpenActivity', array('general',$generalId), array(1), 'logstat');
		}
	}
}
?>
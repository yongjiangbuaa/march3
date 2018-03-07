<?php
/**
 * AllianceMemItem
 * 联盟成员属性
 */
import('persistence.dao.RActiveRecord');
class AllianceHisItem extends RActiveRecord {
	protected $AllianceId;        	//联盟ID
	protected $para1;			//事件参数1
	protected $para2;			//事件参数2
	protected $para3;			//
	protected $para4;
	protected $para5;
	protected $type;				//事件类型  1：盟主让贤， 2：成员加入 ，3：成员退出 ，4：任命副盟主,5:被踢，6：降职
	protected $createTime; 		//创建时间
	
	static function getItems($uid){
		
		$playerProfile = UserProfile::getWithUID($player->uid);
		if($playerProfile->league)
			return null;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancehis where rank <= 100 order by createTime DESC";
		$res = $mysql->execResult($sql, 100);
		return $res;
	}
	
	static function getNewItems($uid, $itemUids){
		$data = array();
		foreach($itemUids as $itemUid){
			$mailItem = self::getWithUID($itemUid);
			$data[] = self::resArr($mailItem);
		}
		return $data;
	}
	
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
	static function getWithUID($allianceUid){
		return self::getOne(__CLASS__, $allianceUid);
	}
	
	//返回成员页数的数据，有$range指定数据库范围
	//联盟祭拜记录不返回
	static function getHisItems($allianceUid,$loweLimit,$upLimit){
		$loweLimit = $loweLimit - 1;
//		p($loweLimit);
//		p($upLimit);
//		p($allianceUid);
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancehis where AllianceId = '{$allianceUid}' and type != 10 order by createTime DESC limit {$loweLimit},{$upLimit}";
		$num = $upLimit - $loweLimit;
		$res = $mysql->execResult($sql, $num);
		
		$count = self::getHisCount($allianceUid);
		return array(
		'count' => $count,
		'historyList' => $res,
		);
	
	}
	//联盟祭拜记录
	static function getAllianceWorshipRecord($allianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancehis where AllianceId = '{$allianceId}' and type = 10 order by createTime DESC ";
		$data = $mysql->execResultWithoutLimit($sql);
		$count = count($data);
		if($count > 10) {
			$time = $data[9]['createTime'];
			$sql = "delete from alliancehis where AllianceId = '{$allianceId}' and type = 10 and createTime < {$time}";
			$mysql->execute($sql);
			for($i=10;$i<$count;$i++) {
				unset($data[$i]);
			}
		}
		return $data;
	}
	
	 //取得现有事件总数
	static function getHisCount($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->exist('alliancehis', array('AllianceId' => $allianceUid));
	}

	//添加事件
	static function addHis($history){
		import('service.action.ConstCode');
		$count = self::getHisCount($history->AllianceId);
		$hisItem = null;
			if($count >= ConstCode::SYSTEM_MAIL_LIMIT){
				$hisItem = self::replaceHis($history->AllianceId);
			}
			if(!$hisItem){
				$hisItem = new self;
				$hisItem->AllianceId = $history->AllianceId;
			}
				$hisItem->para1 = $history->para1;
				$hisItem->para2 = $history->para2;
				$hisItem->para3 = $history->para3;
				$hisItem->para4 = $history->para4;
				$hisItem->para5 = $history->para5;
				$hisItem->type = $history->type;
				if($history->createTime) {
					$hisItem->createTime = $history->createTime;
				} else {
					$hisItem->createTime = time();
				}
			$hisItem->save();


	}
	
	 //覆盖事件
	static function replaceHis($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancehis where AllianceId='{$allianceUid}' order by createTime asc limit 1";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}


}
?>
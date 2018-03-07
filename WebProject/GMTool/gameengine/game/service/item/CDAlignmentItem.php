<?php
/**
 * CD队列
 */
import('persistence.dao.RActiveRecord');
class CDAlignmentItem extends RActiveRecord {

	protected $type;//CD类型（1.建筑升级、2.兵种、科技升级合并）
	protected $ownerId;//所属玩家
 	protected $expireTime;//失效时间时间
 	protected $redFlag;//是否可用0可用1不可用 
 	protected $cdTime;//CD到期时间
 	protected $pos;//排序

 	const TABLE = 'cdalignment';
	/**
	 * 初始化
	 */
	static function init($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$items = array();
		import('service.item.ItemSpecManager');
		$buildingCount = ItemSpecManager::singleton('default', 'item.xml')->getItem('player_cdnum')->k1;
		for($i=0;$i<$buildingCount;$i++){
			$items[] = array('uid'=>getGUID(),'ownerId'=>$uid,'type'=>1,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0);
		}
		$items[] = array('uid'=>getGUID(),'ownerId'=>$uid,'type'=>2,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0);
		$items[] = array('uid'=>getGUID(),'ownerId'=>$uid,'type'=>3,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0);
		$items[] = array('uid'=>getGUID(),'ownerId'=>$uid,'type'=>4,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0);
		$items[] = array('uid'=>getGUID(),'ownerId'=>$uid,'type'=>5,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0);
		$mysql->addBatch(self::TABLE, $items);
		return $items;
	}
	public function getItems($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get(self::TABLE, array('ownerId'=>$uid), null, 100);
		$data = Array();
		if(!$res){
			$res = $this->init($uid);
			$data = $res;
		}
		else{
			$isExist = false;
			$farmExist = false;
			foreach ($res as $item){
				if($item['type']==4){
					$isExist = true;
				}
				if($item['type'] == 5)
				{
					$farmExist = true;
				}
				$data[] = $item;
			}
			if(!$isExist){
				$alignment = self::initCdAlignmentByType($uid,4);
				$data[] = array('uid'=>$alignment->uid,'ownerId'=>$alignment->ownerId,'type'=>$alignment->type,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0,'pos'=>0);
			}
			if(!$farmExist){
				$alignment1 = self::initCdAlignmentByType($uid,5);
				$data[] = array('uid'=>$alignment1->uid,'ownerId'=>$alignment1->ownerId,'type'=>$alignment1->type,'expireTime'=>0,'redFlag'=>0,'cdTime'=>0,'pos'=>0);
			}
		}
		return $data;
	}
	static public function initCdAlignmentByType($userUid,$cdType){
			$alignment = new self();
			$alignment->uid = getGUID();
			$alignment->ownerId = $userUid;
			$alignment->type = $cdType;
			$alignment->expireTime = 0;
			$alignment->redFlag = 0;
			$alignment->cdTime = 0;
			$alignment->pos = 0;
			$alignment->save();
			return $alignment;
	}
	static function getAlignmentByType($user,$type){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$currentTime = time();
		$sql = "select * from cdalignment where ownerId = '{$user}' and type = '{$type}' and (expireTime > '{$currentTime}' || expireTime = 0) order by pos asc";
		$res = $mysql->execResult($sql,100);
		return self::to($res,true);
	}
	static function getAlignmentByPos($user,$type,$pos){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$currentTime = time();
		$sql = "select * from cdalignment where ownerId = '{$user}' and type = '{$type}' and pos = '{$pos}'";
		$res = $mysql->execResult($sql,1);
		return self::to($res);
	}
 	/**
 	 * 根据主键取得记录对象实例
 	 *
 	 * @param unknown_type $uid
 	 * @return unknown
 	 */
 	static function getWithUID($uid){
 		return self::getOne(__CLASS__, $uid);
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
}
?>
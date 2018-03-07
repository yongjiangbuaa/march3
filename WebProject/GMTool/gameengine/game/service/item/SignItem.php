<?php
/**
 * SignItem
 * 
 * 签到属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class SignItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;                  //用户UID
	protected $times;				//当月签到次数
	protected $signList = array();  //当月签到的具体日期{2012.1.1,2012.1.15,...}

 	

 	//读取签到表
  	function getItems($uid){
 		
 		$rewardList = array();
 		$data = array();
		import('service.item.ItemSpecManager');
		$xmlItems = ItemSpecManager::singleton('default', 'sign.xml')->getGroup('sign');
 		$signItem = self::getWithUID($uid);
		if(!$signItem){
			$signItem = self::init($uid);
		}else{
			if(count($signItem->signList)){
				$year = date('y')+2000;
				$month = date('m') + 0;
				$timeArr1 = Array();
				$timeArr1 = explode('-',$signItem->signList[0]);
				if($year >$timeArr1[0] || ($month+0) >($timeArr1[1]+0)){
					$signItem->times = 0;
					$signItem->signList = array();	
					$signItem->save();
				}
			}
			
			
		}
		
		import('service.action.CalculateUtil');
		foreach($xmlItems as $xmlItem){			
			$rewardList[]= CalculateUtil::getInfoByRewardId($xmlItem->reward);	
		}
 		$data[] = Array(
	 		'uid'=>$signItem->uid,
 			'itemId'=>null,
	 		'times'=>$signItem->times,
	 		'signList'=>$signItem->signList,
	 		'rewardList'=>$rewardList,
 		);
 		return $data;
 	}
 	
 	//初始化数据库
	function init($uid){
		$signItem = new self;
		$signItem->uid = $uid;
		$signItem->times = 0;
		$signItem->save();
		return $signItem;
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
			$res->unserializeProperty('signList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('signList');
		parent::save();
		$this->unserializeProperty('signList');
	}
	
}

?>
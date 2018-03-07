<?php
/**
 * EffectItem
 * 
 * 城市状态属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class EffectItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid; //用户uid
	protected $effectList; //buff状态列表 array($status_id => array('statusId' => status_id, 'endTime' => timestamp:buff结束时间)...)
	                       //状态效果用status_id去status文件中索引
	
	public function getItems($uid){
		import('service.action.CalculateUtil');
		$current_time = time();
		$data = array();
//		$effects = CalculateUtil::getGoodsStatus($uid);
//		if(is_array($effects)){
//			foreach ($effects as $key => $effect){
//				$data[] = array(
//					'itemId' => $key,
//					'endTime' => $effect['endTime'],
//					'value' => $effect['value'],
//				);
//			}
//		}
		$effectItem = self::getWithUID($uid);
		if(!$effectItem || $effectItem->effectList == null){
			return array();
		}
		$data = array();
		import('service.action.ConstCode');
		$effectList = $effectItem->effectList;
		$effectList = self::resetCityPicTime($uid, $effectItem);
		foreach ($effectList as $key => $effect){
			if($effect['endTime'] < $current_time) continue;
			$xmlStatus = ItemSpecManager::singleton('default', 'item.xml')->getItem($effect['statusId']);
			$cityStatusFlag = in_array($effect['statusId'], ConstCode::$cityStatusFlag) == true ? 1 : 0;
			$data[] = array(
				'itemId' => $effect['statusId'],
				'startTime' => $effect['startTime'],
				'endTime' => $effect['endTime'],
				'effect1' => $xmlStatus->effect1,
				'value1' => $xmlStatus->value1,
				'effect2' => $xmlStatus->effect2,
				'value2' => $xmlStatus->value2,
				'effect3' => $xmlStatus->effect3,
				'value3' => $xmlStatus->value3,
				'cityStatusFlag' => $cityStatusFlag,
			);
		}
		return $data;
	}
	/**
	 * 记录某一作用状态
	 * 
	 * 
	 */
	static function addStatusById($userId,$statusId){
		$effectItem = self::getWithUID($userId);
		if(!$effectItem){
			$effectItem = new self();
			$effectItem->uid = $userId;
		}
		$effectList = $effectItem->effectList == null ? array() : $effectItem->effectList;
		$xmlStatus = ItemSpecManager::singleton('default', 'item.xml')->getItem($statusId);
		$addTime = $xmlStatus->time;
		if($effectList[$statusId]['endTime']&&$effectList[$statusId]['endTime']> time()){
			$effectList[$statusId]['endTime']+=$addTime;
		}else{
			$effectList[$statusId]['statusId']=$statusId;
			$effectList[$statusId]['endTime']=time()+$addTime;
		}
		$effectItem->effectList = $effectList;
		$effectItem->save();
		return array(
				'itemId' => $statusId,
				'endTime' =>$effectList[$statusId]['endTime'],
				'effect1' => $xmlStatus->effect1,
				'value1' => $xmlStatus->value1,
				'effect2' => $xmlStatus->effect2,
				'value2' => $xmlStatus->value2,
				'effect3' => $xmlStatus->effect3,
				'value3' => $xmlStatus->value3,
			);
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if (is_object($cacheVal))
			return $cacheVal;
		elseif (is_array($cacheVal)) 
			return null;
			
		$res = self::getOne(__CLASS__, $uid);
		if($res)
			$res->unserializeProperty('effectList');
		$cacheVal = $res;
		if(!$res)
		{
			$cacheVal = array();
		}
		parent::setCacheValue($cachekey, $cacheVal);
		return $res;
	}
	
	/**
	 * 使用城市外观钢铁侠的玩家第一次恢复为60d,填并发送邮件
	 */
	static function resetCityPicTime($uid, $effectItem) {
		$effectList = $effectItem->effectList;
		if($effectList) {
			$statusId = '1100019';
			$effect = $effectList[$statusId];
			$current_time = time();
			if($effect && $current_time < $effect['endTime'] && !isset($effect['firstAddTimeFlag'])) {
				$xmlStatus = ItemSpecManager::singleton('default', 'item.xml')->getItem($statusId);
				$effect['firstAddTimeFlag'] = 1;
				$effect['endTime'] = $current_time + $xmlStatus->time;
				$effectList[$statusId] = $effect;
				$effectItem->effectList = $effectList;
				$effectItem->save();
				import('service.item.MailItem');
				$mailXml = ItemSpecManager::singleton('cn', 'item.xml')->getItem('7949');
				$title = $mailXml->description;
				$contents = $mailXml->description1;
				$user = UserProfile::getWithUID($uid);
				mailItem::addMail('system', $user, 1, $title, $contents);
			}
		}
		return $effectList;
	}
	
	public function save(){
		$this->serializeProperty('effectList');
		parent::save();
		$this->unserializeProperty('effectList');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
}
?>
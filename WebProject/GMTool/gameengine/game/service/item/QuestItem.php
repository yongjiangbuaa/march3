<?php
/**
 * 
 * 任务属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class QuestItem extends RActiveRecord {
	protected $ownerId;     //玩家uid
	protected $type;		//任务类型
	protected $target;		//任务目标类型
	protected $nums;        //已完成次数
	protected $status;      //状态 0-待领取,1-已领取,2-已完成
	protected $del;		//0：正常,1:删除
	
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
		return self::getOne(__CLASS__, $uid);
	}
	
	static function initQuest($user){
		import('service.action.QuestClass');
		$quest = Quest::singleton($user);
		$quest->doInit();
	}
	
	/**
	 * 根据任务目标类型查询
	 *
	 * @param String $uid
	 * @param Integer $target
	 */
	static function getWithTarget($uid, $target){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('quest', array(
			'ownerId' => $uid,
			'target' => $target,
			'del' => 0,
		),null, 100);
		return self::to($res, true);
	}
	
	/*
	 * 根据任务类型查询
	 */
	static function getWithType($uid, $type, $status = null){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$conArr = array(
			'ownerId' => $uid,
			'type' => $type,
		);
		if(is_int($status)){
			$conArr['status'] = $status;
		}
		$res = $mysql->get('quest', $conArr, null, 100);
		return self::to($res, true);
	}
	
	/*
	 * 检查任务条件限制
	 */
	static function questCheck($userProfile, $taskIds){
		import('service.action.QuestClass');
		$quest = Quest::singleton($userProfile);
		$mainQuestList = self::getWithType($userProfile->uid, 0);
		if(!is_array($taskIds)){
			return self::checkOne($userProfile, $quest, $mainQuestList, $taskIds);
		}else{
			foreach ($taskIds as $taskId){
				if(!self::checkOne($userProfile, $quest, $mainQuestList, $taskId)){
					return false;
				}
			}
			return true;
		}
	}
	
	static function checkOne($userProfile, $quest, $mainQuestList, $taskId){
		$flag = false;
		if(!$mainQuestList){
			return true;
		}
		foreach ($mainQuestList as $questItem){
			if($questItem->itemId == $taskId){
				$questItem->fillXMLProperty(LoadXMLUtil::replaceFileName('quest.xml',$userProfile));
				$quest->questStatusCheck($questItem);
				if($questItem->status == 2){
					$flag = true;
					break;
				}
			}
		}
		if(!$flag){
			$preArr = self::getCompleteQuestOnBefore($userProfile->uid, $mainQuestList, false);
			foreach ($preArr as $value){
				if($value['itemId'] == $taskId){
					$flag = true;
					break;
				}
			}
		}
		return $flag;
	}
	
	/*
	 * 取得已领取的日常任务数量
	 */
	static function getDailyQuestCount($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "select count(uid) count from quest where ownerId='{$uid}' and del=0 and type=2 and status=1";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	/*
	 * 取得已领取的联盟任务数量
	 */
	static function getAllianceQuestCount($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "select count(uid) count from quest where ownerId='{$uid}' and del=0 and type=3 and status=1";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	/*
	 * 取得现有的联盟任务数量
	 */
	static function getAllianceCount($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "select count(uid) count from quest where ownerId='{$uid}' and type=3";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	static function getItems($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('quest', array(
			'ownerId' => $uid,
		),null, 100);
		$res = self::to($res, true);
		import('service.action.QuestClass');
		$userProfile = UserProfile::getWithUID($uid);
		$quest = Quest::singleton($userProfile);
		$quest->setQuestList($res);
		$quest->autoFlushDailyQuest();
		//if($userProfile->league)
		$quest->autoFlushAllianceQuest();
		$data = $quest->getItems();
		$preQuestList = self::getCompleteQuestOnBefore($uid, $quest->getMainQuestList(false));
		return array_merge($data, $preQuestList);
	}
	
	static function getNewItems($uid, $itemUids){
		import('service.action.QuestClass');
		import('service.action.LoadXMLUtil');
		$userProfile = UserProfile::getWithUID($uid);
		$quest = Quest::singleton($userProfile);
		foreach ($itemUids as $itemUid){
			$questItem = self::getWithUID($itemUid);
			$questItem->fillXMLProperty(LoadXMLUtil::replaceFileName('quest.xml',$userProfile));
			$data[] = $quest->getResArr($questItem);
		}
		return $data;
	}
	
	/*
	 * 清除头天活跃任务
	 */
	static function removeActiveQuest($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		//活跃度任务更新
		$activeNum = $mysql->exist('quest', array('ownerId'=>$uid,'type'=>1));
		if($activeNum > 0 && $activeNum != 23){
			$mysql->execute("delete from quest where ownerId='{$uid}' and type=1");
			import('service.item.ItemSpecManager');
			import('service.action.LoadXMLUtil');
			$userProfile = UserProfile::getWithUID($uid);
			$xmlQuests = LoadXMLUtil::loadXmlFile('quest.xml',$userProfile)->getGroup('quest_list');
			foreach($xmlQuests as $xmlQuest){
				if($xmlQuest->type1 == '1'){
					$questItem = new self();
					$questItem->itemId = $xmlQuest->id;
					$questItem->type = $xmlQuest->type1;
					$questItem->status = 1;
					$questItem->ownerId = $uid;
					$questItem->target = $xmlQuest->type3;
					$questItem->save();
				}
			}
		}else{
// 			$sql = "delete from quest where ownerId='{$uid}' and type=1";
			$sql = "update quest set `nums`=0,`status`=1 where ownerId='{$uid}' and type=1";
			$mysql->execute($sql);
		}
		return true;
	}
	
	/*
	 * 取得之前已完成的所有剧情任务
	 */
	static function getCompleteQuestOnBefore($uid, $mainQuestList, $ret = true){
		import('service.item.QuestRecordItem');
		import('service.action.LoadXMLUtil');
		$questRecordItem = QuestRecordItem::getRecords($uid);
		$userProfile = UserProfile::getWithUID($uid);
		$data = array();
		if(is_array($mainQuestList) && count($mainQuestList) > 0){
			foreach ($questRecordItem->questList as $id=>$count){
				$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$userProfile)->getItem($id);
				if($ret && !$xmlQuest->v_return){
					continue;
				}
				$data[] = array(
					'uid' => null,
					'itemId' => $id,
					'status' => 2
				);
			}
		}else{
			$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$userProfile)->getGroup('quest_list');
			foreach ($xmlQuest as $quest){
				if($quest->v_return){
					$data[] = array(
						'uid' => null,
						'itemId' => $quest->id,
						'status' => 2
					);
				}
			}
		}
		return $data;
	}
	
	static function recursiveFind($questId, $xmlQuest, &$preArr, $ret){
		$isFirst = 1;
		foreach($xmlQuest as $quest){
			if(preg_match("/,/", $quest->series)){
				$ids = explode(',', $quest->series);
			}else{
				$ids[] = $quest->series;
			}
			if(in_array($questId, $ids)){
				$isFirst = 0;
				if(!in_array($quest->id, $preArr)){
					if($ret){
						if($quest->v_return){
							$preArr[] = $quest->id;
						}
					}else{
						$preArr[] = $quest->id;
					}
					self::recursiveFind($quest->id, $xmlQuest, $preArr, $ret);
					break;
				}else{
					$isFirst = 1;
					break;
				}
			}
		}
		if($isFirst){
			return false;
		}
	}
	
	public function fillXMLProperty($xml, $itemId = null){
		parent::fillXMLProperty($xml, $itemId);
		if($this->target != $this->type3){
			$this->target = $this->type3;
			$this->save();
		}
	}	
}
?>
<?php
import('persistence.dao.RActiveRecord');
class MessageBallItem extends RActiveRecord {
	protected $fromUser;     //发送人UID
	protected $toUser;    //接受人UID
	protected $contents;  //存放的填充message的必要参数，用','分割
	
	static function getItems($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('messageball', array(
			'toUser' => $uid,
		),null, 100);
		$res = self::addTempMessageBall($uid, $res);
		$data = array();
		if(!$res){
			return $data;
		}
		$res = self::handleOutDateMessageBalls($res);
		self::removeAll($uid);
		if(!$res) {
			return $data;
		}
		foreach ($res as $message){
			$data[] = self::resArr($message);
		}
		return $data;
	}
	
	static function handleOutDateMessageBalls($messageBalls) {
		if($messageBalls) {
			$currTime = time();
			foreach($messageBalls as $key => $messageBall) {
				if($messageBall['itemId'] == 70 && $currTime > $messageBall['contents']) {
					unset($messageBalls[$key]);
				}
			}
		}
		return $messageBalls;
	}
	
	//增加临时消息球，不用保存，只是在登录的时候显示下
	static function addTempMessageBall($uid, $res) {
		$userLeague = UserProfile::getWithUID($uid)->league;
		if($userLeague) {
			import('service.item.ProclaimWarItem');
			$proclaimOwnerRecord = ProclaimWarItem::getProclaimOwnerRecordByTime($userLeague, 3);
			if($proclaimOwnerRecord) {
				$res[] = array(
					'uid' => getGUID(),
					'itemId' => 101, 'fromUser' => $uid, 
					'toUser' => $uid, 'contents' => 1
				);
			} 
			//联盟黑暗入侵开始后发送消息球
			import('service.item.AllianceActivityItem');
			import('service.item.ItemSpecManager');
			$activityId = '8887';
			$xmlActivity = ItemSpecManager::singleton('default', 'activity.xml')->getItem($activityId);
			$allianceDarkItem = AllianceActivityItem::selectAllianceActItem($userLeague, $activityId, strtotime($xmlActivity->start_time));
			if($allianceDarkItem && !$allianceDarkItem->battleResult) {
				$res[] = array('uid' => getGUID(), 'itemId' => 102, 'toUser' => $uid);
			}
		}
		return $res;
	}
	
	static function getNewItems($uid, $itemUids){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$messages = array();
		foreach ($itemUids as $itemUid){
			if(!$itemUid) continue;
			$res = $mysql->get('messageball', array(
				'uid' => $itemUid,
			));
			if($res[0]){
				$messages[] = $res[0];
			}
		}
		$data = array();
		if(!$messages){
			return $data;
		}
		foreach($messages as $message){
			$data[] = self::resArr($message);
			if($message['itemId'] != 70) { //军情消息球特殊处理
				self::removeOne($message['uid']);
			}
		}
		return $data;
	}
	
	/**
	 * 添加一条记录到消息球
	 *
	 * @param Object $fromUser 发送方
	 * @param Object $toUser   接收方
	 * @param String $itemId   消息模板ID
	 * @param Array $contents  模板中需要替换的参数
	 */
	static function addMessageBall($from, $to, $itemId, $contents = null){
		$messageBallItem = new self;
		$messageBallItem->itemId = $itemId;
		$messageBallItem->fromUser = $from;
		$messageBallItem->toUser = $to;
		if($contents){
			$messageBallItem->contents = implode(',', $contents);
		}
		$messageBallItem->save();
		//数据存入聊天
		import('service.action.ChatClass');
		$chatContents['mode'] = 11;
		$chatContents['modeValue'] = 'messageBallItems';
		$chatContents['contents'] = $messageBallItem->uid;
		$userProfile = UserProfile::getWithUID($to);
		Chat::message($userProfile)->setContents($chatContents)->sendOneMessage();
		return $messageBallItem;
	}
	
	/*
	 * 删除所有已读消息球
	 */
	static function removeAll($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "delete from messageball where toUser='{$uid}'";
		$mysql->execute($sql);
	}
	
	static function removeOne($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "delete from messageball where uid='{$uid}'";
		$mysql->execute($sql);
	}
	
	static function selectMessageBall($fromUser, $toUser, $itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = " select uid,contents from messageball where fromUser = '{$fromUser}' and toUser = '{$toUser}' and itemId = {$itemId} ";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	static function resArr(Array $message){
		return array(
				'uid' => $message['uid'],
				'itemId' => $message['itemId'],
				'fromUser' => $message['fromUser'],
				'contents' => $message['contents'],
			);
	}
}
?>
<?php
/**
 * 
 * 邮件模型类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class MailItem extends RActiveRecord{
	protected $type;	 //邮件类型：1-系统,2-玩家 3-战俘,4-世界征讨,5-联盟邮件
	protected $toUser;   //收件人UID
	protected $fromUser; //发送人UID
	protected $fromName; //发送人姓名
	protected $title;	 //标题
	protected $contents; //内容
	protected $flag;	 //特殊邮件标记, 0-普通;1-特殊
	protected $createTime; //创建时间
	protected $status;   //状态：1-未读,2-已读
	protected $fightReport;	//战报UID
	protected $rewardId;   //附件奖励ID，用于用户领取奖励
	protected $rewardStatus;   //0,未领取，1已领取\
	
	static function getItems($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from mail where toUser='{$uid}' order by status ASC,createTime DESC";
		$res = $mysql->execResult($sql, 200);
		import('service.action.CalculateUtil');
		$data= Array();
		if ($res)
		{
			foreach($res as $mailItem){
				$rewardList = Array();
				if($mailItem['rewardId'] && $mailItem['rewardId'] != '0'){
					$rewardList = self::getMailRewardItem($mailItem['rewardId']);
				}
				$mailItem['rewardList'] = $rewardList;
				$data[] = $mailItem;
			}
		}
		return $data;
	}
	
	/**
	 * 根据mail里面的rewardid获取前台显示的rewardList
	 * @param string $rewardId
	 * @return 
	 */
	static function getMailRewardItem(&$rewardId){
		import('service.action.CalculateUtil');
		//翻倍处理
		if(preg_match("/\*/", $rewardId)) {
			list($multiply,$rewardItemId) = explode('*', $rewardId);
			$multiply -= 1;
		}else{
			$multiply = 0;
			$rewardItemId = $rewardId;
		}
		$ratio = array();
		if(preg_match("/-/", $rewardItemId)) {
			list($rd, $ratio) = explode('-', $rewardItemId);
			$ratio = json_decode($ratio,true);
			$rewardItem = CalculateUtil::getMultiplyReward(1,$rd);
			$rewardId = $rd;
		} elseif(preg_match("/,/", $rewardItemId))
		{
			$rewardItem = CalculateUtil::getRewardItem($rewardItemId);
		}else{
			$rewardItem = CalculateUtil::getMultiplyReward(1,$rewardItemId);
		}
		$rewardItem = CalculateUtil::addRewardRatio(null, $multiply*100, $rewardItem,false);
		$rewardList = CalculateUtil::getInfoByRewardId(null, $rewardItem, $ratio);
		return $rewardList;
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
	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	/**
	 * 取得邮件总数
	 *
	 * @param String $uid  玩家uid
	 * @param Integer $type  邮件类型
	 */
	static function getMailCount($uid, $type){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->exist('mail', array('toUser' => $uid, 'type' => $type));
	}
	
	/**
	 * 取得旧替换邮件
	 *
	 * @param unknown_type $uid
	 * @param unknown_type $type
	 */
	static function getReplaceMail($uid, $type){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from mail where toUser='{$uid}' and type = '{$type}' and flag=0 order by createTime asc limit 1";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	
	/**
	 * 添加邮件
	 *
	 * @param Object $fromUser
	 * @param Object $toUser
	 * @param Integer $type
	 * @param String $title
	 * @param String $contents
	 */
	static function addMail($fromUser, $toUser, $type, $title, $contents, $flag = 0, $createTime = null,$rewardId= '0',$reportId = null){
		import('service.action.ConstCode');
		$count = self::getMailCount($toUser->uid, $type);
		$mailItem = null;
		//系统邮件
		if($type == 1 || $type == 4){
			if($count >= ConstCode::SYSTEM_MAIL_LIMIT){
				$mailItem = self::getReplaceMail($toUser->uid, $type);
			}
			if(!$mailItem){
				$mailItem = new self;
				$mailItem->type = $type;
				$mailItem->toUser = $toUser->uid;
				$mailItem->fromName = 'system';
			}
			$mailItem->title = $title;
			$mailItem->contents = $contents;
			$mailItem->flag = $flag;
			$mailItem->rewardId = $rewardId;//money;2000|gold;100
			$mailItem->rewardStatus = 0;
			$mailItem->fightReport = $reportId;
			if(empty($createTime)){
				$mailItem->createTime = time();
			}else{
				$mailItem->createTime = $createTime;
			}
			$mailItem->status = 1;
			$mailItem->save();
		}
		//玩家邮件
		if($type == 2||$type == 5){
			if($count >= ConstCode::USER_MAIL_LIMIT){
				$mailItem = self::getReplaceMail($toUser->uid, $type);
			}
			if(!$mailItem){
				$mailItem = new self;
				$mailItem->type = $type;
				$mailItem->toUser = $toUser->uid;
			}
			
			$mailItem->fromUser = $fromUser->uid;
			$mailItem->fromName = $fromUser->name;
			$mailItem->title = $title;
			$mailItem->contents = $contents;
			$mailItem->flag = $flag;
			$mailItem->createTime = time();
			$mailItem->rewardId = $rewardId;
			$mailItem->rewardStatus = 0;
			$mailItem->status = 1;
			$mailItem->save();
		}
		//战俘系统邮件
		if($type == 3)
		{
			if($count >= ConstCode::SYSTEM_MAIL_LIMIT){
				$mailItem = self::getReplaceMail($toUser->uid, $type);
			}
			if(!$mailItem){
				$mailItem = new self;
				$mailItem->type = $type;
				$mailItem->toUser = $toUser->uid;
				$mailItem->fromName = 'system';
			}
			$mailItem->title = $title;
			$mailItem->contents = $contents;
			$mailItem->flag = $flag;
			$mailItem->rewardId = $rewardId;
			$mailItem->rewardStatus = 0;
			if(empty($createTime)){
				$mailItem->createTime = time();
			}else{
				$mailItem->createTime = $createTime;
			}
			$mailItem->status = 1;
			$mailItem->save();
		}
		if($mailItem){
			//数据存入聊天
			import('service.action.ChatClass');
			$chatContents['mode'] = 11;
			$chatContents['modeValue'] = 'mailItems';
			$chatContents['contents'] = $mailItem->uid;
			Chat::message($toUser)->setContents($chatContents)->sendOneMessage();
		}
		return true;
	}
	
	/**
	 * 对发送邮件的进一步封装
	 * @param UserProfile $user
	 * @param string $mailId item.xml中的ID
	 * @param array $params 替换邮件内容中的参数,按照顺序加入数组.
	 * @param int $sendTime 用于需要伪装邮件发送时间的
	 * @param string $rewardId 用于需要附加奖励的
	 * @param string $reportId 对于需要附加战报ID
	 */
	static function sendMailByID($user, $mailId, $mailType=1, $params=array(), $sendTime=null, $rewardId=null, $reportId=null) {
		if(is_null($sendTime)) {
			$sendTime = time();
		}
		$mailXml = ItemSpecManager::singleton('cn', 'item.xml')->getItem($mailId);
		$mailTitle = $mailXml->description;
		$mailBody = xml_replace($mailXml->description1, $params);
		mailItem::addMail('system', $user, $mailType, $mailTitle, $mailBody, 0, $sendTime, $rewardId, $reportId);
	}
	
	/*
	 * 删除单封邮件
	 */
	static function removeOneMail($uid, $userUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('mail', array('uid' => $uid, 'toUser' => $userUid));
	}
	/*
	 * 删除15天过期邮件
	 */
	static function removeExpiredMail($uid){
		$time = time()-15*24*3600;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from mail where toUser='{$uid}' and createTime<'{$time}' and (rewardId='0' or (rewardId !='0' and rewardStatus ='1'))";
		$mysql->execute($sql);
	}
	/*
	 * 按类型批量删除邮件
	 */
	static function removeAllMail($type, $userUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "delete from mail where toUser='{$userUid}' and type={$type}";
		return $mysql->execute($sql);
	}
	
	static function resArr($mailItem){
		$rewardId = $mailItem->rewardId;
		if($mailItem->rewardId && $mailItem->rewardId !='0'){
				//$rewardId = $mailItem->rewardId;
				$rewardList = self::getMailRewardItem($rewardId);
		}
		return array(
			'uid' => $mailItem->uid,
			'itemId' => null,
			'fromUser' => $mailItem->fromUser,
			'toUser' => $mailItem->toUser,
			'fromName' => $mailItem->fromName,
			'title' => $mailItem->title,
			'contents' => $mailItem->contents,
			'flag' => $mailItem->flag,
			'createTime' => $mailItem->createTime,
			'status' => $mailItem->status,
			'fightReport' => $mailItem->fightReport,
			'type' => $mailItem->type,
			'rewardId' => $rewardId,
			'rewardStatus' => $mailItem->rewardStatus,
			'rewardList' => $rewardList,
		);
	}
}
?>
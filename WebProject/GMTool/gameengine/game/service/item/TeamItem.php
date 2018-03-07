<?php
/**
 * TeamItem
 * 队伍模型
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class TeamItem extends RActiveRecord {
	protected $uid;
	protected $type;       //组队类型(预留字段) 1:pve组队,2:pvp
	protected $itemId;
	protected $battleId;   //团战Id
	protected $create_at;  //队伍创建时间,timestamp
	protected $fightPower; //战斗力限制
	protected $nums;       //成员数量
	protected $name;       //队长名字
	protected $status;     //1:已开战,0:等待中
	protected $alliance;   // 是否有联盟限制
	protected $autoBattle; //是否自动开战
	protected $autoSweep; //是否自动横扫千军
	protected $forcesLimit; //队伍的兵力限制
	protected $round; //万里长征当前团队打到第几关
	protected $vs; //联盟对战双方的得分情况
	protected $fight_time; //开战时间
	protected $leaderUid;
	
	const PVE = 1;
	const PVP = 2;
	const ATTACKER = 1;
	const DEFENSER = 2;
	const TABLE = 'team';
	
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
	 * 根据活动ID查找队伍
	 *
	 * @param String $itemId
	 */
	static function getTeamsWithItemId($itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get(self::TABLE, array('itemId' => $itemId, 'status' => 0), null, 100);
		return self::to($res, true);
	}
	
	static function getTeamsWithItemIdAndTime($itemId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where itemId = '{$itemId}' and create_at >= {$time}";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function getActivityPlayerJoin($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select t.itemId as activityId from team as t,teammember as m where m.uid ='{$uid}' and m.ownerid=t.uid";
		$res = $mysql->execResult($sql, 1);
		return $res;
	}
	
	/**
	 * 创建队伍
	 *
	 * @param unknown_type $uid
	 * @param unknown_type $itemId
	 * @param unknown_type $type
	 */
	static function create($itemId, $battleId, $user, $fightPower = 0, $allianceLimit, $type = 1, $autoBattle, $forcesLimit, $autoSweep){
		$teamItem = new self;
		$teamItem->itemId = $itemId;
		$teamItem->type = $type;
		$teamItem->battleId = $battleId;
		$teamItem->create_at = time();
		$teamItem->fightPower = $fightPower;
		$teamItem->leaderUid = $user->uid;
		$teamItem->name = $user->name;
		$teamItem->nums = 1;
		$teamItem->autoSweep = $autoSweep;
		if($allianceLimit == 1) {
			$teamItem->alliance = $user->league;
		}
		$teamItem->autoBattle = $autoBattle;
		$teamItem->forcesLimit = $forcesLimit;
		$teamItem->save();
		return $teamItem;
	}
	
	static function createAllianceTeam($activityId, $battleId, $teamName, $attAllianceId, $defAllianceId){
		$teamItem = new self;
		$teamItem->itemId = $activityId;
		$teamItem->type = 2;
		$teamItem->battleId = $battleId;
		$teamItem->create_at = time();
		$teamItem->fightPower = 0;
		$teamItem->name = $teamName;
		$teamItem->nums = 1;
		$vsName = explode('-vs-', $teamName);
		if($defAllianceId && $vsName[1]) {
			$def = array('uid' => $defAllianceId, name => $vsName[1], 'points' => 0);
		} else {
			$def = null;
		}
		$att = array('uid' => $attAllianceId, name => $vsName[0], 'points' => 0);
		$teamItem->vs = array('1' => $att, '2' => $def);
		$teamItem->autoBattle = 'N';
		import('service.item.ItemSpecManager');
		$xmlActivity = ItemSpecManager::singleton('default', 'activity.xml')->getItem($activityId);
		$teamItem->fight_time = strtotime($xmlActivity->start_time);
		$teamItem->save();
		return $teamItem;
	}
	
	static function checkAllianceTeam(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from " . self::TABLE . " where type = 2";
		return $mysql->execute($sql);
	}
	
	static function getOutDateTeam($endTime, $itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, create_at, status from " . self::TABLE . " where type = 1 and itemId = '{$itemId}' and create_at < " . $endTime;
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 查询平局的盟战队伍
	 */
	static function selectNoResultAllanceBattleTeams($startTime) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select t.uid, t.type, t.itemId, t.round, tbr.round as battleRound from team t left join teambattleround tbr on t.uid = tbr.uid where t.type = 2 and t.round != 1 and t.create_at >= {$startTime}";
		return $mysql->execResultWithoutLimit($sql);
	}
		
	static function clear($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::TABLE . " where uid = '" . $teamUid . "'";
		$mysql->execute($sql);
	}
	
	public function save() {
		$this->serializeProperty('vs');
		parent::save();
		$this->unserializeProperty('vs');
	}
}
?>
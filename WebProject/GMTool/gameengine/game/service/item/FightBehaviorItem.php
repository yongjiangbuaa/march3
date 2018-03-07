<?php
import('persistence.dao.RActiveRecord');
class FightBehaviorItem extends RActiveRecord {
	protected $fromUser;    //攻方
	protected $toUser;		//守方
	protected $type;		//行为类型：1-占领;2-掠夺;3-摧毁;4-驻守别人;5-驻守自己
	protected $waitTime;    //等待时间(前往)
	protected $endTime;		//结束时间(返回)
	protected $forces;      //携带兵力资源总数
	protected $mineral;		//单兵携带矿物
	protected $oil;			//单兵携带石油
	protected $food;		//单兵携带粮食
	protected $matrix;		//阵法信息
	protected $generalList; //阵法武将信息
	protected $x;        	//出征位置x坐标
	protected $y;		 	//出征位置y坐标
	protected $status;		//0:未处理;1:经过战斗处理;2:经过返回处理
	
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
	 * 取得玩家战斗行为记录
	 *
	 * @param unknown_type $fromUser
	 * @param unknown_type $toUser
	 * @return unknown
	 */
	static function getRecordBetweenPlayers($fromUser, $x, $y, $toUser){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$condition = array();
		$condition['fromUser'] = $fromUser;
		if($x && $y){
			$condition['x'] = $x;
			$condition['y'] = $y;
		}
		if($toUser){
			$condition['toUser'] = $toUser;
		}
		$res = $mysql->get('fightbehavior', $condition);
		return self::to($res);
	}
	
	/**
	 * 取得坐标点的有效行为记录列表
	 *
	 * @param Integer $x
	 * @param Integer $y
	 */
	static function getRecordsBySite($x, $y){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from fightbehavior where x={$x} and y={$y} and status=0 and waitTime < {$time} order by waitTime asc";
		$res = $mysql->execResult($sql, 100);
		return self::to($res, true);
	}
	
	/**
	 * 取得坐标点行为返回记录列表
	 *
	 * @param Integer $x
	 * @param Integer $y
	 */
	static function getDoneRecordsBySite($x, $y){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from fightbehavior where  x={$x} and y={$y} and status=1 and endTime < {$time}";
		$res = $mysql0->execResult($sql, 100);
		return self::to($res, true);
	}
}
?>
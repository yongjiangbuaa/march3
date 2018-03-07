<?php
/**
 * PrisonerItem
 * 
 * 战俘log属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class PrisonerlogItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $slaverId; //被俘虏用户uid
	protected $ownerId;  // 主人uid
	protected $conquer_time;  // 被俘虏时间
	protected $end_time;     	// 战俘关系解除时间
	protected $fight_time;		// 抢夺战俘时间
	/**
     +----------------------------------------------------------
     * 记录被俘虏时间
     +----------------------------------------------------------
     * @method savelog
     * @access static public
     * @param $slaverId     被俘虏用户uid
     * @param $ownerId      主人uid
     * @param $conquer_time 被俘虏时间
     * 
     +----------------------------------------------------------
     * @return              返回相应信息
     +----------------------------------------------------------
     */		
	static public function logConquerTime($slaverId, $ownerId, $conquer_time=null){
		import('util.mysql.XMysql');
		if(empty($conquer_time)){
			$conquer_time = time();
		}
		$end_time = $conquer_time + 24 * 3600;
		$mysql = XMysql::singleton();
		$sql = "select * from prisonerlog where slaverId = '{$slaverId}' and ownerId = '{$ownerId}'";
		$result = $mysql->execResult($sql,1);
		if(!empty($result)){
			//如果有记录
			$sql2 = "update prisonerlog set conquer_time={$conquer_time},end_time ={$end_time}  where slaverId = '{$slaverId}' and ownerId = '{$ownerId}'";
			$mysql->execute($sql2);
		}else{
			$prlog = new self();
			$prlog->slaverId = $slaverId;
			$prlog->ownerId = $ownerId;
			$prlog->conquer_time = $conquer_time;
			$prlog->end_time = $end_time;
			$prlog->fight_time = 0;
			$prlog->save();
		}
		return;
		
	}
	/**
     +----------------------------------------------------------
     * 记录抢夺战俘时间
     +----------------------------------------------------------
     * @method savelog
     * @access static public
     * @param $slaverId     被抢夺战俘用户uid
     * @param $ownerId      主人uid
     * @param $conquer_time 被抢夺时间
     * 
     +----------------------------------------------------------
     * @return              返回相应信息
     +----------------------------------------------------------
     */		
	static public function logFightTime($slaverId, $ownerId, $fight_time=null){
		import('util.mysql.XMysql');
		if(empty($fight_time)){
			$fight_time = time();
		}
		$mysql = XMysql::singleton();
		$sql = "select * from prisonerlog where slaverId = '{$slaverId}' and ownerId = '{$ownerId}'";
		$result = $mysql->execResult($sql,1);
		if(!empty($result)){
			//如果有记录
			$sql2 = "update prisonerlog set fight_time={$fight_time} where slaverId = '{$slaverId}' and ownerId = '{$ownerId}'";
			$mysql->execute($sql2);
		}else{
			$prlog = new self();
			$prlog->slaverId = $slaverId;
			$prlog->ownerId = $ownerId;
			$prlog->conquer_time = 0;
			$prlog->end_time = 0;
			$prlog->fight_time = $fight_time;
			$prlog->save();
		}
		return;
		
	}
	
	
	/**
     +----------------------------------------------------------
     * 记录逃跑时间
     +----------------------------------------------------------
     * @method savelog
     * @access static public
     * @param $slaverId     被俘虏用户uid
     * @param $ownerId      主人uid
     * @param $endTime      结束时间
     +----------------------------------------------------------
     * @return              返回相应信息
     +----------------------------------------------------------
     */		
	static public function logEndTime($slaverId, $ownerId, $endTime=null){
		if(empty($endTime)){
			$endTime = time();
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql2 = "update prisonerlog set end_time ={$endTime}  where slaverId = '{$slaverId}' and ownerId = '{$ownerId}'";
		$mysql->execute($sql2);
	}
	
}
?>
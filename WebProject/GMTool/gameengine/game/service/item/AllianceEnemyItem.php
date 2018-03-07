<?php

class AllianceEnemyItem extends RActiveRecord {
	protected $allianceId1;      	//联盟1
	protected $allianceId2; 		//联盟2
	protected $endTime; 			//敌对关系结束时间

	public function getItems($uid){
			
	}
	
	/**
	 * 建立敌对关系
	 * 
	 * */
	
	static function createAllianceEnemy($allianceId1,$allianceId2){
		import('service.item.ItemSpecManager');
		$data_config = ItemSpecManager::singleton()->getItem("alliance_battle1");
		$diffTime = $data_config->k1*3600;
		$allianceEnemyItem = new self;
		$allianceEnemyItem->allianceId1 = $allianceId1;
		$allianceEnemyItem->allianceId2 = $allianceId2;
		$allianceEnemyItem->endTime= time()+$diffTime;
		$allianceEnemyItem->save();
		return $allianceEnemyItem;
	}
	
	/**
	 * 清除敌对关系
	 * 
	 * */
	
	static function deleteAllianceEnemy(){
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from allianceenemy where endTime<'{$currTime}'";
		$result = $mysql->execute($sql);

	}
	
	/**
	 * 清除敌对关系By leagueUid
	 * 
	 * */
	
	static function deleteSelfAllianceEnemy($allianceUid){
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from allianceenemy where allianceId1='{$allianceUid}' or allianceId2 = '{$allianceUid}'";
		$result = $mysql->execute($sql);

	}
	
	/**
	 * 查找自己联盟的某一个敌对关系
	 * 
	 * */
	static function getOneAllianceEnemy($allianceId1,$allianceId2){
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceenemy where endTime>'{$currTime}' and ((allianceId1='{$allianceId1}' and allianceId2 = '{$allianceId2}')or (allianceId1='{$allianceId2}' and allianceId2 = '{$allianceId1}')) limit 1";
		$result = $mysql->execResult($sql,1);	
		return self::to($result);

	}
	
	/**
	 * 查找所有到期的敌对关系
	 * 
	 * */
	
	static function getInvalidAllianceEnemy(){
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceenemy where endTime<'{$currTime}'";
		$results = $mysql->execResult($sql,500);
		return $results;
	}
	/**
	 * 查找自己联盟的所有敌对关系
	 * 
	 * */
	
	static function getAllianceEnemy($uid){
		import('service.item.AllianceItem');
		$data = Array();
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceenemy where endTime>'{$currTime}' and (allianceId1='{$uid}' or allianceId2='{$uid}')";
		$results = $mysql->execResult($sql,100);
		if(!empty($results)){
			foreach ($results as $result){
				if($result['allianceId1'] == $uid){
					$allianceEnemy = AllianceItem::getWithUID($result['allianceId2']);
					$type = 1;
				}else{
					$allianceEnemy = AllianceItem::getWithUID($result['allianceId1']);	
					$type = 2;
				}
				$data[] = self::retArr($allianceEnemy,$result['endTime'],$type);	
			}	
		}
		return $data;	
	}
	/**
	 * 查找自己敌对联盟个数,根据type查询，主动 和被动数量。
	 * 
	 * */
	
	static function getAllianceEnemyCount($uid,$type=1){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$currTime = time();
		if($type==1)//主动
			$sql = "select count(*) as count from allianceenemy where endTime>'{$currTime}' and allianceId1='{$uid}' ";
		else 
			$sql = "select count(*) as count from allianceenemy where endTime>'{$currTime}' and allianceId2='{$uid}' ";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];	
	}
	static function retArr($alliance,$time,$type){
		
		return Array(
			'leagueEnemyId'=> $alliance->uid,
			'leagueEnemyName'=> $alliance->name,
			'endTime'=> $time,
			'type'=> $type,
		);
		
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
		return self::getOne(__CLASS__,$uid);
	}
	
}

?>
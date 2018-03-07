<?php
import('persistence.dao.RActiveRecord');
class FarmHistoryItem extends RActiveRecord
{
	protected $ownerId;
	protected $type;
	protected $time;
	protected $param1;
	protected $param2;
	protected $param3; 
	
	public function getItems($uid)
	{	
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from farmhistory where ownerId = '{$uid}' order by time desc limit 10";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;	
	}
	
	public  static function getWithUID($uid)
	{
		return self::getOne(__CLASS__, $uid);
	}
	
	static function addFarmHistory($history)
	{
		$hisItem = new self;
		$hisItem->ownerId = $history->ownerId;
		$hisItem->stealUid = $history->stealUid;
		$hisItem->type = $history->type;
		$hisItem->time = $history->time;
		$hisItem->param1 = $history->param1;
		$hisItem->param2 = $history->param2;
		$hisItem->param3 = $history->param3;
		$hisItem->param4 = $history->param4;
		$hisItem->param5 = $history->param5;
		$hisItem->save();
	}
}
<?php
import('persistence.dao.RActiveRecord');
class CrossArenaRecordItem extends RActiveRecord {
	protected $attacker;    //挑战方
	protected $defender;    //被挑战方
	protected $attName;		//攻方昵称
	protected $defName;     //防方昵称
	protected $attRank;     //攻方当前排名
	protected $defRank;     //防方当前排名
	protected $win;         //输赢状态 0:输,1:赢
	protected $attTrend;    //趋势 0:不变,1:升,2:降:
	protected $defTrend;	//趋势 0:不变,1:升,2:降:
	protected $createAt;	//创建时间
	protected $reportUid;   //战报uid
	
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
	
	/*
	 * 创建一条记录
	 */
	static function createNewRecord($attacker, $defender,$arenaAtt, $arenaDef, $win, $reportUid){
		$arenaRecordItem = new self;
		$arenaRecordItem->attacker = $attacker->uid;
		$arenaRecordItem->defender = $defender->uid;
		$arenaRecordItem->attName = $attacker->name;
		$arenaRecordItem->defName = $defender->name;
		$arenaRecordItem->attRank = $arenaAtt->rank;
		$arenaRecordItem->defRank = $arenaDef->rank;
		$arenaRecordItem->win = $win;
		$arenaRecordItem->attTrend = $arenaAtt->trend;
		$arenaRecordItem->defTrend = $arenaDef->trend;
		$arenaRecordItem->createAt = time();
		$arenaRecordItem->reportUid = $reportUid;
		$arenaRecordItem->save();
		return $arenaRecordItem;
	}
	
	/**
	 * 取得与自己相关的记录
	 */
	static function getRecords($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from crossarenarecord where attacker='{$uid}' or defender='{$uid}' order by createAt desc limit 10";
		$results = $mysql->execResultWithoutLimit($sql);
		$records = Array();
		import('service.action.CalculateUtil');
		if(count($results)>0){
			foreach ($results as $result){
				if($result['attacker']==$uid){
					$loserUid = $result['defender'];
				}else{
					$loserUid = $result['attacker'];
				}
				if($loserUid){
					$userColor = CalculateUtil::getUserAttrGrow($loserUid);
				}else{
					$userColor = 1;
				}
				$result['userColor'] = $userColor;
				$records[] = $result; 
			}
		}
		return $records;
	}
	
	static function getfightReprot ($reportUid) {
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sql = "select report from fightreport where uid = '{$reportUid}'";
		$sqlData = $mysql->execResultWithoutLimit($sql);
		$report = $sqlData[0]['report'];
		$report = json_decode($report, TRUE);
		return $report;
	}
}
?>
<?php
/**
 * AllianceItem
 * 联盟列表属性
 */
import('persistence.dao.RActiveRecord');
class ContrRankItem extends RActiveRecord {
//	protected $uid;     		 	//用户uid 也是主键
	protected $name;				//用户名字
	protected $AllianceId;			//联盟Id
	protected $contribution1;		//日贡献1
	protected $contribution2;		//日贡献2
	protected $contribution3;		//历史贡献1
	protected $contribution4;		//历史贡献2
	protected $time;				//记录每日日期
	
	//请求联盟列表。
	
	public  function getItems($uid){
		$playerProfile = UserProfile::getWithUID($uid);
		if(!$playerProfile->league)
			return Array();
		$data = array();
		$data[]= self::getSelfAlliance($uid,$playerProfile->league);
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
		return self::getOne(__CLASS__,$uid);
	}
	
	//返回日贡献的数据
	static function getDailyItems($loweLimit,$upLimit,$allianceUid){

		$loweLimit = $loweLimit-1;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		//$sql = "select * from contrrank where AllianceId = '{$allianceUid}' order by contribution1 DESC,contribution2 DESC limit {$loweLimit},{$upLimit}";
		$sql = "select * from contrrank where uid in (select MemberId from alliancemem where AllianceId = '{$allianceUid}' and `status` = 1) order by contribution1 DESC,contribution2 DESC limit {$loweLimit},{$upLimit}";
		
//		$sql = "select * from (select *,contribution1 + contribution2 as sum from ContrRank where AllianceId = '{$allianceUid}' ) as t order by t.sum desc limit {$loweLimit},{$upLimit}";
		$num = $upLimit - $loweLimit;
		$res = $mysql->execResult($sql, $num);
		
		import('service.item.AllianceItem');
		$allianceItem = AllianceItem::getWithUID($allianceUid);
		$count = $allianceItem->memberNum;
		$arr = array();
		if(is_array($res)){

				foreach ($res as $resItem){
					$tempItem['uid'] = $resItem['uid'];
					$tempItem['name'] = $resItem['name'];
					$tempItem['AllianceId'] = $resItem['AllianceId'];
					if(date('Y-m-d',$res[0][time])!= date('Y-m-d')){
						
						self::clearContr($allianceUid);
						$time = time();
						self::setContrTime($time);
						$tempItem['sum1'] = 0;
						$tempItem['sum2'] = 0;
					}else{
						$tempItem['sum1'] = $resItem['contribution1'];
						$tempItem['sum2'] = $resItem['contribution2'];
					}
					$arr[] = $tempItem;	
				}
				
	
				
		}
		
		return array(
				'count' => $count,
				'dailyRankList' => $arr,
				);
	
	}

	
	//返回历史贡献的数据
	static function getHisItems($loweLimit,$upLimit,$allianceUid){

		$loweLimit = $loweLimit-1;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
//		$sql = "select * from ContrRank where AllianceId = '{$allianceUid}' order by contribution1 ASC limit {$loweLimit},{$upLimit}";
//		$sql = "select * from (select *,contribution3 + contribution4 as sum2 from ContrRank where AllianceId = '{$allianceUid}' ) as t2 order by t2.sum2 desc limit {$loweLimit},{$upLimit}";
	///	$sql = "select * from contrrank where uid in (select MemberId from alliancemem where AllianceId = '{$allianceUid}' and `status` = 1) order by contribution3 DESC,contribution4 DESC limit {$loweLimit},{$upLimit}";
	//	$sql = "select * from ContrRank where AllianceId = '{$allianceUid}' order by contribution3 DESC,contribution4 DESC limit {$loweLimit},{$upLimit}";
		$sql = "select * from contrrank cr right join (select MemberId from alliancemem where AllianceId = '{$allianceUid}' and status=1) as am on cr.uid=am.MemberId order by contribution3 DESC,contribution4 DESC limit {$loweLimit},{$upLimit}";
		
		$num = $upLimit - $loweLimit;
		$res = $mysql->execResult($sql, $num);
		
		import('service.item.AllianceItem');
		$allianceItem = AllianceItem::getWithUID($allianceUid);
		$count = $allianceItem->memberNum;
		
		$arr = array();
		foreach ($res as $resItem){
			$tempItem['uid'] = $resItem['uid'];
			$tempItem['name'] = $resItem['name'];
			$tempItem['AllianceId'] = $resItem['AllianceId'];
			$tempItem['sum1'] = $resItem['contribution3'];
			$tempItem['sum2'] = $resItem['contribution4'];
			$tempItem['sum'] = $resItem['contribution4']*1000+$resItem['contribution3']/100;
			$arr[] = $tempItem;		
			
		}
		return array(
		'count' => $count,
		'hisRankList' => $arr,

		);
	
	}

	//添加成员
	static function addMember($userId,$name,$AllianceId){
		
		/*$itemMemm = self::getWithUID($userId);
		if($itemMemm){
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton();
			$sql = "update ContrRank set time=$time";
			$resdata = $mysql->execute($sql);
		}else{*/
			$contrdailyItem = new self;
			$contrdailyItem->uid = $userId;
			$contrdailyItem->name =	$name ;
			$contrdailyItem->AllianceId = $AllianceId;
			$contrdailyItem->contribution1 = 0;
			$contrdailyItem->contribution2 = 0;
			$contrdailyItem->contribution3 = 0;
			$contrdailyItem->contribution4 = 0;
			$contrdailyItem->time = time();
			$contrdailyItem->save();
		//}
		
		return $contrdailyItem;
	}
	
	 //删除成员
	static function deleteMember($userId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('contrrank', array('uid' => $userId));
	}
	
	 //清空每日贡献表
	static function clearContr($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update contrrank set contribution1=0,contribution2=0 ";
		$resdata = $mysql->execute($sql);
	}
	 //计算联盟成员贡献的联盟物资
	static function getTotalResource($allianceUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select SUM(contribution4) as total from  contrrank where  AllianceId ='{$allianceUid}'";
		$res = $mysql->execResult($sql);
		return $res[0]['total'];
	}
	
	 //更改当日时间
	static function setContrTime($time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update contrrank set time=$time";
		$resdata = $mysql->execute($sql);
	}
	//删除整个联盟的成员
	static function removeAlliance($allianceItem){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->del('contrrank', array('AllianceId' => $allianceItem));
		
	}
	
	 //更新每日贡献值
	static function updateContr($userId,$value1,$value2){
		
		$memItem = self::getWithUID($userId);
		if(date('Y-m-d',$memItem->time)!= date('Y-m-d')){
			self::clearContr($memItem->AllianceId);
			self::setContrTime(time());
		}
		$memItem->contribution1 += $value1;
		$memItem->contribution2 += $value2;
		$memItem->contribution3 += $value1;
		$memItem->contribution4 += $value2;
		
		$memItem->save();
	}
	
	
	 //取得每日贡献榜
	static function getSelfAlliance($uid,$allianceUid){
		$allianceItem = self::getWithUID($allianceUid);
//		p($allianceItem);
		$data= self::resArr($uid,$allianceItem);
		return $data;
	}
	
	static function resArr($uid,$allianceItem){
		import('service.item.AllianceMemItem');
		$allianceMemItem = AllianceMemItem::getAllianceMem($allianceItem->uid,$uid);
		
		return array(
		'itemId' => null,
		'leader' => $allianceItem->leader,
		'name' => $allianceItem->name,
		'exp' => $allianceItem->exp,
		'level' => $allianceItem->level,		
		'createTime' => $allianceItem->createTime,
		'post' => $allianceItem->post,
		'declaration' => $allianceItem->declaration,
		'country' => $allianceItem->country,
		'memberNum' => $allianceItem->memberNum,
		'vpNum' => $allianceItem->vpNum,
		'vpLimitNum' => $allianceItem->vpLimitNum,
		'memLimitNum' => $allianceItem->memLimitNum,
		'type' => $allianceMemItem->type,
		'allianceMemUid' => $allianceMemItem->uid,
		);
	}
	
	
}
?>
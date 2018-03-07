<?php
/**
 * 名将堂玩家刷将数据
 */
import('persistence.dao.RActiveRecord');
class FamousGeneralItem extends RActiveRecord{
	protected $currentGeneral1;	//最后一次刷新出来的将军Id1
	protected $currentGeneral2;	//最后一次刷新出来的将军Id2
	protected $currentGeneral3;	//最后一次刷新出来的将军Id3
	protected $hourTimes = 0;	//本小时已经刷新过的次数
	protected $dayTimes = 0;	//当天已经刷新过的次数
	protected $lastUpdate = 0;	//时间戳记录最后一次刷新的时间
	protected $notPurpleG = 0;	//(使用礼券，道具，金币时)紫将未出现的次数
	protected $notOrageG = 0;	//(使用礼券，道具，金币时)橙将未出现的次数
	protected $notGoldG = 0;	//(使用礼券，道具，金币时)金将未出现的次数
	protected $notPurpleS = 0;	//(使用银币时)紫将未出现的次数
	protected $notOrageS = 0;	//(使用银币时)橙将未出现的次数
	protected $notGoldS = 0;	//(使用银币时)金将未出现的次数
	protected $ensureColor = 0;	//3-紫 4-橙  5-金 
	protected $firstGold = 1;	//1-第一次使用金币刷新得紫将 0-已经用金币刷过
	protected $vipFreeTimes = 0; //VIP用户赠送的高级刷新次数
	protected $recruit1 = 0;	//第一个位置的武将已经招募
	protected $recruit2 = 0;	//第二个位置的武将已经招募
	protected $recruit3 = 0;	//第三个位置的武将已经招募
	protected $progressBar = 0; //紫色幸运值
	protected $progressBar1 = 0;//橙色幸运值 
	protected $progressBar2 = 0;//金色幸运值
	protected $ordersTimes = 0;	//点将令刷新次数
	protected $sysGoldTimes = 0;//礼券刷新次数
	protected $usrGoldTimes = 0;//金币刷新次数
	protected $haveGold = 0;	//是否刷新出来过金将0 没出现过 1出现
	const TABLE = 'famousgeneral';
	
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
		$res = self::getOne(__CLASS__, $uid);
		$currentTime = time();
		if(!$res) {
			$item = new self();
			$item->uid = $uid;
			$item->lastUpdate = $currentTime;
			$item->save();
			return $item;
		}
		else {
			if (-1 == $res -> haveGold) {
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "SELECT user , data6 AS gold FROM logstat WHERE type = 'famousgeneral' AND user = '{$uid}' AND data6 !=0 LIMIT 1";
				$haveGold = $mysql->execResult($sql);
				$res -> haveGold = $haveGold[0]['gold'];
				$res -> save ();
			}
			if (-1 == $res -> progressBar) {
				$res -> progressBar = 0;
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "SELECT param1 AS way, COUNT(*) AS nums FROM logstat WHERE type = 'famousgeneral' AND user = '{$uid}' GROUP BY param1 LIMIT 4";
				$data = $mysql->execResultWithoutLimit($sql);
				foreach ($data as $one) {
					switch ($one['way']) {
						case 1:
							$res -> progressBar += (5 * $one['nums']);
							break;
						case 2:
							$res -> progressBar += (20 * $one['nums']);
							break;
						case 3:
							$res -> progressBar += (20 * $one['nums']);
							break;
						case 4:
							$res -> progressBar += (100 * $one['nums']);
							break;
					}
				}
				//@todo目前进度条是一直往上加的
				//$res -> progressBar = min ($item -> k1, $res -> famousGeneralItem -> progressBar);
				$res -> save ();
			}
			
			if (-1 == $res -> progressBar1 && -1 == $res -> progressBar2) {
				import('service.item.ItemSpecManager');
				$item = ItemSpecManager::singleton('default','item.xml')->getItem('general_refresh');
				//初始化紫橙金幸运值
				$oProgressBar = $res -> progressBar;
				$res->progressBar = min(intval($oProgressBar/100*70), $item->k1);//紫
				$res->progressBar1 = min(intval($oProgressBar/100*20), $item->k2);//橙
				$res->progressBar2 = min(intval($oProgressBar/100*16), $item->k3);//金
				$res->save ();
			}
			
			//每小时重置hourTimes次数
			if (date('Y-m-d-H',$res->lastUpdate) != date('Y-m-d-H',$currentTime)) {
				$res -> hourTimes = 0;
				$res -> save ();
			}
			//每日重置datTimes次数
			if(date('Y-m-d',$res->lastUpdate) != date('Y-m-d',$currentTime)) {
				$res -> dayTimes = 0;
				$res -> vipFreeTimes = 0;
				$res -> save();
			}
		}
		return $res;
	}
}
?>
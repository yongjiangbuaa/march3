<?php
/**
 * DialyResetItem
 * 
 * 每日重置的数据
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class YellowDiamondItem extends RActiveRecord {
	protected $yellowVipPay = 0;					//黄钻充值次数
	protected $yellowVipPayReward = 0;				//黄钻充值领奖状态
	protected $yellowVipPayRewardNum = 0;			//黄钻领取次数

	
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
		$yellowDiamond = self::getOne(__CLASS__, $uid);
		if(!$yellowDiamond){
			$yellowDiamond = new self();
			$yellowDiamond->uid = $uid;
			$yellowDiamond->yellowVipPay  = 0;
			$yellowDiamond->yellowVipPayReward  = 0;
			$yellowDiamond->yellowVipPayRewardNum = 0;
			$yellowDiamond->save();
		}
		return $yellowDiamond;
	}
	
	/*
	 * 
	 */
//	public function getItems($uid){
//		
//		$yellowDiamond = self::getWithUID($uid);
//		if(!$yellowDiamond){
//			$yellowDiamond = new self();
//			$yellowDiamond->uid = $uid;
//			$yellowDiamond->yellowVipPay  = 0;
//			$yellowDiamond->yellowVipPayReward  = 0;
//			$yellowDiamond->yellowVipPayRewardNum = 0;
//			$yellowDiamond->save();
//		}
//		$data[] = self::retArr($yellowDiamond);
//		return $data;
//	}
	public function retArr($yellowDiamond){
		
		return Array(
			'yellowVipPay'=>$yellowDiamond->yellowVipPay,
			'yellowVipPayReward'=>$yellowDiamond->yellowVipPayReward,
			'yellowVipPayRewardNum'=>$yellowDiamond->yellowVipPayRewardNum,
		);
	}
	


	


}
?>
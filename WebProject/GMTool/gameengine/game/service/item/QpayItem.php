<?php
/**
 * PrisonerItem
 * 
 * 战俘属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class QpayItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;       				//订单id openid + billno
	protected $openid;	  				//平台用户id
	protected $ownerid;					//游戏用户uid
	protected $level;						//支付时候的等级
	protected $billno;					//支付流水号
	protected $sendtime;				//时间戳  发货时间
	protected $goodsid;					//商品id
	protected $price;					//商品价格
	protected $num;						//商品数量
	protected $amt;						//Q点/Q币消耗金额或财付通游戏子账户的扣款金额    0.1Q点为单位
	protected $payamt_coins;			//扣取的游戏币总数，单位为Q点
	protected $pubacct_payamt_coins;	//扣取的抵用券总金额，单位为Q点
	protected $zoneid;			
	
	/**
	 * 新建立记录
	 * 
	 */
	static function singleton() {
 		return new self;
 	}
	
 	/**
 	 * 根据主键取得记录对象实例
 	 *
 	 * @param unknown_type $uid
 	 * @return unknown
 	 */
 	static function getWithUID($uid){
 			
 		$res = self::getOne(__CLASS__, $uid);

 		return $res;
 	}
}
?>
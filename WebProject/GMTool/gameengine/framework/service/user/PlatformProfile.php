<?php
import('service.user.PlatformUser');
import('persistence.dao.RActiveRecord');
/**
 * PlatformProfile
 * 
 * user platform profile
 * 
 * 用户平台属性模型
 * 
 * @Entity
 * @package user
 */
class PlatformProfile extends RActiveRecord implements PlatformUser{
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 */
	protected $platformAddress = null;

	protected $userUID = null;
	
	/** 
	 * <b>userUID getter</b>
	 * 
	 * <b>获取userUID属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getUserUID(){
		return $this->userUID;
	}
	
	/** 
	 * <b>platformAddress getter</b>
	 * 
	 * <b>获取platformAddress属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getPlatformAddress(){
		return $this->platformAddress;
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
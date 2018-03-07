<?php
/** @entity  *****/	
class AdminUser extends ModelBase{
	public $name;
	public $password;
	public $enabled =1;
	public $createtime =0;
	public $lastactive =0;
	public $email;
	public $expiretime = 0;
	public $group_id;
	/** @serialize **/
	public $permitions = array();
	
	public static function login($uid){
		$expire = 30*60;		
		$citylife = &$GLOBALS['citylife'];
		$cache = $citylife['cache'];
		$time = md5(time());
		setcookie('time',$time);
		$cache->set("admin_session_".$time,$uid,$expire);
		$testv = $cache->get("admin_session_".$time);
		
	}
	public static function checkLogin(){
		$expire = 30*60;		
		$time = $_COOKIE['time'];
		$citylife = &$GLOBALS['citylife'];
		$cache = $citylife['cache'];
		$id = $cache->get("admin_session_".$time);
		if($id!=false){
			return $id;
		}
		return false;
	}
	public static function logout(){
		$expire = 0;		
		$citylife = &$GLOBALS['citylife'];
		$cache = $citylife['cache'];
		$time = $_COOKIE['time'];
		setcookie('time',0);
		$cache->delete("admin_session_".$time);
	}
}
?>
<?php
/**
 * XKeyGenerator
 * 
 * XingCloud key generator tool
 * 
 * 行云key生成工具
 * 
 * @package util
 * @author Wangqi
 *
 */
class XKeyGenerator{
	private static $instance = null;
	
	private function __construct(){
		
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>单例方法，返回XKeyGenerator的单例</b>
	 * 
	 * @param 
	 * @return XKeyGenerator
	 * @throws XException
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * item uid generator
	 * 
	 * 物品uid生成器，根据用户游戏内id，物品类名，前台传入的uniqueString进行计算
	 * 
	 * @param string $gameUserId
	 * @param string $className
	 * @param string $uniqueString
	 */
	public function genItemUid($gameUserId, $className, $uniqueString){
		$baseStr = $gameUserId.'&'.$className.'&'.$uniqueString;
		$itemUid = md5($baseStr);
		return $itemUid;
	}
}
?>
<?php
define('STATISTICS_DIR', dirname(__FILE__));
//if(!isset($GLOBALS['THRIFT_ROOT'])){
//	$GLOBALS['THRIFT_ROOT'] = STATISTICS_DIR.'/thrift';
//}
//require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
//require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
//require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
//require_once $GLOBALS['THRIFT_ROOT'].'/transport/THttpClient.php';
//require_once $GLOBALS['THRIFT_ROOT'].'/transport/TFramedTransport.php';

require_once STATISTICS_DIR.'/LogContainer.php';
require_once 'xingcloud.php';

/**
 * XA
 * xingcloud analytics
 * 
 * 统计分析控制器
 */
class XA
{
	private static $instance = null;
	private static $type =null;
	private static $location =null;
	/**
	* Constructor of xa
	*/
	private function __construct()
	{
		$filename=STATISTICS_DIR."/XAEvent.php";
		if(file_exists($filename)){
			require_once $filename;
		}
	}
	
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回xa实例，每个类对应的实例有且仅有一个
	 * 
	 * @return XA
	 */
	static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * uid setter
	 * 
	 * 设置uid属性
	 * 
	 * @param string $uid
	 */
	public function setUID($uid){
		$logger=LogContainer::singleton();
		$logger->setUID($uid);
	}
	/**
	 * uid getter
	 * 
	 * 获取uid属性
	 * 
	 * @return string
	 */
	public function getUid(){
		$logger=LogContainer::singleton();
		return $logger->getUid($uid);
	}
	/**
	 * appid setter
	 * 
	 * 设置appid属性
	 * 
	 * @param string $appid
	 */
	public function setAppid($appid){
		$logger=LogContainer::singleton();
		$logger->setAppid($appid);
	}
	/**
	 * appid getter
	 * 
	 * 获取appid属性
	 * 
	 * @return string
	 */
	public function getAppid(){
		$logger=LogContainer::singleton();
		return $logger->getAppid($appid);
	}
	/**
	 * track an evnet 
	 * 
	 * 向LogContainer添加一条count日志
	 * 
	 * @param string $name 事件的
	 * @param array $map 动作参数
	 */
	public function trackEvent($name, $map)
	{
		$logger=LogContainer::singleton();
		$logger->addLog($name, $map);
	}

	/**
	 * send out statistics log
	 */
	public function sendLog(){
		$log = LogContainer::singleton()->getLog();
		if($log==null) return;
		$statistics=Statistics::sendLog($log);
	}
	
	
}
?>
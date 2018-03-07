<?php
/**
 * LogContainer
 * 
 * log container
 * 
 * 统计分析日志存储池
 * 
 * @author Wangqi
 * @package statistics
 */
class LogContainer{
	private static $instance = null;
	private $stats=array();
	private $appid=NULL;
	private $uid=NULL;
	
	private function __construct()
	{
	}
	/**
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回LogContainer实例，每个类对应的实例有且仅有一个
	 * 
	 * @return LogContainer
	 */
	public static function singleton() {
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
		if($this->uid){
			return FALSE;
		}
		$this->uid = $uid;
	}
	/**
	 * uid getter
	 * 
	 * 获取uid属性
	 * 
	 * @return string
	 */
	public function getUid(){
		return $this->uid;
	}
	/**
	 * appid setter
	 * 
	 * 设置appid属性
	 * 
	 * @param string $appid
	 */
	public function setAppid($appid){
		if($this->appid){
			return FALSE;
		}
		$this->appid = $appid;
	}
	/**
	 * appid getter
	 * 
	 * 获取appid属性
	 * 
	 * @return string
	 */
	public function getAppid(){
		return $this->appid;
	}
	/**
	 * add a xingcloud service log
	 * 
	 * 添加一条行云服务统计日志
	 * 
	 * @param string $serviceName 服务名称
	 * @param array $parameters 服务参数
	 */
	public function addLog($name, $map){
		$temp['name']=$name;
		$temp['data'] = $map;
		$temp['timestamp'] = $_SERVER['REQUEST_TIME'];
		$this->stats[]=$temp;
	}
//	/**
//	 * add a count log
//	 * 
//	 * 添加一条count日志
//	 * 
//	 * @param string $type 动作类型
//	 * @param array $event 动作参数
//	 * @param int $amount 此事件影响的数值
//	 */
//	public function count($type,$event,$amount){
//		$temp['statfunction']='count';
//		$data[0]=$type;
//		for($i=1;$i<=5;$i++){
//			if(isset($event[$i-1]))$data[$i]=$event[$i-1];		
//			else $data[$i]="";	
//		}
//		$data[6]=$amount;
//		$temp['data'] = $data;
//		$temp['timestamp'] = $_SERVER['REQUEST_TIME'];
//		$this->stats[]=$temp;
//	}
//	/**
//	 * add an average log
//	 * 
//	 * 添加一条average日志
//	 * 
//	 * @param string $type 动作类型
//	 * @param array $event 动作参数
//	 * @param int $amount 此事件影响的数值
//	 */
//	public function avg($type,$event,$amount){
//		$temp['statfunction']='avg';
//		$data[0]=$type;
//		for($i=1;$i<=5;$i++){
//			if(isset($event[$i-1]))$data[$i]=$event[$i-1];		
//			else $data[$i]="";	
//		}
//		$data[6]=$amount;
//		$temp['data'] = $data;
//		$temp['timestamp'] = $_SERVER['REQUEST_TIME'];
//		$this->stats[]=$temp;
//	}
	/**
	 * get added logs as a json string
	 * 
	 * 获取已经添加的日志，返回一个json字符串
	 * 
	 * @return string
	 */
	public function getLog(){
		$log = NULL;
		if(empty($this->appid) && empty($this->uid)){
			return NULL;
		}
		$log['signedParams']['appid']=$this->appid;
		$log['signedParams']['uid']=$this->uid;
		$log['signedParams']['timestamp']=time();
		$log['stats']=$this->stats;
		$log = json_encode($log);
		return $log;
	}
}


?>
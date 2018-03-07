<?php
define('ALL', 0);
define('DEBUG', 100);
define('INFO', 200);
define('WARN', 300);
define('ERROR', 400);
define('FATAL', 500);
define('CRITICAL', 500);
define('OFF', 1000);
import('module.event.XEvent');
/**
 * XLoggingEvent
 * 
 * logging event
 * 
 * 日志事件，实现XEvent接口
 * 日志事件中携带以下信息：发布事件的类名、方法名、代码行数、事件等级、事件消息等
 * 
 * @author Tianwei
 * @package event 
 */
class XLoggingEvent implements XEvent{
	protected $class = null;
	protected $method = null;
	protected $line = null;
	protected $level = null;
	protected $message = null;
	protected $serviceName = NULL;
	protected $timestamp = null;
	protected $gameUserId = null;
	
	/**
	 * construct method
	 * 
	 * 构造函数
	 * 
	 * @param string $class
	 * @param string $method
	 * @param int $line
	 * @param int $level
	 * @param string $message
	 * @param string $serviceName
	 */
	public function __construct($class, $method, $line, $level, $message, $serviceName=NULL){
		if(is_object($class)){			
			$this->class = get_class($class);
		}else{
			$this->class = $class;
		}
		$this->method = $method;
		$this->line = $line;
		$this->level = $level;
		$this->message = $message;
		$this->serviceName = $serviceName;
		$this->timestamp = microtime(true);
		import('module.util.session.XSession');
		$httpSession = XSession::singleton()->getInstance();
		if($httpSession){
			$request = $httpSession->getRequest();
			if($request){
				$this->gameUserId = $request->getGameUserId();
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see XEvent::getSource()
	 */
	public function getSource(){
		return $this->class;
	}
	/**
	 * class getter
	 * 
	 * 获取class属性
	 * 
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
	/**
	 * method getter
	 * 
	 * 获取method属性
	 * 
	 * @return string
	 */
	public function getMethod(){
		return $this->method;
	}
	/**
	 * line getter
	 * 
	 * 获取line属性
	 * 
	 * @return int
	 */
	public function getLine(){
		return $this->line;
	}
	/**
	 * level getter
	 * 
	 * 获取level属性
	 * 
	 * @return int
	 */
	public function getLevel(){
		return $this->level;
	}
	/**
	 * get the string representation of level
	 * 
	 * 获取level属性的字符串描述形式
	 * 
	 * @return string
	 */
	public function getLevelInString(){
		switch($this->level){
			case DEBUG:
				return 'DEBUG';
			case INFO:
				return 'INFO';
			case WARN:
				return 'WARN';
			case ERROR:
				return 'ERROR';
			case CRITICAL:
				return 'CRITICAL';
			default:
				return 'DEBUG';				
		}
	}
	/**
	 * message getter
	 * 
	 * 获取message属性
	 * 
	 * @return string
	 */
	public function getMessage(){
		return $this->message;
	}
	/**
	 * serviceName getter
	 * 
	 * 获取serviceName属性
	 * 
	 * @return string
	 */
	public function getServiceName(){
		return $this->serviceName;
	}
	/**
	 * get the timestamp with microseconds of this event
	 * 
	 * 获取事件发布时的微秒级时间
	 * 
	 * @return string
	 */
	public function getTimestamp(){
		return $this->timestamp;
	}
	/**
	 * gameUserId getter
	 * 
	 * 获取游戏用户ID
	 * 
	 * @return string
	 */
	public function getGameUserId(){
		return $this->gameUserId;
	}
}
?>
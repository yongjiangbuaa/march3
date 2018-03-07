<?php
import('module.util.logger.layout.XLoggerLayout');
/**
 * XSimpleLoggerLayout
 * 
 * simple logger layout
 * 
 * 简单日志格式，实现XLoggerLayout
 * 
 * @author Tianwei
 * @package util.logger
 */
class XSimpleLoggerLayout implements XLoggerLayout{
	protected $pattern = "%d{y-m-d@H:i:s}\t%p\t%u\t%c\t%f:%l\t%m\t%ip\t%microtime%n";
	protected $event = null;
	/**
	 * construct method
	 * 
	 * 构造函数
	 * 
	 * @param array $params
	 */
	public function __construct($params = array()){
		if(isset($params['pattern'])){
			$this->pattern = $params['pattern'];
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see XLoggerLayout::format()
	 */
	public function format(XLoggingEvent $event){
		$this->event = $event;
		$message = preg_replace_callback("/%[\-\.\d]*[a-zA-Z]+(\\{.+\\})*/i", array($this, "_filter"), $this->pattern);
		import('module.util.logger.XExceptionEvent');
		if($event instanceof XExceptionEvent){
			$message .= $event->getException()->getTraceAsString().NEW_LINE;
			$data = $event->getData();
			if(is_array($data)){
				$data = json_encode($data);
			}
			$message .= $data.NEW_LINE;
		}
		return $message;
	}
	
	protected function _filter($data){
		if(!is_array($data)){
			return $data;
		}
		foreach($data as $value){
			preg_match("/%[\-\.\d]*([a-zA-Z]+)/i", $value, $match);
			switch($match[1]){
				case 'd':
					if(preg_match("/\\{(.+)\\}/i", $value, $date)){
						$microtime = $this->event->getTimestamp();
						$timeArr = explode('.', $microtime);
						return date($date[1], $timeArr[0]).'.'.$timeArr[1];
					}	
					return date('r', $this->event->getTimestamp());
				case 'p':
					return $this->event->getLevelInString();
				case 'u':
					return $this->event->getGameUserId();
				case 'c':
					return $this->event->getClass();	
				case 'f':
					return $this->event->getMethod();
				case 'l':
					return $this->event->getLine();
				case 'm':
					return $this->event->getMessage();	
				case 'ip':
					return $_SERVER['REMOTE_ADDR'];
				case 'n':
					return NEW_LINE;
				default:
					if(function_exists($match[1])){
						return $match[1]();
					}else{						
						return $value;
					}
			}
		}
	}
}
?>
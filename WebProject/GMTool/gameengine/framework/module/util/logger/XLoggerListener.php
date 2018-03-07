<?php
import('module.event.XEventListener');
import('module.util.logger.XLoggingEvent');
/**
 * XLoggerListener
 * 
 * xingcloud logging event listener
 * 
 * 行云日志事件监听器，实现XEventListener接口
 * 
 * @EventListener(onEvent="XLoggingEvent|XingCloudClosedEvent")
 * @author Tianwei
 * @package util.logger
 */
class XLoggerListener implements XEventListener{
	/**
	 * (non-PHPdoc)
	 * @see XEventListener::onEvent()
	 */
	public function onEvent(XEvent $event){
		if($event instanceof XLoggingEvent){
			return $this->log($event);
		}
		if($event instanceof XingCloudClosedEvent){
			return $this->close();
		}
	}
	
	protected function log(XLoggingEvent $event){
		$serviceName = $event->getServiceName();
		$type = 'services';
		if(preg_match('/^action\./i', $serviceName)){
			$serviceName = preg_replace('/^action\./i', '', $serviceName);
			$type = 'actions';
		}elseif(preg_match('/^change\./i', $serviceName)){
			$serviceName = preg_replace('/^change\./i', '', $serviceName);
			$type = 'changes';
		}
		$elapsedTime = 0;
		if($serviceName=='XingcloudSlowRequestLog'){
			$type = null;
		}
		$config = XConfig::singleton()->get('logger');
		$default = false;
		if(isset($config['default'])){
			$default = $config['default'];
		}
		if($type && !($event instanceof XExceptionEvent)){
			//check if service logging disabled
			if(isset($config[$type])
				&& isset($config[$type]['disabled'])
				&& is_array($config[$type]['disabled'])
				&& array_key_exists($serviceName, $config[$type]['disabled'])){
				return false;
			}
			//check if service logging enabled when all service disabled by default
			if(!$default){
				if(!isset($config[$type])
					|| !isset($config[$type]['enabled'])
					|| !is_array($config[$type]['enabled'])
					|| (is_array($config[$type]['enabled'])&&!array_key_exists($serviceName, $config[$type]['enabled'])))
				return true;
			}
		}
		$appenders = XConfig::singleton()->get("logger.appender");
		if(!is_array($appenders)){
			return false;
		}
		$level = $event->getLevel();
		$results = array();
		foreach($appenders as $key => $value){
			if(!isset($value['type'])
				|| !isset($value['threshold'])
				|| constant(strtoupper($value['threshold'])) > $level){
				continue;
			}
			$results[$key] = $value;
		}
		if(empty($results)){
			return false;
		}
		import('module.util.logger.XingCloudLogger');
		$logger =  XingCloudLogger::singleton();
		foreach($results as $key => $value){
			if($logger->hasAppender($key)){
				continue;
			}
			$className = 'X'.ucfirst($value['type']).'LoggerAppender';
			import('module.util.logger.appender.'.$className);	
			$appender = new $className($key, $value);
			$layoutClass = isset($value['layout']['class']) ? $value['layout']['class'] : 'XSimpleLoggerLayout';				
			import('module.util.logger.layout.'.$layoutClass);
			$layout = new $layoutClass(isset($value['layout']) ? $value['layout'] : array());
			$appender->setLayout($layout);
			if(isset($value['maxfilesize'])){
				$appender->setMaxfilesize($value['maxfilesize']);
			}
			if(isset($value['slowrequest'])){
				$appender->setSlowRequestThreshold($value['slowrequest']);
			}
			$logger->addAppender($appender);
		}
		$logger->log($event);
		return true;
	}
	
	protected function close(){
		import('module.util.logger.XingCloudLogger');
		XingCloudLogger::singleton()->close();
	}
}
?>
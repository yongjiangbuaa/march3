<?php
import('module.event.XEventListener');
import('module.event.XEventPublisher');
/**
 * XEventContext
 * 
 * event context class, publishing events
 * 
 * 事件场景类，发布事件
 * 
 * <b>目前只支持同步式的事件发布与监听，不支持异步事件处理</b>
 * 
 * @author Tianwei
 * @package event
 */
final class XEventContext implements XEventListener, XEventPublisher{
	private static $instance = null;
	private $listeners = array();
	private $eventListeners = array();
	private static $singletons = array();
	
	private function __construct(){
	}
	
	/** 
	 * XEventContext singleton method
	 * 
	 * 单例函数，返回XEventContext对象
	 * 
	 * @return XEventContext
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/** 
	 * add a listener in global queue
	 * 
	 * 在全局监听队列中加入一个监听者
	 * 
	 * @param string $listener 监听者类名
	 * @return XEventContext
	 */
	public function addListener($listener){
		$this->listeners[] = $listener;
		return $this;
	}
	
	/** 
	 * returns a listener object by its class name
	 * 
	 * 返回一个监听者
	 * 
	 * @param string $listener 监听者类名
	 * @return XEventListener
	 */	
	public function getListener($listener){
		if(!isset(self::$singletons[$listener])){
			self::$singletons[$listener] = new $listener();
		}
		return self::$singletons[$listener];
	}
	
	/** 
	 * add a listener in event queue
	 * 
	 * 在某个事件监听队列中加入一个监听者
	 * 
	 * @param string $listener 监听者类名
	 * @param string $event 事件类名
	 * @return XEventContext
	 */
	public function addListenerByEvent($listener, $event){
		if(!isset($this->eventListeners[$event])){
			$this->eventListeners[$event] = array();
		}
		$this->eventListeners[$event][] = $listener;
		return $this;
	}
	
	/** 
	 * add a listener in event queue by params
	 * 
	 * 根据参数在某个事件监听队列中加入一个监听者
	 * 
	 * @param string $listener 监听者类名
	 * @param array $params 参数
	 * @return XEventContext
	 */	
	public function addListenerByParams($listener, $params){
		if(!isset($params['onEvent'])){
			return $this->addListener($listener);
		}
		$onEvent = $params['onEvent'];
		$splits = explode('|', $onEvent);
		foreach($splits as $split){
			$this->addListenerByEvent($listener, trim($split, ' '));
		}
		return $this;
	}
		
	/** 
	 * returns a listener queue
	 * 
	 * 返回事件监听者队列
	 * 
	 * <b>如果$event为空，则返回全局监听者队列</b>
	 * 
	 * @param string $event 事件类名，可以为空
	 * @return XEventListener
	 */		
	public function listeners($event = null){
		if(is_null($event)){			
			return $this->listeners;
		}
		if(isset($this->eventListeners[$event])){
			return $this->eventListeners[$event];
		}
		return array();
	}
		
	/** 
	 * handle an event
	 * 
	 * 处理一个事件
	 * 
	 * @param XEvent $event 事件对象
	 */		
	public function onEvent(XEvent $event){
		import('module.event.XTypeMatchEvent');
		import('module.event.XTypeFilterEvent');
		$class = get_class($event);
		if($class instanceof XTypeMatchEvent){
			$type = $event->getType();
			foreach($this->eventListeners as $key => $value){
				if(preg_match($type, $key)){
					$this->_onEventByType($event, $key);
				}
			}
		}elseif($class instanceof XTypeFilterEvent){
			$type = $event->getType();
			if(isset($this->eventListeners[$type])){
				$this->_onEventByType($event, $type);
			}
		}else{
			foreach($this->eventListeners as $key => $value){
				if($event instanceof $key){					
					$this->_onEventByType($event, $key);
				}
			}
		}
		foreach($this->listeners as $listener){
			$this->getListener($listener)->onEvent($event);
		}
	}
	
	private function _onEventByType(XEvent $event, $type){
		foreach($this->eventListeners[$type] as $listener){
			$this->getListener($listener)->onEvent($event);
		}
	}
	
	/** 
	 * publish an event
	 * 
	 * 发布一个事件
	 * 
	 * @param XEvent $event 事件对象
	 */	
	public function publish(XEvent $event){
		$this->onEvent($event);
	}
}
?>
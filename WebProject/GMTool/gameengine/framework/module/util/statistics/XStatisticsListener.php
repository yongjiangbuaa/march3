<?php
import('module.event.XEventListener');
/**
 * XStatisticsListener
 * 
 * statistics event listener
 * 
 * 统计分析事件监听器
 * 
 * @EventListener(onEvent="ServiceFinishedEvent|ActionFinishedEvent|AuditChangeFinishedEvent")
 * @author Tianwei
 * @package util.statistics 
 */
class XStatisticsListener implements XEventListener{
	/**
	 * (non-PHPdoc)
	 * @see XEventListener::onEvent()
	 */
	public function onEvent(XEvent $event){
		import('util.config.XConfig');
		$config = XConfig::singleton()->get('statistics');
		if(!is_array($config)){
			return false;
		}
		$default = false;
		if(isset($config['default'])){
			$default = $config['default'];
		}
		if($event instanceof ServiceFinishedEvent){
			$request = $event->getSource();
			$serviceName = $request->getClassName().'.do'.ucfirst($request->getMethodName());
			//check if service statistics disabled
			if(isset($config['services'])
				&& isset($config['services']['disabled'])
				&& is_array($config['services']['disabled'])
				&& array_key_exists($serviceName, $config['services']['disabled'])){
				return false;
			}
			//check if service statistics enabled when all service disabled by default
			if(!$default){
				if(!isset($config['services'])
					|| !isset($config['services']['enabled'])
					|| !is_array($config['services']['enabled'])
					|| (is_array($config['services']['enabled'])&&!array_key_exists($serviceName, $config['services']['enabled'])))
				return true;
			}
			$userUid = $request->getGameUserId();
			import('service.user.UserFactory');
			$user = UserFactory::singleton()->get($userUid);
			if(empty($user)){
				return false;
			}
			$platformAddress = $user->getPlatformAddress();
			$platformArr = explode('_', $platformAddress);
			if(empty($platformArr) || count($platformArr)!=2){
				return false;
			}
			$platformUserUid = $platformArr[0];
			$platformUid = $platformArr[1];
			import('statistics.StatisticsManager');
			StatisticsManager::singleton()->addServiceLog($platformUid, $platformUserUid, isset($config['services']['enabled'][$serviceName]) ? $config['services']['enabled'][$serviceName] : $serviceName, $request->getParameters());
			return true;
		}
		if($event instanceof ActionFinishedEvent){
			$actionRequest = $event->getSource();
			$actionName = $actionRequest->getActionName();
			if(strpos($actionName, '.') === false){
				$actionName = ucfirst($actionName);
			}else{
				$pos = strrpos($actionName, '.') + 1;
				$actionName{$pos} = strtoupper($actionName{$pos});
			}
			//check if service statistics disabled
			if(isset($config['actions'])
				&& isset($config['actions']['disabled'])
				&& isset($config['actions']['disabled'][$actionName])){
				return false;
			}
			//check if service statistics enabled when all service disabled by default
			if(!$default){
				if(!isset($config['actions'])
					|| !isset($config['actions']['enabled'])
					|| !isset($config['actions']['enabled'][$actionName]))
				return true;
			}
			import('statistics.StatisticsManager');
			StatisticsManager::singleton()->addLog($event->getPlatformUID(), $event->getUserUID(), 'action', isset($config['actions']['enabled'][$actionName]) ? $config['actions']['enabled'][$actionName] : 'user.action.'.$actionName, json_encode($actionRequest->getParameters()));
			return true;
		}
		if($event instanceof AuditChangeFinishedEvent){
			$data = $event->getSource();
			if(!isset($data['name'])){
				return FALSE;
			}
			$changeName = $data['name'];
			if(strpos($changeName, '.') === false){
				$changeName = ucfirst($changeName);
			}else{
				$pos = strrpos($changeName, '.') + 1;
				$changeName{$pos} = strtoupper($changeName{$pos});
			}
			//check if service statistics disabled
			if(isset($config['changes'])
				&& isset($config['changes']['disabled'])
				&& isset($config['changes']['disabled'][$changeName])){
				return false;
			}
			//check if service statistics enabled when all service disabled by default
			if(!$default){
				if(!isset($config['changes'])
					|| !isset($config['changes']['enabled'])
					|| !isset($config['changes']['enabled'][$changeName]))
				return true;
			}
			import('statistics.StatisticsManager');
			StatisticsManager::singleton()->addLog($event->getPlatformUID(), $event->getUserUID(), 'change', isset($config['changes']['enabled'][$changeName]) ? $config['changes']['enabled'][$changeName] : 'user.change.'.$changeName, json_encode($data));
			return true;
		}
		return false;
	}
}
?>
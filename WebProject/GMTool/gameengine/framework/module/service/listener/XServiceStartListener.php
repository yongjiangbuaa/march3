<?php
import('module.event.XEventListener');
/**
 * XServiceStartListener
 * 
 * service start listener
 * 
 * 服务开始监听器，监听ServiceStartedEvent
 * 
 * @EventListener(onEvent="ServiceStartedEvent")
 * @package service
 * @author Wangqi
 */
class XServiceStartListener implements XEventListener{
	/**
	 * (non-PHPdoc)
	 * @see XEventListener::onEvent()
	 */
	public function onEvent(XEvent $event){
		$request = $event->getSource();
		$parameters = $request->getParameters();
		$classPath = $request->getClassPath();
		$classPath = substr($classPath, 1);
		if($classPath!='admin' && is_array($parameters) && array_key_exists('info', $parameters) && is_array($parameters['info'])){
			$gameUserId = null;
			$info = $parameters['info'];
			if(array_key_exists('gameUserId', $info) && !empty($info['gameUserId'])){
				$gameUserId = $info['gameUserId'];
			}elseif(array_key_exists('platformAppId', $info) && !empty($info['platformAppId']) && array_key_exists('platformUserId', $info) && !empty($info['platformUserId'])){
				$platformAddress = $request->getPlatformAddress();
				import('service.user.UserFactory');
				$platformUser = UserFactory::singleton()->getPlatformUser($platformAddress);
				if(is_object($platformUser)){
					$gameUserId = $platformUser->getUserUID();
				}
			}
			if(!empty($gameUserId)){
				import('service.user.UserFactory');
				$user = UserFactory::singleton()->get($gameUserId);
				if(is_object($user)){
					$userStatus = $user->getUserStatus();
					if(!empty($userStatus)){
						$freezeStatus = $userStatus->getFreezeStatus();
						$freezeStartTime = $userStatus->getFreezeStartTime();
						$freezeEndTime = $userStatus->getFreezeEndTime();
						$now = $_SERVER['REQUEST_TIME'];
						if ($userStatus->getFreezeStatus() && $now>=$freezeStartTime && $now<=$freezeEndTime){
							import('module.context.XException');
							throw new XException('user was freezed');
						}
					}
				}
			}
		}
		
	}
}
?>
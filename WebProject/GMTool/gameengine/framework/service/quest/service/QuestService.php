<?php
/**
 * QuestService
 * 
 * quest service
 * 
 * 任务服务
 * 
 * @Pointcut('protocol|auth')
 * @author Wangqi
 * @package quest
 */
class QuestService extends XAbstractService{
	/**
	 * <b>get user active quests</b>
	 * 
	 * <b>获取用户当前任务列表。</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGet(XServiceRequest $request){
		$user_uid = $request->getUserUID();
		if(!$user_uid){
			return XServiceResult::clientError('invalid param user_uid');
		}
		import('service.quest.ActiveQuestManager');
		$result = ActiveQuestManager::singleton()->queryActiveQuest($user_uid);
		if(empty($result)){
			return $this->_success(array('actived'=>array()));
		}
		return $this->_success($result);
	}

	/**
	 * <b>accept a quest</b>
	 * 
	 * <b>用户手动接受一项任务</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID，任务的ID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string	quest_uid 任务的ID
	 * @return XServiceResult
	 */
	public function doAccept(XServiceRequest $request){
		$user_uid = $request->getUserUID();
		if(!$user_uid){
			return XServiceResult::clientError('invalid param user_uid');
		}
		$quest_uid = $request->getParameter('quest_uid');
		if(!$quest_uid){
			return XServiceResult::clientError('invalid param quest_uid');
		}
		$success = FALSE;
		import('service.quest.ActiveQuestManager');
		$success = ActiveQuestManager::singleton()->acceptActiveQuest($user_uid, $quest_uid);
		if(!$success){
			return $this->_serverError('no such quest or add quest failed');
		}
		return $this->_success($quest_uid);
	}
	
	/**
	 * <b>sumbit a request to complete an active quest</b>
	 * 
	 * <b>用户手动提交请求完成一项任务</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID，任务的ID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string	quest_uid 任务的ID
	 * @return XServiceResult
	 */
	public function doSubmit(XServiceRequest $request){
		$user_uid = $request->getUserUID();
		if(!$user_uid){
			return XServiceResult::clientError('invalid param user_uid');
		}
		$quest_uid = $request->getParameter('quest_uid');
		if(!$quest_uid){
			return XServiceResult::clientError('invalid param quest_uid');
		}
		import('service.quest.ActiveQuestManager');
		$success = ActiveQuestManager::singleton()->completeActiveQuest($user_uid, $quest_uid);
		if(!$success){
			return $this->_serverError('quest not completed or already completed');
		}
		return $this->_success($quest_uid);
	}
	
}

?>
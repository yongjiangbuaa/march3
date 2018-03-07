<?php
/**
 * ActionService
 * 
 * action service
 * 
 * action服务
 * 
 * @Pointcut('protocol|auth')
 * @author Tianwei
 * @package action 
 */
class ActionService extends XAbstractService{
	/**
	 * <b>invoke a group of actions, return the execution result</b>
	 * 
	 * <b>调用一组用户Action的服务，返回该组Action执行结果</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string id 该组Action的序列ID
	 * @ServiceParam array info Action相关信息，包括platformAppId, platformUserId, gameUserId等
	 * @ServiceParam array data 该组Action的调用请求信息
	 * @return XMultiResult
	 */
	public function doExecute(XServiceRequest $request){
		import('service.action.XActionContext');
		$context = XActionContext::singleton();
		return $context->execute($request);
	}
}
?>
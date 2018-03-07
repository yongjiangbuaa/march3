<?php
/**
 * TutorialService
 * 
 * tutorial service
 * 
 * 新手引导服务
 * 
 * @Pointcut('protocol|auth')
 * @author Tianwei
 * @package tutorial
 */
class TutorialService extends XAbstractService{
	/**
	 * <b>get the tutorial infomation of a user</b>
	 * 
	 * <b>获取新手引导服务，返回指定引导名的引导信息。</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID和指定引导名。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string tutorial 引导名
	 * @return XServiceResult
	 */
	public function doGet(XServiceRequest $request){
		$tutorialName = $request->getParameter('tutorial');
		if(!$tutorialName){
			return XServiceResult::clientError('invalid param tutorial');
		}
		import('service.tutorial.TutorialFactory');
		$tutorial = TutorialFactory::get($request->getUserUID());
		$tutorialStep = $tutorial->getItem($tutorialName);
		if(!$tutorialStep){
			return $this->_success(array()); 
		}
		return $this->_success($tutorialStep);
	}
	
	/**
	 * <b>update a tutorial step</b>
	 * 
	 * <b>更新新手引导步骤的服务，更新指定引导名的引导到新的步骤。</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID，指定引导名，引导新步骤名和引导新步骤索引。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string tutorial 引导名
	 * @ServiceParam string name 引导新步骤名
	 * @ServiceParam string index 引导新步骤索引
	 * @return XServiceResult
	 */
	public function doStep(XServiceRequest $request){
		import('service.tutorial.TutorialFactory');
		$tutorial = TutorialFactory::get($request->getUserUID());
		$step = TutorialFactory::createStep($request);
		$tutorial->addItem($step);
		$tutorial->save();
		return $this->_success($tutorial);
	}
	
	/**
	 * <b>complete a tutorial</b>
	 * 
	 * <b>完成新手引导的服务，完成指定引导名的新手引导。</b>
	 * 
	 * <b>参数要提供用户在游戏中的UID，指定引导名。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string tutorial 引导名
	 * @return XServiceResult
	 */
	public function doComplete(XServiceRequest $request){
		import('service.tutorial.TutorialFactory');
		$tutorial = TutorialFactory::get($request->getUserUID());
		$tutorial->complete($request->getParameter('tutorial'));
		$tutorial->save();
		return $this->_success($tutorial);
	}
}
?>
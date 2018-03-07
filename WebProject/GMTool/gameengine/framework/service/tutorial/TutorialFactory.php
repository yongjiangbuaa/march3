<?php
/**
 * TutorialFactory
 * 
 * tutorial factory class
 * 
 * 新手引导工厂类
 * 
 * @author Tianwei
 * @package tutorial
 */
class TutorialFactory{
	/** 
	 * <b>query a tutorial by given uid</b>
	 * 
	 * <b>根据给定的uid对新手引导进行查询，并返回结果</b>
	 * 
	 * @return Tutorial
	 */
	public static function get($uid){
		import('service.tutorial.Tutorial');
		import('service.tutorial.TutorialStep');
		$tutorial = Tutorial::getWithUID($uid);
		if(!$tutorial){
			$tutorial = new Tutorial();
			$tutorial->set('uid', $uid);
			$tutorial->save();
		}
		return $tutorial;
	}

	/** 
	 * <b>create a tutorial step by the given tutorial name, step name and step index</b>
	 * 
	 * <b>根据给定的新手引导名、步骤名、步骤索引来创建一个新手引导步骤</b>
	 * 
	 * @return TutorialStep
	 */
	public static function createStep(XActionRequest $request){
		$tutorial = $request->getParameter('tutorial');
		$userUid = $request->getUserUID();
		import('util.mysql.XMysql');
		if(XMysql::singleton()->exist('tutorialstep', array('uid'=>$userUid,'tutorial'=>$tutorial)))
		{
			return;
		}
// 		import('service.tutorial.Tutorial');
		import('service.tutorial.TutorialStep');
		$step = new TutorialStep();
		$step->setTutorial($request->getParameter('tutorial'));
		$step->setUserUid($request->getUserUID());
		$step->setTime(time());
		$step->save();
		return $step;
	}
}
?>
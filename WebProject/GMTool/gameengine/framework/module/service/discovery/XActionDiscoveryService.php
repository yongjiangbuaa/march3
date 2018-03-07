<?php
import('module.service.XAbstractService');
/**
 * XActionDiscoveryService
 * 
 * discovery all the existing actions
 * 
 * action API发现服务，发现已有的action API
 * 
 * @author Wangqi
 * @Pointcut('auth|cache')
 * @package service
 */
class XActionDiscoveryService extends XAbstractService{
	/**
	 * <b>discovery all the actions under GAME_SERVICE_DIR, return the API list</b>
	 * 
	 * <b>action API发现服务，返回GAME_SERVICE_DIR目录下所有的action列表。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Cache(policy='application')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGetServices(XRequest $request){
		$apis = array();
		$this->getClasses($apis, GAME_SERVICE_DIR.__DS__.'action');
		return $this->_success($apis);
	}
	
	protected function getClasses(&$array, $dir, $suffix=''){
		if(!is_dir($dir)){
			return;
		}
		$classDir = new XFile($dir);
		$files = $classDir->listFiles();
		foreach($files as $file){
			$fileName = $file->getName();
			$path = $file->getPath();
			if($file->isFile()){
				if(!preg_match('/Action\.php$/i', $fileName)){
					continue;
				}
				$api = preg_replace('/\.php$/i', '', $fileName);
				$api = trim($suffix.'.'.$api, '.');
				$array[] = $api;
			}elseif($file->isDirectory()){
				$this->getClasses($array, $path, $suffix.'.'.$fileName);
			}
		}
		ksort($array);
	}
}
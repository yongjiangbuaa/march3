<?php
import('module.service.status.XStatusService');
/**
 * XAdminStatusService
 * 
 * admin resource file status service
 * 
 * admin资源文件状态服务
 * 
 * @author Tianwei
 * @Pointcut('auth')
 * @package service
 */
class XAdminStatusService extends XStatusService{
	/**
	 * <b>get the status of admin resource files under XINGCLOUD_SERVICE_DIR</b>
	 * 
	 * <b>获取XINGCLOUD_SERVICE_DIR目录下admin资源文件的状态</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='admin')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doStatus(XRequest $request){
		$root = XINGCLOUD_SERVICE_DIR;
		$apis = array();
		$this->getClasses($apis, $root);
		return $this->_success($apis);
	}
	
	/**
	 * <b>discovery all the admin services(*AdminService.php) under the given directory</b>
	 * 
	 * <b>递归检索给定目录下所有匹配*AdminService.php的文件</b>
	 * 
	 * @param array $array 传入数组的引用，用于存储检索结果
	 * @param string $dir 目标目录，会对该目录进行深度检索
	 * @param string $suffix 文件前缀(目录结构)，用.分割父目录与子目录，如action.lib, admin.service
	 */
	protected function getClasses(&$array, $dir, $suffix=''){
		if(!is_dir($dir)){
			return;
		}
		$classDir = new XFile($dir);
		$files = $classDir->listFiles();
		foreach($files as $file){
			$fileName = $file->getName();
			if(preg_match('/^\./i', $fileName)){
				continue;
			}
			$path = $file->getPath();
			if($file->isFile()){
				if(!preg_match('/AdminService\.php$/i', $fileName)){
					continue;
				}
				$api = preg_replace('/AdminService\.php$/i', '', $fileName);
				$api{0} = strtolower($api{0});
				$suffix = preg_replace('/\.+service$/i', '', $suffix);
				$api = trim($suffix.'.'.$api, '.');
				$methods = $this->getMethods($path, preg_replace('/\.php$/i', '', $fileName));
				if(is_array($methods) && count($methods) > 0){						
					$array[$api] = $methods;
				}
			}elseif($file->isDirectory()){
				$this->getClasses($array, $path, $suffix.'.'.$fileName);
			}
		}
		ksort($array);
	}
}
?>
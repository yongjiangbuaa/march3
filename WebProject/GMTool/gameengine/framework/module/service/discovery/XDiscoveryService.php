<?php
import('module.service.XAbstractService');
import('module.service.XServiceAnnotation');
import('util.io.XFile');
/**
 * XDiscoveryService
 * 
 * discovery all the existing services, except the admin API
 * 
 * API发现服务，返回API列表，不包括admin API。
 * 
 * @author Tianwei
 * @Pointcut('auth|cache')
 * @package service
 */
class XDiscoveryService extends XAbstractService{
	/**
	 * <b>discovery all the services under XINGCLOUD_SERVICE_DIR, except admin services, return the API list</b>
	 * 
	 * <b>admin API发现服务，返回XINGCLOUD_SERVICE_DIR目录下所有的 API列表，不包括admin API。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache(policy='application')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGetServices(XRequest $request){
		$apis = array();
		$this->getClasses($apis, XINGCLOUD_SERVICE_DIR);
		$this->getClasses($apis, GAME_SERVICE_DIR);
		return $this->_success($apis);
	}
	
	/**
	 * <b>discovery all the services(*Service.php) under the given directory, bypass the admin services(*AdminService.php)</b>
	 * 
	 * <b>递归检索给定目录下所有匹配*Service.php的文件，检索时跳过admin service(*AdminService.php)</b>
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
			if(preg_match('/^\./i', $fileName)
				|| preg_match('/^admin/i', $fileName)){
				continue;
			}
			$path = $file->getPath();
			if($file->isFile()){
				if(preg_match('/AdminService\.php$/i', $fileName)
					|| !preg_match('/Service\.php$/i', $fileName)){
					continue;
				}
				$api = preg_replace('/Service\.php$/i', '', $fileName);
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
	
	/**
	 * <b>get the method names of a given class</b>
	 * 
	 * <b>获取给定类的所有方法名</b>
	 * 
	 * <b>会对方法名进行过滤和重命名，方法的作用域必须是public，名称为doSomthing（S为大写字母），重命名后为somthing（s为小写字母）</b>
	 * 
	 * @param string $path 类所在的完整路径，如果类不存在，返回空数组
	 * @param string $className 类名
	 * @return array
	 */
	protected function getMethods($path, $className){
		$included = include_once($path);
		if($included === FALSE){
			return array();
		}
		$results = array();
		$class = new ReflectionClass($className);
		$methods = $class->getMethods();
		foreach($methods as $method){
			if($method->isPublic()
				&& preg_match('/^do[A-Z]\w+/', $method->getName())){
				$serviceName = preg_replace('/^do([A-Z])/ie', "strtolower('\\1')", $method->getName());
				$result = XServiceAnnotation::singleton($className)->service($serviceName);
				if(!$result){
					$result = array();
				}
				$result['serviceName'] = $serviceName;
				$results[$serviceName] = $result;
			}
		}
		ksort($results);
		return $results;
	}
}
?>
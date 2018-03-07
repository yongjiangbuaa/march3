<?php
import('module.mvc.route.XRouteParser');
/**
 * XRESTRouteParser
 * 
 * rest route parser, implements the XRouteParser interface
 * 
 * rest路由解析器，实现XRouteParser接口
 * 
 * @final
 * @author Tianwei
 * @package mvc
 */
final class XRESTRouteParser implements XRouteParser{
	/**
	* parse the route information and params
	*
	* 解析路由信息及其参数，加载controller
	*
	* @param string $dir
	* @param string $route
	* @param array $params
	*/
	public function parse($dir, $route, $params = array()){		
		$newRoute = urldecode(preg_replace("/\/(\d+)[\?\/]*/i", '', $route));
		$newParams = $params;
		preg_match("/\/(\d+)[\?\/]*/i", $route, $id);
		if(isset($id[1])){
			$newParams['id'] = $id[1];
		}
		$names = preg_split('/\/+/i', $newRoute, 0, PREG_SPLIT_NO_EMPTY);
		$count = count($names);
		//first match: $route = controller + action		
		if($count >= 2){
			$path = implode('/', array_slice($names, 0, $count-2));
			if($this->_controller('/'.$path.'/'.$names[$count-2], $dir.'/'.$path, $names[$count-2], $names[$count-1], $newParams)){
				return true;
			}
		}
		//second match: $route = controller, action = index
		if($count >= 1){
			$path = implode('/', array_slice($names, 0, $count-1));
			if($this->_controller('/'.$path.'/'.$names[$count-1], $dir.'/'.$path, $names[$count-1], 'index', $newParams)){
				return true;
			}
		}
		//third match: $route/index.php = controller, action = index
		if($this->_controller('/'.$newRoute.'/index', $dir.'/'.$newRoute, 'index', 'index', $newParams)){
			return true;
		}
		return false;
	}
	
	private function _controller($route, $path, $controllerName, $actionName, $params = array()){
		$className = x_class_name($controllerName).'Controller';
		$newActionName = 'do'.x_name_to_java($actionName);
		import('module.mvc.route.XRouter');
		return XRouter::singleton()->loadController($route, $path.'/'.$className.'.php', $className, $newActionName, $params);
	}
}
?>
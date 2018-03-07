<?php
if(!defined("AMFPHP_BASE")){
	define("AMFPHP_BASE", XINGCLOUD_PLUGIN_DIR.'/amfphp/');
}
include_once(AMFPHP_BASE . "shared/util/MethodTable.php");
/**
 * A built-in amfphp service that allows introspection into services and their methods.
 * Remove from production servers
 */
class DiscoveryService
{
	/**
	 * Get the list of services
	 * @returns An array of array ready to be bound to a Tree
	 */
	function getServices()
	{
		$this->_omit = array();
		$this->_path = $GLOBALS['amfphp']['servicePath'];
		$services = array_merge($this->_listServices(XINGCLOUD_SERVICE_DIR.__DS__), $this->_listServices(GAME_SERVICE_DIR.__DS__));
		//Now sort on key
		ksort($services);
		$out = array();
		foreach($services as $key => $val)
		{
			if($key == "zzz_default")
			{
				foreach($val as $key2 => $val2)
				{
					$out[] = array("label" => $val2[0], "data" => $val2[1]);
				}
			}
			else
			{
				$children = array();
				foreach($val as $key2 => $val2)
				{
					$children[] = array("label" => $val2[0], "data" => $val2[1]);
				}
				$out[] = array("label" => $key, "children" => $children, "open" => true);
			}
		}
		return $out;
	}
	
	/**
	 * Describe a service and all its methods
	 * @param $data An object containing 'label' and 'data' keys
	 */
	function describeService($data)
	{
		$className = $data['label'];
		$className = x_name_to_java($className);
		if(!preg_match('/Service$/i', $className)){
			$className .= 'Service';
		}
		//Sanitize path
		$path = str_replace('..', '', $data['data']);
		//Generate the method table from this info
		$this->_path = $GLOBALS['amfphp']['servicePath'];
		
		foreach(array(
			XINGCLOUD_SERVICE_DIR,
			GAME_SERVICE_DIR,
		) as $basePath){
			foreach(array(
				'service'.__DS__,
				'',
			) as $servicePrefix){
				$file = $basePath . __DS__ . $path . $servicePrefix . $className . '.php';
				if(file_exists($file)){
					include_once($file);
					$methodTable = $this->createMethodTable($className);
					$classComment = $className;
					return array($methodTable, $classComment);
				}
			}
		}
		$file = $this->_path . $path . $className . '.php';
		if(file_exists($file)){
			include_once($file);
			$methodTable = $this->createMethodTable($className);
			$classComment = $className;
			return array($methodTable, $classComment);
		}
		return array();
	}
	
	function _listServices($dir = "", $suffix = "")
	{
		if($dir == "")
		{
			$dir = $this->_path;
		}
		$services = array();
		if(in_array($suffix, $this->_omit)){ return; }
		import('util.io.XFile');
		$classDir = new XFile($dir.$suffix);
		$files = $classDir->listFiles();
		foreach ($files as $file){
			$fileName = $file->getName();
			if(preg_match('/^\./i', $fileName)
				|| preg_match('/^admin/i', $fileName)){
				continue;
			}
			$path = $file->getPath();
			if($file->isFile()){
				if(strpos($path, '.methodTable') !== FALSE
					|| preg_match('/AdminService\.php$/i', $fileName)
					|| !preg_match('/Service\.php$/i', $fileName)){
					continue;
				}
				$index = strrpos($fileName, '.');
				$before = substr($fileName, 0, $index);
				$after = substr($fileName, $index + 1);
				
				if($after == 'php')
				{
					$loc = "zzz_default";
					if($suffix != "")
					{
						$loc = str_replace(DIRECTORY_SEPARATOR,'.', substr($suffix, 0, -1));
					}
					
					$loc = preg_replace('/\.+service$/i', '', $loc);
					
					if($services[$loc] == NULL)
					{
						$services[$loc] = array();
					}
					$serviceName = preg_replace('/\Service$/i', '', $before);
					$serviceName{0} = strtolower($serviceName{0});
					$servicePath = preg_replace('/[\\/\\\]+service/i', '', $suffix);
					$services[$loc][] = array($serviceName, $servicePath);
					//array_push($this->_classes, $before);
				}
			}elseif($file->isDirectory()){
				$insideDir = $this->_listServices($dir, $suffix . $fileName . DIRECTORY_SEPARATOR);
				if(is_array($insideDir))
				{
					$services = $services + $insideDir;
				}
			}
		}
		return $services;
	}
	
	private function createMethodTable($className){
		import('module.service.XServiceAnnotation');
		$annotations =  XServiceAnnotation::singleton($className)->serviceAnnotations();
		$methodTable = array();
		foreach ($annotations as $methodName => $annotation){
			$table = array();
			$table['description'] = $annotation['description'];
			$params = $annotation['param'];
			$amfParams = array();
			foreach ($params as $key => $param){
				$amfParam = implode(' ' , $param);
				$amfParams[$key] = $amfParam;
			}
			$table['arguments'] = $amfParams;
			//$table['access'] = 'private';
			$methodTable[$methodName] = $table;
		}
		return $methodTable;
	}
	
}
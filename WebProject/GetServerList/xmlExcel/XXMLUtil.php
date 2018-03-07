<?php
/**
* XXMLUtil
*
* xml utility
*
* xml解析工具，用于xml和array之间的转换
*
* @author Wangqi
* @package util.xml
*/
class XXMLUtil{
	private static $instance = null;
	private $doms = array();
	
	/**
	 * @return XXMLUtil
	 */
	public static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * parse a xml file
	 * @param String $file the file to be parse
	 * @param String $xpath the XPATH of the query
	 * @param Boole $returnDom return a SimpleXML DOM or an array
	 */
	public function parseFile($file, $xpath, $returnDom=FALSE){
		$dom = $this->dom($file);
		if(empty($dom)){
			return NULL;
		}
		$nodes = $dom->xpath($xpath);
		if(empty($nodes) || !is_array($nodes)){
			return NULL;
		}
		if($returnDom){
			return $nodes;
		}
		$result = $this->simpleXMLToArray($nodes);
		return $result;
	}
	
	/**
	 * parse a xml file from root node
	 * @param String $file the file to be parse
	 * @param Boole $returnDom return a SimpleXML DOM or an array
	 */
	public function parseRoot($file, $returnDom=FALSE){
		$root = '';
		$dom = $this->dom($file);
		$namespace = key($dom->getNamespaces());
		$root = '/'.(empty($namespace)?$dom->getName():($namespace.':'.$dom->getName()));
		$result = $this->parseFile($file, $root);
		return $result[0];
	}
	
	/**
	 * convert an array to xml
	 * @param array $array the array to be converted
	 * @param string $root root name of the xml
	 * @param string $rootNamespace namespace of the root node
	 * @param string $rootNamespaceUri uri of root namespace
	 */
	public function arrayToXML($array, $root='', $rootNamespace='', $rootNamespaceUri=''){
		if(empty($array) || count($array)==0 || empty($root)){
			return '<?xml version="1.0" encoding="utf-8"?>';
		}
		$dom = new DOMDocument('1.0', 'utf-8');
		if(empty($rootNamespace) || empty($rootNamespaceUri)){
			$rootNode = $dom->createElement($root);
		}else{
			$rootNode = $dom->createElementNS($rootNamespaceUri, $rootNamespace.':'.$root);
		}
		$rootNode = $this->constructElement($dom, $rootNode, $array);
		$dom->appendChild($rootNode);
		return $dom->saveXML();
	}
	
	/**
	 * convert a xml string to array
	 * @param string $xml
	 * @param string $root
	 * @param string $namespace
	 */
	public function xmlToArray($xml, $root, $namespace=NULL){
		$array = array();
		$dom = simplexml_load_string($xml);
		$xpath = '/';
		if($namespace){
			$xpath .= $namespace.':';
		}
		$rootDom = $dom->xpath($xpath.$root);
		if(empty($rootDom) || count($rootDom)==0 || empty($rootDom[0])){
			return array();
		}
		$array = $this->simpleXMLToArray($rootDom[0]);
		
		return $array;
	}
	
	/**
	 * load the specified xml file
	 * @param string $file
	 */
	protected function dom($file){
		if(!isset($this->doms[$file])){
			$this->doms[$file] = simplexml_load_file($file);
		}
		return $this->doms[$file];
	}
	
	private function simpleXMLToArray($node){
		if(empty($node)){
			return array();
		}
		if(count($node)==0){
			$result = '';
			$attrs = $node->attributes();
			$result = strval($node);
			$result = trim($result);
			return $result;
		}
		$array = array();
		foreach ($node as $key => $value){
			if(!array_key_exists($key, $array)){
				$array[$key] = array();
			}			
			if(empty($value)){
				$array[$key] = null;
			}else if(is_object($value) && is_a($value, 'SimpleXMLElement')){
				$array[$key] = $this->elementToArray($value);
			}else{
				$array[$key] = $this->simpleXMLToArray($value);
			}
		}
		return $array;
	}
	
	private function elementToArray($element){
		if(is_array($element)){
			return $this->elementsToArray($element);
		}
		$values = get_object_vars($element);
		if(!is_array($values)){
			return $values;
		}
		if(empty($values)){
			return null;
		}
		$array = array();
		foreach($values as $key => $value){
			if(is_object($value) && is_a($value, 'SimpleXMLElement')){
				$array[$key] = $this->elementToArray($value);
				continue;
			}
			if(is_array($value)){
				$array[$key] = $this->elementsToArray($value);
				continue;
			}
			$array[$key] = $value;
		}
		return $array;
	}
	
	private function elementsToArray($elements){
		$array = array();
		foreach($elements as $key => $value){
			if(is_object($value) && is_a($value, 'SimpleXMLElement')){
				$array[$key] = $this->elementToArray($value);
				continue;
			}
			if(is_array($value)){
				$array[$key] = $this->elementsToArray($value);
				continue;
			}
			$array[$key] = $value;
		}
		return $array;
	}
	
	private function constructElement($dom, $rootNode, $input){
		foreach ($input as $key=>$value){
			if(is_int($key)){
				$key = gettype($value);
			}
			if(is_array($value)){
				$element = $this->constructElement($dom, $dom->createElement($key), $value);
			}else{
				$element = $dom->createElement($key, $value);
			}
			$rootNode->appendChild($element);
		}
		return $rootNode;
	}
}
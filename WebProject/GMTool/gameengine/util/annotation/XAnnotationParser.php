<?php
/**
 * XAnnotationParser
 * 
 * Annotation Parser Class
 * 
 * 解析类注释信息的类
 * 
 * @author Tianwei
 * @package util.annotation
 */
class XAnnotationParser{
	/** 
	 * parse annotation from comments
	 * 
	 * 在指定的注释块中解析annotation
	 * 
	 * @param string $comment 注释块
	 * @return array
	 */	
	public function parse($comment){
		$annotations = array();
		$lines = explode("\n", $comment);
		foreach($lines as $line){
			if(!strpos($line, '@')){
				continue;
			}
			$match = array();
			if(strpos($line, '(')){
				preg_match("/@(\w+)\s*\(\s*[\"']\s*([^\"']+)\s*[\"']\s*\)\s*$/i", $line, $match);
				if(isset($match[1]) && isset($match[2])){				
					x_array_set($annotations, $match[1], $this->_trimValue($match[2]), true);
					continue;
				}
			}
			preg_match("/@(\w+)\s*(\((.+)\s*\))*\s*$/i", $line, $match);
			if(!isset($match[1])){
				continue;
			}
			if(!isset($match[3])){				
				x_array_set($annotations, $match[1], true, true);
				continue;
			}
			$annotations[$match[1]] = array();
			$props = array();
			if(preg_match_all("/(\w+)\s*=\s*\(([^\(\)]+)\)/i", $match[3], $array)){
				foreach($array[1] as $key => $value){
					$annotations[$match[1]][$array[1][$key]] = array();
					$this->_parseNode($annotations[$match[1]][$array[1][$key]], $array[2][$key]);
					$node = str_replace("'", "\'", str_replace("\"", "\\\"", $array[2][$key]));
					$match[3] = preg_replace("/{$array[1][$key]}\s*=\s*\({$node}\)/i", "", $match[3]);
				}
			}
			$props = preg_split("/\s*,\s*/i", $match[3]);
			foreach($props as $prop){
				$this->_parseNode($annotations[$match[1]], $prop);
			}
		}
		return $annotations;
	}
	
	private function _parseNode(array &$array, $node){
		if(preg_match("/\s*,\s*/i", $node)){
			$props = preg_split("/\s*,\s*/i", $node);
			foreach($props as $prop){
				$this->_parseNode($array, $prop);
			}
			return $array;
		}
		if(preg_match("/^\s*([\w\d\-\_]+)\s*$/i", $node, $match)){
			x_array_set($array, $match[1], true, true);
		}elseif(preg_match("/^\s*([\w\d\-\_]+)\s*=\s*['\"]*(.+)['\"]*\s*$/i", $node, $match)){
			x_array_set($array, $match[1], $this->_trimValue($match[2]), true);
		}elseif(preg_match("/^\s*([\w\d\-\_]+)\s*=\s*\((.+)\)$/i", $node, $match)){
			$array[$match[1]] = array();
			if(isset($match[2])){
				$this->_parseNode($array[$match[1]], $match[2]);
			}
		}
		return $array;
	}
	
	private function _trimValue($_value){
		$value = trim($_value, "'\"");
		if(in_array($value, array('TRUE', 'true'))){
			return true;
		}
		if(in_array($value, array('FALSE', 'false'))){
			return false;
		}
		return $value;
	}
}
?>
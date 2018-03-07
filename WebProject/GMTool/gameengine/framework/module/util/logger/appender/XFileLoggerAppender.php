<?php
import('module.util.logger.appender.XAbstractLoggerAppender');
/**
 * XFileLoggerAppender
 * 
 * file logger appender
 * 
 * 文件日志添加器，继承XAbstractLoggerAppender
 * 
 * @author Tianwei
 * @package util.logger
 */
class XFileLoggerAppender extends XAbstractLoggerAppender{
	protected $file = null;
	
	/**
	 * construct method
	 * 
	 * 构造函数，须指定日志添加器的名称、参数等
	 * 
	 * @param string $name
	 * @param array $params
	 */
	public function __construct($name, $params = array()){
		parent::__construct($name, $params);
		$this->target = $this->getFilePath($params);
	}
	
	
	/**
	 * get log file path
	 * 
	 * 获取日志文件路径
	 * 
	 * @return string
	 */	
	protected function getFilePath($params = array()){
		if(isset($params['target'])){
			return $params['target'];
		}
		return GAME_LOG_DIR.__DS__.$this->name.'.log';
	}
	
	protected function output($message) {
		error_log($message, 3, $this->target);
		$this->checkFileSize();
	}
	
	protected function checkFileSize(){
		$filesize = filesize($this->target)/1024;
		if($this->getMaxfilesize()>0 && $filesize>=$this->getMaxfilesize()){
			$maxSuffix = $this->getMaxSuffix();
			rename($this->target, $this->target.'.'.($maxSuffix+1));
		}
	}
	
	protected function getMaxSuffix(){
		import('util.io.XFile');
		$dir = new XFile(dirname($this->target));
		$basename = basename($this->target);
		$suffix = 0;
		foreach ($dir->listFiles() as $file){
			$filename = $file->getName();
			$pos = strpos($filename, $basename);
			if($pos===false){
				continue;
			}
			if($filename==$basename){
				continue;
			}
			$pos = strrpos($filename, '.');
			$file_suffix = intval(substr($filename, $pos+1));
			if($file_suffix>$suffix){
				$suffix = $file_suffix;
			}
		}
		return $suffix;
	}
}
?>
<?php
/**
 * XFileAppender
 * 
 * file appender Class
 * 
 * 文件续写类，在指定文件末尾处续写内容
 * 
 * @author Tianwei
 * @final
 * @package util.io
 */
final class XFileAppender{
	protected $file = null;
	protected $handler = null;
	
	/**
	 * construct method
	 * 
	 * 构造函数，传入参数为指定文件的XFile实例
	 * 
	 * @param XFile $file
	 */
	public function __construct(XFile $file){
		$this->file = $file;
		$this->handler = @fopen($file->getPath(), 'a+');
		if($this->handler
			&& !@flock($this->handler, LOCK_EX)){
			@fclose($this->handler);
			$this->handler = null;
		}
	}
	/**
	 * append content in the end of the file
	 * 
	 * 在文件末尾处续写内容
	 * 
	 * @param string $content
	 * @return boolean
	 */
	public function append($content){
		if(!$this->ready()){
			return false;
		}
		return @fwrite($this->handler, $content);			
	}
	/**
	 * close the file handler
	 * 
	 * 关闭文件handler
	 */
	public function close(){
		if($this->ready()){
			@flock($this->handler, LOCK_UN);
			@fclose($this->handler);
			$this->handler = null;
		}
	}
	/**
	 * check whether the file handler is ready
	 * 
	 * 检查文件handler是否初始化
	 * 
	 * @return boolean
	 */
	public function ready(){
		return !is_null($this->handler) && $this->handler !== false;
	}
}
?>
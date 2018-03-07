<?php
/**
 * XFileWriter
 * 
 * file writer Class
 * 
 * 文件写入类，写入文件内容
 * 
 * @author Tianwei
 * @final
 * @package util.io
 */
final class XFileWriter{
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
		$this->file->create();
		$this->handler = @fopen($file->getPath(), 'w+');
		if($this->handler
			&& !@flock($this->handler, LOCK_EX)){
			@fclose($this->handler);
			$this->handler = null;
		}
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
	/**
	 * write specified content in spedified position of the file
	 * 
	 * 向文件中指定位置写入指定内容
	 * 
	 * @param string $content
	 * @param int $start
	 * @param int $length
	 */
	public function write($content, $start = NULL, $length = NULL){
		if(!$this->ready()){
			return false;
		}
		if(!is_null($start)){
			$this->setPosition($start);
		}		
		if(!is_null($length)){
			return @fwrite($this->handler, $content, $length);
		}
		return @fwrite($this->handler, $content);
	}
	/**
	 * Returns the current position of the file read/write pointer
	 * 
	 * 返回当前文件指针的位置
	 * 
	 * @return int
	 */
	public function position(){
		if(!$this->ready()){
			return false;
		}
		return ftell($this->handler);
	}
	/**
	 * set the position of the file pointer
	 * 
	 * 设置文件指针的位置
	 * 
	 * @param int $position
	 * @return int
	 */
	public function setPosition($position){
		if(!$this->ready()){
			return false;
		}
		return @fseek($this->handler, $position, SEEK_SET);
	}
}
?>
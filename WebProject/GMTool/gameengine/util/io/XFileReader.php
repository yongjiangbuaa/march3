<?php
/**
 * XFileReader
 * 
 * file reader Class
 * 
 * 文件读取类，读取指定文件内容
 * 
 * @author Tianwei
 * @final
 * @package util.io
 */
final class XFileReader{
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
		$this->handler = @fopen($file->getPath(), 'r');
		if($this->handler
			&& !@flock($this->handler, LOCK_SH)){
			@fclose($this->handler);
			$this->handler = null;
		}
		register_shutdown_function(array($this, 'close'));
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
	 * read content by start position and length
	 * 
	 * 根据起始位置和长度读取文件内容
	 * 
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public function read($start, $length){
		if(!$this->ready()){
			return null;
		}
		$position = $this->position();
		$this->setPosition($start);
		$content = fread($this->handler, $length);
		$this->setPosition($position);
		return $content;
	}
	/**
	 * read one byte from the file
	 * 
	 * 从文件中读取一个byte的内容
	 * 
	 * @return string
	 */
	public function readByte(){
		if(!$this->ready()){
			return null;
		}
		return fread($this->handler, 1);
	}
	/**
	 * read one char from the file
	 * 
	 * 从文件中读取一个char的内容
	 * 
	 * @return string
	 */
	public function readChar(){
		if(!$this->ready()){
			return null;
		}
		return fgetc($this->handler);
	}
	/**
	 * read one line from the file
	 * 
	 * 从文件中读取一行内容
	 * 
	 * @return string
	 */
	public function readLine(){
		if(!$this->ready()){
			return null;
		}
		return fgets($this->handler);
	}
	/**
	 * read all contents from the file
	 * 
	 * 从文件中读取所有内容
	 * 
	 * @return string
	 */
	public function readAll(){
		if(!$this->ready()){
			return null;
		}
		return file_get_contents($this->file->getPath());
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
	 * Rewind the position of a file pointer
	 * 
	 * 重置文件读取指针
	 * 
	 * @return boolean
	 */
	public function reset(){
		if(!$this->ready()){
			return false;
		}
		return @rewind($this->handler);
	}
	/**
	 * skip a specified length of content of the file
	 * 
	 * 重设文件指针位置，跳过指定长度的内容
	 * 
	 * @return boolean
	 */
	public function skip($length){
		if(!$this->ready()){
			return false;
		}
		return @fseek($this->handler, $length, SEEK_CUR);
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
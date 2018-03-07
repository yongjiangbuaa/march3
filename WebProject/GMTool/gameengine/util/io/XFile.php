<?php
/**
 * XFile
 * 
 * file Manager Class
 * 
 * 文件管理类，用于操作系统中文件、文件夹的操作
 * 
 * @author Tianwei
 * @final
 * @package util.io
 */
final class XFile{
	private $filename = null;
	
	/**
	 * construct method
	 * 
	 * 构造方法，传入参数为需要操作的文件、文件夹的完整路径名称
	 * 
	 * @param string $filename
	 */
	public function __construct($filename){
		$this->filename = $filename;
	}
	/**
	 * check whether the file is readable
	 * 
	 * 查看文件是否可读
	 * 
	 * @return boolean
	 */
	public function canRead(){
		return is_readable($this->filename);
	}
	/**
	 * check whether the file is writeable
	 * 
	 * 查看文件是否可写
	 * 
	 * @return boolean
	 */
	public function canWrite(){
		return is_writeable($this->filename);
	}
	/**
	 * copy a file to a new destination
	 * 
	 * 将一个指定文件拷贝到指定目的地
	 * 
	 * @param string $from
	 * @param string $to
	 * @param boolean $replace
	 */
	public static function copy($from, $to, $replace = false){
		if(!is_object($from)){
			$from = new XFile($from);
		}
		if(!is_object($to)){
			$to = new XFile($to);
		}
		$from->copyTo($to, $replace);
	}
	/**
	 * copy the current file to a specified destination
	 * 
	 * 将本文件拷贝到指定目的地
	 * 
	 * @param XFile $to
	 * @param boolean $replace
	 */
	public function copyTo(XFile $to, $replace = false){
		if(!$this->exists()){
			return false;
		}
		if($this->isFile()){
			if(!$replace && $to->exists()){
				return false;
			}
			@copy($this->getPath(), $to->getPath());
			return true;
		}
		if(!$this->isDirectory()){
			return false;
		}
		$to->mkdir();
		$files = $this->listFiles();
		foreach($files as $file){
			XFile::copy($file, $to->getPath().'/'.$file->getName(), $replace);
		}
	}
	/**
	 * create the file
	 * 
	 * 在操作系统中创建当前文件
	 */
	public function create(){
		if($this->exists()){
			return true;
		}
		if(!is_dir(dirname($this->filename))){
			@mkdir(dirname($this->filename), 0777, true);
		}
		$file = @fopen($this->filename, "w+");
		if($file){
			@fclose($file);
			return true;
		}	
		return false;
	}
	/**
	 * delete the file
	 * 
	 * 在操作系统中删除当前文件
	 */
	public function delete(){
		if($this->isFile()){
			if(!@unlink($this->filename)
				&& $this->exists()){
				return false;
			}
			return true;
		}
		if(!$this->isDirectory()){
			return false;
		}
		$children = $this->listFiles();
		foreach($children as $child){
			$child->delete();
		}
		if(!@rmdir($this->filename)
			&& $this->exists()){
			return false;
		}
		return true;
	}
	/**
	 * register delete() method as shundown function
	 * 
	 * 将delete()方法注册为shutdown function，请求结束后调用delete()方法删除本文件
	 */
	public function deleteOnExit(){
		register_shutdown_function(array($this, 'delete'));
	}
	/**
	 * check whether the file exists
	 * 
	 * 检测当前文件是否存在
	 * 
	 * @return boolean
	 */
	public function exists(){
		return file_exists($this->filename);	
	}
	/**
	 * get the base name of the file
	 * 
	 * 获取当前文件的基础名称，不包括路径信息
	 * 
	 * @return string
	 */
	public function getName(){
		return basename($this->filename);
	}
	/**
	 * get parent directory's path of the file
	 * 
	 * 获取当前文件父目录路径信息
	 * 
	 * @return string
	 */
	public function getParent(){
		return dirname($this->filename);
	}
	/**
	 * get the XFile instance of parent directory's path
	 * 
	 * 获取当前文件父目录路径的XFile实例
	 * 
	 * @return XFile
	 */
	public function getParentFile(){
		return new XFile(dirname($this->filename));
	}
	/**
	 * get the full path name of the file
	 * 
	 * 获取当前文件的完整路径名
	 * 
	 * @return string
	 */
	public function getPath(){
		return $this->filename;
	}
	/**
	 * check whether the specified path is a directory
	 * 
	 * 检测指定的文件路径是否是目录
	 * 
	 * @return boolean
	 */
	public function isDirectory(){
		return is_dir($this->filename);
	}
	/**
	 * check whether the specified path is a file
	 * 
	 * 检测指定的文件路径是否是文件
	 * 
	 * @return boolean
	 */
	public function isFile(){
		return is_file($this->filename);
	}
	/**
	 * get the last modified time
	 * 
	 * 获取最后修改时间戳
	 * 
	 * @return int
	 */
	public function lastModified(){
		return filemtime($this->filename);
	}
	/**
	 * get the size of the file
	 * 
	 * 获取文件大小
	 * 
	 * @return int
	 */
	public function length(){
		return filesize($this->filename);
	}
	/**
	 * if the specified path is a directory, list all the files in this directory
	 * 
	 * 如果指定路径是目录，列出该目录下所有的文件名
	 * 
	 * @return array
	 */
	public function listFiles(){
		if(!$this->isDirectory()){
			return array();
		}
		$handler = @opendir($this->filename);
		if(!$handler){
			return array();
		}
		$files = array();
		while($file = @readdir($handler)){
			if($file{0} == '.'){
				continue;
			}
			$files[] = new XFile($this->filename.__DS__.$file);
		}
	    @closedir($handler);	
	    return $files;
	}
	/**
	 * make a new directory in the specified path
	 * 
	 * 在指定路径创建文件夹
	 * 
	 * @return boolean
	 */
	public function mkdir(){
		if($this->exists()){
			return true;
		}
		if(!@mkdir($this->filename, 0777, true)
			&& !$this->exists()){
			return false;
		}
		return true;
	}
	/**
	 * rename the file
	 * 
	 * 重命名文件
	 * 
	 * @param string $newName
	 */
	public function rename($newName){
		return @rename($this->filename, $newName);
	}
}
?>
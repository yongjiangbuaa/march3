<?php
import('module.service.result.XAbstractResult');
/**
 * XFileResult
 * 
 * file result class
 * 
 * 文件类型的结果类
 * 
 * @author Tianwei
 * @package service
 */
class XFileResult extends XAbstractResult{
	protected $contentType;
	protected $contentDispotion = 'inline';
	protected $fileName;
	protected $filePath;
	protected $content;
	protected $timestamp;
	
	/** 
	 * <b>construct method</b>
	 * 
	 * <b>默认构造方法</b>
	 * 
	 * @param XFile $file XFile实体
	 * @return XFileResult
	 */
	function __construct(XFile $file){
		$this->setFileName($file->getName());
		$this->setFilePath($file->getPath());
		$this->setContent(gzcompress(file_get_contents($file->getPath()),9));
		//$this->setContent(file_get_contents($file->getPath()));
		$this->setTimestamp(filemtime($file->getPath()));
	}
	
	/** 
	 * <b>content setter</b>
	 * 
	 * <b>设置content属性的方法</b>
	 * 
	 * @param string $content
	 * @return XFileResult
	 */	
	public function setContent($content){
		$this->content = $content;
		return $this;
	}	

	/** 
	 * <b>content getter</b>
	 * 
	 * <b>获取content属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getContent(){
		return $this->content;
	}
	
	/** 
	 * <b>contentDispotion setter</b>
	 * 
	 * <b>设置contentDispotion属性的方法</b>
	 * 
	 * @param string $contentDispotion
	 * @return XFileResult
	 */	
	public function setContentDispotion($contentDispotion){
		$this->contentDispotion = $contentDispotion;
		return $this;
	}
	
	/** 
	 * <b>contentDispotion getter</b>
	 * 
	 * <b>获取contentDispotion属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getContentDispotion(){
		return $this->contentDispotion;
	}
	
	/** 
	 * <b>contentType setter</b>
	 * 
	 * <b>设置contentType属性的方法</b>
	 * 
	 * @param string $contentType
	 * @return XFileResult
	 */	
	public function setContentType($contentType){
		$this->contentType = $contentType;
		return $this;
	}

	/** 
	 * <b>contentType getter</b>
	 * 
	 * <b>获取contentType属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getContentType(){
		return $this->contentType;
	}
	
	/** 
	 * <b>fileName setter</b>
	 * 
	 * <b>设置fileName属性的方法</b>
	 * 
	 * @param string $fileName
	 * @return XFileResult
	 */	
	public function setFileName($fileName){
		$this->fileName = $fileName;
		return $this;
	}
	
	/** 
	 * <b>fileName getter</b>
	 * 
	 * <b>获取fileName属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getFileName(){
		return $this->fileName;
	}
	
	/** 
	 * <b>filePath setter</b>
	 * 
	 * <b>设置filePath属性的方法</b>
	 * 
	 * @param string $filePath
	 * @return XFileResult
	 */	
	public function setFilePath($filePath){
		$this->filePath = $filePath;
		return $this;
	}
	
	/** 
	 * <b>filePath getter</b>
	 * 
	 * <b>获取filePath属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getFilePath(){
		return $this->filePath;
	}	
	
	/** 
	 * <b>timestamp setter</b>
	 * 
	 * <b>设置timestamp属性的方法</b>
	 * 
	 * @param string $timestamp
	 * @return XFileResult
	 */	
	public function setTimestamp($timestamp){
		$this->timestamp = $timestamp;
		return $this;
	}
	
	/** 
	 * <b>timestamp getter</b>
	 * 
	 * <b>获取timestamp属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getTimestamp(){
		return $this->timestamp;
	}	
}
?>
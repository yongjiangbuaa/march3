<?php
import('module.stream.XAbstractStream');
import('util.xml.XXMLUtil');
/**
 * XXMLStream
 * 
 * XML stream
 * 
 * <b>XML格式数据流</b>
 * 
 * @author Tianwei
 * @package stream 
 */
class XXMLStream extends XAbstractStream{
	protected $input = array();
	
	/**
	 * get input data from XML request
	 * 
	 * 从XML请求中读取输入数据
	 * 
	 * XML请求的命名空间应为xingcloud
	 * 
	 * 一个XML请求示例：
	 * <code>
	 * <?xml version="1.0" encoding="utf-8" ?>
	 * <xingcloud:request xmlns:xingcloud="http://www.iw.com/sns/request/">
	 * <user_uid>1018138244da56947bd48e</user_uid>
	 * </xingcloud:request>
	 * </code>
	 * 
	 * @return array
	 */
	public function getInput(){
		if(!empty($this->input)){
			return $this->input;
		}
		import('util.http.XServletRequest');
		$request = XServletRequest::singleton();
		$this->input = XXMLUtil::singleton()->xmlToArray($this->getInputRawData(), 'request', 'xingcloud');
		return $this->input;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInputParams()
	 */
	public function getInputParams(){
		return  $this->getInput();
	}
	
	/**
	 * output the result data
	 * 
	 * 输出结果给前台
	 * 
	 * 使用utf-8编码，Content-type为text/xml
	 * 
	 * 返回XML命名空间为xingcloud，示例：
	 * <code>
	 * <?xml version="1.0" encoding="utf-8"?>
	 * <xingcloud:result xmlns:xingcloud="http://www.iw.com/sns/result/">
	 * ......
	 * </xingcloud:result>
	 * </code>
	 * 
	 * @param string $output
	 */
	public function output($output){
		$this->setContentType('text/xml');
		$this->setCharset('utf-8');
		$output = XXMLUtil::singleton()->arrayToXML($output, 'result', 'xingcloud', 'http://www.iw.com/sns/result/');
		return parent::output($output);
	}
}
?>
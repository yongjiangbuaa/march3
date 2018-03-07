<?php
import('module.stream.XAbstractStream');
/**
 * XFileStream
 * 
 * file stream
 * 
 * <b>文件数据流</b>
 * 
 * @author Tianwei
 * @package stream 
 */
class XFileStream extends XAbstractStream{	
	protected $clientCache = TRUE;
	
	/**
	 * get input data
	 * 
	 * 读取输入数据
	 * 
	 * @return array
	 */
	public function getInput(){
		import('util.http.XServletRequest');
		$request = XServletRequest::singleton();
		$params = $request->gets();
		if(!$request->isPost()){			
			return $params;
		}
		$rawData = $this->getInputRawData();
		if(!$rawData){
			return $params;
		}
		$data = array();
		if($rawData{0} == '{'){
			$data = @json_decode($rawData, true);			
		}
		if(empty($data)){
			$data = $request->posts();
		}
		if(is_array($data) || is_object($data)){
			$data = (array) $data;				
		    foreach($data as $key => $value){
		    	$params[$key] = $value;
		    }
		}
		return $params;	
	}
	
	/**
	 * output the result data
	 * 
	 * 输出结果给前台
	 * 
	 * <b>文件输出前将会进行gzip压缩，前台解析文件前需要先解压</b>
	 * 
	 * <b>文件会自动在客户端进行缓存，以减小服务器压力</b>
	 * 
	 * @param string $output
	 */
	public function output($output){
		if(is_array($output)){
			parent::output(json_encode($output));
			return false;
		}
		$this->setContentType($output->getContentType());
		$fileName = $output->getFileName();
		if($fileName){		
			header("Content-Disposition: {$output->getContentDispotion()}; filename=\"{$fileName}\"");
		}
		$content = $output->getContent();
		if($this->gzip){
			$content = gzcompress($content);
		}
		$len = strlen($content);
		header("Content-Length: $len");
		return parent::output($content);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::outputRawData()
	 */
	public function outputRawData($rawData){
		return file_put_contents('php://output', $rawData);
	}
}
?>
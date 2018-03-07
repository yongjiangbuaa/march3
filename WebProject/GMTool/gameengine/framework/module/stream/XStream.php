<?php
/**
 * XStream
 * 
 * stream interface
 * 
 * 数据流接口
 * 
 * @author Tianwei
 * @interface
 * @package stream 
 */
interface XStream{
	/**
	 * read raw data from the request body
	 * 
	 * 通过PHP输入流读取非表单形式的POST数据
	 * 
	 * @return string
	 */
	function getInputRawData();
	
	/**
	 * write raw data to the output buffer
	 * 
	 * 通过PHP输出流输出原始数据
	 * 
	 * @param string $rawData
	 */
	function outputRawData($rawData);
	
	/**
	 * get input data
	 * 
	 * 读取输入数据
	 * 
	 * @return array
	 */
	function getInput();
	
	/**
	 * get input parameters
	 * 
	 * 读取请求的参数
	 * 
	 * @return array
	 */
	function getInputParams();
	
	/**
	 * output the result data
	 * 
	 * 输出结果给前台
	 * 
	 * @param string $output
	 */
	function output($output);
}
?>
<?php
/**
 * XProtocol
 * 
 * protocol interface
 * 协议接口
 * @author Tianwei
 * @interface
 * @package protocol 
 */
interface XProtocol{
	/** 
	 * stream setter
	 * 
	 * 设置数据流实例
	 * 
	 * @return XProtocol
	 */
	function setStream(XStream $stream);	
	/**
	 * stream getter
	 * 
	 * 获取数据流实例
	 * 
	 * @return XStream
	 */
	function getStream();	
	/**
	 * name getter
	 * 
	 * 获取name属性
	 * 
	 * @return string
	 */
	function getName();
	/**
	 * gateway getter
	 * 
	 * 获取gateway属性
	 * 
	 * @return string
	 */	
	function getGateway();
	
	/**
	 * request getter
	 * 
	 * 获取XRequest实例
	 * 
	 * @return XRequest
	 */
	function getRequest();
	
	/**
	 * response getter
	 * 
	 * 获取XResponse实例
	 * 
	 * @return XResponse
	 */
	function getResponse();
}
?>
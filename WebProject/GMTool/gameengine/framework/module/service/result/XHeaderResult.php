<?php
define('HTTP_BAD_REQUEST', 400);
define('HTTP_NOT_FOUND', 404);
import('module.service.result.XAbstractResult');
/**
 * XHeaderResult
 * 
 * header result class
 * 
 * header类型的结果类
 * 
 * @author Tianwei
 * @package service
 */
class XHeaderResult extends XAbstractResult{
	protected $status = 200;
	
	public static $HEADERS = array(
		400 => 'HTTP/1.1 400 Bad Request',	
		401 => 'HTTP/1.1 401 Unauthorized',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',	
	);
	
	/** 
	 * <b>status getter</b>
	 * 
	 * <b>获取status属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getStatus(){
		return $this->status;
	}
	
	/** 
	 * <b>status setter</b>
	 * 
	 * <b>设置status属性的方法</b>
	 * 
	 * @param string $status
	 * @return XHeaderResult
	 */	
	public function setStatus($status){
		$this->status = $status;
		return $this;
	}
}
?>
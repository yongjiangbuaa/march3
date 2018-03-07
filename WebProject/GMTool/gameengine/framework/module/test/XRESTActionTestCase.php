<?php
import_class('module.test.XAbstractRESTActionTestCase');
/**
 * XRESTActionTestCase
 * 
 * rest action testcase class
 * 
 * rest service testcase基础类，继承XAbstractRESTActionTestCase
 * 
 * @author Wangqi
 * @package test
 */
class XRESTActionTestCase extends XAbstractRESTActionTestCase{
	protected $protocol = 'rest';
	protected $appendProtocol = TRUE;
	protected $requestMethod = 1;//0=get, 1=post
	protected $initUserUid = TRUE;
	protected $requestArr = array();
	
}


?>
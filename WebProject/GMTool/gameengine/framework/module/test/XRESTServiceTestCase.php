<?php
import_class('module.test.XAbstractRESTServiceTestCase');
/**
 * XRESTServiceTestCase
 * 
 * rest service testcase class
 * 
 * rest service testcase基础类，继承XAbstractRESTServiceTestCase
 * 
 * @author Wangqi
 * @package test
 */
class XRESTServiceTestCase extends XAbstractRESTServiceTestCase{
	protected $protocol = 'rest';
	protected $appendProtocol = TRUE;
	protected $requestMethod = 1;//0=get, 1=post
	protected $initUserUid = TRUE;
	protected $parameters = array();
	
}

?>
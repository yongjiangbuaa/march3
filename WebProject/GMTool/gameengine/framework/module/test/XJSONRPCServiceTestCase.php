<?php
import_class('module.test.XAbstractJSONRPCServiceTestCase');
/**
 * XJSONRPCServiceTestCase
 * 
 * jsonrpc service testcase class
 * 
 * jsonrpc service testcase基础类，继承XAbstractJSONRPCServiceTestCase
 * 
 * @author Wangqi
 * @package test
 */
class XJSONRPCServiceTestCase extends XAbstractJSONRPCServiceTestCase{
	protected $protocol = 'jsonrpc';
	protected $appendProtocol = TRUE;
	protected $requestMethod = 1;//0=get, 1=post
	protected $responseId = 1;
	protected $initUserUid = TRUE;
	protected $enableAuth = TRUE;
}

?>
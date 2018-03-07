<?php
import_class('module.test.XAbstractJSONRPCActionTestCase');
/**
 * XJSONRPCActionTestCase
 * 
 * jsonrpc action testcase class
 * 
 * jsonrpc action testcase基础类，继承XAbstractJSONRPCActionTestCase
 * 
 * @author Wangqi
 * @package test
 */
class XJSONRPCActionTestCase extends XAbstractJSONRPCActionTestCase{
	protected $protocol = 'jsonrpc';
	protected $appendProtocol = TRUE;
	protected $requestMethod = 1;//0=get, 1=post
	protected $responseId = 1;
	protected $initUserUid = TRUE;
}


?>
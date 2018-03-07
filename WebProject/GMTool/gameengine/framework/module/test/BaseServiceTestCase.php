<?php
import('module.test.XAbstractServiceTestCase');
/**
 * BaseServiceTestCase
 * 
 * base service testcase class
 * 
 * service testcase基础类，继承XAbstractServiceTestCase
 * 
 * @author Wangqi
 * @package test 
 */
class BaseServiceTestCase extends XAbstractServiceTestCase{
	protected $enableService=TRUE;
	protected $initUserUid = TRUE;
}



?>
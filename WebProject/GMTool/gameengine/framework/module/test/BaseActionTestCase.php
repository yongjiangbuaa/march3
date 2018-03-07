<?php
import('module.test.XAbstractActionTestCase');
/**
 * BaseActionTestCase
 * 
 * base action testcase class
 * 
 * action testcase基础类，继承XAbstractActionTestCase
 * 
 * @author Wangqi
 * @package test 
 */
class BaseActionTestCase extends XAbstractActionTestCase{
	protected $enableService=TRUE;
	protected $initUserUid = TRUE;
}
<?php
import('util.model.XObject');
import('module.service.result.XResult');
/**
 * XAbstractResult
 * 
 * abstract result class
 * 
 * <b>result的抽象方法，实现XResult接口，继承XObject类</b>
 * 
 * @author Tianwei
 * @abstract
 * @package service 
 */
abstract class XAbstractResult extends XObject implements XResult{
	/**
	 * (non-PHPdoc)
	 * @see XResult::asArray()
	 */
	public function asArray(){
		return array();
	}
}
?>
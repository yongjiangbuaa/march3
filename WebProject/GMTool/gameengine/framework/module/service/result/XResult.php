<?php
/**
 * XResult
 * 
 * result interface
 * 
 * 结果接口
 * 
 * @author Tianwei
 * @interface
 * @package service 
 */
interface XResult{
	/**
	 * <b>convert the properties of XResult object into an array</b>
	 * 
	 * <b>将XResult的属性转化为数组形式</b>
	 * 
	 * @return array
	 */
	public function asArray();
}
?>
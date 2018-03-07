<?php
import('module.context.XException');
/**
 * XRouteNotFoundException
 * 
 * route not found exception
 * 
 * 未找到路由时抛出的异常
 * 
 * @author Tianwei
 * @package mvc
 */
class XRouteNotFoundException extends XException{
	protected $code = 404;
}
?>
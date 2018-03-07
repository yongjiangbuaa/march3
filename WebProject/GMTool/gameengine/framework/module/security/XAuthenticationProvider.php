<?php
/**
 * XAuthenticationProvider
 * 
 * authentication provider
 * 
 * 验证提供者接口
 * 
 * @author Tianwei
 * @interface
 * @package security 
 */
interface XAuthenticationProvider{
	/**
	 * authenticate
	 * 
	 * 进行验证
	 * 
	 * @param object $object
	 * @param string $method
	 * @param array $args
	 */
	public function authenticate($object, $method, $args);
}
?>
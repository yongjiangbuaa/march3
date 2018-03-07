<?php
/**
 * XException
 * 
 * framework exception class
 * 
 * 框架异常类，支持多语言异常信息
 * 
 * @author Tianwei
 * @package context 
 */
class XException extends Exception {
	private $locales = array();
	
	/** 
	 * construct method
	 * 
	 * 构造函数
	 * 
	 * @param string $message 错误消息
	 * @param int $code 错误代码
	 * @param array $locales 多语言错误消息数组
	 */
	function __construct($message, $code = 500, $locales = array()) {
		parent::__construct($message, $code);
		foreach($locales as $key => $value){
			$this->addLocaleMessage($key, $value);
		}
	}
	
	/** 
	 * add a localized message
	 * 
	 * 添加多语言错误消息
	 * 
	 * @param string $locale 语言信息，通常为语言缩写代码
	 * @param string $message 该语言对应错误消息
	 * @return XException
	 */
	function addLocaleMessage($locale, $message) {
		$this->locales[$locale] = $message;
		return $this;
	}
	
	/** 
	 * get a localized message
	 * 
	 * 获取多语言错误消息
	 * 
	 * @param string $locale 语言信息，通常为语言缩写代码
	 * @return string 该语言对应错误信息
	 */	
	function getLocaleMessage($locale) {
		if(isset($this->locales[$locale])){
			return $this->locales[$locale];
		}
		return null;
	}
}
?>
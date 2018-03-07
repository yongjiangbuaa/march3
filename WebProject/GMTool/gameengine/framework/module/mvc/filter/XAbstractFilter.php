<?php
import('module.mvc.filter.XFilter');
/**
 * XAbstractFilter
 * 
 * abstract class of the filter
 * 
 * 过滤器抽象类
 * 
 * @abstract
 * @author Tianwei
 * @package mvc
 */
abstract class XAbstractFilter implements XFilter{
	protected $config = array();
	protected $breakChain = false;
	/**
	 * construct method
	 * 
	 * 构造方法
	 * 
	 * @param array $config
	 */
	public function __construct(array $config = array()){
		$this->config = $config;
	}
	
	/**
	 * execute the filter
	 * 
	 * 执行过滤器，调用顺序为beforeFilter(), XFilterChain->doFilter()方法，afterFilter()
	 * 
	 * @param XServletRequest $request
	 * @param XServletResponse $response
	 * @param XFilterChain $chain
	 */
	final public function doFilter(XServletRequest $request, XServletResponse $response, XFilterChain $chain){
		$this->beforeFilter($request, $response);
		if(!$this->breakChain){			
			$chain->doFilter($request, $response);
		}
		$this->afterFilter($request, $response);
	}
	
	protected function beforeFilter(XServletRequest $request, XServletResponse $response){
		
	}
	
	protected function afterFilter(XServletRequest $request, XServletResponse $response){
		
	}
	
	protected function setBreakChain($breakChain){
		$this->breakChain = $breakChain;
	}	
}
?>
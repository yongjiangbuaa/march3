<?php
import('util.http.XServletRequest');
import('util.http.XServletResponse');
import('module.mvc.filter.XFilterChain');
/**
 * @package mvc
 */
final class XFilterDispatcher implements XFilterChain{
	private static $instance = null;
	protected $filters = array();
	
	private function __construct() {
		$this->load();
	}
	
	/**
	 * @return XFilterDispatcher
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * @return XFilterDispatcher
	 */	
	public function doFilter(XServletRequest $request, XServletResponse $response){
		$filter = current($this->filters);
		next($this->filters);
		if($filter && $filter instanceof XFilter){
			$filter->doFilter($request, $response, $this);
		}		
		return $this;
	}
	
	protected function load(){
		import('util.config.XConfig');
		$config = XConfig::singleton()->get("filter");
		if(!is_array($config)
			|| empty($config)){
			return true;
		}
		foreach($config as $key => $value){
			if(!isset($value['class'])){
				continue;
			}
			import('module.mvc.filter.'.$value['class']);
			$this->filters[] = new $value['class']($value);
		}
		return true;
	}
}
?>
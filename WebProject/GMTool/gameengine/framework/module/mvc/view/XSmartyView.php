<?php
import('module.mvc.view.XAbstractView');
/**
 * XSmartyView
 * 
 * smarty view class
 * 
 * smarty视图类
 * 
 * @author Tianwei
 * @package mvc
 */
class XSmartyView extends XAbstractView{
	protected $extesion = 'tpl';
	protected static $smarty = null;
	
	/**
	 * construct method
	 * 
	 * 构造方法
	 */
	function __construct() {
		if (!self::$smarty) {
			import('plugin.smarty.XSmarty');
			self::$smarty = &XSmarty::load();
		}
		$compile_dir = GAME_TMP_DIR.'/templates_c';
		if(!is_dir($compile_dir)) {
			@mkdir($compile_dir, 0777, true);
		}
		self::$smarty->compile_dir = $compile_dir;
		self::$smarty->debugging = false;
		self::$smarty->left_delimiter = '<{';
		self::$smarty->right_delimiter = '}>';
	}
	
	/**
	 * display the smarty view
	 * 
	 * 执行smarty视图的显示方法
	 * 
	 * 会首先将XSmartyView的参数值（contents属性，由setAll()或set()方法赋值）assign到smarty实例，然后会调用smarty实例的display()方法
	 */
	public function view(){
		self::$smarty->assign($this->contents);
		$name = x_name_to_c(preg_replace("/^do/e", "", $this->name));
		$file = GAME_MVC_DIR.'/views/'.$this->route.'/'.$name.'.'.$this->extesion;
		if(!is_file($file)){
			return;
		}
		self::$smarty->display("file:".$file);
	}
}
?>
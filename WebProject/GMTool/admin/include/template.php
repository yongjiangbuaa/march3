<?php
if(!function_exists('MooTemplate')){
	defined('IN_MOOPHP') or define('IN_MOOPHP',true);
	
// 	require_once FRAMEWORK . '/template/Template.class.php';
	require_once 'Template.class.php';
	
	/**
	 * 解析模版
	 * @deprecated 请使用renderTemplate函数
	 * @see renderTemplate
	 *
	 */
	function MooTemplate($file) {
		$tplfile = MOOPHP_TEMPLATE_DIR.'/'.$file. '.htm';
		if(!file_exists($tplfile)){
			die("template file $tplfile not exists");
		}
		$objfile = MOOPHP_DATA_DIR.'/'.$file.'.tpl.php';
		if(!file_exists($objfile) || filemtime($tplfile) > filemtime($objfile)) {
			if(!file_exists(MOOPHP_DATA_DIR)){
				mkdir(MOOPHP_DATA_DIR,0777,true);
			}
			if(!file_exists(dirname($objfile))){
				mkdir(dirname($objfile),0777,true);
			}
			$t = new Template();
			$t->setHeader("<?php if(!defined('IN_MOOPHP')) exit('Access Denied');?>\r\n");
			if($t->complie($tplfile,$objfile) === false){
				die('Cannot write to template cache.');
			}
		}
		return $objfile;
	}
}
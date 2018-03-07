<?php
import('module.util.logger.appender.XFileLoggerAppender');
/**
 * XDailyFileLoggerAppender
 * 
 * daily file logger appender
 * 
 * 每日文件日志添加器，继承XFileLoggerAppender
 * 
 * @author Tianwei
 * @package util.logger
 */
class XDailyFileLoggerAppender extends XFileLoggerAppender{
	/**
	 * @return string
	 */	
	protected function getFilePath($params = array()){
		if(isset($params['target'])){
			return $params['target'];
		}
		return GAME_LOG_DIR.__DS__.$this->name.'.'.date("Y-m-d").'.log';
	}	
}
?>
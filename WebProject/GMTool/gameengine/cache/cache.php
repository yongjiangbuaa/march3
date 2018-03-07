<?php
/**
 * cache模块入口
 * @package cache
 */
/**
 * @ignore
 */
defined('CACHE_DIR') or define("CACHE_DIR", dirname(__FILE__));
/**
 * @ignore
 */
defined('CACHE_MODULE_DIR') or define("CACHE_MODULE_DIR", CACHE_DIR.'/module');
/**
 * @ignore
 */
defined('CACHE_CONFIG_DIR') or define("CACHE_CONFIG_DIR", CACHE_DIR.'/config');

require_once dirname(CACHE_DIR).'/util/util.php';
?>
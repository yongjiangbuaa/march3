<?php
/**
 * 竞技场
 *
 */

Class StatData {
	static public  $goldType = 2;//1金币2礼券3直接调支付
	static public $lang = 'cn';//平台语言
	static public $share;//触发分享后传参
	static public $pf;//平台
	static public $dbInfo;//数据库连接信息
	static public $mongodbInfo;//mogodb连接信息
	static public $redisInfo;//redis连接信息
}
?>
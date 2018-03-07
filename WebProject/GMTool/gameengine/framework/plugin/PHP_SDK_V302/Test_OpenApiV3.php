<?php

/**
 * OpenAPI V3 SDK 示例代码
 *
 * @version 3.0.0
 * @author open.qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 * @ History:
 *               3.0.0 | nemozhang | 2011-12-12 11:11:11 | initialization
 */


require_once 'OpenApiV3.php';

// 应用基本信息
$appid = 10881;
$appkey = '408eb8a7ca6b47c8a7a9c5c04bc5f4f5';

// OpenAPI的服务器IP 
// 最新的API服务器地址请参考wiki文档: http://wiki.open.qq.com/wiki/API3.0%E6%96%87%E6%A1%A3 
$server_name = '113.108.20.23';

// 用户的OpenID/OpenKey
$openid = '00000000000000000000000005BA61AE';
$openkey = 'EADEBA876BCE731BF74019DB57E38A74';

// 所要访问的平台, pf的其他取值参考wiki文档: http://wiki.open.qq.com/wiki/API3.0%E6%96%87%E6%A1%A3 
$pf = 'qzone';


$sdk = new OpenApiV3($appid, $appkey);
$sdk->setServerName($server_name);

$ret = get_user_info(&$sdk, $openid, $openkey, $pf);
print_r("===========================\n");
print_r($ret);

/**
 * 获取好友资料
 *
 * @param object $sdk OpenApiV3 Object
 * @param string $openid openid
 * @param string $openkey openkey
 * @param string $pf 平台
 * @return array 好友资料数组
 */
function get_user_info($sdk, $openid, $openkey, $pf)
{
	$params = array(
		'openid' => $openid,
		'openkey' => $openkey,
		'pf' => $pf,
	);
	
	$script_name = '/v3/user/get_info';

	return $sdk->api($script_name, $params);
}

// end of script

<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 15/11/24
 * Time: 16:19
 */
define('PUSH_ROOT', __DIR__);
require_once PUSH_ROOT.'/Push.php';

//11-24 17:02:11.536: D/cocos2d-x debug info(18567): parse_reg 3fbbeb66-7af8-4b6c-b34f-420db02493c9
//11-24 17:33:42.790: D/cocos2d-x debug info(21019): parse_reg 6f7d445e-d330-49fb-88bd-f74c25868b7e

$parse_token = 'e5147749-1e87-4966-a69c-11d2d2a510c6';// zihui
$parse_token = '1b60742a-bd94-496e-97a3-8ac5db3bad33'; // shu 614306000002
// $parse_token = '00259c57-5489-4c02-9933-54bc849b614b'; // shu 42469875000001 huawei
// $parse_token = '75c3330a-aab6-4624-8ef5-0d2aec0e75e6'; // 
// $parse_token = '5b6452007dcc64b396bb1ed3cf986a076dec64641aa7fe8cd0925ae92575c3b0'; // shu ios

$message = 'test parse notification at ' . microtime(true);

$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';

$push = new Push ( $parse_app_id, $parse_api_key );
$push->device = Push::DEVICE_TYPE_ANDROID;
// $push->device = Push::DEVICE_TYPE_IOS;

$result = $push->pushToUser($parse_token, $message );

echo "call Parse: $parse_app_id\n", "msg=$message\n", "result=";
print_r ( $result );
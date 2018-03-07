<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/9/13
 * Time: 14:38
 */


$expedition_info = array();
$expedition_info['serverId'] = 3;
$expedition_info['matchServerId'] = 2;
$expedition_info['isOpen'] = true;
$expedition_info['result'] = 'NONE';
$expedition_info['activityEndTime'] = 1473427176000;
$expedition_info['status'] = 'WATTING';
$expedition_info['count'] = 1;

$redis = new Redis();
$redis->connect('localhost', 6379);

$redis->hSet("SERVER_EXPEDITION_KEY", "3", json_encode($expedition_info));

$expedition_info['serverId'] = '2';
$expedition_info['matchServerId'] = '3';

$redis->hSet("SERVER_EXPEDITION_KEY", "2", json_encode($expedition_info));


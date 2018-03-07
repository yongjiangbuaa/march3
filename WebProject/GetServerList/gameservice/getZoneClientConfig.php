<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 2016/12/14
 * Time: 15:04
 */

$config = array();
$item = array('zone' => 'COK1',
    'host' => 'localhost',
    'port' => 3011);
$config[] = $item;
$item['zone'] = 'COK2';
$config[] = $item;

echo json_encode($config);

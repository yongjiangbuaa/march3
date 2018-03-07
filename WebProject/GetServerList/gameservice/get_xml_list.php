<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/30
 * Time: 17:09
 */
include __DIR__ . '/common.php';

define('RESOURCE_BASE', DOCS_ROOT . '/resource');

$pattern = RESOURCE_BASE . '/*.xml';

$list = glob($pattern);
$result = array();

foreach ($list as $file_name) {
    $name = basename($file_name);
    if($name == 'world.xml'){
        continue;
    }
    $item = array();
    $item['file'] = $name;
    $item['mtime'] = filemtime($file_name);
    $item['md5'] = md5_file($file_name);
    $item['size'] = filesize($file_name);
    $result[] = $item;
}
header('Content-Type: application/json');
echo json_encode($result);
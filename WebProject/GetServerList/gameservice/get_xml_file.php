<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/30
 * Time: 17:21
 */
include __DIR__ . '/common.php';

$file = $_GET['f'];

if(substr_count($file,'..') >= 1){
    header("HTTP/1.0 404 Not Found");
    return;
}
$file_path = DOCS_ROOT . '/resource/' . $file;
if(file_exists($file_path)){
    header("Content-Type:text/xml");
    readfile($file_path);
}else{
    header("HTTP/1.0 404 Not Found");
}
<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/15
 * Time: 10:38
 */
date_default_timezone_set('UTC');

if(php_sapi_name() !== 'cli') {
    $sig = 'G4Oq3Eru';
    $sig_g = $_GET['s'];
    if ($sig !== $sig_g) {
        exit();
    }
}
error_reporting(0);
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors',1);

header("Content-Type:text/xml");
readfile(dirname(__DIR__) . '/resource/servers.xml');

<?php
//define('XA_LOCAL_TYPE','http');
require_once './XA.php';
$xa=XA::getInstance();
//$xa->setLocation("10.18.138.171");
$xa->setAppid("phpsdk@1_2_3");
$xa->setUID("uid");
$xa->trackEvent("user.visit", array("a"=>"1","b"=>"2"));
$xa->sendLog();
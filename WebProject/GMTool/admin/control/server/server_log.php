<?php
if ($_REQUEST ['uid'])
    $uid = $_REQUEST ['uid'];
if( isset($uid)){
    $filename = '/usr/local/march/server.log';  //about 500MB
    $output = shell_exec('grep '.$uid .' ' . $filename . '| tail -n100 ');
    echo str_replace(PHP_EOL, '<br />', $output);         //add newlines
    exit;
}else
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
$uid =
$filename = '/usr/local/march/server.log';  //about 500MB
$output = shell_exec('exec tail -n200 ' . $filename . '|  grep '.$uid);  //only print last 50 lines
echo str_replace(PHP_EOL, '<br />', $output);         //add newlines
?>
<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/4/15
 * Time: 20:56
 */

$start = strtotime('2016-01-01');
$end = strtotime('2016-03-12');

$script = 'sh /data/htdocs/stats/cron/stats_signAll.sh fixdate=%s';

for($i = 0; $i < 200; ++$i){
    $time = strtotime("+$i day",$start);
    if($time > $end){
        break;
    }
    file_put_contents('run.sh',sprintf($script,date('Y-m-d',$time)) . PHP_EOL, FILE_APPEND);
}

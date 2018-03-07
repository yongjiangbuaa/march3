<?php
/**
 * Created by PhpStorm.
 * User: tangjp
 * Date: 2017/3/29
 * Time: 下午3:42
 */
$hostdir="/data/htdocs/download/";
$mysqlFile=array();

$filesnames = scandir($hostdir);

foreach ($filesnames as $name) {
    if('.'===$name || '..'===$name)
    {
        continue;
    }
    $mysqlFile[$name]="http://p1coq.elexapp.com/t/".$name;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php

$download_android_url_key = "DOWNLOAD_ANDROID_URL";//安卓下载地址存放的redis key
$default_url = "http://coqcn.eleximg.com/dl/clashofqueens/coq.apk";
$headLine ="安卓下载地址,请谨慎修改！！";

if($_REQUEST['type']=='update'){
    $newUrl = $_REQUEST['newUrl'];
    $newUrl = trim($newUrl);
    if (!empty($newUrl)) {
        $page->redis(8, $download_android_url_key, $newUrl);
        $headLine = "更新成功";
    }
}

$oldUrl = $page->redis(7,$download_android_url_key,null,null);
if(empty($oldUrl)){
    $oldUrl = $default_url;
}

include( renderTemplate("{$module}/{$module}_{$action}") );




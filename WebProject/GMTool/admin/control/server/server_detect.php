<?php
!defined('IN_ADMIN') && exit('Access Denied');
$headLine = "服务器监控配置";
//修改配置
if($_REQUEST['action'] == 'modify'){
    $newNum = 0 + $_REQUEST['newNum'];
    if(file_exists(ADMIN_ROOT.'/onlineDetect.php')){
        $content = file(ADMIN_ROOT.'/onlineDetect.php');
        $str = '';
        foreach ($content as $value){
            if(strpos($value, "efine('TIMES',")){
                $value = preg_replace('(\\d[\\d\\.]*\\d)',$newNum, $value);
            }
            $str .= $value;
        }
        file_put_contents(ADMIN_ROOT.'/onlineDetect.php', $str);
    }
}

if($_REQUEST['action'] == 'get'){
    //读取当前配置
    if(file_exists(ADMIN_ROOT.'/onlineDetect.php')){
        $content = file(ADMIN_ROOT.'/onlineDetect.php');
        foreach ($content as $value){
            if(strpos($value, "efine('TIMES',")){
                $count = preg_match('(\\d[\\d\\.]*\\d)', $value,$matchs);
                if($count > 0){
                    $times = $matchs[0];
                    exit($times);
                }
                else{
                    exit('监控文件不存在!');
                }
            }
        }
    }
    else{
        $headAlert = '监控文件不存在!';
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
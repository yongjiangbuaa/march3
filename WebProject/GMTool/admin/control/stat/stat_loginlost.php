<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['getstat']){
    $result = $page->redis(10,'tutorial:');
    if($result){
        $allData = array();
        foreach ($result as $eKey){
            $temp = $page->redis(1,$eKey);
            foreach ($temp as $key=>$value){
                if($key >=1000 && $key <= 2000 ){
                    $allData[$key] += $value;
                }
            }
        }
    
    }
    else{
        $errorMsg = '没有数据';
    }
    asort($allData);
    $html = '<table class="listTable" cellspacing=1 padding=0>';
    $html .= '<tr><td>节点</td><td>次数</td></td>';
    foreach ($allData as $key=>$value){
        $html .='<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
    } 
    $html .= '</table>';
    echo $html;
    exit();
}
if($_REQUEST['deletedata']){
 $result = $page->redis(10,'tutorial:');
    if($result){
        foreach ($result as $eKey){
            $page->redis(9,$eKey);
        }
        exit('成功删除'.count($result).'项数据！');
    }
    else{
        exit('查找数据为空!');
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
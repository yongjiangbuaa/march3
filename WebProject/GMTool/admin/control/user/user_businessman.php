<?php
!defined('IN_ADMIN') && exit('Access Denied');

if($_REQUEST['contents'])
    $contents = $_REQUEST['contents'];
if($contents){
    $contents = addslashes($contents);
    $arr = explode(';',$contents);
    if(count($arr) <1) {
        $headAlert='uid错误';
    }


    $arruids2 = array_chunk($arr,50);
    $uidServerArray = array();
    foreach($arruids2 as $key=>$value){
        $value1 = array_values($value);

        $result['ret']['data'] = cobar_getAccountInfoByGameuids($value1);
        foreach ($result['ret']['data'] as $curRow){
            $uidServerArray[$curRow['gameUid']]=$curRow['server'];
        }
    }

    foreach($uidServerArray as $uid=>$server) {

        if(!is_numeric($uid)){
            $headAlert='uid错误';
            break;
        }
        $server = 's'.$server;
        $opeDate = date('Y-m-d H:i:s');

        $ret = $page->webRequest('kickuser', array('uid' => $uid), $server);

        $sql = "UPDATE userprofile SET isBusinessman=1 WHERE uid='$uid'";
        $re = $page->executeServer($server,$sql, 2);

        adminLogUser($adminid, $uid, $server, array('businessman' => $active,'action'=>'businessman'));

    }


}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
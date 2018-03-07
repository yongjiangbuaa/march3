<?php

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
date_default_timezone_set('UTC');

define('PATH_ROOT',dirname(__FILE__));

//print_r($_POST);
//file_put_contents('upload_trace.log',print_r($_POST,true),FILE_APPEND);
$input = $_POST['data'];
if(empty($input)){
    $params = array();
}else{
    $params = json_decode($input, true);
}


try{
    if(check_params($params)){
        require PATH_ROOT . '/upload/UploadFmPhoto.class.php';
        $uploader = new UploadFmPhoto();
        $re = $uploader->execute($params);
        $re['status'] = 1;
    }else{
        $re = array('error' => 'sign error', 'errno' => 3, 'status' => 0);
    }

}catch (Exception $e){
    $re = array('error' => $e->getMessage(), 'errno' => $e->getCode(), 'status' => 0);
}

function check_params($params){
    if(empty($params)){
        return false;
    }
    $sig_key = 'g!w1xz@s2dy#y3l';
    $gameuid = strval($params['gameuid']);
    if(empty($gameuid)){
        return false;
    }
    $photo_seq = intval($params['photo_seq']);
    $sign = $params['_s'];

    $sign_str = $sig_key . 'gameuid' . $gameuid . 'photo_seq' . $photo_seq ;
    $sign_expected = strtoupper(md5($sign_str));
    if($sign_expected === $sign){
        return true;
    }
    $msg = "fm sign str $sign_str expected $sign_expected actual $sign" . PHP_EOL;
    file_put_contents('upload_error.log',$msg , FILE_APPEND);
    return false;
}
$resp = array('response' => array($re));
echo json_encode($resp);
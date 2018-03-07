<?php

require_once dirname(dirname(__FILE__)).'/service/SDKServerService.php';
require_once dirname(dirname(__FILE__)).'/model/SDKException.php';
require_once dirname(dirname(__FILE__)).'/util/ConfigHelper.php';
require_once dirname(dirname(__FILE__)).'/util/LoggerHelper.php';

/**
 * 支付前客户端签名计算服务类
 * <br>客户端在支付前需调用CP服务端的签名生成方法，CP服务端将签名返回给客户端
 * <br><b>注意：此类主要为示例签名计算方法</b>
 * <br>具体实现需要CP依据自己与客户端通信的协议加密处理等，避免安全性问题
 */
try{
    // 接收HTTP POST信息
    $request = file_get_contents("php://input");

    // 测试数据
    //$request = '{"callbackInfo":"callbackInfo","notifyUrl":"notifyUrl","amount":"amount","cpOrderId":"cpOrderId","accountId":"accountId"}';
    LoggerHelper::info("[PayChargeSignService.php]收到的客户端待签名的请求：".$request);

    // 处理支付回调请求
    $reqData = json_decode($request,true);
    if($reqData!=null){
        $baseService = new BaseSDKService();

        //定义签名时排除在外的key,即：指定key不参与签名
        $notInKey = array("signType");
        $signSource = $baseService->getSignDataWithoutNotInKey($reqData, $notInKey).ConfigHelper::getStrVal("sdkserver.game.apikey");//组装签名原文
        $sign = md5($signSource);//MD5加密签名

        LoggerHelper::info("[PayChargeSignService.php]"."[签名原文]:".$signSource);
        LoggerHelper::info("[PayChargeSignService.php]"."[签名结果]:".$sign);
        $array = array('sign' => $sign);

        echo json_encode($array, true);
        return;
    }
    else{
        LoggerHelper::info("[PayChargeSignService.php]"."接口返回异常");
        $array = array('sign' => null);
        echo json_encode($array, true);
        return;
    }

}
catch (SDKException $e){
    LoggerHelper::info("[PayChargeSignService.php]".$e->getMessage());
    $array = array('sign' => null);
    echo json_encode($array, true);
    return;
}
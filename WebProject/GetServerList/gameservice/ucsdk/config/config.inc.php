<?php
return array(
    #环境参数配置
    #端游服务端测试环境访问地址：http://sdk.test4.9game.cn
    #'sdkserver.baseUrl'=>"http://sdk.test4.9game.cn",
    #端游服务端生产环境访问地址：http://sdk.9game.cn
    'sdkserver.baseUrl'=>"http://sdk.9game.cn",
    #端游服务端端口，不配置时默认为80(线上端口为80)
    'sdkserver.baseUrl.port'=>"80",

    #端游游戏数据收集生产环境访问地址：http://collect.sdkyy.9game.cn
    'sdkgamedata.baseUrl'=>"http://collect.sdkyy.9game.cn",
    #端游游戏数据收集端口，不配置时默认为8080(线上端口为8080)
    'sdkgamedata.baseUrl.port'=>"8080",

    #game参数配置（须填写完整）
    'sdkserver.game.cpId'=>"78924",
    'sdkserver.game.gameId'=>"660361",
    'sdkserver.game.apikey'=>"47c982bd57f4011b2fb9385d400777c1",

    #运行时参数配置，是否输出debug日志
    'sdkserver.debug'=>"true",

    //输出debug日志的保存路径,只在debug配置为true时生效，默认值是/var/tmp/,最后需以/结尾
    'sdkserver.debug.filepath'=>"/data/log/getserverlist/",

    //以下是相关时间参数的配置
    //连接超时时间【单位:秒】 默认:5
    'connectTimeOut' => "3"
);
<?php

$host = gethostbyname(gethostname());
if ($host == '10.1.9.247' || $host == '127.0.0.1' || PHP_OS == 'Darwin') {
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 0);//inner test
}elseif ( $host == 's1807-dal9.flyingbird.com' || $host=='169.46.138.173' || $host=='91-87'){  //新inforbright和跳板机都走这个
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 9);//online
}else{
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 1);//online test
}
if(PRODUCT_SEVER_TYPE == 0){
    //dev
    define('GLOBAL_DB_SERVER_IP', '10.1.9.247');
    define('GLOBAL_DB_SLAVE_IP', '10.1.9.247');
    define('GLOBAL_DB_SERVER_USER', 'march');
    define('GLOBAL_DB_SERVER_PWD', 'march123456');
    //废弃ad库
    define('AD_DB_SERVER_IP', '10.1.9.247');
    define('AD_DB_SERVER_USER', 'march');
    define('AD_DB_SERVER_PWD', 'march123456');
    define('STATS_DB_SERVER_IP', '10.1.9.247');
    define('STATS_DB_SERVER_USER', 'march_stat');
    define('STATS_DB_SERVER_PWD', 'march123456');
    define('GAME_DB_SERVER_USER', 'march');
    define('GAME_DB_SERVER_PWD', 'march123456');

    define('new_AD_DB_SERVER_IP', '10.1.9.247');
    define('new_AD_DB_SERVER_USER', 'march');
    define('new_AD_DB_SERVER_PWD', 'march123456');
    define('new_AD_DB_SERVER_DATABASE', 'data');

}else if(PRODUCT_SEVER_TYPE == 9){
    //online
    define('GLOBAL_DB_SERVER_IP', '123.206.90.153');
    define('GLOBAL_DB_SLAVE_IP', '123.206.90.153');
    define('GLOBAL_DB_SERVER_USER', 'march');
    define('GLOBAL_DB_SERVER_PWD', '123456');
    //废弃ad库
    define('AD_DB_SERVER_IP', '10.1.9.247');
    define('AD_DB_SERVER_USER', 'march');
    define('AD_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');
    //
    define('STATS_DB_SERVER_IP', '10.1.9.247'); //改为新地址
    define('STATS_DB_SERVER_USER', 'root');
    define('STATS_DB_SERVER_PWD', 'K2NDBm6zegpiE');
    define('GAME_DB_SERVER_USER', 'march');
    define('GAME_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');

    define('new_AD_DB_SERVER_IP', '10.1.9.247');
    define('new_AD_DB_SERVER_USER', 'root');
    define('new_AD_DB_SERVER_PWD', 'data123$');
    define('new_AD_DB_SERVER_DATABASE', 'data');
}

//通用的
define('GLOBAL_DEPLOY_DB_NAME', 'marchdb_admin_deploy');
define('GLOBAL_TEMPLATE_DBNAME', 'marchdb_template');
define('GLOBAL_DB_DBNAME', 'marchdb_global');

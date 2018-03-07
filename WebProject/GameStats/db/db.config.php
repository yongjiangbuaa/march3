<?php

$host = gethostbyname(gethostname());
if ($host == '10.1.16.211' || $host == '127.0.0.1' || PHP_OS == 'Darwin') {
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 0);//inner test
}elseif ( $host == 's1807-dal9.flyingbird.com' || $host=='169.46.138.173' || $host=='91-87'){  //新inforbright和跳板机都走这个
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 9);//online
}else{
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 1);//online test
}
if(PRODUCT_SEVER_TYPE == 0){
    //dev
    define('GLOBAL_DB_SERVER_IP', '10.1.16.211');
    define('GLOBAL_DB_SLAVE_IP', '10.1.16.211');
    define('GLOBAL_DB_SERVER_USER', 'cok');
    define('GLOBAL_DB_SERVER_PWD', '1234567');
    //废弃ad库
    define('AD_DB_SERVER_IP', '10.1.16.211');
    define('AD_DB_SERVER_USER', 'cok');
    define('AD_DB_SERVER_PWD', '1234567');
    define('STATS_DB_SERVER_IP', '10.1.16.211');
    define('STATS_DB_SERVER_USER', 'cok_stat');
    define('STATS_DB_SERVER_PWD', '1234567');
    define('GAME_DB_SERVER_USER', 'cok');
    define('GAME_DB_SERVER_PWD', '1234567');

    define('new_AD_DB_SERVER_IP', '10.1.16.211');
    define('new_AD_DB_SERVER_USER', 'cok');
    define('new_AD_DB_SERVER_PWD', '1234567');
    define('new_AD_DB_SERVER_DATABASE', 'coq_data');

}else if(PRODUCT_SEVER_TYPE == 9){
    //online
    define('GLOBAL_DB_SERVER_IP', '10.82.60.173');
    define('GLOBAL_DB_SLAVE_IP', '10.155.110.57');
    define('GLOBAL_DB_SERVER_USER', 'gow');
    define('GLOBAL_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');
    //废弃ad库
    define('AD_DB_SERVER_IP', '10.41.81.106');
    define('AD_DB_SERVER_USER', 'gow');
    define('AD_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');
    //
    define('STATS_DB_SERVER_IP', '10.153.120.26'); //改为新地址
    define('STATS_DB_SERVER_USER', 'root');
    define('STATS_DB_SERVER_PWD', 'K2NDBm6zegpiE');
    define('GAME_DB_SERVER_USER', 'gow');
    define('GAME_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');

    define('new_AD_DB_SERVER_IP', '10.84.187.165');
    define('new_AD_DB_SERVER_USER', 'root');
    define('new_AD_DB_SERVER_PWD', 'elexdata123$');
    define('new_AD_DB_SERVER_DATABASE', 'coq_data');
}

//通用的
define('GLOBAL_DEPLOY_DB_NAME', 'cokdb_admin_deploy');
define('GLOBAL_TEMPLATE_DBNAME', 'cokdb_template');
define('GLOBAL_DB_DBNAME', 'cokdb_global');

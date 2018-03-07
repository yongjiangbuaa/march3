<?php
!defined('IN_ADMIN') && exit('Access Denied');
require_once ADMIN_ROOT . "/include/XMySQL.php";
$op = $_REQUEST["op"];

if(!$_REQUEST['startDate']){
    $startDate = date("Y-m-d",time()-86400*7);
}else{
    $startDate = $_REQUEST['startDate'];
}
if(!$_REQUEST['endDate']){
    $endDate = date("Y-m-d",time());
}else{
    $endDate = $_REQUEST['endDate'];
}
if(empty($_REQUEST['limit'])) $limit = 100;
$actiontype = intval($_REQUEST['actiontype']);
$oper = trim($_REQUEST['oper']);
$userId = trim($_REQUEST['userId']);
$mysql = new XMySQL($page->getMySQLInfo(false, 's1'));
if($op == 'search'){
    $starttime = strtotime($startDate);
    $endtime = strtotime($endDate)+86400;
    $limit = abs(intval($_REQUEST['limit']));

    $where = array();
    if(!empty($oper)) $where[] = " adminname = '$oper' ";
    if(!empty($userId)) $where[] = " target_uid = '$userId' ";
    if(!empty($actiontype)) $where[] = " action_type = $actiontype ";
    $where[] = " create_time > $starttime and create_time < $endtime ";
    $whereStr = implode(' and ',$where);
    $result =  AdminAuditLog::getInstance()->getLog($whereStr,$limit);
    $auditLog = $result['ret']['data'];
    $auditLogNum = !empty($result['data']['effect']) ?$result['data']['effect']:count($auditLog);

}


    //TODO查询
    $tablename = "admin";
    $where = "1=1";
    $alladmin = $mysql->get($tablename, $where, null ,1000, 'order by groupid desc,username');
    $ALLACTION = AdminAuditLog::getInstance()->getAllActionDescribe();
//    print_r($result);
    $ADMINLIST = $alladmin;

include( renderTemplate("{$module}/{$module}_{$action}") );

?>
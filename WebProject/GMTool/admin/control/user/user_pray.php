<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21
 * Time: 11:40
 */
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
$start_time = date('Y-m-d',time()-86400*7);
$end_time = date('Y-m-d',time()+86400);
if ($_REQUEST ['type'] == 1) {
    $start_time = $_REQUEST['start_time']?strtotime($_REQUEST['start_time'])*1000:strtotime($start_time)*1000;
    $end_time = $_REQUEST['end_time']?strtotime($_REQUEST['end_time'])*1000:strtotime($end_time)*1000;
    $time = "and timeStamp>=$start_time and timeStamp<=$end_time ";
    if ($_REQUEST['username'])
        $username = $_REQUEST['username'];
    if ($_REQUEST['useruid'])
        $useruid = $_REQUEST['useruid'];

    $monthArr = monthList($start_time/1000,$end_time/1000);
    $server=$currentServer;
    $server = substr($server,1);
    $dbArray = array(
        'date' => array('name' => '时间',),
        'userid' => array('name' => '玩家uid',),
        'type' => array('name' => '类型(1为点击，2为使用祝福)',),
        'num' => array('name' => '次数',),
    );
    if ($type) {
        $_pam = '';
        if ($username) {
            $account_list = cobar_getValidAccountList('name', $username);
            $uid = $account_list[0]['gameUid'];
            $_pam = $uid;
        } else {
            $_pam = $useruid;
        }

        $count = 0;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;

            $sql_pass = "select userid,type, date, count(*) num from $db_start where server_id=$server and category=11 and userid = '{$_pam}' $time group by date,type";

            if(isset($sql_sum)){
                if($count == 1){
                    $sql_sum = ' ('.$sql_sum.') '.'union all'.' ('.$sql_pass.') ';
                }else{
                    $sql_sum .= 'union all'.' ('.$sql_pass.')';
                }
//			$sql_sum = $sql_sum . " union " . $sql_pass ;
                //有这个union ,则上边sql语句不能加分号 ,order by 放最后 ,分句加括号
            }else{
                $sql_sum = $sql_pass;
                $count++;
            }
        }
        $result = query_infobright($sql_sum);

//        $sql = "select userid,type, date, count(*) num from log_rbi where category=11 and userid = '{$_pam}' $time group by date,type;";
        $result_sum = $result['ret']['data'];

//    $result=json_encode($result,TRUE);
        if (!$result['error'] && $result['ret']['data']) {
            $sum = $result['ret']['data'];
        }else{
            $error_msg = search($result);
            $sum = array();
        }
        $start_time = date('Y-m-d H:i:s',$start_time/1000);
        $end_time = date('Y-m-d H:i:s',$end_time/1000);
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
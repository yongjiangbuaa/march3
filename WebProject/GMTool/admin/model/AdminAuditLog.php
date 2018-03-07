<?php 

//
// 管理员日志
//

class AdminAuditLog
{
	private static  $logger;
    private $basePage;

//    const ACTION_LOGIN = 1;
    const ACTION_CHANGE_USER = 2;
    const ACTION_CHANGE_SYSTEM = 3;

    public function getAllActionDescribe(){
        return array(
//            self::ACTION_LOGIN=>'登陆',
            self::ACTION_CHANGE_USER=>'修改用户属性',
            self::ACTION_CHANGE_SYSTEM=>'修改系统设置',
        );
    }

    public static function getInstance(){
        if(self::$logger === null){
            self::$logger = new self();
        }
        return self::$logger;
    }

    function __construct() {
        $this->basePage = new BasePage();
    }
/*
  `serial_id` int(11) NOT NULL auto_increment,
  `adminname` varchar(50) NOT NULL,
  `target_uid` varchar(50) NOT NULL default '',
  `action_type` tinyint(4) NOT NULL,
  `action_detail` varchar(2000) NOT NULL default '',
  `ip` int(11) NOT NULL default '0',
  `create_time` int(10) NOT NULL default 0,
*/
    public function writeLog($adminid='',$target_uid='',$target_server = '',$action_type='',$action_detail=''){
        if($adminid == '') $adminid = $this->basePage->getAdmin();
        if($target_uid == '') $target_uid = $this->basePage->getAdmin();
        $ip = $this->getUserIp();
        $create_time = time();
        $sql = "insert into ".$this->getTableName().
            " (adminname,target_uid,target_server,action_type,action_detail,ip,create_time)
             values ('{$adminid}','{$target_uid}','{$target_server}','{$action_type}','{$action_detail}','{$ip}','{$create_time}');";
//   1://查询，自备limit
//	 2://修改
//	 3://查询，无limit限制
        return $this->basePage->globalExecute($sql,2);
    }

    public function getLog($where = '',$limit = 0){
        if(!empty($where)) $where = ' and '.$where;
        $type = 3;
        if($limit != 0){
            $type = 1;
            $limit = ' limit '.$limit;
        }
        $sql = 'select * from '.$this->getTableName().' where 1  '.$where.' order by create_time DESC  '.$limit;
        $res = $this->basePage->globalExecute($sql,$type);
        return $res;
    }







    private function getTableName(){
        return 'admin_audit_log';
    }
    public function getUserIp(){
        return $_SERVER["REMOTE_ADDR"];
    }

}
?>
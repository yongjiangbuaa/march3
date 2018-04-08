<?php
/**
 * @Pointcut('protocol|auth')
 */
import ( 'module.services.XAbstractService' );

$host = gethostbyname(gethostname());
if ($host == '10.1.16.211' || $host == '10.173.2.11') {
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 0);//inner test
}elseif ($host == '91-87'){
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 9);//online
}else{
    defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 1);//online test
}

// import ( 'module.persistence.Query' );
class GmService extends XAbstractService {
    protected $slaveDB = true;
    private $p_sql = '';
    /**
     * 服务器配置相关
     * @param XServiceRequest $request
     */
    public function doServer(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        import('service.action.GMServerAction');
        $action = new GMServerAction();
        return $action->execute($request);
    }
    /**
     * MYSQL数据库查询等操作
     * @param XServiceRequest $request
     */
    public function doMysql(XServiceRequest $request){
        $this->__init($request);
        import('service.action.GMMysqlAction');
        $action = new GMMysqlAction();
        return $action->execute($request);
    }
    /**
     * 查询玩家信息
     * @param XServiceRequest $request
     */
    public function doSearch(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        import('service.action.GMSearchAction');
        $action = new GMSearchAction();
        return $action->execute($request);
    }
    /**
     * REDIS访问类
     * @param XServiceRequest $request
     */
    public function doRedis(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        import('service.action.GMRedisAction');
        $action = new GMRedisAction();
        return $action->execute($request);
    }
    /**
     * 修改玩家信息
     * @param XServiceRequest $request
     * @return XServiceResult
     */
    public function doModify(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        import('service.action.GMModifyAction');
        $action = new GMModifyAction();
        return $action->execute($request);
    }
    /**
     * KeyValue数据库查询
     * @param XServiceRequest $request
     */
    public function doKvsearch(XServiceRequest $request){
        $this->__init($request);
        $params = $request->getParameters();
        $params = $params['data']['params'];
        import("persistence.orm.PersistenceSession");
        try {
            if($params['itemName'] == 'UserProfile')
            {
                // 			$class = 'domain.user.' . $params['item_name'];
                $data = UserFactory::singleton()->get($params['uid']);
            }
            else
            {
                $class = 'service.item.' . $params['itemName'];
                import($class);
                $data = $params['itemName']::getWithUID($params['uid']);
            }
        } catch (Exception $e) {
            $data = $e->getMessage();
        }
        return XServiceResult::success($data);
    }
    /**
     * 将本服的每个用户首次支付记录导入指定服，玩家在平台上首次充值记录
     * @param XServiceRequest $request
     * @return XServiceResult
     */
    public function doInsertPayData(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        //获得玩家首次充值时的数据
        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $sql = "select * from qpay group by ownerid order by sendtime asc ";
        $sqlDatas = $mysql->execResultWithoutLimit($sql);

        //数据插入指定服
        import('service.action.DataClass');
        if(xingcloud_get('mysql_host') == 'URLIP'){
            $dbPf = 'local';
        }else{
            if(StatData::$lang == 'cn')
                $dbPf = 'qzone';
            elseif(StatData::$lang == 'en')
                $dbPf = 'facebook';
        }
        import('util.mysql.XMysql');
        import('service.action.CalculateUtil');
        $dataMysql = XMysql::singleton(CalculateUtil::getDBParam($dbPf));
        $batchSql = array();
        $addCount =0;
        foreach ($sqlDatas as $sqlData)
        {
            $batchSql[] = "('". implode("','", $sqlData) . "')";
            $addCount++;
            if($addCount >= 100){
                $dataMysql->execute("REPLACE INTO `plaftormfirstpay` VALUES ".implode(',', $batchSql));
                $batchSql = array();
                $addCount =0;
                if(mysql_error()){
                    $dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
                    if (is_dir($dir) || @mkdir($dir, 0777))
                        file_put_contents($dir.'/insertPayData.log',time().' sql:'.$sql.' err:'.mysql_error()."\n",FILE_APPEND);
                    $data['error'] += 1;
                }
                else {
                    $data['success'] += 1;
                    $data['effect'] += mysql_affected_rows();
                }
            }
        }
        if($batchSql){
            $dataMysql->execute("REPLACE INTO `plaftormfirstpay` VALUES ".implode(',', $batchSql));
            if(mysql_error()){
                $dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
                if (is_dir($dir) || @mkdir($dir, 0777))
                    file_put_contents($dir.'/insertPayData.log',time().' sql:'.$sql.' err:'.mysql_error()."\n",FILE_APPEND);
                $data['error'] += 1;
            }
            else {
                $data['success'] += 1;
                $data['effect'] += mysql_affected_rows();
            }
        }
        return XServiceResult::success($data);
    }
    /**
     * 将本服的每个用户的平台ID和服ID导入指定服，滚服用户记录
     * @param XServiceRequest $request
     * @return XServiceResult
     */
    public function doInsertNewUser(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        //查询玩家平台ID 所在服ID 注册时间
        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $sql = "select count(1) as total from platformprofile";
        $sqlDatas = $mysql->execResultWithoutLimit($sql);
        $totalCount = $sqlDatas[0]['total'];

        //数据插入指定服
        import('service.action.DataClass');
        if(xingcloud_get('mysql_host') == 'URLIP'){
            $dbPf = 'local';
        }else{
            if(StatData::$lang == 'cn')
                $dbPf = 'qzone';
            elseif(StatData::$lang == 'en')
                $dbPf = 'facebook';
        }
        import('util.mysql.XMysql');
        import('service.action.CalculateUtil');
        $dataMysql = XMysql::singleton(CalculateUtil::getDBParam($dbPf));

        $pageLimit = 10000;
        $totalPage = ceil($totalCount/$pageLimit);
        for ($i = 0; $i < $totalPage;$i++){
            $pageStart = $i * $pageLimit;
            $sql = "select pf.platformAddress as platformAddress,SUBSTRING_INDEX(pf.platformAddress,CONCAT('_', SUBSTRING_INDEX(SUBSTRING_INDEX(pf.platformAddress,'@',1),'_',-1), '@'),1) as openid , SUBSTRING_INDEX(SUBSTRING_INDEX(pf.platformAddress,'@',-1),'_',1) as pf , SUBSTRING_INDEX(SUBSTRING_INDEX(pf.platformAddress,'_',-2),'_',1) as lang , SUBSTRING_INDEX(pf.platformAddress,'_',-1) as zoneid , u.uid as userUid, u.registerTime as registerTime from platformprofile pf inner join userprofile u on pf.userUID = u.uid where u.registerTime < 1377224715 order by pf.platformAddress limit $pageStart,$pageLimit";
            $sqlDatas = $mysql->execResultWithoutLimit($sql);

            $batchSql = array();
            $addCount =0;
            foreach ($sqlDatas as $sqlData)
            {
                $platformAddress = $sqlData['platformAddress'];
                $openid = $sqlData['openid'];
                $userUid = $sqlData['userUid'];
                $pf = $sqlData['pf'];
                $lang = $sqlData['lang'];
                $zoneid = $sqlData['zoneid'];
                $timeStamp = $sqlData['registerTime'];
                $batchSql[] = "('$platformAddress','$userUid','$openid','$pf','$lang','$zoneid','$timeStamp')";
                $addCount++;
                if($addCount >= 1000){
                    $sql = "INSERT INTO `platformfirstreg` VALUES ".implode(',', $batchSql);
                    $dataMysql->execute($sql);
                    $batchSql = array();
                    $addCount =0;
                    if(mysql_error()){
                        $dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
                        if (is_dir($dir) || @mkdir($dir, 0777))
                            file_put_contents($dir.'/insertNewUser.log',time().' sql:'.$sql.' err:'.mysql_error()."\n",FILE_APPEND);
                        $data['error'] += 1;
                    }
                    else {
                        $data['success'] += 1;
                        $data['effect'] += mysql_affected_rows();
                    }
                }
            }
            if($batchSql){
                $sql = "INSERT INTO `platformfirstreg` VALUES ".implode(',', $batchSql);
                $dataMysql->execute($sql);
                if(mysql_error()){
                    $dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
                    if (is_dir($dir) || @mkdir($dir, 0777))
                        file_put_contents($dir.'/insertNewUser.log',time().' sql:'.$sql.' err:'.mysql_error()."\n",FILE_APPEND);
                    $data['error'] += 1;
                }
                else {
                    $data['success'] += 1;
                    $data['effect'] += mysql_affected_rows();
                }
            }
        }
        return XServiceResult::success($data);
    }
    public function doModifyUser(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        import('service.user.UserProfile');
        import('service.user.PlatformProfile');
        $info = $request->getParameter('info');
        if($info['gameUserId'])
            $user = UserProfile::getWithUID($info['gameUserId']);
        elseif($info['gameUserName'])
        {
            $user = UserProfile::getWithName($info['gameUserName']);
        }
        else
        {
            $platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
            $user = UserProfile::getWithUID($platformProfile->userUID);
        }
        if(!$user){
            return XServiceResult::clientError("user not exists");
        }
        //修改相关数值
        $params = $request->getParameter('data');
        $modify = array();
        foreach ($params as $key => $param){
            if(substr($key,0,5) != 'value' || $param == null)
                continue;
            $realKey = substr($key, 6 , strlen($key));
            if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param) || in_array($realKey, array('league','buttonIndex','tabIndex','name','platformAddress'))){
                //如果值有变化，写入log
                if($user->{$realKey} != $param){
                    if($realKey == 'platformAddress')
                    {
                        continue;
                        import("service.user.PlatformProfile");
                        $platformProfile = PlatformProfile::getWithUID($user->platformAddress);
                        if(PlatformProfile::getWithUID($param))
                            continue;
                        if($platformProfile){
// 							$platformProfile->platformAddress = $param;
// 							$platformProfile->save();
// 							$modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
// 							$user->set($realKey, $param);
                            $platformProfile->remove();
                        }
                        $newPlatformProfile = new PlatformProfile();
                        $newPlatformProfile->userUID = $user->uid;
                        $newPlatformProfile->platformAddress = $param;
                        $newPlatformProfile->save();
                        $modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
                        $user->set($realKey, $param);
                    }
                    else
                    {
// 						if($realKey == 'name'){
// 							import('util.cache.XCache');
// 							$cache = XCache::singleton();
// 							$cache->setKeyPrefix('IK2');
// 							$cache->delete('USERPROFILENAME_'.md5($user->{$realKey}));
// 						}
                        if($realKey == 'user_gold'){
                            $user->goldOffered = 1;
                            import('service.action.CalculateUtil');
                            CalculateUtil::addGoldLog($user,'GM',1,null,$param - $user->{$realKey},$param);
                        }
                        if($realKey == 'system_gold'){
                            import('service.action.CalculateUtil');
                            CalculateUtil::addGoldLog($user,'GM',2,null,$param - $user->{$realKey},$param);
                        }
                        $modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
                        $user->set($realKey, $param);
                    }
                    if($realKey != 'speakingForbid' && $realKey != 'seize'){
                        $user->onLoadKey = md5($user->uid . microtime(true));
                    }
                }
            }
        }
        $user->save();
        //获得更新后的数据
        if($info['gameUserId'])
            $user = UserProfile::getWithUID($info['gameUserId']);
        elseif($info['gameUserName'])
        {
            $user = UserProfile::getWithName($info['gameUserName']);
        }
        else
        {
            $platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
            $user = UserProfile::getWithUID($platformProfile->userUID);
        }
        return XServiceResult::success(array('user'=>$user,'modify'=>$modify));
    }
    public function doGMGet(XServiceRequest $request){
        $this->slaveDB = false;
        $this->__init($request);
        $info = $request->getParameter('data');
        if($info['gameUserId'])
            $user = UserProfile::getWithUID($info['gameUserId']);
        elseif($info['gameUserName'])
        {
            $user = UserProfile::getWithName($info['gameUserName']);
        }
        else
        {
            $platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
            $user = UserProfile::getWithUID($platformProfile->userUID);
        }
        $platformAddress = $user->platformAddress;
        $address = explode('@', $platformAddress);//11_424_ik2@elex337_tw_1=>11_424_ik2,elex337_tw_1
        $platformUserId = '';
        $app_id = explode('_', $address[0]);//11,424,ik2
        $sig_api_key = $sig_app_id = end($app_id).'@'.$address[1];//ik2@elex337_tw_1
        for ($i=0;$i<count($app_id)-1;$i++)
        {
            if($platformUserId != '')
                $platformUserId .= '_';
            $platformUserId .= $app_id[$i];//11_424
        }
        $sig_auth_key = md5($platformUserId . $sig_app_id . $sig_api_key . 'c5944690dfad012f5a17782bcb1b6cfd');
        return XServiceResult::success(array('user'=>$user,'sig_auth_key'=>$sig_auth_key));
    }
    public function doFlushApc(XServiceRequest $request){
        $this->__init($request);
        apc_clear_cache("user");
        return XServiceResult::success(1);
    }
    public function doTestInviteDB(XServiceRequest $request){
        $this->__init($request);
        import("service.action.QFriendInviteClass");
        $result['connect'] = QFriendInvite::singleton(null)->testDB();
        return XServiceResult::success($result);
    }
    public function doMysqlConfig(XServiceRequest $request){
        XAppConfig::singleton()->_loadFile();
        XAppConfig::singleton()->_loadYAML();
        $data = XAppConfig::singleton()->getConfig();
        return XServiceResult::success($data);
    }
    public function doGetCache(XServiceRequest $request){
        $this->__init($request);
        import('util.cache.XCache');
        $cache = new XCache();
        $params = $request->getParameters();
        $params = $params['data']['params'];
        $itemPos = strpos($params['key'], 'Item');
        if($itemPos !== false){
            $itemName = substr($params['key'], 3, $itemPos-3) . 'Item';
            import('service.item.'.$itemName);
        }
        $data = $cache->get($params['key']);
        return XServiceResult::success($data);
    }
    public function doGetFileLog(XServiceRequest $request){
        $this->__init($request);
        import('util.cache.XCache');
        $cache = new XCache();
        $params = $request->getParameters();
        $params = $params['data']['params'];
        $data = file_get_contents(GAME_LOG_DIR . '/' . $params['key'] . '.log');
        return XServiceResult::success($data);
    }
    public function doDeleteCache(XServiceRequest $request){
        $this->__init($request);
        import('util.cache.XCache');
        $cache = new XCache();
        $params = $request->getParameters();
        $params = $params['data']['params'];
        $data = $cache->delete($params['key']);
        return XServiceResult::success($data);
    }
    public function doServerTime(XServiceRequest $request){
        $this->__init($request);
        return XServiceResult::success(time());
    }
    public function doRepairReport(XServiceRequest $request){
        $this->__init($request);
        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $sql = "select report from drillfightreport";
        $sqlData = $mysql->execResultWithoutLimit($sql);
        $uid = '';
        if($sqlData){
            foreach ($sqlData as $drillfightreportItem){
                $report = json_decode($drillfightreportItem['report'], true);
                $ruid = $report['first']['fightReportId'];
                if($ruid){
                    $uid .= "'{$ruid}',";
                }
                $ruid = $report['minFightPower']['fightReportId'];
                if($ruid){
                    $uid .= "'{$ruid}',";
                }
            }
        }
        if($uid){
            $uid = substr($uid, 0, -1);
// 			$data['sql'] = $sql = "insert into fightreportdrill select * from fightreport where uid in ($uid)";
            $data['sql'] = $sql = "delete from fightreportdrill where uid not in ($uid) limit 10000";//每次只删除1w条防止慢查询
            $res = $mysql->execute($sql);
            if($res)
                $data['result'] = $res;
            else
                $data['result'] = mysql_error();
            $data['effect'] = $mysql->affected_rows();
        }
        return XServiceResult::success($data);
    }
    public function doRepairUnit(XServiceRequest $request){
        $this->__init($request);
        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $sql = "select * from unit where length(armsList) = 231";
// 		$sql = "select * from unit where uid = '1270015077ef5119a2f'";
        $sqlData = $mysql->execResultWithoutLimit($sql);
        import('service.item.UnitItem');
        if($sqlData){
            $unitItems = UnitItem::toObject('UnitItem',$sqlData,true);
            foreach ($unitItems as $unit){
                $unit->unserializeProperty('armsList');
                $newArmsList = $armsList = array();
                foreach ($unit->armsList as $id=>$value){
                    $unitType = substr($id, 0, 2);
                    $unitLevel = $id%100;
                    $armsList[$unitType] = $unitLevel;
                }
                foreach ($armsList as $unitType=>$level){
                    $armsId = $unitType * 1000 + 100 + $level;
                    $armsId .= '';
                    $newArmsList[$armsId] = array('id'=>$armsId);
                }
                $unit->armsList = $newArmsList;
                $unit->save();
            }
        }
        return XServiceResult::success(count($sqlData));
    }
    public function doStatLanding(XServiceRequest $request){
        set_time_limit(0);
        $this->__init($request);
        $params = $request->getParameters();
        $params = $params['data']['params'];
        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $sqlData = array();
        $dir = dirname(dirname(GAME_LOG_DIR));
        $dir = 'D:\server_log';
        $path = $dir.'/'.$params['date'];
        $update = $exist = $addCount = 0;
        $allfiles = scandir($path);
        foreach ($allfiles as $file) {
            if (in_array($file,array('.','..')))
                continue;
            $datePath = $path.'/'.$file;
            if (is_dir($datePath)&&is_readable($datePath)) {
                $datefiles = scandir($datePath);
                foreach ($datefiles as $filename) {
                    if (in_array($filename,array('.','..')))
                        continue;
                    $fullname = $datePath.'/'.$filename;
                    if (is_file($fullname)) {
                        $platformAddress = substr($filename, 0,strrpos($filename,'.'));
                        $fileData = file($fullname);
                        foreach ($fileData as $time)
                        {
                            $temp = $mysql->get('landing', array('platformAddress'=>$platformAddress));
                            if($temp){
                                if((int)$temp[0]['timeStamp'] <= $time){
                                    $exist++;
                                    break;
                                }else{
                                    $mysql->put('landing', array('platformAddress'=>$platformAddress),array('timeStamp'=>$time));
                                    $update++;
                                    break;
                                }
                            }else{
                                $batchSql[] = array('platformAddress'=>$platformAddress,'timeStamp'=>$time);
// 								$mysql->add('landing', array('platformAddress'=>$platformAddress,'timeStamp'=>$time));
                                $addCount++;
                                if(count($batchSql) > 500){
                                    $mysql->addBatch('landing', $batchSql);
                                    $batchSql = array();
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }
        if($batchSql)
            $mysql->addBatch('landing', $batchSql);
        return XServiceResult::success(array('result'=>1,'exist'=>$exist,'update'=>$update,'add'=>$addCount,'file'=>$params['date']));
    }

    //测试数据库和MemCache是否可用
    public function doTestMysqlMemCache()
    {
        $dbAverageTime = 0.02362084;
        $MemCacheAverage = 0.01645398;
        $apcCacheAverage = 0.0003027;
        $onlineUserUpLimit = 1500;

        import('util.mysql.XMysql');
        $mysql = XMysql::singleton();
        $data['db_ip'] = array('status'=>true,'value'=>xingcloud_get("mysql_host"));
        $data['db_name'] = array('status'=>true,'value'=>xingcloud_get("mysql_db"));
        $sql = "SELECT * FROM userprofile limit 1";
        $dbTimeBefore = microtime(true);
        $sqlTest = $mysql->execResult($sql);
        $dbTimeAfter = microtime(true);
        $data['db_user_exist'] = array('status'=>true,'value'=>'');
        $data['db_query_time'] = array('status'=>true,'value'=>'');
        if(!$sqlTest)
        {
            $data['db_user_exist']['status'] = false;
        }
        $data['db_query_time']['value'] = $dbTimeAfter - $dbTimeBefore;
        if($data['db_query_time']['value'] - $dbAverageTime > 0)
            $data['db_query_time']['status'] = false;

        import('util.cache.XCache');
        $cache = XCache::singleton();
        $cache->setKeyPrefix('IK2');

        $data['memcache_connect'] = array('status'=>true,'value'=>'');
        $data['memcache_get_time'] = array('status'=>true,'value'=>'');
        $key = 'Test_MenCache_APCCache';
        $MemCacheTimeBefore = microtime(true);
        $temp = intval($MemCacheTimeBefore);
        $cache->set($key, $temp);
        $res = $cache->get($key);
        $MemCacheTimeAfter = microtime(true);
        if($res != $temp)
        {
            $data['memcache_connect']['status'] = false;
        }
        $data['memcache_get_time']['value'] = $MemCacheTimeAfter - $MemCacheTimeBefore;
        if($data['memcache_get_time']['value'] - $MemCacheAverage > 0)
            $data['memcache_get_time']['status'] = false;

        $data['online_user'] = array('status'=>true,'value'=>'');
        $currentTime = time();
        $min = date("i",$currentTime);
        $min = $min - $min%5;
        $fiveMin = strtotime(date("Y-m-d H:",$currentTime).$min);
        $data['online_user']['value'] = (int)$cache->get('five_'.$fiveMin);
        if($data['online_user']['value'] > $onlineUserUpLimit)
            $data['online_user']['status'] = false;

        import('util.cache.APCCache');
        $apcCacheObj = new APCCache ();
        $apcCacheObj->setPrefix('IK2');

        $data['apccache_connect'] = array('status'=>true,'value'=>'');
        $data['apccache_get_time'] = array('status'=>true,'value'=>'');
        if($apcCacheObj->test()){
            $apcCacheTimeBefore = microtime(true);
            $temp = intval($apcCacheTimeBefore);
            $apcCacheObj->set($key, $temp);
            $res = $apcCacheObj->get($key);
            $apcCacheTimeAfter = microtime(true);
            if($res != $temp)
            {
                $data['apccache_connect']['status'] = false;
            }
            $data['apccache_get_time']['value'] = $apcCacheTimeAfter - $apcCacheTimeBefore;
            if($data['apccache_get_time']['value'] - $apcCacheAverage > 0)
                $data['apccache_get_time']['status'] = false;
        }
        else
            $data['apccache_connect']['status'] = false;

        global $xingcloudRequestStartTime;
        $data['total_time'] = array('status'=>true,'value'=>$MemCacheTimeAfter - $xingcloudRequestStartTime);

        $returnData = array();
        $returnData["game_name"] = 'IK2';
        $returnData["email"] = 'wangzhiyuan@elex-tech.com';
        $returnData["phone"] = '15901036327';
        $returnData['data'] = $data;
        return $returnData;
// 		return XServiceResult::success($data);
    }


    private function __init($request){
        $params = $request->getParameters();
        $config = array(
            'cn'=>array('language'=>'cn',
                'timezone'=>'Asia/Shanghai',
                'encode'=>'utf-8',),
            'tw'=>array('language'=>'tw',
                'timezone'=>'Asia/Shanghai',
                'encode'=>'utf-8',),
            'pt'=>array('language'=>'pt',
                'timezone'=>'Brazil/East',
                'encode'=>'utf-8',),
            'de'=>array('language'=>'de',
                'timezone'=>'Europe/Berlin',
                'encode'=>'utf-8',),
            'tr'=>array('language'=>'tr',
                'timezone'=>'Turkey',
                'encode'=>'utf-8',),
            'es'=>array('language'=>'es',
                'timezone'=>'Mexico/BajaSur',
                'encode'=>'utf-8',),
            'en'=>array('language'=>'en',
                'timezone'=>'CST6CDT',
                'encode'=>'utf-8',),
        );
        import('service.action.DataClass');
        if($params['lang'])
            StatData::$lang = $params['lang'];
        else
            StatData::$lang = 'cn';
        if(isset($config[StatData::$lang]['timezone']) && ini_get('date.timezone') != $config[StatData::$lang]['timezone']){
            ini_set('date.timezone', $config[StatData::$lang]['timezone']);
        }
        //合法性验证
        if(abs($params['check']['time'] - time()) > 360){
// 			import('module.security.XAuthenticationException');
// 			throw new XAuthenticationException("Invalid operation");
        }
        $sign = md5(substr(md5($params['check']['user']),9,16).$params['check']['user']);//99ba012643db55506bac61483e1b38f0
        $sig = $this->generate_password(11,$params['check']['time']);
        $pass = md5($sign.$sig);
        if($pass != $params['check']['pass']){
// 			import('module.security.XAuthenticationException');
// 			throw new XAuthenticationException("Invalid operation");
        }
        if($params['mainDB']){
            $this->slaveDB = false;
        }
        if($params['slaveDB']){
            $this->slaveDB = true;
        }
        $this->p_sql = $params['data']['params']['sql'];
        if($params['data']['params']['type'] == 5){
            $this->slaveDB = false;
        }
// 		if($this->slaveDB){
// 			$this->__setSlaveDB();
// 		}
        if($params['server']){
            $this->setIFDB($params['server'], $params);
        }
        return true;
    }
    private function __setSlaveDB(){
        $appConfig = XRuntime::singleton()->getAppConfig();
        $config = $appConfig->getConfig();
        //热备数据库关联表
        $backUpLink = array(
        );
        $backUpConfig = $backUpLink[$config['mysql_host'].':'.$config['mysql_port']];
        if($backUpConfig){
            import('service.action.DataClass');
            StatData::$dbInfo = array('host'=>$backUpConfig[0],'port'=>$backUpConfig[1],'user'=>$backUpConfig[2],'password'=>$backUpConfig[3]);
// 			$appConfig->setOneConfig('mysql_host', $backUpConfig[0]);
// 			$appConfig->setOneConfig('mysql_port', $backUpConfig[1]);
// 			$appConfig->setOneConfig('mysql_passwd', $backUpConfig[2]);
        }
    }
    private function setIFDB($server,$params=null){
        import('service.action.DataClass');
        if (PRODUCT_SEVER_TYPE == 0) {
            $dbLink = array(
                'global' => array('main'=>array(GLOBAL_DB_SERVER_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME),
                    'slave'=>array(GLOBAL_DB_SLAVE_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME)),
//                's1' => array('main'=>array('10.1.16.211','3306',GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,'cokdb1'),
//                    'slave'=>array('10.1.16.211','3306',GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,'cokdb1')),
//                's2' => array('main'=>array('10.1.16.211','3306',GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,'cokdb2'),
//                    'slave'=>array('10.1.16.211','3306',GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,'cokdb2')),
            );
            $db_list = get_db_list();
            foreach ($db_list as $dbinfo) {
                $serverid = 's'.$dbinfo['db_id'];
                $db = array(
                    'main'=>array($dbinfo['ip_inner'],$dbinfo['port'],GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,$dbinfo['dbname']),
                    'slave'=>array($dbinfo['slave_ip_inner'],$dbinfo['port'],GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,$dbinfo['dbname'])
                );
                $dbLink[$serverid] = $db;
            }
        }
        if (PRODUCT_SEVER_TYPE == 1) {
            $dbLink = array(
                'global' => array('main'=>array(GLOBAL_DB_SERVER_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME),
                    'slave'=>array(GLOBAL_DB_SLAVE_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME)),
                's1' => array('main'=>array('10.43.227.11','3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,'cokdb1'),
                    'slave'=>array('10.154.59.25','3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,'cokdb1')),
            );
        }
        if (PRODUCT_SEVER_TYPE == 9) {
            $dbLink = array(
                'global' => array('main'=>array(GLOBAL_DB_SERVER_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME),
                    'slave'=>array(GLOBAL_DB_SLAVE_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME)),
            );
            $db_list = get_db_list();
            foreach ($db_list as $dbinfo) {
                $serverid = 's'.$dbinfo['db_id'];
                $db = array(
                    'main'=>array($dbinfo['ip_inner'],$dbinfo['port'],GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,$dbinfo['dbname']),
                    'slave'=>array($dbinfo['slave_ip_inner'],$dbinfo['port'],GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD,$dbinfo['dbname'])
                );
                $dbLink[$serverid] = $db;
            }
// 			file_put_contents('/tmp/changdb.log', print_r($dbLink,true)."\n",FILE_APPEND);
        };


        $dbtype = 'main';
        if ($this->slaveDB) {
            $dbtype = 'slave';
        }
        $config = $dbLink[$server][$dbtype];
        $tmpsql = str_replace("\r\n", '', $this->p_sql);
        $tmpsql = str_replace("\n", '', $tmpsql);
        if (strpos($tmpsql, 'fiveonlinedata') === false
            && strpos($tmpsql, 'user_world where pointId = 1') === false){
// 			file_put_contents('/tmp/testslavemain2.log', "$server $dbtype $tmpsql ".json_encode($config)."\n", FILE_APPEND);
        }
        if($config){
            StatData::$dbInfo = array('host'=>$config[0],'port'=>$config[1],'user'=>$config[2],'password'=>$config[3],'database'=>$config[4]);
        }
        if (PRODUCT_SEVER_TYPE == 0) {
            $redisLink = array(
                'global'=> array('10.1.16.211', '6379'),
//                's1' => array('10.1.16.211',	'6379'),
            );
            $server_list = get_server_list();
            foreach ($server_list as $record) {
                $id = $record['svr_id'];
                if ($id == 0) {
                    continue;
                }
                $redisLink['s'.$id] = array($record['ip_inner'], '6379');
            }
        }
        if (PRODUCT_SEVER_TYPE == 1) {
            $redisLink = array(
                'test' => array('10.50.33.249',	'6379'),
            );
        }
        if (PRODUCT_SEVER_TYPE == 9) {
            $redisLink['global'] = array('10.121.248.63', '6379');
            $server_list = get_server_list();
            foreach ($server_list as $record) {
                $id = $record['svr_id'];
                if ($id == 0) {
                    continue;
                }
                $redisLink['s'.$id] = array($record['ip_inner'], '6379');
            }
        }
        $redisConfig = $redisLink[$server];
        if($redisConfig){
            StatData::$redisInfo = $redisConfig;
        }
// 		file_put_contents('/tmp/changeredis.log', $server.' '.print_r($params,true)."\n",FILE_APPEND);

// 		$mongoLink = array(
// 			'localhost' => array('10.1.5.59','3306','root','admin123','sfsdb'),
// 			'test' => array('10.41.163.12','3306','root','DBPWD','sfsdb1'),
// 		);
// 		$mongoConfig = $mongoLink[$server];
// 		if($mongoConfig){
// 			StatData::$mongodbInfo = $mongoConfig;
// 		}
    }
    //根据时间生成的验证码
    private function generate_password( $length = 8 ,$time = 0) {
        if(!$time)
            $time = time();
        //密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';//!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $password = '';
        $circle = strlen($chars);
        //第一个验证码在字符集的位置
        $index = $time%$circle;
        $timeLen = strlen($time);
        for ( $i = 0; $i < $length; $i++ )
        {
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            // $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
            //根据每一个值获得对应的验证码
            $index = ($index + substr($time,$i%$timeLen,1) * 17 + 59%$circle)%$circle;
            $password .= $chars[$index];
        }
        return $password;
    }
}
?>

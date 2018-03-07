<?php
$serverXmlPath = "/data/htdocs/resource/servers.xml";
$serverBakPath = "/data/htdocs/resource/servers.bak";
$serverCreatePath = "/data/htdocs/resource/servers.bak1";
$pf_serverxmlfile = '/data/htdocs/resource/pf_server.xml';//特殊渠道的servers列表

$countryConfigKey = "COUNTRY_OF_CHOOSE_SERVER";
$pfConfigKey = "PF_OF_CHOOSE_SERVER";//正常渠道pf导量


$channelKey = "RefreshResourceXmlChannel";
$displaymin = 1;

function getAllPfServers(){
    global $pf_serverxmlfile;
    if (file_exists($pf_serverxmlfile)) {
        $xml = simplexml_load_file($pf_serverxmlfile);
    }else{
        return;
    }

    $pfServerList = array();
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    $spec = $array['ItemSpec'];
//	if (count($spec) == 1) {
//		$serverList[$spec['@attributes']['id']] = $spec['@attributes'];
//	}else{
//		foreach ($spec as $svr) {
//			$serverList[$svr['@attributes']['id']] = $svr['@attributes'];
//		}
//	}
    $serverList = $spec['@attributes']['list'];
    $serverStrArr = explode(";", $serverList);
    foreach($serverStrArr as $serverStr){
        $serverArr = explode("-", $serverStr);
        if(count($serverArr) == 1){
            $pfServerList[] = $serverArr[0];
        }else if(count($serverArr) == 2){
            for ($i = $serverArr[0]; $i <= $serverArr[1]; $i++) {
                $pfServerList[] = $i;
            }
        }
    }

    return $pfServerList;
}

/***会更改servers.xml的开服时间
 * @param $serverXmlPath
 * @return string
 */
function generte_serversXml($serverXmlPath, $serverCreatePath, $serverBakPath){
    $xml_str = array();
    $server_list = get_server_list();
    if(count($server_list) == 0){
        return "FAIL";
    }

    $modifyServer = array();
    foreach ($server_list as $record){
        $id = $record['svr_id'];

        $serverXml_info = get_serverXml_info($serverXmlPath, $id); //从原始的配置文件中读取
        if(empty($serverXml_info)){//配置文件中没有，按照数据库中的配置
            $openTime = date ( 'Y-n-j G:i:s', $record['open_time']);
//			$ip = $record['ip_pub'];
            $ip = "s".$id.".coq.elexapp.com";
            $port = $record['port'];
            $zone = $record['zone'];
            $inner_ip = $record['ip_inner'];
            $name = "Kingdom #".$id;
            $db_ref = $record['db_ref'];
            $darr = explode('/', $db_ref);
            if(count($darr) != 2){
                return "FAIL";
            }
            $tmp = explode(':', $darr[0]);
            if(count($tmp) != 2){
                return "FAIL";
            }
            $db_ip = $tmp[0];
            $db_name = $darr[1];
            $modifyServer[$id] = $inner_ip;
        }else{
            $openTime = $serverXml_info['open_time'];
            $ip = $serverXml_info['ip'];
            $port = $serverXml_info['port'];
            $zone = $serverXml_info['zone'];
            $inner_ip = $serverXml_info['inner_ip'];
            $name = $serverXml_info['name'];
            $db_ip = $serverXml_info['db_ip'];
            $db_name = $serverXml_info['db_name'];
            if($serverXml_info['test'] == 'true' && $record['is_test'] == 0){//开服
                $modifyServer[$id] = $inner_ip;
            }
        }

        $server_info = array (
            'id' => $id,
            'name' => $name,
            'ip' => $ip,
            'port' => $port,
            'zone' => $zone,
            'open_time' => $openTime,
            'inner_ip' => $inner_ip,
            'db_ip' => $db_ip,
            'db_name' => $db_name,
            'recommend' => $record['is_recommend'] == 1 ? 'true' : 'false',//这几个标志按照数据库中的走
            'hot' => $record['is_hot'] == 1 ? 'true' : 'false',
            'new' => $record['is_new'] == 1 ? 'true' : 'false',
            'test' => $record['is_test'] == 1 ? 'true' : 'false',
        );

        $server_info['recommend'] = 'false';//暂时全是false;
        $server_info['new'] = 'false';//暂时全是false;
        $nodes = array();
        foreach ( $server_info as $key => $value ) {
            $nodes [] = "$key=" . '"' . $value . '"';
        }
        $item_str = str_pad ( ' ', 4 ) . '<ItemSpec ' . implode ( ' ', $nodes ) . '/>';
        $xml_str [] = $item_str;
    }

    $t0 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
    $t1 = '<tns:database xmlns:tns="http://www.iw.com/sns/platform/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    $t2 = '  <Group id="1">';
    file_put_contents ($serverCreatePath, "$t0\n$t1\n$t2\n" );
    foreach ($xml_str as $str) {
        file_put_contents ($serverCreatePath, "$str\n", FILE_APPEND );
    }
    file_put_contents ($serverCreatePath, "  </Group>\n</tns:database>\n", FILE_APPEND );
    system("cp $serverXmlPath $serverBakPath");//备份
    system("mv $serverCreatePath $serverXmlPath");

    return "OK";
}

function get_serverXml_info($serverXmlPath, $serverId){
    $xml = simplexml_load_file($serverXmlPath);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    $spec = $array['Group']['ItemSpec'];
    if (count($spec) == 1) {
        $serverList[$spec['@attributes']['id']] = $spec['@attributes'];
    }else{
        foreach ($spec as $svr) {
            $serverList[$svr['@attributes']['id']] = $svr['@attributes'];
        }
    }

    $serverXml = $serverList[$serverId];
    return $serverXml;
}
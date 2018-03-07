<?php
!defined('IN_ADMIN') && exit('Access Denied');


function generateCDKey( $length = 8 ) {
	$chars = '23456789abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
	$password = '';
	$max_index = strlen($chars) - 1;
	for ( $i = 0; $i < $length; $i++ )
	{
		$password .= $chars[ mt_rand(0, $max_index) ];
	}
	return $password;
}
function geneCDKeyIntoFile($tType,$times,$num,$file,$series){
	for ($i = 0; $i < $num; $i++) {
		$key = generateCDKey(8);
		$key = strtoupper($key);
		$timeSeries=time();
		file_put_contents($file, $key.",$series,$tType,$timeSeries,$times,$regStartTime,$regEndTime,$selectCountry,$blevelMin,$blevelMax,$ulevelMin,$ulevelMax,$powerMin,$powerMax\n", FILE_APPEND);
	}
}

if($_REQUEST['type'] == 'add')
{
	//生成sql
	$params = $_REQUEST;
	$temp = array();
	foreach ($params as $key=>$value){
		if(substr($key,0,6) != 'reward' || $value == null)
			continue;
		$realKey = substr($key,7,strlen($key));
		$temp[$realKey] = $value;
	}
	$reward = '';
	$checkArr = array('general'=>'genNum', 'goods'=>'goodsNum');
	$filterArr = array('genNum', 'goodsNum');
	foreach ($checkArr as $a=>$b){
		if($temp[$a] && !$temp[$b])
			unset($temp[$a]);
		elseif($temp[$b] && !$temp[$a])
			unset($temp[$b]);
	}
	foreach ($temp as $key=>$value){
		if(in_array($key,$filterArr))
			continue;
		if(in_array($key, array_keys($checkArr))) {
			$rewardArray = explode('|', $value);
			$rewardNumArray = explode('|', $temp[$checkArr[$key]]);
			for ($index = 0; $index < count($rewardArray); $index++) {
				if($rewardArray[$index] && $rewardNumArray[$index]) {
					if($reward)
						$reward .= '|';
					$reward .= $key.','.$rewardArray[$index].','.$rewardNumArray[$index];
				}
			}
		}
		else {
			if($reward)
				$reward .= '|';
			$reward .= $key.',0,'.$value;
		}
	}
	
	$beginTime = $params['beginTime']?strtotime($params['beginTime'])*1000:0;
	$endTime = $params['endTime']?strtotime($params['endTime'])*1000:0;
	$regStartTime = $params['regStartTime']?strtotime($params['regStartTime'])*1000:0;
	$regEndTime = $params['regEndTime']?strtotime($params['regEndTime'])*1000:0;
	$selectCountry = trim($params['selectCountry']);
	if (empty($selectCountry)){
		$selectCountry='';
	}
	$blevelMin = $params['blevelMin']?trim($params['blevelMin']):0;
	$blevelMax = $params['blevelMax']?trim($params['blevelMax']):0;
	$ulevelMin = $params['ulevelMin']?trim($params['ulevelMin']):0;
	$ulevelMax = $params['ulevelMax']?trim($params['ulevelMax']):0;
	$powerMin = $params['powerMin']?trim($params['powerMin']):0;
	$powerMax = $params['powerMax']?trim($params['powerMax']):0;
	$payGold = $params['payGold']?trim($params['payGold']):0;
	$deviceLimit = 'true' == $params['deviceLimit'] ? 1 : 0;
	$multiTimes = 'true' == $params['multiTimes'] ? 1 : 0;
	
	$langs = array(
			'en',
			'zh_Hans',
			'zh_Hant',
			'ko',
			'th',
			'de',
			'ru',
			'pt',
			'ja',
	);
	$tType=0;
	$num =$params['keyNum'];
	$times = $params['keyTimes']?$params['keyTimes']:0;
	$specifiedKeyName=$params['specifiedKeyName']?$params['specifiedKeyName']:'';
	$radioValue = $params['times'];
	$id=date('ymdHi',time());
	$datatitle = new stdClass();
	$datacontent = new stdClass();
	foreach ($langs as $lang) {
		$datatitle->$lang = $params['txttitle'.ucfirst($lang)];
		$datacontent->$lang = $params['txtcontent'.ucfirst($lang)];
	}
	$proUser=$_COOKIE['u'];
	$title = addslashes(json_encode($datatitle));
	$contents = addslashes(json_encode($datacontent));
	$sql = "INSERT INTO activation_config (seriesId, title, contents, reward, num, proUser,type,count,beginTime,endTime,regStart,regEnd,country,bLvMin,bLvMax,uLvMin,uLvMax,powerMin,powerMax,payGold,deviceLimit) VALUES ($id, '$title', '$contents', '$reward', $num, '$proUser',$tType,$times,$beginTime,$endTime,$regStartTime,$regEndTime,'$selectCountry',$blevelMin,$blevelMax,$ulevelMin,$ulevelMax,$powerMin,$powerMax,$payGold,$deviceLimit);";
	$result = $page->globalExecute($sql,2);
	//echo $sql."\n";
	$filePath= '/tmp/cdkey_'.date('Ymd').'.csv';
	if($radioValue=='limitTimes'){
		$tType=1;
	}
	if (1 == $multiTimes) {
		$tType = 2;
	}
	if($specifiedKeyName){
		$timeSeries=time();
		file_put_contents($filePath, $specifiedKeyName.",$id,$tType,$timeSeries,$times\n", FILE_APPEND);
	}else {
		geneCDKeyIntoFile($tType,$times,$num,$filePath,$id);
	}
	//上传102时在DATA后面要加LOCAL
	$keySql="LOAD DATA LOCAL INFILE '$filePath'    
			INTO TABLE activation
			FIELDS TERMINATED BY ','
			LINES TERMINATED BY '\n'
			(code, series,type,timeId,count);";
	//print_r($keySql);
	$result = $page->globalExecute($keySql,2);
	unlink($filePath);
}
if($_REQUEST['action'] == 'again')
{
	$seriesNum=$_REQUEST['series'];
	$temp = explode('_', $seriesNum);
	$seriesId=$temp[0];
	//$oldNum=$temp[1];
	$kNum=$_REQUEST['numValue'];
	$tType=$temp[2];
	$times=$temp[3];
	//$tNum= intval($oldNum)+intval($kNum);
	
	$date=date('Ymd',strtotime('20'.$seriesId));
	$filePath='/tmp/cdkey_'.$date.'.csv';
	geneCDKeyIntoFile($tType,$times,$kNum,$filePath,$seriesId);
	$keySql="LOAD DATA LOCAL INFILE '$filePath'
			INTO TABLE activation
			FIELDS TERMINATED BY ','
			LINES TERMINATED BY '\n'
			(code, series,type,timeId,count);";
	$result = $page->globalExecute($keySql,2);
	unlink($filePath);
	$sql = "update activation_config set num=num+$kNum where seriesId= $seriesId";
	$result = $page->globalExecute($sql,2);
}

if($_REQUEST['action'] == 'addCount')
{
	$seriesNum=$_REQUEST['series'];
	$temp = explode('_', $seriesNum);
	$seriesId=$temp[0];
	$addCount=$_REQUEST['numValue'];
	$sql = "update activation set count=count+$addCount,state=0 where series=$seriesId";
	$result = $page->globalExecute($sql,2);
	$sql = "update activation_config set count=count+$addCount where seriesId=$seriesId";
	$result = $page->globalExecute($sql,2);
}

$title = array('8%'=>'激活码','series','user','state','生成时间');
if($_REQUEST['action'] == 'output')
{
	$series=$_REQUEST['series'];
	$filePath='/tmp/CDKey_20'.$series.'.csv';
	$sql = 'select CONCAT_WS(",",timeId,series,code,state,user,type,count) from activation where series='.$series;
	//线上的102
	$cmd = "/usr/bin/mysql -hDEPLOYIP -uroot -pDBPWD cokdb_global --skip-column-names -e '".$sql."' > $filePath";
	//107上的
	//$cmd = "/usr/bin/mysql -hIPIPIP -uroot -padmin123 cokdb_global --skip-column-names -e '".$sql."' > $filePath";
	//72上的
	//$cmd = "/usr/bin/mysql -hURLIP -uroot -padmin123 cokdb_global --skip-column-names -e '".$sql."' > $filePath";
	$re = system($cmd, $retval);
	if($re === false || $retval == 1){
		echo "生成文件失败";
		return ;
	}
	header("Content-type:text/html;charset=utf-8");
	//用以解决中文不能显示出来的问题
	$filePath=iconv("utf-8","gb2312",$filePath);
	//首先要判断给定的文件存在与否
	if(!file_exists($filePath)){
		echo "没有该文件";
		return ;
	}
	$fp=fopen($filePath,"r");
	$file_size=filesize($filePath);
	//下载文件需要用到的头
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length:".$file_size);
	Header("Content-Disposition: attachment; filename=CDKey_20".$series.'.csv');
	$buffer=1024;
	$file_count=0;
	//向浏览器返回数据
	while(!feof($fp) && $file_count<$file_size){
		$file_con=fread($fp,$buffer);
		$file_count+=$buffer;
		echo $file_con;
	}
	fclose($fp);
// 	unlink($filePath);
	exit();
}

if($_REQUEST['action'] == 'delete')
{
	$series=$_REQUEST['series'];
	$sql = "delete from activation_config where seriesId=$series;";
	$result = $page->globalExecute($sql,2);
	$sql = "delete from activation where series=$series;";
	$result = $page->globalExecute($sql,2);
}


$usedSql="select series,count(user) as usedNum from activation where state!=0 group by series;";
$usedResult = $page->globalExecute($usedSql,3,true);
foreach ($usedResult['ret']['data'] as $usedCurRow){
	$usedNum[$usedCurRow['series']]=$usedCurRow['usedNum'];
}

$timesSql="select code,series from activation where type=1;";
$timesResult = $page->globalExecute($timesSql,3);
//import('service.action.DataClass');
$redis = new Redis();
//$redisConfig = StatData::$globalRedisInfo;
//$redis->connect($redisConfig[0],$redisConfig[1]);
$host = gethostbyname(gethostname());
if ($host == 'IPIPIP' || $host == 'IPIPIP') {
	$redis->connect('URLIP',6379);//72的global库
}elseif ($host == 'IPIPIP'){
	$redis->connect('10.142.9.80',6379);
}else {
	$redis->connect('10.120.232.112',6379);
}
$i=0;
foreach($timesResult['ret']['data'] as $row){
	
	$codeArray[$i]=$row['code'];
	$seriesArray[$i]=$row['series'];
	$i++;
}
$timesArray=$redis -> mget($codeArray);
//print_r($codeArray);
//print_r($seriesArray);
//print_r($timesArray);
foreach ($seriesArray as $key=>$seriesValue){
	$timesNum[$seriesValue]=$timesArray[$key];
}
//$timesNum[$row['series']]=$redis -> get($row['code']);
$redis ->close();

$sql = "select seriesId, title, num, proUser,type,count,beginTime,endTime,regStart,regEnd,country,bLvMin,bLvMax,uLvMin,uLvMax,powerMin,powerMax,payGold,deviceLimit from activation_config order by seriesId";
$result = $page->globalExecute($sql,3,true);
$dbData = array();
$i=1;
foreach ($result['ret']['data'] as $curRow){
	$title = json_decode($curRow['title'], true);
	$dbData[$i]['seriesId'] = $curRow['seriesId'];
	$dbData[$i]['seriesDate'] = date('Y-m-d H:i',strtotime('20'.$curRow['seriesId']));
	$dbData[$i]['title'] = ($title===null)?$curRow['title']:"English:".$title['en']."<br> 简体中文:".$title['zh_Hans'];
	$dbData[$i]['num'] = $curRow['num'];
	$dbData[$i]['usedNum']=intval($usedNum[$curRow['seriesId']]);
	$dbData[$i]['timesNum']=intval($timesNum[$curRow['seriesId']]);
	$dbData[$i]['proUser'] = $curRow['proUser'];
	$dbData[$i]['type'] = $curRow['type'];
	$dbData[$i]['count'] = $curRow['count'];
	
	$dbData[$i]['beginTime'] = date("Y-m-d",$curRow['beginTime']/1000);
	$dbData[$i]['endTime'] = date("Y-m-d",$curRow['endTime']/1000);
	$dbData[$i]['regStart'] = date("Y-m-d",$curRow['regStart']/1000);
	$dbData[$i]['regEnd'] = date("Y-m-d",$curRow['coregEndunt']/1000);
	$dbData[$i]['country'] = $curRow['country'];
	$dbData[$i]['bLvMin'] = $curRow['bLvMin'];
	$dbData[$i]['bLvMax'] = $curRow['bLvMax'];
	$dbData[$i]['uLvMin'] = $curRow['uLvMin'];
	$dbData[$i]['uLvMax'] = $curRow['uLvMax'];
	$dbData[$i]['powerMin'] = $curRow['powerMin'];
	$dbData[$i]['powerMax'] = $curRow['powerMax'];
	$dbData[$i]['payGold'] = $curRow['payGold'];
	$dbData[$i]['deviceLimit'] = $curRow['deviceLimit']?'是':'否';
	
	$i++;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
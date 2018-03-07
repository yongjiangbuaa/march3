<?php
!defined('IN_ADMIN') && exit('Access Denied');

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
	$startTime = $params['startTime'];
	$startTime = str_replace(':', '', $startTime);
	$idType =$params['idType'];
	
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
			'es',
			'fr',
	);
	
	$datatitle = new stdClass();
	$datacontent = new stdClass();
	$dataNotification = new stdClass();
	foreach ($langs as $lang) {
		$datatitle->$lang = $params['txttitle'.ucfirst($lang)];
		$datacontent->$lang = $params['txtcontent'.ucfirst($lang)];
		$dataNotification->$lang = $params['notification'.ucfirst($lang)];
	}
	$title = addslashes(json_encode($datatitle));
	$contents = addslashes(json_encode($datacontent));
	$notification = addslashes(json_encode($dataNotification));
	$ondup=" time= $startTime,title='$title',contents='$contents',reward='$reward',notification='$notification'";
	$sql = "INSERT INTO push_137 (id, time, title, contents, reward, notification) VALUES ($idType, $startTime, '$title', '$contents', '$reward','$notification') 
			ON DUPLICATE KEY UPDATE $ondup;";
// 	print_r($sql);
	$result = $page->globalExecute($sql,2);
}
$id= $_REQUEST['idType'];
if(isset($id)){
	$displayid= '选中的ID为:'.$id;
}else{
	$id=1;
	$displayid= '选中的ID为:'.$id;
}
$sql = "select * from push_137 where id=$id";
$searResult =$page->globalExecute($sql,3);
$searResult=$searResult['ret']['data'][0];
$data['id']=$searResult['id'];
$data['time']= date("H:i",strtotime($searResult['time']));
$data['title']=json_decode($searResult['title'],true);
$data['contents']=json_decode($searResult['contents'],true);
$rew=explode('|', $searResult['reward']);
foreach ($rew as $rescource){
	$temp=explode(',', $rescource);
	if(intval($temp[1])==0){
		$data['reward'][$temp[0]]=$temp[2];
	}
	if($temp[0]=='goods')
	{
		$goodsName.=$temp[1].'|';
		$goodsNum.=$temp[2].'|';
	}
}
$data['reward']['goodsName']=rtrim($goodsName,'|');
$data['reward']['goodsNum']=rtrim($goodsNum,'|');
$data['notification']=json_decode($searResult['notification'],true);


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
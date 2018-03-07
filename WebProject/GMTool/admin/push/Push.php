<?php
class Push {

	const DEVICE_TYPE_ANDROID = 'android';
	const DEVICE_TYPE_IOS = 'ios';
	/**
	 * 
	 * @var PushNotificationUtil
	 */
	private $pushInstance;
	public $device = self::DEVICE_TYPE_ANDROID;
	private $parse_app_id = 'T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	private $parse_api_key = 'mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
	public function __construct($parse_app_id='', $parse_api_key=''){
		require_once PUSH_ROOT.'/PushNotificationUtil.php';
		if (!empty($parse_app_id)) {
			$this->parse_app_id = $parse_app_id;
			$this->parse_api_key = $parse_api_key;
		}
		$this->pushInstance = new PushNotificationUtil($this->parse_app_id, $this->parse_api_key);
	}
	
	public function pushToAll($message){
		return $this->execPush('',$message);
	}
	
	public function pushToMultiUser($userArray,$message){
// 		$userArray = array("085745c8-a477-49de-aa22-e38a9b669379","090e8b10-3667-415b-a617-bfac6b90b768");
		if (empty($userArray)) {
			return 'userArray is Empty!';
		}
		$where = array("\$in"=>$userArray);
		return $this->execPush($where,$message);
	}

	public function pushToUser($deviceToken,$message){
		if (empty($deviceToken)) {
			return 'deviceToken is Empty!';
		}
		return $this->execPush($deviceToken,$message);
	}
	
	private function execPush($deviceToken,$message){
		$start = microtime(true);
		$result = $this->pushInstance->sendAlertMessage($message,$this->device,$deviceToken);
		// if (!isset($result['errmsg'])) {
		// 	echo "send push notification success<br />";
		// 	echo "http code:",$result['http_code'],"<br />";
		// 	echo "response:", $result['data'],"<br />";
		// }else{
		// 	echo "send push notification fail. errno:",$result['errno'],
		// 		', errmsg:',$result['errmsg'],
		// 		', http code:',$result['http_code'],
		// 		"<br />";
		// }
		$time = round(microtime(true)-$start, 2);
		$result['time'] = $time;
		// $runmsg = date('Y-m-d H:i:s ').basename(__FILE__). " $reward_key $message_id $lang msg=[$message]. Completed.t2=".$time;
		// echo $runmsg;
		// file_put_contents('/data/log/notify/specific/run_' . date('Ymd') . '.txt', $runmsg . "\n",FILE_APPEND);
		return $result;
	}
}

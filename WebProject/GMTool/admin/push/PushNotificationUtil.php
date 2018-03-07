<?php
class PushNotificationUtil {
	private $parse_app_id;
	private $parse_rest_key;
	private $parse_api_url;
	public function __construct($appid, $api_key) {
		$this->setParseInfo ( $appid, $api_key );
		$this->parse_api_url = 'https://api.parse.com/1/push';
	}
	public function setParseInfo($appid, $api_key) {
		$this->parse_app_id = $appid;
		$this->parse_rest_key = $api_key;
	}
	protected function getResult($errno, $error,$http_code) {
		return array (
				'errno' => $errno,
				'errmsg' => $error,
				'http_code' => $http_code,
		);
	}
	public function sendAlertMessage($message, $device_type, $deviceToken = '', $type='') {
		$url = $this->parse_api_url;
		if (empty ( $message )) {
			return $this->getResult ( - 1, "message empty" );
		}
		$headers = array (
				'X-Parse-Application-Id: ' . $this->parse_app_id,
				'X-Parse-REST-API-Key: ' . $this->parse_rest_key,
				'Content-Type: application/json' 
		);
		$unique_id_name = 'installationId';
		$alertkey = 'cok_push';
		$os = strtolower($device_type);
		if ($os == 'ios') {
			$unique_id_name = 'deviceToken';
			$alertkey = 'alert';
		}
		$where = array('deviceType' => $os);
		if($deviceToken){
			$where[$unique_id_name] = $deviceToken;
		}
		$data = array (
				$alertkey => $message ,
				'action' => 'com.elex.coq.gp.UPDATE_STATUS',
				'cok_push_type' => $type,
		);
		if ($os == 'ios') {
			$data['badge'] = 1;
		}
		
		$postData = array (
				'where' => $where,
				'expiration_interval' => 86300,
				'data' => $data,
		);
		$strMsg = json_encode ( $postData );
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $strMsg );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );//10秒执行时间
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		$result = curl_exec ( $ch );
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errno = curl_errno ( $ch );
		$error = curl_error ( $ch );
		curl_close ( $ch );
		if ($errno != CURLE_OK || $http_code != '200') {
			return $this->getResult ( $errno, $error,$http_code );
		}
		return self::parseResponse($result, $http_code);
	}
	protected static function parseResponse($data,$http_code){
		if(empty($data)){
			return array('data' => '','http_code' => $http_code);
		}
		list($raw_response_headers, $response_body) = explode("\r\n\r\n", $data, 2);
		$response_header_lines = explode("\r\n", $raw_response_headers);
		if($response_header_lines[0] == 'HTTP/1.1 100 Continue'){
			list($raw_response_headers, $response_body) = explode("\r\n\r\n", $response_body, 2);
			$response_header_lines = explode("\r\n", $raw_response_headers);
		}
		array_shift($response_header_lines);
		$headers = array();
		foreach($response_header_lines as $header_line){
			list($header, $value) = explode(': ', $header_line, 2);
			if(isset($headers[$header])){
				$headers[$header] .= "\n" . $value;
			}
			else{
				$headers[$header] = $value;
			}
		}
		$response = array('data' => $response_body, 'http_code' => $http_code, 'headers' => $headers);
		return $response;
	}
}
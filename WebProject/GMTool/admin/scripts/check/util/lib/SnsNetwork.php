<?php
class SnsNetwork
{
	static public function makeRequest($url, $params, $cookie, $method='post')
	{	
		$ch = curl_init();
		$query_string = http_build_query($params);
		if ('GET' == strtoupper($method)){
			curl_setopt($ch, CURLOPT_URL, "$url?$query_string");
		}else{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($ch, CURLOPT_POST, true);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$cookie_string = self::makeCookieString($cookie);
		if (!empty($cookie_string))
		{
			curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
		}
		$result = curl_exec($ch);
		curl_close($ch);
// 		$currTime = time();
// 		$msg = date ( "Y-m-d H:i:s", $currTime ) . '	' . $currTime . '	' . $url . '	' . json_encode ( $params ) . '	' . $result;
// 		$file = date("Ymd",$currTime);
// 		file_put_contents( LOG_DIR."/http.$file.log", $msg . "\n", FILE_APPEND);
		return $result;
	}
		
	static public function makeQueryString($params)
	{
		if (is_string($params))
			return $params;
			
		$query_string = array();
		foreach ($params as $key => $value)
		{
			array_push($query_string, rawurlencode($key) . '=' . rawurlencode($value));
		}
		$query_string = join('&', $query_string);
		return $query_string;
	}

	static public function makeCookieString($params)
	{
		if (is_string($params))
			return $params;
		
		$cookie_string = array();
		foreach ($params as $key => $value)
		{
			array_push($cookie_string, $key . '=' . $value);
		}
		$cookie_string = join('; ', $cookie_string);
		return $cookie_string;
	}	
}
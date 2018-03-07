<?php
class StatusTestCase extends XRESTServiceTestCase{
	protected $initUserUid = FALSE;
	protected $requestMethod = 0;
	
	public function testStatus(){
		$lang = XConfig::singleton()->get('global.language');
		$apiUrl = $this->domain.'/status';
		$apiUrl .= '?'.http_build_query(array('id'=>$this->id,'lang'=>$lang));
		curl_setopt($this->ch, CURLOPT_POST, $this->requestMethod);
		curl_setopt($this->ch, CURLOPT_URL, $apiUrl);
		$result = curl_exec($this->ch);
		if($result{0}!='{'){
			XAssert::fail('result is not a json string');
		}
		$result = json_decode($result, true);
		XAssert::assertArray($result);
		XAssert::assertKeyExists('id', $result);
		XAssert::assertEquals($this->id, intval($result['id']));
		XAssert::assertKeyExists('code', $result);
		XAssert::assertEquals(200, intval($result['code']));
		XAssert::assertKeyExists('data', $result);
		XAssert::assertKeyExists('item.itemSpec', $result['data']);
		XAssert::assertKeyExists('xml', $result['data']['item.itemSpec']);
		XAssert::assertKeyExists('timestamp', $result['data']['item.itemSpec']['xml']);
		XAssert::assertKeyExists('md5', $result['data']['item.itemSpec']['xml']);
	}
	
}
?>
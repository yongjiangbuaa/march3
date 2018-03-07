<?php
/**
TrophitAttributionHandler.php
Version 2.1

Created by TROPHiT on Jun 18 2014.
Copyright 2014 Kankado Cellular Solutions Ltd. All rights reserved.

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Trophit;



/** The TROPHiT class which implements most voucher redemption tasks for an advertiser.
 * See the Integration Assistant in your TROPHiT dashboard on how to use this class.
 * 
 * Generally speaking, all redeemForXXX methods of this class have the following arguments:
 * @param string $method The redemption method, see TrophitAttributionHandler::xxxRedeem constants
 * @param callback $enableContentFunc The callback to receive redeemed vouchers information.
 * It should have the following interface:<br>
 * <pre>
 * function enableContent($app, $device)
 * </pre>
 * It should return an array of successfully-enabled vouchers.
 * Check out the Integration Assistant in your TROPHiT dashboard for more details and examples of
 * the internal format of each argument and result.
 * @param callback $errorFunc The callback to handle errors upon unsuccessful redemption attempts.
 * It should have the following interface:<br>
 * <pre>
 * function onRedemptionError($app, $device)
 * </pre>
 * If null, no callback will be invoked on errors.
 * 
 * 
 * @author TROPHiT
 *
 */
class TrophitAttributionHandler {
	
	
	const QuickRedeem = "Quick";
	const TransactionalRedeem = "Transactional";
	
	protected $accountKey = null;
	protected $accountSecret = null;
	protected $apiUrl = null;
	
	
	
	/** Construct a TROPHiT Attribution Handler object.
	 * This must be the first call in any TROPHiT redemption process.
	 * 
	 * @param string $accountKey Your TROPHiT account key, provided in the Account Settings menu in your TROPHiT dashboard 
	 * @param string $accountSecret Your TROPHiT account secret, provided in the Account Settings menu in your TROPHiT dashboard
	 * @param string $apiUrl The production base URL of the TROPHiT service
	 */
	public function __construct($accountKey, $accountSecret, $apiUrl = null) {
		$this->apiUrl = is_null($apiUrl) ? "https://api.trophit.com" : $apiUrl;
		$this->accountKey = $accountKey;
		$this->accountSecret = $accountSecret;
	}
	
	
	
	/** Perform voucher redemption for the AppsFlyer tracking provider.
	 * See the class description for details on the arguments
	 */
	public function redeemForAppsFlyer($method, $enableContentFunc, $errorFunc = null) {
		return $this->redeemByJsonBody("{$this->apiUrl}/appsflyer/",
			$method, $enableContentFunc, $errorFunc);
	}
	
	
	
	/** Perform voucher redemption for the AD-X tracking provider.
	 * See the class description for details on the arguments
	 */
	public function redeemForAdx($method, $enableContentFunc, $errorFunc = null) {
		return $this->redeemByRequestParams("{$this->apiUrl}/adx/",
				$method, $enableContentFunc, $errorFunc);
	}
	
	
	
	/** Perform voucher redemption for the MAT (by HasOffers) tracking provider.
	 * See the class description for details on the arguments
	 */
	public function redeemForMAT($method, $enableContentFunc, $errorFunc = null) {
		return $this->redeemByRequestParams("{$this->apiUrl}/mat/",
				$method, $enableContentFunc, $errorFunc);
	}
	
	
	
	/** Perform voucher redemption for the Adjust tracking provider.
	 * See the class description for details on the arguments
	 */
	public function redeemForAdjust($method, $enableContentFunc, $errorFunc = null) {
		return $this->redeemByRequestParams("{$this->apiUrl}/adjust/",
			$method, $enableContentFunc, $errorFunc);
	}

	
	
	/** Perform voucher redemption for the Chartboost tracking provider.
	 * See the class description for details on the arguments
	 */
	public function redeemForChartboost($method, $enableContentFunc, $errorFunc = null) {
		return $this->redeemByRequestParams("{$this->apiUrl}/chartboost/",
			$method, $enableContentFunc, $errorFunc);
	}
	
	
	
	/** Perform voucher redemption for an unknown/proprietary tracking provider.
	 * In addition to the standard redemption arguments, it also requires the following arguments:
	 * 
	 * @param string $method See the class description for details on this argument
	 * @param string $refCode The TROPHiT Reference Code (TRC) for a particular voucher
	 * @param numeric $acceptTime The Unix timestamp (UTC) when the user accepted (i.e. clicked on) the voucher offer
	 * @param string $idfa For iOS traffic, the Apple ID for Advertisers of the device being tracked
	 * @param string $gaid For Android traffic, the Googe ID for Advertisers of the device being tracked
	 * @param string $andid For Android traffic, the Android ID (ignored if $gaid is specified)
	 * @param callback $enableContentFunc See the class description for details on this argument
	 * @param callback $errorFunc See the class description for details on this argument
	 * 
	 * Note at least one of idfa/gaid/andid are required
	 */
	public function redeemForGenericTracker($method, $refCode, $acceptTime, $idfa, $gaid, $andid,
			$enableContentFunc, $errorFunc = null) {
				
		// start redemption and handle results:
		$methodName = "trophit{$method}Redeem";
		$params = array(
				$this->getRedeemParamByMethod($method) => $refCode,
				"acceptTime" => $acceptTime,
				"idfa" => $idfa,
				"gaid" => $gaid,
				"andid" => $andid
		);
		$this->$methodName("{$this->apiUrl}/generic/",
			null, null, $enableContentFunc, $errorFunc, $params);
	}

	
	
	protected function redeemByJsonBody($url, $method, $enableContentFunc, $errorFunc = null) {
		// obtain the request JSON body:
		$body = $this->getRequestBody();
		$json = json_decode($body);
		
		// find the trophit reference info or exit if non-TROPHiT traffic
		$found = $this->isTrophitReferenceFound(get_object_vars($json));
		if ($found !== true) return;
		
		// start redemption and handle results:
		$methodName = "trophit{$method}Redeem";
		$this->$methodName($url, "application/json",
				$body, $enableContentFunc, $errorFunc);
	}

	
	
	protected function redeemByRequestParams($url, $method, $enableContentFunc, $errorFunc = null) {
		// find the trophit reference info or exit if non-TROPHiT traffic
		$found = $this->isTrophitReferenceFound($_REQUEST);
		if ($found !== true) return;
		
		// start redemption and handle results:
		$methodName = "trophit{$method}Redeem";
		$this->$methodName($url, null, null, $enableContentFunc, $errorFunc, $_REQUEST);
	}
	
	
	
	/** Perform a quick redemption and enable redeemed content
	 */
	protected function trophitQuickRedeem($apiUrl, $contentType, $body,
			$enableContentFunc, $errorFunc = null, $params = array()) {
		$methodParam = $this->getRedeemParamByMethod(self::QuickRedeem);
		if (!isset($params[$methodParam]))
			$params[$methodParam] = '';
		$result = $this->trophitCallApi($apiUrl, $params, $contentType, $body);
	
		if (isset($result->error)) {
			if (!empty($errorFunc) && is_callable($errorFunc))
				call_user_func($errorFunc, $result->error);
			return;
		}
		
		// grant the user any content based on redeemed vouchers:
		if (isset($result->result)) {
			call_user_func($enableContentFunc, $result->result->app,
					isset($result->result->device) ? $result->result->device : null
			);
		}
	}
	
	
	/** Perform a transactional redemption and enable redeemed content
	 */
	protected function trophitTransactionalRedeem($apiUrl, $contentType, $body,
			$enableContentFunc, $errorFunc = null, $params = array()) {
		$methodParam = $this->getRedeemParamByMethod(self::TransactionalRedeem);
		if (!isset($params[$methodParam]))
			$params[$methodParam] = '';
		$result = $this->trophitCallApi($apiUrl, $params, $contentType, $body);
	
		if (isset($result->error)) {
			if (!empty($errorFunc) && is_callable($errorFunc))
				call_user_func($errorFunc, $result->error);
			return;
		}
		
		// grant the user any content based on redeemed vouchers,
		// collect codes of enabled vouchers:
		if (isset($result->result)) {
			$enabledVoucherCodes = call_user_func($enableContentFunc,
					$result->result->app,
					isset($result->result->device) ? $result->result->device : null
			);
	
			// commit the enabled vouchers:
			if (count($enabledVoucherCodes) > 0) {
				$this->trophitCallApi($apiUrl,
						array("redeem" => $enabledVoucherCodes),
						null, null);
			}
		}
	}
	
	
	
	protected function getRedeemParamByMethod($method) {
		switch ($method) {
			case self::QuickRedeem: return "quick_redeem";
			case self::TransactionalRedeem: return "begin";
			default: return '';
		}
	}
	
	
	
	/** TROPHiT API utility method: sends requests to TROPHiT and parses their response:
	 */
	protected function trophitCallApi($url, $params,
		 $contentType = null, $body = null) {
		return $this->postUrl($url, $params, $contentType, $body);
// 		$opts = array(
// 			'http' => array(
// 				'timeout' => 15,
// 				'header'  => "Authorization: Basic "
// 						. base64_encode("{$this->accountKey}:{$this->accountSecret}")
// 			)
// 		);
// 		if (!empty($body))
// 			$opts['http']['content'] = $body;
// 		if (!empty($contentType))
// 			$opts['http']['header'] .= "\r\nContent-type: $contentType";
// 		$context = stream_context_create($opts);
// 		$delim = stripos($url, '?') === false ? '?' : '&';
// 		foreach ($params as $name => $value) {
// 			if (is_array($value)) {
// 				foreach($value as $v) {
// 					$url .= $delim . $name . '[]=' . urlencode($v);
// 					$delim = '&';
// 				}
// 			} else {
// 				$url .= "$delim$name=" . urlencode($value);
// 			}
// 			$delim = '&';
// 		}
// 		$data = file_get_contents($url, false, $context);
// 		return json_decode($data);
	}
	
	protected function postUrl($url, $params,
		 $contentType = null, $body = null) {
		$headers = array(
				"Authorization: Basic "
						. base64_encode("{$this->accountKey}:{$this->accountSecret}")
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		if (is_array($headers) && count($headers) > 0)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($curlopt_header)
			curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch,CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (is_array($params) && count($params) > 0) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
		}
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$result = curl_exec($ch);
		return json_decode($result);
	}
	

	
	protected function getRequestBody() {
		$body = file_get_contents('php://input');
		return $body;
	}
	
	
	protected function isTrophitReferenceFound($kvp) {
		$found = false;
		foreach ($kvp as $key => $value) {
			$preg = preg_match('/trophit_cmp_[0-9]+/', $value, $matches);
			if ($preg === 1) return true;
		}
		return false;
	}
	
}
?>
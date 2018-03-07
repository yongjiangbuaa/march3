<?php
	include dirname(__FILE__).'/../../include/pay/payment.php';
	
	try {
		$payment = payment::singleton();
 		$payment->insertDataFromLog();
	} catch (Exception $e) {
		echo $e->getMessage();
	}
?>
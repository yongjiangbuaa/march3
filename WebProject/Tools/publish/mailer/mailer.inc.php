<?php
defined('ROOT') || define('ROOT', dirname(__DIR__));

$maillist = array();
$maillist['default'] = array(
		'pengyue@elex-tech.com',
		'hanyanhong@elex-tech.com',
		'wulingjiang@elex-tech.com',
		'zhaohongfu@elex-tech.com',
		'zhengze@elex-tech.com',
		'wangxianwei@elex-tech.com',
);
$maillist['daoliang'] = array(
		'hcg@elex-tech.com',
		'pengyue@elex-tech.com',
		'hanyanhong@elex-tech.com',
		'wulingjiang@elex-tech.com',
		'zhaohongfu@elex-tech.com',
		'zhengze@elex-tech.com',
		'wangxianwei@elex-tech.com',
		'yutao@elex-tech.com',
);

require_once(ROOT.'/mailer/include/class.phpmailer.php');
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;
$mail->SMTPAuth   = true;
$mail->SMTPSecure = "tls";
$mail->CharSet  = "UTF-8";
$mail->Encoding = "base64";
$mail->Username = "";
$mail->Password = "";
$mail->From = "";
$mail->FromName = "";
$mail->IsHTML(true);

function cokcore_mailer_send_mail($mailgroup,$subject,$content){
	global $mail, $maillist;

	if (empty($mailgroup)) {
		$mg = 'default';
	}
	$maillist = $maillist[$mailgroup];
	
	$mail->Subject = $subject;
	foreach ($maillist as $madd) {
		$mail->AddAddress($madd);
	}

	$mail->Body = $content;
	if(!$mail->Send()) {
		sleep(60);
		if(!$mail->Send()) {
			return "Mailer Error: " . $mail->ErrorInfo;
		}
	}
	
	return 'OK';
}
